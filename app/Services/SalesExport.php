<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

/**
 * Builds the broker's commission table as an Excel file, with a small photo
 * of each property so the sheet reads like the page.
 */
class SalesExport
{
    private const PESO = '"₱"#,##0.00';

    private const THUMB_HEIGHT = 40;

    /** @var array<int, string> Temporary thumbnail files, removed after writing. */
    private array $tempFiles = [];

    /** @var array<string, string|null> Thumbnail per photo URL, so a project photo is fetched once. */
    private array $thumbs = [];

    /**
     * @param  Collection<int, Sale>  $sales
     */
    public function download(Collection $sales, string $brokerName, string $filename): BinaryFileResponse
    {
        $path = $this->build($sales, $brokerName);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }

    /**
     * @param  Collection<int, Sale>  $sales
     */
    public function build(Collection $sales, string $brokerName): string
    {
        $book = new Spreadsheet;
        $book->getProperties()->setCreator('EstateFlow')->setTitle('Commission Records');
        $sheet = $book->getActiveSheet()->setTitle('Commission Records');

        $sheet->setCellValue('A1', 'Commission Records');
        $sheet->setCellValue('A2', $brokerName . ' · exported ' . now()->format('F j, Y g:i A'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->getColor()->setRGB('78716C');

        $headers = ['Agent Name', 'Photo', 'Property / Project', 'Date of Sale', 'Selling Price', 'Agent Commission', 'Broker Commission', 'Commission Status', 'Notes'];
        $sheet->fromArray($headers, null, 'A4');
        $sheet->getStyle('A4:I4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A6B79']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(22);

        $row = 5;
        foreach ($sales as $sale) {
            $sheet->fromArray([
                $sale->agent?->name ?? 'Removed agent',
                '',
                $sale->item_name,
                $sale->sold_at?->format('Y-m-d'),
                $sale->selling_price !== null ? (float) $sale->selling_price : null,
                $sale->agent_commission !== null ? (float) $sale->agent_commission : null,
                $sale->broker_commission !== null ? (float) $sale->broker_commission : null,
                $sale->status_label,
                $sale->notes,
            ], null, "A{$row}", true);

            $sheet->getRowDimension($row)->setRowHeight(36);
            if ($thumb = $this->thumbnail($sale->photo_url)) {
                $drawing = new Drawing;
                $drawing->setPath($thumb);
                $drawing->setName($sale->item_name);
                $drawing->setHeight(self::THUMB_HEIGHT);
                $drawing->setCoordinates("B{$row}");
                $drawing->setOffsetX(4);
                $drawing->setOffsetY(4);
                $drawing->setWorksheet($sheet);
            }
            $row++;
        }

        $last = $row - 1;
        if ($sales->isNotEmpty()) {
            $sheet->setCellValue("A{$row}", 'Total (' . $sales->count() . ' ' . ($sales->count() === 1 ? 'sale' : 'sales') . ')');
            foreach (['E', 'F', 'G'] as $column) {
                $sheet->setCellValue("{$column}{$row}", "=SUM({$column}5:{$column}{$last})");
            }
            $sheet->getStyle("A{$row}:I{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:I{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("E5:G{$row}")->getNumberFormat()->setFormatCode(self::PESO);
            $sheet->getStyle("A5:I{$last}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("I5:I{$last}")->getAlignment()->setWrapText(true);
        } else {
            $sheet->setCellValue('A5', 'No sales match these filters.');
        }

        foreach (['A' => 22, 'B' => 10, 'C' => 34, 'D' => 13, 'E' => 17, 'F' => 18, 'G' => 18, 'H' => 18, 'I' => 30] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
        $sheet->freezePane('A5');
        $sheet->setAutoFilter("A4:I{$last}");

        $path = tempnam(sys_get_temp_dir(), 'ef-sales') . '.xlsx';
        (new Xlsx($book))->save($path);
        $book->disconnectWorksheets();

        foreach ($this->tempFiles as $file) {
            @unlink($file);
        }

        return $path;
    }

    /**
     * A small PNG of the photo, or null when it cannot be read. A missing photo
     * leaves the cell empty instead of failing the whole export.
     */
    private function thumbnail(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        if (array_key_exists($url, $this->thumbs)) {
            return $this->thumbs[$url];
        }

        try {
            $bytes = match (true) {
                str_starts_with($url, 'data:') => base64_decode(substr($url, strpos($url, ',') + 1), true) ?: null,
                str_starts_with($url, asset('storage')) => Storage::disk('public')->get(substr($url, strlen(asset('storage')) + 1)),
                default => Http::timeout(8)->get($url)->throw()->body(),
            };
            $image = $bytes ? @imagecreatefromstring($bytes) : false;
        } catch (Throwable) {
            $image = false;
        }

        if (! $image) {
            return $this->thumbs[$url] = null;
        }

        // Twice the shown size, so it stays sharp when Excel zooms.
        $height = self::THUMB_HEIGHT * 2;
        $width = max(1, (int) round(imagesx($image) * $height / max(1, imagesy($image))));
        // Very wide photos are capped in width; -1 keeps the proportions.
        $thumb = imagescale($image, min($width, $height * 2), -1);
        $file = tempnam(sys_get_temp_dir(), 'ef-thumb') . '.png';
        imagepng($thumb, $file);
        $this->tempFiles[] = $file;

        return $this->thumbs[$url] = $file;
    }
}

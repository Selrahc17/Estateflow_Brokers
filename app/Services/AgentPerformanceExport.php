<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * The annual (or quarterly) sales report as Excel: the ranking on one sheet
 * and each agent's month-by-month activity on another.
 */
class AgentPerformanceExport
{
    private const PESO = '"₱"#,##0.00';

    public function download(AgentPerformance $report, string $filename): BinaryFileResponse
    {
        return response()->download($this->build($report), $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }

    public function build(AgentPerformance $report): string
    {
        $ranking = $report->ranking();
        $book = new Spreadsheet;
        $book->getProperties()->setCreator('EstateFlow')->setTitle("Agent Sales Report {$report->label()}");

        $sheet = $book->getActiveSheet()->setTitle('Ranking');
        $this->title($sheet, "Agent Sales Ranking · {$report->label()}", $report);
        $sheet->fromArray(['Rank', 'Agent', 'Sales', 'Sales Value', 'Listings Added', 'Inquiries', 'Viewings', 'Agent Commission'], null, 'A4');
        $this->header($sheet, 'A4:H4');

        $row = 5;
        foreach ($ranking as $line) {
            $sheet->fromArray([
                $line->rank ? "#{$line->rank}" : '—', $line->agent->name, $line->sales, $line->sales_value,
                $line->listings, $line->inquiries, $line->viewings, $line->commission,
            ], null, "A{$row}", true);
            if ($line->rank === 1) {
                $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                ]);
            }
            $row++;
        }
        if ($ranking->isEmpty()) {
            $sheet->setCellValue('A5', 'No agents yet.');
        } else {
            $last = $row - 1;
            $sheet->setCellValue("B{$row}", 'Team total');
            foreach (['C', 'D', 'E', 'F', 'G', 'H'] as $column) {
                $sheet->setCellValue("{$column}{$row}", "=SUM({$column}5:{$column}{$last})");
            }
            $sheet->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:H{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("D5:D{$row}")->getNumberFormat()->setFormatCode(self::PESO);
            $sheet->getStyle("H5:H{$row}")->getNumberFormat()->setFormatCode(self::PESO);
            $sheet->getStyle("A5:A{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        foreach (['A' => 8, 'B' => 28, 'C' => 9, 'D' => 18, 'E' => 15, 'F' => 11, 'G' => 11, 'H' => 18] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
        $sheet->freezePane('A5');

        $this->monthlySheet($book->createSheet(), $report, $ranking);

        $book->setActiveSheetIndex(0);
        $path = tempnam(sys_get_temp_dir(), 'ef-ranking') . '.xlsx';
        (new Xlsx($book))->save($path);
        $book->disconnectWorksheets();

        return $path;
    }

    /** Each agent's sales, inquiries and viewings per month, in ranking order. */
    private function monthlySheet(Worksheet $sheet, AgentPerformance $report, $ranking): void
    {
        $sheet->setTitle('Monthly Activity');
        $this->title($sheet, "Monthly Activity · {$report->label()}", $report);
        $months = $report->months();
        $monthly = $report->monthly();

        $sheet->fromArray(['Agent', 'Activity', ...$months->map(fn ($month) => $month->format('M'))->all(), 'Total'], null, 'A4');
        $lastColumn = Coordinate::stringFromColumnIndex($months->count() + 3);
        $this->header($sheet, "A4:{$lastColumn}4");

        $row = 5;
        foreach ($ranking as $line) {
            foreach (['sales' => 'Sales', 'inquiries' => 'Inquiries', 'viewings' => 'Viewings'] as $metric => $label) {
                $counts = $months->map(fn ($month) => $monthly[$metric][$line->agent->id][$month->format('Y-m')] ?? 0)->all();
                $sheet->fromArray([$metric === 'sales' ? $line->agent->name : '', $label, ...$counts, array_sum($counts)], null, "A{$row}", true);
                $row++;
            }
            $sheet->getStyle('A' . ($row - 1) . ":{$lastColumn}" . ($row - 1))->getBorders()->getBottom()->setBorderStyle(Border::BORDER_HAIR);
        }
        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(11);
        $sheet->freezePane('C5');
    }

    private function title(Worksheet $sheet, string $title, AgentPerformance $report): void
    {
        $sheet->setCellValue('A1', $title);
        $sheet->setCellValue('A2', $report->broker->name . ' · ' . $report->start->format('M j, Y') . ' to ' . $report->end->format('M j, Y') . ' · exported ' . now()->format('M j, Y g:i A'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->getColor()->setRGB('78716C');
    }

    private function header(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A6B79']],
        ]);
    }
}

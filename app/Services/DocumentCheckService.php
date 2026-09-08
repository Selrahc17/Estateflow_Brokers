<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Str;

class DocumentCheckService
{
    public function check(Document $document): array
    {
        $findings = [];
        $score = 100;
        $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
        $type = strtolower((string) $document->type);
        $name = strtolower((string) $document->name);

        if (!in_array($extension, $allowedExtensions, true)) {
            $score -= 40;
            $findings[] = 'The file type is not supported for automated checking.';
        }

        if (!$document->file_size) {
            $score -= 15;
            $findings[] = 'The file size is missing.';
        }

        $keywords = match ($type) {
            'id' => ['id', 'identification', 'passport', 'license', 'driver'],
            'receipt' => ['receipt', 'payment', 'official', 'deposit'],
            'proof_of_income' => ['income', 'salary', 'payslip', 'employment'],
            'contract' => ['contract', 'agreement', 'deed'],
            default => [],
        };

        if ($keywords && !Str::contains($name, $keywords)) {
            $score -= 15;
            $findings[] = 'The document name does not clearly match its selected type.';
        }

        if (!$document->client_id && !$document->broker_id) {
            $score -= 20;
            $findings[] = 'The document is not linked to a client or broker record.';
        }

        if ($score === 100) {
            $findings[] = 'File type, size, selected type, and record link look consistent.';
        }

        return [
            'status' => $score >= 80 ? 'passed' : ($score >= 50 ? 'needs_review' : 'failed'),
            'score' => max(0, $score),
            'findings' => $findings,
        ];
    }

    public function checkAndStore(Document $document): Document
    {
        $result = $this->check($document);

        $document->update([
            'check_status' => $result['status'],
            'check_score' => $result['score'],
            'check_findings' => $result['findings'],
            'checked_at' => now(),
        ]);

        return $document->refresh();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    public const BROKER_COMPLIANCE_TYPES = [
        'prc_id' => ['label' => 'PRC ID', 'hint' => 'Your current PRC professional ID card.'],
        'bond' => ['label' => 'Bond or Indemnity Insurance', 'hint' => 'Proof of your cash or surety bond, or professional indemnity insurance (at least ₱20,000).'],
        'bir_2303' => ['label' => 'BIR Form 2303', 'hint' => 'Your BIR Certificate of Registration.'],
        'amlc_certificate' => ['label' => 'AMLC Certificate', 'hint' => 'Your AMLC Certificate of Registration.'],
        'ptr' => ['label' => 'Privilege Tax Receipt (PTR)', 'hint' => 'Your current PTR.'],
    ];

    /** IDs a broker uploads for one of their agents. */
    public const AGENT_ID_TYPES = [
        'drivers_license' => "Driver's License",
        'physical_id' => 'Physical ID',
        'postal_id' => 'Postal ID',
        'national_id' => 'National ID',
    ];

    /** Types from the old admin upload form, kept so older files still read well. */
    private const OLDER_TYPES = [
        'license' => 'License',
        'id' => 'Government ID',
        'contract' => 'Contract',
        'accreditation' => 'Accreditation',
        'proof_of_income' => 'Proof of Income',
        'photo' => 'Photo',
        'other' => 'Other',
    ];

    public const STATUSES = [
        'pending' => 'Pending review',
        'needs_more' => 'Needs new copy',
        'rejected' => 'Rejected',
        'verified' => 'Verified',
    ];

    /** @return array<string, string> */
    public static function typeLabels(): array
    {
        return array_map(fn (array $type) => $type['label'], self::BROKER_COMPLIANCE_TYPES)
            + self::AGENT_ID_TYPES
            + self::OLDER_TYPES;
    }

    public function typeLabel(): string
    {
        return self::typeLabels()[$this->type] ?? ucfirst(str_replace('_', ' ', (string) $this->type));
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    /** Older rows store "120 KB"; agent IDs store bytes. */
    public function readableSize(): ?string
    {
        if (blank($this->file_size)) {
            return null;
        }

        if (! is_numeric($this->file_size)) {
            return $this->file_size;
        }

        $kb = $this->file_size / 1024;

        return $kb >= 1024 ? round($kb / 1024, 1).' MB' : max(1, round($kb)).' KB';
    }

    protected $fillable = [
        'client_id', 'agent_id', 'broker_id', 'uploaded_by', 'name', 'type', 'file_path',
        'file_size', 'status', 'notes', 'verified_at', 'check_status',
        'check_score', 'check_findings', 'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'check_findings' => 'array',
            'checked_at' => 'datetime',
        ];
    }

    public function broker()
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
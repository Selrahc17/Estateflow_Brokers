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
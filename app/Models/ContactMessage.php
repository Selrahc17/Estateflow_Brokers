<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    /** Subjects offered on the public contact form. */
    public const SUBJECTS = [
        'Property Inquiry',
        'Reservation Question',
        'Document Submission',
        'General Inquiry',
        'Complaint / Feedback',
    ];

    public const STATUSES = [
        'new' => 'New',
        'handled' => 'Handled',
    ];

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
    ];
}

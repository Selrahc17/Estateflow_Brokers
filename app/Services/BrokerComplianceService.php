<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Document;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;

class BrokerComplianceService
{
    public const FILE_RULES = ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'];

    public const RENEWAL_WARNING_DAYS = 60;

    public function detailRules(?int $ignoreUserId = null): array
    {
        return [
            'phone' => ['required', 'string', 'max:20'],
            'license_number' => ['required', 'string', 'max:100', Rule::unique('users', 'license_number')->ignore($ignoreUserId)],
            'prc_id_expires_at' => ['required', 'date', 'after:today'],
            'dhsud_registration_number' => ['required', 'string', 'max:100'],
            'dhsud_registration_expires_at' => ['required', 'date', 'after:today'],
            'tin' => ['required', 'regex:/^\d{3}-?\d{3}-?\d{3}(-?\d{3,5})?$/'],
        ];
    }

    /**
     * The same rules without the phone number, which has its own place in Settings.
     */
    public function licenseRules(int $userId): array
    {
        return Arr::except($this->detailRules($userId), 'phone');
    }

    public function messages(): array
    {
        return [
            'license_number.unique' => 'This PRC license number is already registered.',
            'prc_id_expires_at.after' => 'Your PRC ID has expired. Please renew it first.',
            'dhsud_registration_expires_at.after' => 'Your DHSUD registration has expired. Please renew it first.',
            'tin.regex' => 'Enter a valid TIN, for example 123-456-789 or 123-456-789-000.',
            'prc_id_file.mimes' => 'Upload your PRC ID as a JPG, PNG, or PDF file.',
            'prc_id_file.max' => 'Your PRC ID file must be 10 MB or smaller.',
            'file.mimes' => 'Upload a JPG, PNG, or PDF file.',
            'file.max' => 'The file must be 10 MB or smaller.',
        ];
    }

    public function attributes(): array
    {
        return [
            'phone' => 'phone number',
            'license_number' => 'PRC license number',
            'prc_id_expires_at' => 'PRC ID expiry date',
            'dhsud_registration_number' => 'DHSUD registration number',
            'dhsud_registration_expires_at' => 'DHSUD registration expiry date',
            'tin' => 'TIN',
            'prc_id_file' => 'PRC ID',
        ];
    }

    /**
     * Every required document type with the broker's latest upload, if any.
     *
     * @return array<string, array{label: string, hint: string, document: ?Document}>
     */
    public function checklist(User $broker): array
    {
        $documents = $broker->complianceDocuments()->latest()->get()->unique('type')->keyBy('type');
        $checklist = [];

        foreach (Document::BROKER_COMPLIANCE_TYPES as $type => $details) {
            $checklist[$type] = $details + ['document' => $documents->get($type)];
        }

        return $checklist;
    }

    /**
     * Saves to the private disk. An upload replaces any earlier copy of that type.
     */
    public function storeDocument(User $broker, string $type, UploadedFile $file): Document
    {
        $path = $file->store("broker-compliance/{$broker->id}", 'local');

        if (! $path) {
            throw new RuntimeException('The compliance document could not be saved.');
        }

        $existing = $broker->complianceDocuments()->where('type', $type)->latest()->first();
        $previousPath = $existing?->file_path;

        $document = $existing ?? new Document([
            'broker_id' => $broker->id,
            'type' => $type,
        ]);

        $document->fill([
            'uploaded_by' => $broker->id,
            'name' => Document::BROKER_COMPLIANCE_TYPES[$type]['label'],
            'file_path' => $path,
            'file_size' => round($file->getSize() / 1024).' KB',
            'status' => 'pending',
            'notes' => null,
            'verified_at' => null,
        ])->save();

        if ($previousPath) {
            Storage::disk('local')->delete($previousPath);
        }

        return $document;
    }

    /**
     * What still blocks approval, in words an admin can act on.
     *
     * @return array<int, string>
     */
    public function outstanding(User $broker): array
    {
        $outstanding = [];

        foreach ($this->attributes() as $field => $label) {
            if ($field !== 'prc_id_file' && blank($broker->{$field})) {
                $outstanding[] = "{$label} is missing";
            }
        }

        if ($broker->prc_id_expires_at?->isPast()) {
            $outstanding[] = 'PRC ID has expired';
        }

        if ($broker->dhsud_registration_expires_at?->isPast()) {
            $outstanding[] = 'DHSUD registration has expired';
        }

        foreach ($this->checklist($broker) as $item) {
            if ($item['document']?->status !== 'verified') {
                $outstanding[] = "{$item['label']} is not verified";
            }
        }

        return $outstanding;
    }

    public function renewalState(?CarbonInterface $expiresAt): string
    {
        if (! $expiresAt) {
            return 'missing';
        }

        if ($expiresAt->lt(today())) {
            return 'expired';
        }

        return $expiresAt->lte(today()->addDays(self::RENEWAL_WARNING_DAYS)) ? 'expiring' : 'valid';
    }

    public function renewalLabel(?CarbonInterface $expiresAt): string
    {
        return match ($this->renewalState($expiresAt)) {
            'missing' => 'Not provided',
            'expired' => 'Expired '.$expiresAt->format('M j, Y'),
            'expiring' => $this->daysLeftLabel($expiresAt),
            default => 'Valid until '.$expiresAt->format('M j, Y'),
        };
    }

    /**
     * Everything that has expired, expires soon, or was never recorded, worst first.
     *
     * @return Collection<int, array{name: string, state: string, label: string, link: string}>
     */
    public function renewalsNeedingAttention(User $broker): Collection
    {
        $settings = route('broker.settings.index');
        $items = collect([
            $this->renewalItem('Your PRC ID', $broker->prc_id_expires_at, $settings),
            $this->renewalItem('Your DHSUD registration', $broker->dhsud_registration_expires_at, $settings),
        ]);

        $agents = $broker->agents()->where('is_approved', true)->orderBy('name')->get();

        foreach ($agents as $agent) {
            $items->push($this->renewalItem(
                "{$agent->name}'s accreditation",
                $agent->prc_id_expires_at,
                route('broker.agents.edit', $agent),
            ));
        }

        $order = ['expired' => 0, 'expiring' => 1, 'missing' => 2];

        return $items->reject(fn (array $item) => $item['state'] === 'valid')
            ->sortBy(fn (array $item) => $order[$item['state']])
            ->values();
    }

    public function notifyAdminsOfRenewal(User $broker, string $type): void
    {
        $label = Document::BROKER_COMPLIANCE_TYPES[$type]['label'];

        foreach (User::where('role', 'admin')->pluck('id') as $adminId) {
            AppNotification::create([
                'user_id' => $adminId,
                'type' => 'broker_renewal',
                'title' => 'Broker document needs review',
                'message' => "{$broker->name} uploaded a new {$label}.",
                'link' => route('admin.brokers.show', $broker),
                'is_read' => false,
            ]);
        }
    }

    public function notifyAdminsWhenReadyForReview(User $broker): void
    {
        $readyForReview = collect($this->checklist($broker))
            ->every(fn (array $item) => in_array($item['document']?->status, ['pending', 'verified'], true));

        if (! $readyForReview) {
            return;
        }

        foreach (User::where('role', 'admin')->pluck('id') as $adminId) {
            AppNotification::create([
                'user_id' => $adminId,
                'type' => 'broker_application',
                'title' => 'Broker application ready for review',
                'message' => "{$broker->name} has uploaded all required documents.",
                'link' => route('admin.brokers.show', $broker),
                'is_read' => false,
            ]);
        }
    }

    private function renewalItem(string $name, ?CarbonInterface $expiresAt, string $link): array
    {
        return [
            'name' => $name,
            'state' => $this->renewalState($expiresAt),
            'label' => $this->renewalLabel($expiresAt),
            'link' => $link,
        ];
    }

    private function daysLeftLabel(CarbonInterface $expiresAt): string
    {
        $days = (int) today()->diffInDays($expiresAt);

        return match ($days) {
            0 => 'Expires today',
            1 => 'Expires tomorrow',
            default => "Expires in {$days} days",
        };
    }
}

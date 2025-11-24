<?php

namespace App\Jobs;

use App\Models\Audit\AuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StoreAuditLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $userId,
        public ?string $companyId,
        public string $action,
        public string $auditableType,
        public string $auditableId,
        public ?array $oldValues = null,
        public ?array $newValues = null,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
        public ?string $url = null,
    ) {}

    public function handle(): void
    {
        AuditLog::create([
            'user_id' => $this->userId,
            'company_id' => $this->companyId,
            'action' => $this->action,
            'auditable_type' => $this->auditableType,
            'auditable_id' => $this->auditableId,
            'old_values' => $this->oldValues,
            'new_values' => $this->newValues,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'url' => $this->url,
            'created_at' => now(),
        ]);
    }
}

<?php

namespace App\Models\Traits;

use App\Jobs\StoreAuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            $model->writeAuditLog('created', null, $model->getAuditAttributes());
        });

        static::updated(function ($model) {
            // only changed attributes
            $old = $model->getOriginal();
            $changes = $model->getChanges();

            $filteredOld = $model->filterAuditAttributes($old, $changes);
            $filteredNew = $model->filterAuditAttributes($model->getAttributes(), $changes);

            // if nothing interesting changed, skip
            if (empty($filteredNew)) {
                return;
            }

            $model->writeAuditLog('updated', $filteredOld, $filteredNew);
        });

        static::deleted(function ($model) {
            $model->writeAuditLog('deleted', $model->getAuditAttributes(), null);
        });
    }

    /**
     * Fields that should NOT be audited (override in model if needed).
     */
    protected array $auditHidden = [
        'password',
        'remember_token',
    ];

    /**
     * If you want to only audit specific attributes, override this
     * in the model with a non-empty array.
     */
    protected array $auditOnly = [];

    /**
     * Customize which attributes to audit.
     */
    public function getAuditAttributes(): array
    {
        $attributes = $this->getAttributes();

        // If $auditOnly is not empty, limit to those keys
        if (! empty($this->auditOnly)) {
            $attributes = array_intersect_key($attributes, array_flip($this->auditOnly));
        }

        // Remove hidden fields
        foreach ($this->auditHidden as $field) {
            unset($attributes[$field]);
        }

        return $attributes;
    }

    /**
     * Filter attributes based on $auditOnly and $auditHidden,
     * and keep only keys that changed (for update).
     */
    protected function filterAuditAttributes(array $attributes, array $changes): array
    {
        // keep only keys that are in $changes
        $attributes = array_intersect_key($attributes, $changes);

        if (! empty($this->auditOnly)) {
            $attributes = array_intersect_key($attributes, array_flip($this->auditOnly));
        }

        foreach ($this->auditHidden as $field) {
            unset($attributes[$field]);
        }

        return $attributes;
    }

    /**
     * Core writer that dispatches the queued job.
     */
    public function writeAuditLog(string $action, ?array $oldValues, ?array $newValues): void
    {
        $user = Auth::user();
        $request = Request::instance();

        // Try to infer company_id from model; fallback to currentCompanyId()
        $companyId = null;

        if (isset($this->company_id)) {
            $companyId = $this->company_id;
        } else {
            $companyId = currentCompanyId();
        }

        StoreAuditLog::dispatch(
            userId: $user?->id,
            companyId: $companyId,
            action: $action,
            auditableType: static::class,
            auditableId: (string) $this->getKey(),
            oldValues: $oldValues,
            newValues: $newValues,
            ipAddress: $request?->ip(),
            userAgent: $request?->userAgent(),
            url: $request?->fullUrl(),
        );
    }

    /**
     * Manually log a custom action from controller/service.
     *
     * Example: $user->audit('login'); or $post->audit('published');
     */
    public function audit(string $action, ?array $metadata = null): void
    {
        $this->writeAuditLog(
            $action,
            null,
            $metadata ?? $this->getAuditAttributes(),
        );
    }
}

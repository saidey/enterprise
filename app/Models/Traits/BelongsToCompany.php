<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Ensures tenant-owned models are always scoped to the current company
 * and that inserts automatically set company_id from the active context.
 */
trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            $companyId = currentCompanyId();

            if ($companyId) {
                $builder->where(
                    $builder->getModel()->getTable().'.company_id',
                    $companyId
                );
            } else {
                // Without an active company context, block data access by default.
                $builder->whereRaw('1 = 0');
            }
        });

        static::creating(function (Model $model) {
            if (empty($model->company_id) && ($companyId = currentCompanyId())) {
                $model->company_id = $companyId;
            }
        });
    }

    /**
     * Explicitly scope a query to a given company ID (useful for jobs/CLI).
     */
    public function scopeForCompany(Builder $query, string $companyId): Builder
    {
        return $query->withoutGlobalScope('company')
            ->where($query->getModel()->getTable().'.company_id', $companyId);
    }
}

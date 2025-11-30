<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use UsesOrderedUuid;
    use Auditable;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'price_monthly',
        'price_yearly',
        'max_users',
        'max_operations',
        'included_modules',
        'description',
        'is_active',
        'trial_days',
    ];

    protected $casts = [
        'included_modules' => 'array',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(\App\Modules\Company\Models\CompanySubscription::class);
    }
}

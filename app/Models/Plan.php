<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use UsesOrderedUuid;
    use Auditable;

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
    ];

    protected $casts = [
        'included_modules' => 'array',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Company\CompanySubscription::class);
    }
}

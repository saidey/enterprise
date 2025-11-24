<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use UsesOrderedUuid;
    use Auditable;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_core',
        'is_active',
    ];

    protected $casts = [
        'is_core' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function companies()
    {
        return $this->belongsToMany(\App\Models\Company\Company::class, 'company_modules')
            ->withPivot(['enabled', 'metadata'])
            ->withTimestamps();
    }
}

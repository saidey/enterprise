<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\UsesOrderedUuid;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use Auditable, UsesOrderedUuid;

    protected $fillable = [
        'name',
        'guard_name',
        'role_scope',
    ];

    public function scopePlatform($query)
    {
        return $query->where('role_scope', 'platform');
    }

    public function scopeCompany($query)
    {
        return $query->where('role_scope', 'company');
    }
}

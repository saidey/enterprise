<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use Auditable, UsesOrderedUuid, SoftDeletes;

    protected static function bootSoftDeletes()
    {
        // Avoid boot-time errors during migrations before the column exists
        $instance = new static;
        if (! Schema::hasColumn($instance->getTable(), $instance->getDeletedAtColumn())) {
            return;
        }

        static::addGlobalScope(new SoftDeletingScope);
    }

    protected $fillable = [
        'name',
        'guard_name',
        'role_scope',
        'description',
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

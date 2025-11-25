<?php

namespace App\Modules\HR\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use UsesOrderedUuid;
    use BelongsToCompany;
    use Auditable;

    public const TYPES = ['cluster', 'division', 'department', 'section', 'unit'];

    protected $fillable = [
        'company_id',
        'operation_id',
        'parent_id',
        'name',
        'type',
        'depth',
    ];

    protected $casts = [
        'depth' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}

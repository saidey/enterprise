<?php

namespace App\Modules\Projects\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WbsItem extends Model
{
    use UsesOrderedUuid;
    use BelongsToCompany;
    use Auditable;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'project_id',
        'parent_id',
        'code',
        'title',
        'description',
        'status',
        'progress',
        'notes',
        'start_date',
        'end_date',
        'assigned_user_id',
        'assigned_team',
        'estimated_cost',
        'actual_cost',
        'rollup_estimated_cost',
        'rollup_actual_cost',
        'quantity_total',
        'quantity_completed',
        'qr_code',
        'latitude',
        'longitude',
        'gps_recorded_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'progress' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'rollup_estimated_cost' => 'decimal:2',
        'rollup_actual_cost' => 'decimal:2',
        'quantity_total' => 'decimal:2',
        'quantity_completed' => 'decimal:2',
        'latitude' => 'float',
        'longitude' => 'float',
        'gps_recorded_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('code');
    }
}

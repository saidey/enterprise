<?php

namespace App\Modules\HR\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;

class DutyRoster extends Model
{
    use UsesOrderedUuid;
    use BelongsToCompany;
    use Auditable;

    protected $fillable = [
        'company_id',
        'operation_id',
        'name',
        'code',
        'starts_at',
        'ends_at',
        'off_days',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'off_days' => 'array',
        'is_active' => 'boolean',
    ];

    public function assignments()
    {
        return $this->hasMany(DutyRosterAssignment::class);
    }
}

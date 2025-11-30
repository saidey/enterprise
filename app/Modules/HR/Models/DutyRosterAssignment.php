<?php

namespace App\Modules\HR\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DutyRosterAssignment extends Model
{
    use UsesOrderedUuid;
    use BelongsToCompany;
    use Auditable;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'operation_id',
        'employee_id',
        'duty_roster_id',
        'start_date',
        'end_date',
        'applied_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function roster()
    {
        return $this->belongsTo(DutyRoster::class, 'duty_roster_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

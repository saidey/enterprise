<?php

namespace App\Modules\HR\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;

class HrSetting extends Model
{
    use UsesOrderedUuid;
    use BelongsToCompany;
    use Auditable;

    protected $fillable = [
        'company_id',
        'operation_id',
        'work_week_start',
        'default_off_days',
    ];

    protected $casts = [
        'work_week_start' => 'integer',
        'default_off_days' => 'array',
    ];
}

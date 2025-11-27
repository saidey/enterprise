<?php

namespace App\Modules\Projects\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;

class ProjectPhase extends Model
{
    use UsesOrderedUuid;
    use BelongsToCompany;
    use Auditable;

    protected $fillable = [
        'company_id',
        'project_id',
        'name',
        'sort_order',
        'status',
        'start_date',
        'end_date',
        'percent_complete',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'percent_complete' => 'decimal:2',
    ];
}

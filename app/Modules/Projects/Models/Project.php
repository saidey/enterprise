<?php

namespace App\Modules\Projects\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use UsesOrderedUuid;
    use BelongsToCompany;
    use Auditable;

    protected $fillable = [
        'company_id',
        'island_id',
        'name',
        'code',
        'client_name',
        'site_location',
        'latitude',
        'longitude',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'status',
        'project_manager_id',
        'budget_amount',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expected_end_date' => 'date',
        'actual_end_date' => 'date',
        'budget_amount' => 'decimal:2',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}

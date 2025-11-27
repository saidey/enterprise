<?php

namespace App\Modules\Projects\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;

class ProjectTask extends Model
{
    use UsesOrderedUuid;
    use BelongsToCompany;
    use Auditable;

    protected $fillable = [
        'company_id',
        'project_id',
        'phase_id',
        'assigned_to',
        'name',
        'due_date',
        'priority',
        'status',
        'percent_complete',
        'latitude',
        'longitude',
        'qr_code',
    ];

    protected $casts = [
        'due_date' => 'date',
        'percent_complete' => 'decimal:2',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}

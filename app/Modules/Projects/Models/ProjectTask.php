<?php

namespace App\Modules\Projects\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Projects\Models\WbsItem;
use App\Modules\Projects\Models\ProjectTaskAttachment;
use App\Modules\Projects\Models\ProjectTaskComment;

class ProjectTask extends Model
{
    use UsesOrderedUuid;
    use BelongsToCompany;
    use Auditable;

    protected $fillable = [
        'company_id',
        'project_id',
        'wbs_item_id',
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

    public function wbsItem()
    {
        return $this->belongsTo(WbsItem::class);
    }

    public function attachments()
    {
        return $this->hasMany(ProjectTaskAttachment::class, 'project_task_id');
    }

    public function comments()
    {
        return $this->hasMany(ProjectTaskComment::class, 'project_task_id')->latest();
    }
}

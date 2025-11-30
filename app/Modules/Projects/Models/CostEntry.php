<?php

namespace App\Modules\Projects\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostEntry extends Model
{
    use UsesOrderedUuid;
    use BelongsToCompany;
    use Auditable;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'project_id',
        'category',
        'description',
        'amount',
        'entry_date',
        'supplier',
        'file_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'entry_date' => 'date',
    ];
}

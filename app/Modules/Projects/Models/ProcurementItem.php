<?php

namespace App\Modules\Projects\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;

class ProcurementItem extends Model
{
    use UsesOrderedUuid;
    use BelongsToCompany;
    use Auditable;

    protected $fillable = [
        'company_id',
        'project_id',
        'name',
        'category',
        'description',
        'quantity',
        'unit',
        'supplier',
        'estimated_cost',
        'actual_cost',
        'status',
        'expected_delivery_date',
        'actual_delivery_date',
        'attachments',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'attachments' => 'array',
    ];
}

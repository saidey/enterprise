<?php

namespace App\Modules\Projects\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;

class Island extends Model
{
    use UsesOrderedUuid;
    use BelongsToCompany;
    use Auditable;

    protected $fillable = [
        'company_id',
        'name',
        'atoll',
        'manager_id',
        'notes',
        'kpis',
    ];

    protected $casts = [
        'kpis' => 'array',
    ];
}

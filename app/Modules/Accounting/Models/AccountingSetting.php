<?php

namespace App\Modules\Accounting\Models;

use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;

class AccountingSetting extends Model
{
    use UsesOrderedUuid;
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'currency',
        'fiscal_year_start',
        'decimal_places',
    ];
}

<?php

namespace App\Models\Company;

use App\Models\Traits\UsesOrderedUuid;
use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    use HasFactory;
    use UsesOrderedUuid;
    use Auditable;
    use BelongsToCompany;

    protected $table = 'operations';

    protected $fillable = [
        'company_id',
        'name',
        'code',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

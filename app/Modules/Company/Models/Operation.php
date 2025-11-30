<?php

namespace App\Modules\Company\Models;

use App\Models\Traits\UsesOrderedUuid;
use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Operation extends Model
{
    use HasFactory;
    use UsesOrderedUuid;
    use Auditable;
    use BelongsToCompany;
    use SoftDeletes;

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

<?php

namespace App\Modules\HR\Models;

use App\Modules\Company\Models\Company;
use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\UsesOrderedUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use UsesOrderedUuid;
    use BelongsToCompany;
    use Auditable;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'operation_id',
        'department_id',
        'user_id',
        'employee_id',
        'name',
        'title',
        'status',
        'start_date',
        'email',
    ];

    protected $casts = [
        'start_date' => 'date',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

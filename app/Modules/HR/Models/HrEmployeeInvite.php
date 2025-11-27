<?php

namespace App\Modules\HR\Models;

use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;

class HrEmployeeInvite extends Model
{
    use UsesOrderedUuid;

    protected $fillable = [
        'company_id',
        'employee_id',
        'token',
        'expires_at',
        'accepted_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

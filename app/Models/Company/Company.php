<?php

namespace App\Models\Company;

use App\Models\Traits\UsesOrderedUuid;
use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    use UsesOrderedUuid;
    use Auditable;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'trade_name',
        'business_registration_no',
        'tax_id',
        'email',
        'phone',
        'industry',
        'address_line1',
        'address_line2',
        'island',
        'atoll',
        'postal_code',
        'country',
        'slug',
        'status',
        'subscription_status',
    ];

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'company_user')
            ->withPivot(['role', 'is_owner', 'is_default'])
            ->withTimestamps();
    }

    public function operations()
    {
        return $this->hasMany(Operation::class);
    }
}

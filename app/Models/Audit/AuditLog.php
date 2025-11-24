<?php

namespace App\Models\Audit;

use App\Models\Company\Company;
use App\Models\Company\Operation;
use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\UsesOrderedUuid; // if you're using ordered UUIDs
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use UsesOrderedUuid; // or remove if not using
    use BelongsToCompany;

    protected $table = 'audit_logs';

    public $timestamps = false; // we only use created_at

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function operation()
    {
        return $this->belongsTo(Operation::class);
    }
}

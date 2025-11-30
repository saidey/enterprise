<?php

namespace App\Models\Audit;

use App\Modules\Company\Models\Company;
use App\Modules\Company\Models\Operation;
use App\Models\Traits\BelongsToCompany;
use App\Models\Traits\UsesOrderedUuid; // if you're using ordered UUIDs
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditLog extends Model
{
    use UsesOrderedUuid; // or remove if not using
    use BelongsToCompany;
    use SoftDeletes;

    protected $table = 'audit_logs';

    public $timestamps = false; // we only use created_at

    protected $fillable = [
        'user_id',
        'company_id',
        'operation_id',
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

    protected $appends = [
        'created_at_human',
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

    public function getCreatedAtHumanAttribute(): ?string
    {
        return $this->created_at
            ? Carbon::parse($this->created_at)->timezone(config('app.timezone'))->toDayDateTimeString()
            : null;
    }
}

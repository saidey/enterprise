<?php

namespace App\Models;

use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;

class SubscriptionRenewalSubmission extends Model
{
    use UsesOrderedUuid;

    protected $fillable = [
        'company_id',
        'user_id',
        'quote_id',
        'file_path',
        'original_name',
        'notes',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(\App\Modules\Company\Models\Company::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function quote()
    {
        return $this->belongsTo(\App\Models\Invoice::class, 'quote_id');
    }
}

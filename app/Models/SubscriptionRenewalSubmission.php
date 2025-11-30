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
}

<?php

namespace App\Models;

use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use UsesOrderedUuid;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'subscription_id',
        'plan_id',
        'number',
        'status',
        'gst_percent',
        'amount_ex_gst',
        'gst_amount',
        'total_amount',
        'period_start',
        'period_end',
        'issued_at',
        'due_at',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount_ex_gst' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'issued_at' => 'date',
        'due_at' => 'date',
        'paid_at' => 'date',
    ];

    public function lines()
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function company()
    {
        return $this->belongsTo(\App\Modules\Company\Models\Company::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}

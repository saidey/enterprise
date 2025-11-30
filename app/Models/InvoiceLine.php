<?php

namespace App\Models;

use App\Models\Traits\UsesOrderedUuid;
use Illuminate\Database\Eloquent\Model;

class InvoiceLine extends Model
{
    use UsesOrderedUuid;

    protected $fillable = [
        'invoice_id',
        'description',
        'qty',
        'unit_price',
        'amount_ex_gst',
        'gst_amount',
        'total_amount',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount_ex_gst' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}

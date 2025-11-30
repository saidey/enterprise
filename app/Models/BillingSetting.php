<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingSetting extends Model
{
    protected $fillable = [
        'gst_percent',
        'invoice_prefix',
        'seller_company_id',
        'seller_operation_id',
    ];
}

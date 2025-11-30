<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('billing_settings', 'seller_company_id')) {
                $table->uuid('seller_company_id')->nullable()->after('invoice_prefix');
            }
            if (! Schema::hasColumn('billing_settings', 'seller_operation_id')) {
                $table->uuid('seller_operation_id')->nullable()->after('seller_company_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('billing_settings', function (Blueprint $table) {
            if (Schema::hasColumn('billing_settings', 'seller_company_id')) {
                $table->dropColumn('seller_company_id');
            }
            if (Schema::hasColumn('billing_settings', 'seller_operation_id')) {
                $table->dropColumn('seller_operation_id');
            }
        });
    }
};

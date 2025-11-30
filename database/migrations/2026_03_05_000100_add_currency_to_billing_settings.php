<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('billing_settings', 'currency')) {
                $table->string('currency', 10)->default('MVR')->after('invoice_prefix');
            }
        });
    }

    public function down(): void
    {
        Schema::table('billing_settings', function (Blueprint $table) {
            if (Schema::hasColumn('billing_settings', 'currency')) {
                $table->dropColumn('currency');
            }
        });
    }
};

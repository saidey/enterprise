<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounting_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('accounting_settings', 'gst_percent')) {
                $table->decimal('gst_percent', 5, 2)->default(0)->after('currency');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounting_settings', function (Blueprint $table) {
            if (Schema::hasColumn('accounting_settings', 'gst_percent')) {
                $table->dropColumn('gst_percent');
            }
        });
    }
};

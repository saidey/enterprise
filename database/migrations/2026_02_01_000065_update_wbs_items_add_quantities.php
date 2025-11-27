<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wbs_items', function (Blueprint $table) {
            $table->decimal('quantity_total', 12, 2)->default(0)->after('rollup_actual_cost');
            $table->decimal('quantity_completed', 12, 2)->default(0)->after('quantity_total');
        });
    }

    public function down(): void
    {
        Schema::table('wbs_items', function (Blueprint $table) {
            $table->dropColumn(['quantity_total', 'quantity_completed']);
        });
    }
};

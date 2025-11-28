<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->uuid('wbs_item_id')->nullable()->after('project_id');
            $table->foreign('wbs_item_id')->references('id')->on('wbs_items')->nullOnDelete();
            $table->index(['company_id', 'wbs_item_id']);
        });
    }

    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropForeign(['wbs_item_id']);
            $table->dropIndex('project_tasks_company_id_wbs_item_id_index');
            $table->dropColumn('wbs_item_id');
        });
    }
};

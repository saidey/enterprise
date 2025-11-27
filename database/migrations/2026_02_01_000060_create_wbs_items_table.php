<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wbs_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('project_id');
            $table->uuid('parent_id')->nullable();
            $table->string('code'); // auto-generated hierarchical code e.g., 1.2.3
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('not_started'); // not_started, in_progress, completed
            $table->decimal('progress', 5, 2)->default(0); // auto for parents
            $table->text('notes')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->uuid('assigned_user_id')->nullable();
            $table->string('assigned_team')->nullable();
            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->decimal('rollup_estimated_cost', 15, 2)->default(0);
            $table->decimal('rollup_actual_cost', 15, 2)->default(0);
            $table->string('qr_code')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('gps_recorded_at')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('wbs_items')->cascadeOnDelete();
            $table->foreign('assigned_user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['company_id', 'project_id']);
            $table->unique(['project_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wbs_items');
    }
};

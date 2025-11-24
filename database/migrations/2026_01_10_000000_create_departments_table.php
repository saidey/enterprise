<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('operation_id')->nullable();
            $table->uuid('parent_id')->nullable();

            $table->string('name');
            $table->string('type')->nullable(); // cluster, division, department, section, unit
            $table->unsignedInteger('depth')->default(0);

            $table->timestamps();

            $table->foreign('company_id')
                ->references('id')->on('companies')
                ->onDelete('cascade');

            $table->foreign('operation_id')
                ->references('id')->on('operations')
                ->onDelete('set null');

            $table->foreign('parent_id')
                ->references('id')->on('departments')
                ->onDelete('cascade');

            $table->unique(['company_id', 'parent_id', 'name']); // unique under same parent for a company
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};

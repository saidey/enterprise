<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duty_roster_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('operation_id')->nullable();
            $table->uuid('employee_id');
            $table->uuid('duty_roster_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->uuid('applied_by')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('operation_id')->references('id')->on('operations')->nullOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('duty_roster_id')->references('id')->on('duty_rosters')->cascadeOnDelete();
            $table->foreign('applied_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['company_id', 'employee_id']);
            $table->index(['company_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duty_roster_assignments');
    }
};

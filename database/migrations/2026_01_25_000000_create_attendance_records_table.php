<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->uuid('operation_id')->nullable();
            $table->uuid('employee_id');
            $table->date('attendance_date');
            $table->string('status'); // present, absent, late, leave, wfh, off
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();
            $table->integer('late_minutes')->default(0);
            $table->string('source')->nullable(); // manual, device, api, import
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('operation_id')->references('id')->on('operations')->nullOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();

            $table->unique(['company_id', 'employee_id', 'attendance_date']);
            $table->index(['company_id', 'attendance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};

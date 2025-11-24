<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('company_id');
            $table->uuid('operation_id')->nullable();
            $table->uuid('department_id')->nullable();
            $table->uuid('user_id')->nullable();

            $table->string('employee_id')->nullable(); // custom employee code/id
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('status')->default('active'); // active, probation, on_leave, exited, etc.
            $table->date('start_date')->nullable();
            $table->string('email')->nullable();

            $table->timestamps();

            $table->foreign('company_id')
                ->references('id')->on('companies')
                ->onDelete('cascade');

            $table->foreign('operation_id')
                ->references('id')->on('operations')
                ->onDelete('set null');

            $table->foreign('department_id')
                ->references('id')->on('departments')
                ->onDelete('set null');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('set null');

            $table->unique(['company_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

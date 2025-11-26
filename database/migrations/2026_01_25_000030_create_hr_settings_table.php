<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->unique();
            $table->uuid('operation_id')->nullable();
            $table->unsignedTinyInteger('work_week_start')->default(0); // 0=Sun,1=Mon...
            $table->json('default_off_days')->nullable(); // array of ints 0..6
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('operation_id')->references('id')->on('operations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_settings');
    }
};

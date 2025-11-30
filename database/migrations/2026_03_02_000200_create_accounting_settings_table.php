<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            $table->string('currency', 10)->default('USD');
            $table->string('fiscal_year_start', 5)->default('01-01'); // MM-DD
            $table->string('timezone')->nullable();
            $table->unsignedTinyInteger('decimal_places')->default(2);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->unique('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_settings');
    }
};

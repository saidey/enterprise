<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('trade_name')->nullable()->after('name');
            $table->string('business_registration_no', 100)->nullable()->after('trade_name');
            $table->string('tax_id', 100)->nullable()->after('business_registration_no');
            $table->string('email')->nullable()->after('tax_id');
            $table->string('phone', 50)->nullable()->after('email');
            $table->string('industry')->nullable()->after('phone');
            $table->string('address_line1')->nullable()->after('industry');
            $table->string('address_line2')->nullable()->after('address_line1');
            $table->string('island')->nullable()->after('address_line2');
            $table->string('atoll')->nullable()->after('island');
            $table->string('postal_code', 20)->nullable()->after('atoll');
            $table->string('country')->nullable()->after('postal_code');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'trade_name',
                'business_registration_no',
                'tax_id',
                'email',
                'phone',
                'industry',
                'address_line1',
                'address_line2',
                'island',
                'atoll',
                'postal_code',
                'country',
            ]);
        });
    }
};

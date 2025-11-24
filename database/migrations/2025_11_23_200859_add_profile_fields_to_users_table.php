<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');

            $table->string('avatar_url')->nullable()->after('phone');

            $table->string('national_id', 20)->unique()->nullable()->after('avatar_url');
            $table->date('date_of_birth')->nullable()->after('national_id');
            $table->string('gender', 20)->nullable()->after('date_of_birth');

            $table->string('address_line1')->nullable()->after('gender');
            $table->string('address_line2')->nullable()->after('address_line1');
            $table->string('island')->nullable()->after('address_line2');
            $table->string('atoll')->nullable()->after('island');

            $table->string('emergency_contact_name')->nullable()->after('atoll');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'avatar_url',
                'national_id',
                'date_of_birth',
                'gender',
                'address_line1',
                'address_line2',
                'island',
                'atoll',
                'emergency_contact_name',
                'emergency_contact_phone',
            ]);
        });
    }
};

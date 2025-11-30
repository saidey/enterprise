<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('islands', function (Blueprint $table) {
            if (! Schema::hasColumn('islands', 'location_type')) {
                $table->string('location_type')->default('island')->after('company_id');
            }
            if (! Schema::hasColumn('islands', 'country')) {
                $table->string('country')->nullable()->after('location_type');
            }
            if (! Schema::hasColumn('islands', 'region')) {
                $table->string('region')->nullable()->after('country'); // state / province / atoll
            }
            if (! Schema::hasColumn('islands', 'city')) {
                $table->string('city')->nullable()->after('region'); // city / town
            }
        });
    }

    public function down(): void
    {
        Schema::table('islands', function (Blueprint $table) {
            if (Schema::hasColumn('islands', 'location_type')) {
                $table->dropColumn('location_type');
            }
            if (Schema::hasColumn('islands', 'country')) {
                $table->dropColumn('country');
            }
            if (Schema::hasColumn('islands', 'region')) {
                $table->dropColumn('region');
            }
            if (Schema::hasColumn('islands', 'city')) {
                $table->dropColumn('city');
            }
        });
    }
};

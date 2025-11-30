<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_renewal_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('subscription_renewal_submissions', 'quote_id')) {
                $table->uuid('quote_id')->nullable()->after('user_id');
                $table->foreign('quote_id')->references('id')->on('invoices')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_renewal_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('subscription_renewal_submissions', 'quote_id')) {
                $table->dropForeign(['quote_id']);
                $table->dropColumn('quote_id');
            }
        });
    }
};

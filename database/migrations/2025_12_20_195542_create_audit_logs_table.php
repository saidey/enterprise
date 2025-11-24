<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('user_id')->nullable()->index(); // who did it
            $table->uuid('company_id')->nullable();
            $table->string('action')->index();                     // created, updated, deleted, custom label
            $table->string('auditable_type');             // model class, e.g. App\Models\User
            $table->string('auditable_id');               // model id (uuid or int)

            $table->json('old_values')->nullable();       // before
            $table->json('new_values')->nullable();       // after

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['auditable_type', 'auditable_id']);

            $table->foreign('company_id')
                ->references('id')->on('companies')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};

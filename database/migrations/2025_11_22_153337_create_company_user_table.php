<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_user', function (Blueprint $table) {

            $table->uuid('company_id');
            $table->uuid('user_id');

            $table->string('role')->default('member'); // owner, admin, member…
            $table->boolean('is_owner')->default(false);
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            $table->unique(['company_id', 'user_id']);

            $table->foreign('company_id')
                ->references('id')->on('companies')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_user');
    }
};

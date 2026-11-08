<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'id_poli')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('id_poli')->nullable()->constrained('poli')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep the nullable relationship on rollback to avoid removing existing user data.
    }
};

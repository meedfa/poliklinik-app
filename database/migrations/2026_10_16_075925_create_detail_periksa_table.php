<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $missingIdPeriksa = ! Schema::hasColumn('detail_periksa', 'id_periksa');
        $missingIdObat = ! Schema::hasColumn('detail_periksa', 'id_obat');

        if (! $missingIdPeriksa && ! $missingIdObat) {
            return;
        }

        if (DB::table('detail_periksa')->exists()) {
            throw new RuntimeException('Tabel detail_periksa berisi data tetapi strukturnya belum lengkap. Lengkapi data lama sebelum melanjutkan migration.');
        }

        Schema::table('detail_periksa', function (Blueprint $table) use ($missingIdObat, $missingIdPeriksa) {
            if ($missingIdPeriksa) {
                $table->foreignId('id_periksa')->constrained('periksa')->cascadeOnDelete();
            }

            if ($missingIdObat) {
                $table->foreignId('id_obat')->constrained('obat')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The earlier migration owns this table; do not drop existing data on rollback.
    }
};

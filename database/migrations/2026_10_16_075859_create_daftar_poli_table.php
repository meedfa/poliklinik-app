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
        $missingIdPasien = ! Schema::hasColumn('daftar_poli', 'id_pasien');
        $missingIdJadwal = ! Schema::hasColumn('daftar_poli', 'id_jadwal');
        $missingKeluhan = ! Schema::hasColumn('daftar_poli', 'keluhan');
        $missingNoAntrian = ! Schema::hasColumn('daftar_poli', 'no_antrian');

        if (! $missingIdPasien && ! $missingIdJadwal && ! $missingKeluhan && ! $missingNoAntrian) {
            return;
        }

        if (DB::table('daftar_poli')->exists()) {
            throw new RuntimeException('Tabel daftar_poli berisi data tetapi strukturnya belum lengkap. Lengkapi data lama sebelum melanjutkan migration.');
        }

        Schema::table('daftar_poli', function (Blueprint $table) use ($missingIdJadwal, $missingIdPasien, $missingKeluhan, $missingNoAntrian) {
            if ($missingIdPasien) {
                $table->foreignId('id_pasien')->constrained('users')->cascadeOnDelete();
            }

            if ($missingIdJadwal) {
                $table->foreignId('id_jadwal')->constrained('jadwal_periksa')->cascadeOnDelete();
            }

            if ($missingKeluhan) {
                $table->text('keluhan');
            }

            if ($missingNoAntrian) {
                $table->integer('no_antrian');
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

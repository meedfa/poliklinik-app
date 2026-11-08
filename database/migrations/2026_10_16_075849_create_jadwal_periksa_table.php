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
        $missingIdDokter = ! Schema::hasColumn('jadwal_periksa', 'id_dokter');
        $missingHari = ! Schema::hasColumn('jadwal_periksa', 'hari');
        $missingJamMulai = ! Schema::hasColumn('jadwal_periksa', 'jam_mulai');
        $missingJamSelesai = ! Schema::hasColumn('jadwal_periksa', 'jam_selesai');

        if (! $missingIdDokter && ! $missingHari && ! $missingJamMulai && ! $missingJamSelesai) {
            return;
        }

        if (DB::table('jadwal_periksa')->exists()) {
            throw new RuntimeException('Tabel jadwal_periksa berisi data tetapi strukturnya belum lengkap. Lengkapi data lama sebelum melanjutkan migration.');
        }

        Schema::table('jadwal_periksa', function (Blueprint $table) use ($missingHari, $missingIdDokter, $missingJamMulai, $missingJamSelesai) {
            if ($missingIdDokter) {
                $table->foreignId('id_dokter')->constrained('users')->cascadeOnDelete();
            }

            if ($missingHari) {
                $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']);
            }

            if ($missingJamMulai) {
                $table->time('jam_mulai');
            }

            if ($missingJamSelesai) {
                $table->time('jam_selesai');
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

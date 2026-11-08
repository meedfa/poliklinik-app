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
        $missingIdDaftarPoli = ! Schema::hasColumn('periksa', 'id_daftar_poli');
        $missingTglPeriksa = ! Schema::hasColumn('periksa', 'tgl_periksa');
        $missingCatatan = ! Schema::hasColumn('periksa', 'catatan');
        $missingBiayaPeriksa = ! Schema::hasColumn('periksa', 'biaya_periksa');

        if (! $missingIdDaftarPoli && ! $missingTglPeriksa && ! $missingCatatan && ! $missingBiayaPeriksa) {
            return;
        }

        if (DB::table('periksa')->exists()) {
            throw new RuntimeException('Tabel periksa berisi data tetapi strukturnya belum lengkap. Lengkapi data lama sebelum melanjutkan migration.');
        }

        Schema::table('periksa', function (Blueprint $table) use ($missingBiayaPeriksa, $missingCatatan, $missingIdDaftarPoli, $missingTglPeriksa) {
            if ($missingIdDaftarPoli) {
                $table->foreignId('id_daftar_poli')->constrained('daftar_poli')->cascadeOnDelete();
            }

            if ($missingTglPeriksa) {
                $table->dateTime('tgl_periksa');
            }

            if ($missingCatatan) {
                $table->text('catatan')->nullable();
            }

            if ($missingBiayaPeriksa) {
                $table->integer('biaya_periksa');
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

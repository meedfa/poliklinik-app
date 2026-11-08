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
        $missingNamaPoli = ! Schema::hasColumn('poli', 'nama_poli');
        $missingKeterangan = ! Schema::hasColumn('poli', 'keterangan');

        if (! $missingNamaPoli && ! $missingKeterangan) {
            return;
        }

        if (DB::table('poli')->exists()) {
            throw new RuntimeException('Tabel poli berisi data tetapi strukturnya belum lengkap. Lengkapi data lama sebelum melanjutkan migration.');
        }

        Schema::table('poli', function (Blueprint $table) use ($missingKeterangan, $missingNamaPoli) {
            if ($missingNamaPoli) {
                $table->string('nama_poli', 25);
            }

            if ($missingKeterangan) {
                $table->text('keterangan')->nullable();
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

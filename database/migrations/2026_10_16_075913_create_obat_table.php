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
        $missingNamaObat = ! Schema::hasColumn('obat', 'nama_obat');
        $missingKemasan = ! Schema::hasColumn('obat', 'kemasan');
        $missingHarga = ! Schema::hasColumn('obat', 'harga');

        if (! $missingNamaObat && ! $missingKemasan && ! $missingHarga) {
            return;
        }

        if (DB::table('obat')->exists()) {
            throw new RuntimeException('Tabel obat berisi data tetapi strukturnya belum lengkap. Lengkapi data lama sebelum melanjutkan migration.');
        }

        Schema::table('obat', function (Blueprint $table) use ($missingHarga, $missingKemasan, $missingNamaObat) {
            if ($missingNamaObat) {
                $table->string('nama_obat');
            }

            if ($missingKemasan) {
                $table->string('kemasan', 35)->nullable();
            }

            if ($missingHarga) {
                $table->integer('harga');
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

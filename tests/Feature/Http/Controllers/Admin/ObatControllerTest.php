<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ObatControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_open_medicine_list(): void
    {
        $admin = User::factory()->admin()->create(['nama' => 'Qadira Admin']);
        $obat = Obat::factory()->create(['nama_obat' => 'Paracetamol']);

        $response = $this->actingAs($admin)->get(route('obat.index'));

        $response->assertOk();
        $response->assertViewIs('admin.obat.index');
        $response->assertSeeTextInOrder(['Qadira Admin', 'admin', 'Q']);
        $response->assertSee($obat->nama_obat);
    }

    public function test_admin_can_create_valid_medicine(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('obat.store'), [
            'nama_obat' => 'Amoxicillin',
            'kemasan' => 'Strip',
            'harga' => 25000,
        ]);

        $response->assertRedirect(route('obat.index'));
        $response->assertSessionHas('success', 'Data obat berhasil dibuat.');
        $this->assertDatabaseHas('obat', [
            'nama_obat' => 'Amoxicillin',
            'kemasan' => 'Strip',
            'harga' => 25000,
        ]);
    }

    public function test_required_medicine_input_is_rejected_when_empty(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->from(route('obat.create'))
            ->post(route('obat.store'), []);

        $response->assertRedirect(route('obat.create'));
        $response->assertSessionHasErrors(['nama_obat', 'harga']);
        $this->assertDatabaseEmpty('obat');
    }

    public function test_non_numeric_price_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('obat.store'), [
            'nama_obat' => 'Ibuprofen',
            'kemasan' => 'Strip',
            'harga' => 'dua puluh ribu',
        ]);

        $response->assertSessionHasErrors(['harga']);
        $this->assertDatabaseEmpty('obat');
    }

    public function test_negative_price_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('obat.store'), [
            'nama_obat' => 'Ibuprofen',
            'kemasan' => 'Strip',
            'harga' => -1,
        ]);

        $response->assertSessionHasErrors(['harga']);
        $this->assertDatabaseEmpty('obat');
    }

    public function test_packaging_longer_than_database_limit_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('obat.store'), [
            'nama_obat' => 'Ibuprofen',
            'kemasan' => str_repeat('a', 36),
            'harga' => 18000,
        ]);

        $response->assertSessionHasErrors(['kemasan']);
        $this->assertDatabaseEmpty('obat');
    }

    public function test_admin_can_open_edit_page_with_current_values(): void
    {
        $admin = User::factory()->admin()->create();
        $obat = Obat::factory()->create([
            'nama_obat' => 'Cetirizine',
            'kemasan' => 'Strip',
            'harga' => 12000,
        ]);

        $response = $this->actingAs($admin)->get(route('obat.edit', $obat));

        $response->assertOk();
        $response->assertViewIs('admin.obat.edit');
        $response->assertViewHas('obat', fn (Obat $viewObat): bool => $viewObat->is($obat));
        $response->assertSee('Cetirizine');
    }

    public function test_admin_can_update_medicine_without_creating_another_record(): void
    {
        $admin = User::factory()->admin()->create();
        $obat = Obat::factory()->create([
            'nama_obat' => 'Cetirizine',
            'kemasan' => 'Strip',
            'harga' => 12000,
        ]);

        $response = $this->actingAs($admin)->put(route('obat.update', $obat), [
            'nama_obat' => 'Cetirizine 10 mg',
            'kemasan' => 'Botol',
            'harga' => 15000,
        ]);

        $response->assertRedirect(route('obat.index'));
        $response->assertSessionHas('success', 'Data obat berhasil diperbarui.');
        $this->assertDatabaseCount('obat', 1);
        $this->assertDatabaseHas('obat', [
            'id' => $obat->id,
            'nama_obat' => 'Cetirizine 10 mg',
            'kemasan' => 'Botol',
            'harga' => 15000,
        ]);
    }

    public function test_admin_can_delete_unused_medicine(): void
    {
        $admin = User::factory()->admin()->create();
        $obat = Obat::factory()->create();

        $response = $this->actingAs($admin)->delete(route('obat.destroy', $obat));

        $response->assertRedirect(route('obat.index'));
        $response->assertSessionHas('success', 'Data obat berhasil dihapus.');
        $this->assertModelMissing($obat);
    }

    public function test_medicine_used_in_examination_history_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $obat = Obat::factory()->create();
        $detailPeriksa = $this->createExaminationDetailFor($obat);

        $response = $this->actingAs($admin)->delete(route('obat.destroy', $obat));

        $response->assertRedirect(route('obat.index'));
        $response->assertSessionHas('error', 'Obat tidak dapat dihapus karena sudah digunakan dalam riwayat pemeriksaan.');
        $this->assertModelExists($obat);
        $this->assertModelExists($detailPeriksa);
    }

    public function test_missing_medicine_returns_404_on_edit(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('obat.edit', 999999));

        $response->assertNotFound();
    }

    public function test_show_route_returns_405_method_not_allowed(): void
    {
        $admin = User::factory()->admin()->create();
        $obat = Obat::factory()->create();

        $response = $this->actingAs($admin)->get('/admin/obat/'.$obat->id);

        $response->assertMethodNotAllowed();
    }

    #[DataProvider('nonAdminRoles')]
    public function test_non_admin_user_is_forbidden_from_medicine_crud(string $role): void
    {
        $user = User::factory()->withRole($role)->create();

        $response = $this->actingAs($user)->get(route('obat.index'));

        $response->assertForbidden();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function nonAdminRoles(): array
    {
        return [
            'dokter' => ['dokter'],
            'pasien' => ['pasien'],
        ];
    }

    private function createExaminationDetailFor(Obat $obat): DetailPeriksa
    {
        $poli = Poli::create([
            'nama_poli' => 'Poli Umum',
            'keterangan' => null,
        ]);
        $dokter = User::factory()->withRole('dokter')->create(['id_poli' => $poli->id]);
        $pasien = User::factory()->create();
        $jadwal = JadwalPeriksa::create([
            'id_dokter' => $dokter->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
        ]);
        $daftarPoli = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'keluhan' => 'Demam',
            'no_antrian' => 1,
        ]);
        $periksa = Periksa::create([
            'id_daftar_poli' => $daftarPoli->id,
            'tgl_periksa' => now(),
            'catatan' => null,
            'biaya_periksa' => 150000,
        ]);

        return DetailPeriksa::create([
            'id_periksa' => $periksa->id,
            'id_obat' => $obat->id,
        ]);
    }
}

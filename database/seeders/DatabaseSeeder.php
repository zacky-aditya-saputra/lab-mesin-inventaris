<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Laboratory;
use App\Models\LoanItem;
use App\Models\LoanLog;
use App\Models\LoanRequest;
use App\Models\StudentActivityLog;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        LoanLog::query()->delete();
        LoanItem::query()->delete();
        LoanRequest::query()->delete();
        Tool::withTrashed()->forceDelete();
        Category::query()->delete();
        User::query()->delete();
        Laboratory::query()->delete();
        StudentActivityLog::query()->delete();

        $lpp = Laboratory::create([
            'name' => 'Laboratorium Proses Produksi',
            'code' => 'LPP',
            'slug' => 'laboratorium-proses-produksi',
            'location' => 'Gedung D Lantai 1',
            'description' => 'Laboratorium proses produksi dan pemesinan konvensional.',
            'is_active' => true,
        ]);

        $lke = Laboratory::create([
            'name' => 'Laboratorium Konversi Energi & Otomotif',
            'code' => 'LKE',
            'slug' => 'laboratorium-konversi-energi-otomotif',
            'location' => 'Gedung D Lantai 1',
            'description' => 'Laboratorium konversi energi dan kajian otomotif.',
            'is_active' => true,
        ]);

        $lmat = Laboratory::create([
            'name' => 'Laboratorium Material & Metalurgi',
            'code' => 'LMAT',
            'slug' => 'laboratorium-material-metalurgi',
            'location' => 'Gedung D Lantai 2',
            'description' => 'Laboratorium material, metalurgi, dan pengujian bahan.',
            'is_active' => true,
        ]);

        $lcnc = Laboratory::create([
            'name' => 'Laboratorium CNC, CAD/CAM & Otomasi',
            'code' => 'LCNC',
            'slug' => 'laboratorium-cnc-cadcam-otomasi',
            'location' => 'Gedung D Lantai 2',
            'description' => 'Laboratorium pemesinan CNC, desain CAD/CAM, dan otomasi.',
            'is_active' => true,
        ]);

        $lfab = Laboratory::create([
            'name' => 'Laboratorium Pengelasan & Fabrikasi',
            'code' => 'LFAB',
            'slug' => 'laboratorium-pengelasan-fabrikasi',
            'location' => 'Bengkel Fabrikasi',
            'description' => 'Laboratorium pengelasan dan fabrikasi logam.',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Dr. Ahmad Surya, M.T.',
            'identity_number' => '197505102003121002',
            'email' => 'kepalalab@unwahas.ac.id',
            'password' => bcrypt('password'),
            'phone_number' => '081234567801',
            'role' => 'kepala_lab',
            'identity_type' => 'pegawai',
            'institution' => 'Fakultas Teknik Unwahas',
        ]);

        User::create([
            'name' => 'Rina Wulandari',
            'identity_number' => '199003152015032001',
            'email' => 'laboran.lpp@unwahas.ac.id',
            'password' => bcrypt('password'),
            'phone_number' => '081234567811',
            'role' => 'laboran',
            'laboratory_id' => $lpp->id,
            'identity_type' => 'pegawai',
            'institution' => 'Fakultas Teknik Unwahas',
        ]);

        User::create([
            'name' => 'Bagus Prakoso',
            'identity_number' => '199110202016042002',
            'email' => 'laboran.lke@unwahas.ac.id',
            'password' => bcrypt('password'),
            'phone_number' => '081234567812',
            'role' => 'laboran',
            'laboratory_id' => $lke->id,
            'identity_type' => 'pegawai',
            'institution' => 'Fakultas Teknik Unwahas',
        ]);

        User::create([
            'name' => 'Siti Nurhaliza',
            'identity_number' => '199205122017012003',
            'email' => 'laboran.lmat@unwahas.ac.id',
            'password' => bcrypt('password'),
            'phone_number' => '081234567813',
            'role' => 'laboran',
            'laboratory_id' => $lmat->id,
            'identity_type' => 'pegawai',
            'institution' => 'Fakultas Teknik Unwahas',
        ]);

        User::create([
            'name' => 'Ahmad Fauzi',
            'identity_number' => '199307182018012004',
            'email' => 'laboran.lcnc@unwahas.ac.id',
            'password' => bcrypt('password'),
            'phone_number' => '081234567814',
            'role' => 'laboran',
            'laboratory_id' => $lcnc->id,
            'identity_type' => 'pegawai',
            'institution' => 'Fakultas Teknik Unwahas',
        ]);

        User::create([
            'name' => 'Dewi Lestari',
            'identity_number' => '199401252019022005',
            'email' => 'laboran.lfab@unwahas.ac.id',
            'password' => bcrypt('password'),
            'phone_number' => '081234567815',
            'role' => 'laboran',
            'laboratory_id' => $lfab->id,
            'identity_type' => 'pegawai',
            'institution' => 'Fakultas Teknik Unwahas',
        ]);

        User::create([
            'name' => 'Dimas Prasetyo',
            'identity_number' => '32602100001',
            'email' => 'dimas@std.unwahas.ac.id',
            'password' => bcrypt('password'),
            'phone_number' => '081234567802',
            'role' => 'klien',
            'identity_type' => 'mahasiswa',
            'institution' => 'Teknik Mesin S1',
        ]);

        User::create([
            'name' => 'Nabila Putri',
            'identity_number' => '32602100002',
            'email' => 'nabila@std.unwahas.ac.id',
            'password' => bcrypt('password'),
            'phone_number' => '081234567803',
            'role' => 'klien',
            'identity_type' => 'mahasiswa',
            'institution' => 'Teknik Mesin S1',
        ]);

        $alatUkurPresisi = Category::create([
            'name' => 'Alat Ukur Presisi',
            'code' => 'UK',
            'slug' => 'alat-ukur-presisi',
            'description' => 'Peralatan ukur dimensi dan toleransi mekanik presisi tinggi.',
        ]);

        $mesinPerkakas = Category::create([
            'name' => 'Mesin Perkakas',
            'code' => 'MS',
            'slug' => 'mesin-perkakas',
            'description' => 'Mesin fabrikasi dan pemesinan logam konvensional.',
        ]);

        $elektronika = Category::create([
            'name' => 'Elektronika',
            'code' => 'EL',
            'slug' => 'elektronika',
            'description' => 'Peralatan elektronika, instrumentasi, dan pengukuran listrik.',
        ]);

        $fabrikasiLas = Category::create([
            'name' => 'Fabrikasi & Las',
            'code' => 'LS',
            'slug' => 'fabrikasi-las',
            'description' => 'Peralatan pengelasan busur listrik dan fabrikasi logam.',
        ]);

        $ujiMaterial = Category::create([
            'name' => 'Uji Material',
            'code' => 'MT',
            'slug' => 'uji-material',
            'description' => 'Peralatan pengujian sifat material, kekerasan, dan ketahanan bahan.',
        ]);

        Tool::create([
            'category_id' => $alatUkurPresisi->id,
            'laboratory_id' => $lpp->id,
            'code' => 'LAB-MESIN-001',
            'name' => 'Jangka Sorong Digital Mitutoyo',
            'slug' => 'jangka-sorong-digital-mitutoyo',
            'specification' => 'Ketelitian 0.01mm, rentang ukur 150mm, LCD display digital',
            'image_path' => 'tools/jangka-sorong-digital.jpg',
            'total_stock' => 10,
            'available_stock' => 10,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $alatUkurPresisi->id,
            'laboratory_id' => $lpp->id,
            'code' => 'LAB-MESIN-002',
            'name' => 'Mikrometer Luar (Outside Micrometer)',
            'slug' => 'mikrometer-luar-0-25mm',
            'specification' => 'Ketelitian 0.001mm, rentang 0-25mm, ratchet stop thimble',
            'image_path' => 'tools/mikrometer-luar.jpg',
            'total_stock' => 6,
            'available_stock' => 6,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $mesinPerkakas->id,
            'laboratory_id' => $lpp->id,
            'code' => 'LAB-MESIN-003',
            'name' => 'Mesin Bubut Konvensional Colchester',
            'slug' => 'mesin-bubut-konvensional-colchester',
            'specification' => 'Panjang bed 1500mm, swing over bed 300mm, spindle bore 40mm',
            'image_path' => 'tools/mesin-bubut-konvensional.jpg',
            'total_stock' => 3,
            'available_stock' => 3,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $mesinPerkakas->id,
            'laboratory_id' => $lcnc->id,
            'code' => 'LAB-MESIN-004',
            'name' => 'Mesin Frais Universal',
            'slug' => 'mesin-frais-universal',
            'specification' => 'Meja kerja 1000x250mm, spindle speed 60-1800 rpm, dividing head',
            'image_path' => 'tools/mesin-frais-universal.jpg',
            'total_stock' => 2,
            'available_stock' => 2,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $fabrikasiLas->id,
            'laboratory_id' => $lfab->id,
            'code' => 'LAB-MESIN-005',
            'name' => 'Mesin Las SMAW Inverter 200A',
            'slug' => 'mesin-las-smaw-inverter-200a',
            'specification' => 'Arus output 20-200A, duty cycle 60%, input 220V 1-fasa',
            'image_path' => 'tools/mesin-las-smaw.jpg',
            'total_stock' => 5,
            'available_stock' => 5,
            'is_active' => true,
        ]);
    }
}

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
     * Seed the application's database with 4 primary laboratories,
     * roles, categories, and standardized inventory tools.
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

        // 4 Kelompok Laboratorium Utama Teknik Mesin Unwahas (BAB123.md & PRD v3.0)
        $lpr = Laboratory::create([
            'name' => 'Laboratorium Perancangan',
            'code' => 'LPR',
            'slug' => 'laboratorium-perancangan',
            'location' => 'Gedung D Lantai 2',
            'description' => 'Laboratorium perancangan mesin, gambar teknik, fisika terapan, pneumatik & hidrolik, dan sistem tenaga listrik.',
            'is_active' => true,
        ]);

        $lpp = Laboratory::create([
            'name' => 'Laboratorium Proses Produksi',
            'code' => 'LPP',
            'slug' => 'laboratorium-proses-produksi',
            'location' => 'Gedung D Lantai 1',
            'description' => 'Laboratorium proses produksi, pemesinan konvensional, CNC, fabrikasi las, dan pengecoran logam.',
            'is_active' => true,
        ]);

        $lmat = Laboratory::create([
            'name' => 'Laboratorium Material',
            'code' => 'LMAT',
            'slug' => 'laboratorium-material',
            'location' => 'Gedung D Lantai 2',
            'description' => 'Laboratorium analisa material, metalurgi, perlakuan panas, dan pengujian sifat mekanik bahan teknik.',
            'is_active' => true,
        ]);

        $len = Laboratory::create([
            'name' => 'Laboratorium Energi',
            'code' => 'LEN',
            'slug' => 'laboratorium-energi',
            'location' => 'Gedung D Lantai 1',
            'description' => 'Laboratorium fenomena dasar mesin, konversi energi, refrigerasi, dan uji prestasi mesin.',
            'is_active' => true,
        ]);

        // Kepala Laboratorium (Super Admin)
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

        // 4 Laboran Terisolasi per Laboratorium
        User::create([
            'name' => 'Bagus Prakoso, S.T.',
            'identity_number' => '199110202016042002',
            'email' => 'laboran.lpr@unwahas.ac.id',
            'password' => bcrypt('password'),
            'phone_number' => '081234567811',
            'role' => 'laboran',
            'laboratory_id' => $lpr->id,
            'identity_type' => 'pegawai',
            'institution' => 'Fakultas Teknik Unwahas',
        ]);

        User::create([
            'name' => 'Rina Wulandari, A.Md.',
            'identity_number' => '199003152015032001',
            'email' => 'laboran.lpp@unwahas.ac.id',
            'password' => bcrypt('password'),
            'phone_number' => '081234567812',
            'role' => 'laboran',
            'laboratory_id' => $lpp->id,
            'identity_type' => 'pegawai',
            'institution' => 'Fakultas Teknik Unwahas',
        ]);

        User::create([
            'name' => 'Siti Nurhaliza, S.T.',
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
            'name' => 'Ahmad Fauzi, S.T.',
            'identity_number' => '199307182018012004',
            'email' => 'laboran.len@unwahas.ac.id',
            'password' => bcrypt('password'),
            'phone_number' => '081234567814',
            'role' => 'laboran',
            'laboratory_id' => $len->id,
            'identity_type' => 'pegawai',
            'institution' => 'Fakultas Teknik Unwahas',
        ]);

        // Klien / Mahasiswa
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

        // Kategori Master
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
            'description' => 'Mesin fabrikasi dan pemesinan logam konvensional serta CNC.',
        ]);

        $elektronika = Category::create([
            'name' => 'Elektronika & Instrumentasi',
            'code' => 'EL',
            'slug' => 'elektronika-instrumentasi',
            'description' => 'Peralatan elektronika, instrumentasi, otomasi pneumatik, dan pengukuran listrik.',
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
            'description' => 'Peralatan pengujian sifat mekanik material, kekerasan, dan ketahanan bahan.',
        ]);

        $konversiEnergi = Category::create([
            'name' => 'Konversi Energi',
            'code' => 'EN',
            'slug' => 'konversi-energi',
            'description' => 'Peralatan uji prestasi mesin, dinamometer, dan kajian termal fluida.',
        ]);

        // Sample Tools 4 Laboratorium dengan Standar SKU LABTM{Lab}-{Kategori}-{MMYY}-{NoUrut}
        $mmyy = now()->format('my');

        // 1. Laboratorium Perancangan (LPR) - Lab ID: 1
        Tool::create([
            'category_id' => $elektronika->id,
            'laboratory_id' => $lpr->id,
            'code' => "LABTM{$lpr->id}-EL-{$mmyy}-001",
            'name' => 'Digital Storage Oscilloscope 100MHz',
            'slug' => 'digital-storage-oscilloscope-100mhz',
            'specification' => 'Bandwidth 100MHz, 2 Channel, Real-time sampling rate 1GSa/s, 7 inch WVGA color display.',
            'image_path' => 'tools/oscilloscope.jpg',
            'total_stock' => 4,
            'available_stock' => 4,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $elektronika->id,
            'laboratory_id' => $lpr->id,
            'code' => "LABTM{$lpr->id}-EL-{$mmyy}-002",
            'name' => 'Multimeter Digital Fluke 179 True-RMS',
            'slug' => 'multimeter-digital-fluke-179',
            'specification' => 'Tegangan AC/DC 1000V, arus 10A, frekuensi, kapasitansi, akurasi dasar 0.09%.',
            'image_path' => 'tools/multimeter-digital.jpg',
            'total_stock' => 8,
            'available_stock' => 8,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $elektronika->id,
            'laboratory_id' => $lpr->id,
            'code' => "LABTM{$lpr->id}-EL-{$mmyy}-003",
            'name' => 'Trainer Elektro-Pneumatik Modular',
            'slug' => 'trainer-elektro-pneumatik-modular',
            'specification' => 'Modul silinder kerja ganda, katup solenoid 5/2 dan 3/2, unit regulator tekanan 0-10 bar.',
            'image_path' => null,
            'total_stock' => 2,
            'available_stock' => 2,
            'is_active' => true,
        ]);

        // 2. Laboratorium Proses Produksi (LPP) - Lab ID: 2
        Tool::create([
            'category_id' => $alatUkurPresisi->id,
            'laboratory_id' => $lpp->id,
            'code' => "LABTM{$lpp->id}-UK-{$mmyy}-001",
            'name' => 'Jangka Sorong Digital Mitutoyo',
            'slug' => 'jangka-sorong-digital-mitutoyo',
            'specification' => 'Ketelitian 0.01mm, rentang ukur 150mm, LCD display digital.',
            'image_path' => 'tools/jangka-sorong-digital.jpg',
            'total_stock' => 10,
            'available_stock' => 10,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $alatUkurPresisi->id,
            'laboratory_id' => $lpp->id,
            'code' => "LABTM{$lpp->id}-UK-{$mmyy}-002",
            'name' => 'Mikrometer Luar (Outside Micrometer)',
            'slug' => 'mikrometer-luar-0-25mm',
            'specification' => 'Ketelitian 0.001mm, rentang 0-25mm, ratchet stop thimble.',
            'image_path' => 'tools/mikrometer-luar.jpg',
            'total_stock' => 6,
            'available_stock' => 6,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $mesinPerkakas->id,
            'laboratory_id' => $lpp->id,
            'code' => "LABTM{$lpp->id}-MS-{$mmyy}-001",
            'name' => 'Mesin Bubut Konvensional Colchester',
            'slug' => 'mesin-bubut-konvensional-colchester',
            'specification' => 'Panjang bed 1500mm, swing over bed 300mm, spindle bore 40mm.',
            'image_path' => 'tools/mesin-bubut-konvensional.jpg',
            'total_stock' => 3,
            'available_stock' => 3,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $mesinPerkakas->id,
            'laboratory_id' => $lpp->id,
            'code' => "LABTM{$lpp->id}-MS-{$mmyy}-002",
            'name' => 'Mesin Frais Universal Bernardo',
            'slug' => 'mesin-frais-universal',
            'specification' => 'Meja kerja 1000x250mm, spindle speed 60-1800 rpm, dividing head universal.',
            'image_path' => 'tools/mesin-frais-universal.jpg',
            'total_stock' => 2,
            'available_stock' => 2,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $fabrikasiLas->id,
            'laboratory_id' => $lpp->id,
            'code' => "LABTM{$lpp->id}-LS-{$mmyy}-001",
            'name' => 'Mesin Las SMAW Inverter 200A',
            'slug' => 'mesin-las-smaw-inverter-200a',
            'specification' => 'Arus output 20-200A, duty cycle 60%, input 220V 1-fasa, proteksi thermal.',
            'image_path' => 'tools/mesin-las-smaw.jpg',
            'total_stock' => 5,
            'available_stock' => 5,
            'is_active' => true,
        ]);

        // 3. Laboratorium Material (LMAT) - Lab ID: 3
        Tool::create([
            'category_id' => $ujiMaterial->id,
            'laboratory_id' => $lmat->id,
            'code' => "LABTM{$lmat->id}-MT-{$mmyy}-001",
            'name' => 'Universal Testing Machine (UTM) 50kN',
            'slug' => 'universal-testing-machine-50kn',
            'specification' => 'Kapasitas beban 50kN, uji tarik, tekan, dan bending, load cell digital sensor.',
            'image_path' => null,
            'total_stock' => 1,
            'available_stock' => 1,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $ujiMaterial->id,
            'laboratory_id' => $lmat->id,
            'code' => "LABTM{$lmat->id}-MT-{$mmyy}-002",
            'name' => 'Hardness Tester Rockwell Digital',
            'slug' => 'hardness-tester-rockwell-digital',
            'specification' => 'Skala HRA, HRB, HRC, beban awal 10kgf, beban total 60/100/150kgf, auto-indentation.',
            'image_path' => null,
            'total_stock' => 2,
            'available_stock' => 2,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $ujiMaterial->id,
            'laboratory_id' => $lmat->id,
            'code' => "LABTM{$lmat->id}-MT-{$mmyy}-003",
            'name' => 'Mikroskop Metalurgi Optik Trinokuler',
            'slug' => 'mikroskop-metalurgi-optik-trinokuler',
            'specification' => 'Perbesaran 50x - 1000x, kamera digital CMOS 5MP, pencahayaan refleksi halogen.',
            'image_path' => null,
            'total_stock' => 2,
            'available_stock' => 2,
            'is_active' => true,
        ]);

        // 4. Laboratorium Energi (LEN) - Lab ID: 4
        Tool::create([
            'category_id' => $konversiEnergi->id,
            'laboratory_id' => $len->id,
            'code' => "LABTM{$len->id}-EN-{$mmyy}-001",
            'name' => 'Test Bed Uji Prestasi Motor Bakar 4-Langkah',
            'slug' => 'test-bed-uji-prestasi-motor-bakar-4-langkah',
            'specification' => 'Engine bensin 150cc, dynamometer hidrolik/eddy current, sensor torsi dan RPM digital.',
            'image_path' => null,
            'total_stock' => 1,
            'available_stock' => 1,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $konversiEnergi->id,
            'laboratory_id' => $len->id,
            'code' => "LABTM{$len->id}-EN-{$mmyy}-002",
            'name' => 'Bomb Calorimeter Isoperibol Digital',
            'slug' => 'bomb-calorimeter-isoperibol-digital',
            'specification' => 'Rentang kalor 10000 - 40000 J/g, resolusi suhu 0.0001 K, bejana oksigen stainless steel 316.',
            'image_path' => null,
            'total_stock' => 1,
            'available_stock' => 1,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $konversiEnergi->id,
            'laboratory_id' => $len->id,
            'code' => "LABTM{$len->id}-EN-{$mmyy}-003",
            'name' => 'Anemometer Digital & Pitot Tube Sensor',
            'slug' => 'anemometer-digital-pitot-tube',
            'specification' => 'Rentang kecepatan udara 0.4 - 35 m/s, diferensial tekanan manometer, interface USB.',
            'image_path' => null,
            'total_stock' => 3,
            'available_stock' => 3,
            'is_active' => true,
        ]);
    }
}

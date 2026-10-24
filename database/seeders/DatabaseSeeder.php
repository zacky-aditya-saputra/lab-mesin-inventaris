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
            'image_path' => 'tools/f1mGLLI9VKQdUhByY4OSojYqAEj7msEXwX2VdKGj.jpg',
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
            'image_path' => 'tools/hJ0XRwaEj2rbM30FhrkVht2IDDirYIkRC20dah70.jpg',
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
            'specification' => 'Modul silinder kerja ganda, katup solenoid 5/2 dan 3/2, unit kompressor udara piston 0-10 bar.',
            'image_path' => 'tools/Kompressor Angin - Tipe Piston.jpg',
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
            'image_path' => 'tools/Krisboww - KW 14 - 651.jpg',
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
            'image_path' => 'tools/Krisboww - KW 14 - 1056.jpg',
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
            'image_path' => 'tools/Mesin_Bubut - CQ623GX1000 (411165).jpg',
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
            'image_path' => 'tools/Bor-Universal_ZX7550CW.jpg',
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
            'image_path' => 'tools/Inverter Welding - ZX 7160.jpg',
            'total_stock' => 5,
            'available_stock' => 5,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $mesinPerkakas->id,
            'laboratory_id' => $lpp->id,
            'code' => "LABTM{$lpp->id}-MS-{$mmyy}-003",
            'name' => 'Mesin Sekrap Konvensional (Shaping Machine)',
            'slug' => 'mesin-sekrap-konvensional',
            'specification' => 'Stroke length 450mm, feed rate otomatis, sudut toolhead dapat diatur hingga 45 derajat.',
            'image_path' => 'tools/Mesin Sekrap.jpg',
            'total_stock' => 2,
            'available_stock' => 2,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $mesinPerkakas->id,
            'laboratory_id' => $lpp->id,
            'code' => "LABTM{$lpp->id}-MS-{$mmyy}-004",
            'name' => 'Mesin Gerinda Tangan Bosch GWS 700',
            'slug' => 'mesin-gerinda-tangan-bosch-gws-700',
            'specification' => 'Daya 710 Watt, diameter batu gerinda 100mm, kecepatan tanpa beban 12.000 rpm.',
            'image_path' => 'tools/Gerinda_Bosch - GWS - 700.jpg',
            'total_stock' => 4,
            'available_stock' => 4,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $mesinPerkakas->id,
            'laboratory_id' => $lpp->id,
            'code' => "LABTM{$lpp->id}-MS-{$mmyy}-005",
            'name' => 'Mesin Gerinda Duduk (Bench Grinder) Krisbow',
            'slug' => 'mesin-gerinda-duduk-bench-grinder-krisbow',
            'specification' => 'Ukuran roda 150mm x 20mm, daya 250 Watt, dual grit wheel (kasar dan halus).',
            'image_path' => 'tools/Bench Grinder - KWO 7-75.jpg',
            'total_stock' => 2,
            'available_stock' => 2,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $mesinPerkakas->id,
            'laboratory_id' => $lpp->id,
            'code' => "LABTM{$lpp->id}-MS-{$mmyy}-006",
            'name' => 'Gergaji Mesin Listrik Maktor MT583',
            'slug' => 'gergaji-mesin-listrik-maktor-mt583',
            'specification' => 'Circular saw 185mm, daya 1050 Watt, kapasitas potong 90 derajat hingga 66mm.',
            'image_path' => 'tools/Gergaji_Mesin_Listrik - Maktor - MT583.jpg',
            'total_stock' => 3,
            'available_stock' => 3,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $mesinPerkakas->id,
            'laboratory_id' => $lpp->id,
            'code' => "LABTM{$lpp->id}-MS-{$mmyy}-007",
            'name' => 'Mesin Bor Magnet Portabel JIC-23S',
            'slug' => 'mesin-bor-magnet-portabel-jic-23s',
            'specification' => 'Kekuatan magnet 13.000 N, kapasitas bor core drill hingga 50mm, twist drill 23mm.',
            'image_path' => 'tools/Mesin Bor Magnet - JIC  - 23S.jpg',
            'total_stock' => 2,
            'available_stock' => 2,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $fabrikasiLas->id,
            'laboratory_id' => $lpp->id,
            'code' => "LABTM{$lpp->id}-LS-{$mmyy}-002",
            'name' => 'Mesin Las Titik (Spot Welding Machine)',
            'slug' => 'mesin-las-titik-spot-welding',
            'specification' => 'Kapasitas input 16 kVA, ketebalan pengelasan plat baja hingga 2.0mm + 2.0mm, lengan elektroda tembaga.',
            'image_path' => 'tools/Spot_Welding.jpg',
            'total_stock' => 1,
            'available_stock' => 1,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $mesinPerkakas->id,
            'laboratory_id' => $lpp->id,
            'code' => "LABTM{$lpp->id}-MS-{$mmyy}-008",
            'name' => 'Mesin Injection Molding Plastik Manual Vertikal',
            'slug' => 'mesin-injection-molding-plastik-manual-vertikal',
            'specification' => 'Kapasitas injeksi 30 gram, pemanas silinder barrel 400 Watt, sistem tuas manual vertikal.',
            'image_path' => 'tools/Injection_Molding_Plastik_Manual_Vertikal.jpg',
            'total_stock' => 1,
            'available_stock' => 1,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $mesinPerkakas->id,
            'laboratory_id' => $lpp->id,
            'code' => "LABTM{$lpp->id}-MS-{$mmyy}-009",
            'name' => 'Alat Derek / Hoist Crane Workshop',
            'slug' => 'alat-derek-hoist-crane-workshop',
            'specification' => 'Kapasitas angkat maksimal 2 Ton, rantai baja galvanis grade 80, rem otomatis ganda.',
            'image_path' => 'tools/Alat Derek.jpg',
            'total_stock' => 2,
            'available_stock' => 2,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $mesinPerkakas->id,
            'laboratory_id' => $lpp->id,
            'code' => "LABTM{$lpp->id}-MS-{$mmyy}-010",
            'name' => 'Mesin Bubut Presisi Otomatis SNK 360',
            'slug' => 'mesin-bubut-presisi-otomatis-snk-360',
            'specification' => 'Distance between centers 1000mm, variable speed inverter drive, digital readout 2-axis.',
            'image_path' => 'tools/Mesin_Bubut_Otomatis - SNK 360.jpg',
            'total_stock' => 2,
            'available_stock' => 2,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $fabrikasiLas->id,
            'laboratory_id' => $lpp->id,
            'code' => "LABTM{$lpp->id}-LS-{$mmyy}-003",
            'name' => 'Mesin Las Inverter TIG/MMA Lakoni Hawk 200E',
            'slug' => 'mesin-las-inverter-tig-mma-lakoni-hawk-200e',
            'specification' => 'Dual fungsi TIG Argon dan MMA Elektroda, arus 10-200A, gas pre-flow/post-flow control.',
            'image_path' => 'tools/Lakoni - Inverter Welding - Hawk TIO 200 e.jpg',
            'total_stock' => 3,
            'available_stock' => 3,
            'is_active' => true,
        ]);

        Tool::create([
            'category_id' => $mesinPerkakas->id,
            'laboratory_id' => $lpp->id,
            'code' => "LABTM{$lpp->id}-MS-{$mmyy}-011",
            'name' => 'High Pressure Jet Cleaner Workshop',
            'slug' => 'high-pressure-jet-cleaner-workshop',
            'specification' => 'Tekanan kerja 100-140 bar, debit air 6.5 liter/menit, selang hidrolik tekanan tinggi 8 meter.',
            'image_path' => 'tools/Jet Cleaner.jpg',
            'total_stock' => 2,
            'available_stock' => 2,
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
            'image_path' => 'tools/Mesin_Press - GLII2 M - 4.jpg',
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
            'image_path' => 'tools/Mesin perkakas otomatis - VM6030.jpg',
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
            'image_path' => 'tools/mDPsBeAO2T33zRBLVLF7VTdYel7lFOYXpNW5hKjb.jpg',
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
            'specification' => 'Engine diesel 4-langkah silinder tunggal, dynamometer hidrolik, sensor torsi dan RPM digital.',
            'image_path' => 'tools/Diesel Engine - Dongfeng.jpg',
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
            'image_path' => 'tools/LG4sVTDftHZmp7s4CBINr3lyFsqzkGYn6KkYf3yf.jpg',
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
            'image_path' => 'tools/4zmUZzyCxRRRFhkE65erqOmI9Ad3sKB6XgsKfnr4.jpg',
            'total_stock' => 3,
            'available_stock' => 3,
            'is_active' => true,
        ]);
    }
}

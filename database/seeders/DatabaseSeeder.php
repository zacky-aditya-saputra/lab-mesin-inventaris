<?php

namespace Database\Seeders;

use App\Models\Category;
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
        Tool::query()->delete();
        Category::query()->delete();
        User::query()->delete();

        User::create([
            'name' => 'Admin',
            'identity_number' => '198501012010011001',
            'email' => 'admin@unwahas.ac.id',
            'password' => bcrypt('password'),
            'phone_number' => '081234567801',
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Dimas Prasetyo',
            'identity_number' => '32602100001',
            'email' => 'dimas@std.unwahas.ac.id',
            'password' => bcrypt('password'),
            'phone_number' => '081234567802',
            'role' => 'student',
        ]);

        User::create([
            'name' => 'Nabila Putri',
            'identity_number' => '32602100002',
            'email' => 'nabila@std.unwahas.ac.id',
            'password' => bcrypt('password'),
            'phone_number' => '081234567803',
            'role' => 'student',
        ]);

        $alatUkurPresisi = Category::create([
            'name' => 'Alat Ukur Presisi',
            'slug' => 'alat-ukur-presisi',
            'description' => 'Peralatan ukur dimensi dan toleransi mekanik presisi tinggi.',
        ]);

        $mesinPerkakas = Category::create([
            'name' => 'Mesin Perkakas',
            'slug' => 'mesin-perkakas',
            'description' => 'Mesin fabrikasi dan pemesinan logam konvensional.',
        ]);

        $alatLasFabrikasi = Category::create([
            'name' => 'Alat Las & Fabrikasi',
            'slug' => 'alat-las-fabrikasi',
            'description' => 'Peralatan pengelasan busur listrik dan fabrikasi logam.',
        ]);

        Tool::create([
            'category_id' => $alatUkurPresisi->id,
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
            'category_id' => $alatLasFabrikasi->id,
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

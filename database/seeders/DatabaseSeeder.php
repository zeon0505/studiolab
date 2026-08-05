<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Item;
use App\Models\DailyAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin Account
        $admin = User::updateOrCreate(
            ['email' => 'admin@staimas.com'],
            [
                'name' => 'Administrator Studio & Lab',
                'no_wa' => '081234567890',
                'password' => Hash::make('adminstamas'),
            ]
        );

        // Akun Admin Yoga
        $adminYoga = User::updateOrCreate(
            ['email' => 'yoga@staimas.com'],
            [
                'name' => 'Yoga',
                'no_wa' => '081234567899',
                'password' => Hash::make('adminyoga'),
            ]
        );

        // 2. PJ / Staff Accounts
        $pj1 = User::updateOrCreate(
            ['email' => 'budi@staimas.com'],
            [
                'name' => 'Budi Santoso, M.Kom.',
                'no_wa' => '081234567891',
                'password' => Hash::make('staffstaimas'),
            ]
        );

        $pj2 = User::updateOrCreate(
            ['email' => 'ani@staimas.com'],
            [
                'name' => 'Ani Wijaya, S.Pd.',
                'no_wa' => '081234567892',
                'password' => Hash::make('staffstaimas'),
            ]
        );

        $pj3 = User::updateOrCreate(
            ['email' => 'hendra@staimas.com'],
            [
                'name' => 'Hendra Pratama',
                'no_wa' => '081234567893',
                'password' => Hash::make('staffstaimas'),
            ]
        );

        // 3. User Demo Accounts (Mahasiswa & Dosen)
        User::updateOrCreate(
            ['email' => 'mahasiswa@staimas.com'],
            [
                'name' => 'Zaky Ahmad (Mahasiswa)',
                'no_wa' => '089876543210',
                'password' => Hash::make('mahasiswastaimas'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'dosen@staimas.com'],
            [
                'name' => 'Dr. Indra Fauzi, M.Pd. (Dosen)',
                'no_wa' => '089876543211',
                'password' => Hash::make('dosenstaimas'),
            ]
        );

        // 4. Seed Items (Studio)
        $studioItems = [
            [
                'nama' => 'Kamera DSLR Canon EOS 200D',
                'kategori' => 'studio',
                'tipe' => 'peralatan',
                'deskripsi' => 'Kamera DSLR handal untuk kebutuhan fotografi dan videografi studio.',
                'gambar' => null,
                'status' => 'tersedia',
                'stok' => 2,
                'kapasitas_kursi' => 0,
            ],
            [
                'nama' => 'Mic Wireless Rode Wireless GO II',
                'kategori' => 'studio',
                'tipe' => 'peralatan',
                'deskripsi' => 'Mikrofon nirkabel dual-channel berukuran ringkas untuk rekaman suara jernih.',
                'gambar' => null,
                'status' => 'tersedia',
                'stok' => 3,
                'kapasitas_kursi' => 0,
            ],
            [
                'nama' => 'Tripod Manfrotto Professional',
                'kategori' => 'studio',
                'tipe' => 'peralatan',
                'deskripsi' => 'Tripod kokoh dengan fluid head, cocok untuk pergerakan kamera yang mulus.',
                'gambar' => null,
                'status' => 'tersedia',
                'stok' => 4,
                'kapasitas_kursi' => 0,
            ],
            [
                'nama' => 'Ruang Studio Podcast & Broadcasting',
                'kategori' => 'studio',
                'tipe' => 'ruangan',
                'deskripsi' => 'Ruangan ber-AC kedap suara yang dilengkapi mixer audio, mikrofon podcast, dan lighting profesional.',
                'gambar' => null,
                'status' => 'tersedia',
                'stok' => 1,
                'kapasitas_kursi' => 6,
            ],
        ];

        // Seed Items (Laboratorium)
        $labItems = [
            [
                'nama' => 'Komputer PC Intel i7 (Lab Komputer)',
                'kategori' => 'laboratorium',
                'tipe' => 'peralatan',
                'deskripsi' => 'PC workstation dengan spesifikasi tinggi untuk editing video, olah data, dan praktikum teknologi.',
                'gambar' => null,
                'status' => 'tersedia',
                'stok' => 20,
                'kapasitas_kursi' => 0,
            ],
            [
                'nama' => 'LCD Proyektor Epson Professional',
                'kategori' => 'laboratorium',
                'tipe' => 'peralatan',
                'deskripsi' => 'Proyektor tingkat kecerahan tinggi untuk presentasi dan microteaching.',
                'gambar' => null,
                'status' => 'tersedia',
                'stok' => 2,
                'kapasitas_kursi' => 0,
            ],
            [
                'nama' => 'Ruang Laboratorium Bahasa & Komputer',
                'kategori' => 'laboratorium',
                'tipe' => 'ruangan',
                'deskripsi' => 'Ruangan laboratorium utama dengan kapasitas 30 orang dilengkapi komputer client dan sistem audio laboratorium bahasa.',
                'gambar' => null,
                'status' => 'tersedia',
                'stok' => 1,
                'kapasitas_kursi' => 30,
            ],
            [
                'nama' => 'Ruang Laboratorium Microteaching',
                'kategori' => 'laboratorium',
                'tipe' => 'ruangan',
                'deskripsi' => 'Ruang praktek mengajar yang dilengkapi cermin dua arah (two-way mirror) dan kamera perekam evaluasi.',
                'gambar' => null,
                'status' => 'tersedia',
                'stok' => 1,
                'kapasitas_kursi' => 15,
            ],
        ];

        foreach (array_merge($studioItems, $labItems) as $item) {
            Item::updateOrCreate(
                ['nama' => $item['nama']],
                $item
            );
        }

        // 5. Seed Daily Assignments (PJ Harian Awal)
        $daysMap = [
            'senin' => $pj1->id,
            'selasa' => $pj2->id,
            'rabu' => $pj1->id,
            'kamis' => $pj2->id,
            'jumat' => $pj3->id,
            'sabtu' => $pj3->id,
        ];

        foreach ($daysMap as $day => $userId) {
            DailyAssignment::updateOrCreate(
                ['hari' => $day],
                ['user_id' => $userId]
            );
        }
    }
}

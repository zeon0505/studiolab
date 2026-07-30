# Tugas Pembangunan Studiolab STAIMAS

- [x] **Fase 1: Database & Model**
  - [x] Buat migration untuk tabel `items`
  - [x] Buat migration untuk tabel `bookings`
  - [x] Jalankan migration lokal
  - [x] Buat model `Item.php` dan `Booking.php` beserta logic relasinya
  - [x] Buat seeder awal untuk daftar peralatan & ruangan contoh

- [x] **Fase 2: Controllers & Routing**
  - [x] Buat `PageController.php` untuk melayani halaman publik
  - [x] Buat `AdminController.php` untuk dashboard admin
  - [x] Daftarkan seluruh rute di `routes/web.php`

- [x] **Fase 3: Layouts & CSS**
  - [x] Set up layout template `layouts/app.blade.php` (Publik)
  - [x] Set up layout template `layouts/admin.blade.php` (Admin)

- [x] **Fase 4: Halaman Publik (Views)**
  - [x] Buat halaman Beranda `welcome.blade.php`
  - [x] Buat halaman Galeri Peralatan `pages/peralatan.blade.php`
  - [x] Buat halaman Alur Peminjaman `pages/alur.blade.php`
  - [x] Buat halaman Struktur Pengelola `pages/struktur.blade.php`
  - [x] Buat halaman Form Peminjaman `pages/peminjaman.blade.php` (Menggunakan Livewire)

- [x] **Fase 5: Dashboard Admin (Views)**
  - [x] Buat halaman list booking & manajemen item di admin (`admin/dashboard.blade.php` & `admin/items.blade.php` & `admin/login.blade.php`)

- [x] **Fase 6: Verifikasi & Linter**
  - [x] Jalankan `php -l` untuk syntax check
  - [x] Tes kirim form peminjaman dan upload bukti

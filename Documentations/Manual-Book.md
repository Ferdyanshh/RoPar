# Manual Book RoPar

## 1. Tentang Aplikasi

RoPar (Sistem Peminjaman Robot Pacar) adalah aplikasi web untuk mengelola katalog robot, unit, anggota, peminjaman, pengembalian, denda, dan laporan. Anggota mencari dan meminjam unit. Admin mengelola data serta memproses pengembalian.

## 2. Peran Pengguna

| Peran | Kemampuan utama |
|---|---|
| Guest | Registrasi dan login |
| Anggota | Melihat katalog, mencari robot, mengajukan pinjaman, melihat pinjaman dan profil sendiri |
| Admin | Mengelola kategori, robot, unit, anggota, pengembalian, riwayat, dan laporan |

Pengembalian hanya dapat diproses admin. Anggota menghubungi admin untuk mengembalikan unit.

## 3. Aturan Bisnis

- Maksimal **dua pinjaman aktif** untuk setiap anggota.
- Lama pinjaman adalah **lima hari** sejak tanggal pinjam.
- Denda keterlambatan adalah **Rp5.000 per hari**.
- Tanggal pengembalian dan denda final dihitung oleh server ketika admin memproses pengembalian.
- Unit dengan status rusak atau sedang dipinjam tidak dapat dipinjam.
- Alamat profil wajib diisi. Nomor telepon mengikuti validasi aplikasi: 8–15 karakter angka, dengan tanda `+` opsional di depan.

## 4. Persiapan dan Instalasi Lokal

### 4.1 Prasyarat

- PHP 8.3 atau lebih baru
- Composer 2.x
- MySQL atau MariaDB aktif
- Node.js dan npm
- Git (opsional, untuk mengambil source)

### 4.2 Siapkan source dan database

Di PowerShell, buka folder proyek lalu jalankan:

```powershell
composer install
npm install
npm run build
```

Buat database kosong bernama `ropar`, misalnya dari MySQL client/phpMyAdmin:

```sql
CREATE DATABASE ropar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Salin konfigurasi contoh dan atur kredensial database di `.env`:

```powershell
Copy-Item .env.example .env
```

Pastikan nilai `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` cocok dengan MySQL lokal. Isi `APP_KEY` dengan menjalankan:

```powershell
php artisan key:generate
```

Jalankan migration dan data demo **pada database demo yang kosong**:

```powershell
php artisan migrate --seed
php artisan storage:link
```

Seeder menyiapkan admin, dua akun anggota, katalog, unit, dan contoh pinjaman. Seeder pinjaman tidak mengulang data transaksi jika anggota demo utama sudah memiliki pinjaman. Hindari `migrate:fresh` jika database berisi data yang ingin dipertahankan.

Jalankan aplikasi:

```powershell
php artisan serve
```

Buka `http://127.0.0.1:8000` di browser. Untuk perubahan aset saat pengembangan, jalankan `npm run dev` di terminal terpisah.

### 4.3 Akun demo

| Peran | Email default | Kata sandi default |
|---|---|---|
| Admin | `admin@robotpacar.test` | `password` |
| Anggota demo utama | `member@robotpacar.test` | `password` |
| Anggota demo kedua | `member2@robotpacar.test` | `password` |

Nilai akun dapat dioverride lewat `.env` (`ADMIN_EMAIL`, `ADMIN_PASSWORD`, `DEMO_MEMBER_EMAIL`, `DEMO_MEMBER_PASSWORD`). Jangan gunakan kata sandi demo untuk server publik.

## 5. Panduan Anggota

### 5.1 Registrasi

1. Buka `/register`.
2. Isi username, email, kata sandi minimal delapan karakter, dan konfirmasi kata sandi.
3. Isi nama lengkap, nomor telepon, dan alamat. Tanggal lahir bersifat opsional dan harus sebelum hari ini.
4. Tekan **Daftar Sekarang**. Jika data valid, akun anggota dan profil dibuat, lalu pengguna masuk ke aplikasi.

### 5.2 Login dan logout

1. Buka `/login` dan masukkan email serta kata sandi.
2. Sistem mengarahkan pengguna ke dashboard sesuai rolenya.
3. Pilih **Keluar** dari menu untuk mengakhiri sesi.

### 5.3 Mencari robot dan melihat unit

1. Buka **Katalog Robot** (`/robots`).
2. Ketik nama robot pada kolom pencarian atau pilih chip kategori.
3. Pilih kartu robot untuk membuka detail.
4. Periksa deskripsi, kategori, foto bila tersedia, kode unit, dan status ketersediaan.

Jika robot tidak memiliki foto, aplikasi menampilkan ilustrasi placeholder.

### 5.4 Mengajukan pinjaman

1. Dari detail robot, pilih unit berstatus tersedia.
2. Tekan tombol pinjam dan konfirmasi.
3. Jika anggota belum mencapai dua pinjaman aktif, transaksi tersimpan dan tanggal jatuh tempo ditetapkan lima hari setelah tanggal pinjam.
4. Buka **Pinjaman Saya** untuk melihat pinjaman aktif dan riwayat.

Jika sudah memiliki dua pinjaman aktif, permintaan berikutnya ditolak. Anggota tidak memproses pengembalian dari halaman ini; hubungi admin.

### 5.5 Mengubah profil

1. Buka **Profil Saya** (`/profile`).
2. Ubah username, nama lengkap, nomor telepon, alamat, mata uang tampilan (Rupiah atau US Dollar), atau tanggal lahir. Pilihan mata uang berada tepat di bawah alamat domisili dan berlaku untuk akun anggota maupun admin.
3. Email akun ditampilkan sebagai informasi dan tidak dapat diubah di form ini.
4. Tekan **Simpan Perubahan**. Jumlah denda ditampilkan dalam mata uang yang dipilih, dikonversi menggunakan kurs tetap yang dapat diatur melalui `IDR_PER_USD`. Data denda tetap disimpan dalam Rupiah.

## 6. Panduan Admin

### 6.1 Dashboard dan navigasi

Setelah login sebagai admin, dashboard menyediakan ringkasan. Gunakan menu samping untuk membuka data kategori, robot, unit, anggota, pinjaman aktif, dan riwayat.

### 6.2 Mengelola data master

Di halaman masing-masing, admin dapat menambah, melihat, mengubah, dan menghapus data:

- **Kategori:** nama kategori dan deskripsi.
- **Robot:** nama, deskripsi, foto bila diperlukan, dan satu atau lebih kategori.
- **Unit:** robot induk, kode unik, serta status tersedia atau rusak. Status dipinjam mengikuti transaksi pinjaman.
- **Anggota:** akun dan data profil.

Ikuti validasi pada form. Kode unit harus unik. Kategori yang masih digunakan robot tidak dapat dihapus. Data yang masih terikat transaksi dapat ditolak saat dihapus.

### 6.3 Memproses pengembalian

1. Buka **Pinjaman Aktif** (`/admin/loans/active`).
2. Temukan anggota dan unit yang dikembalikan; periksa tanggal pinjam dan jatuh tempo.
3. Pilih **Proses Pengembalian**, lalu konfirmasi.
4. Server mencatat tanggal proses, menghitung hari keterlambatan dan denda, mengubah status pinjaman menjadi dikembalikan, serta mengembalikan unit menjadi tersedia.
5. Periksa ringkasan hasil dan lanjutkan ke riwayat bila perlu.

Rumus denda: `maks(0, tanggal kembali − tanggal jatuh tempo) × Rp5.000`. Contoh: keterlambatan tiga hari menghasilkan Rp15.000. Tanggal dari browser tidak menjadi sumber tanggal final.

### 6.4 Melihat, memfilter, dan mencetak riwayat

1. Buka **Riwayat Peminjaman** (`/admin/loans/history`).
2. Filter berdasarkan rentang tanggal pinjam, status, anggota, atau kata kunci.
3. Periksa kolom peminjam, unit, tanggal, keterlambatan, denda, status, dan admin pemroses.
4. Tekan **Cetak Laporan**. Dialog cetak browser akan terbuka setelah data dimuat.
5. Pilih printer atau **Save as PDF** untuk menyimpan laporan.

## 7. Struktur Proyek

| Bagian | Lokasi | Fungsi |
|---|---|---|
| Route | `routes/web.php` | Rute halaman dan endpoint backend serta middleware akses |
| Migration | `database/migrations/` | Definisi dan perubahan skema database |
| Seeder | `database/seeders/` | Akun demo, katalog, unit, dan transaksi contoh |
| Model | `app/Models/` | Entitas Eloquent dan relasi database |
| Controller | `app/Http/Controllers/` | Validasi request dan alur aplikasi |
| Service | `app/Services/` | Aturan transaksi peminjaman/pengembalian dan denda |
| Enum | `app/Enums/` | Nilai role dan status yang dibatasi |
| View | `resources/views/` | Antarmuka Blade untuk anggota dan admin |
| Test | `tests/Feature/` | Pemeriksaan alur aplikasi secara otomatis |

## 8. Teknologi dan Komponen

- Laravel menangani routing, validasi server, session authentication, middleware, ORM, migration, dan seeder.
- Blade menyusun halaman; Tailwind CSS membentuk tampilan; Alpine.js menangani interaksi antarmuka.
- Eloquent mengakses MySQL melalui model dan relasi.
- PHPUnit menjalankan automated test.
- Cetak laporan memakai print stylesheet browser; pengguna dapat memilih printer atau menyimpan PDF.

## 9. Pemecahan Masalah

| Gejala | Pemeriksaan |
|---|---|
| Koneksi database gagal | Pastikan MySQL aktif, database sudah dibuat, dan nilai DB di `.env` benar. |
| Halaman menampilkan error setelah mengubah `.env` | Jalankan `php artisan config:clear`, kemudian muat ulang aplikasi. |
| Foto tidak muncul | Pastikan file tersedia di storage dan jalankan `php artisan storage:link`. |
| Perubahan CSS/JS tidak terlihat | Jalankan `npm run build` atau `npm run dev`, lalu muat ulang browser. |
| Login demo gagal | Pastikan seeder telah dijalankan dan cek apakah variabel akun demo dioverride di `.env`. |
| Halaman admin ditolak | Pastikan akun yang digunakan memiliki role admin. |

## 10. Batasan dan Catatan

- Manual book ini menggambarkan alur aplikasi pada branch kerja saat dokumen dibuat; nama menu dapat berubah jika antarmuka diperbarui.
- Pelaksanaan UAT manual dan persetujuannya dicatat di `Hasil-UAT.md`; automated test tidak menggantikan persetujuan pengguna.
- Repository kerja saat dokumen disusun menggunakan GitHub. PRD menyebut GitLab; lokasi repository harus disesuaikan dengan keputusan tim sebelum penyerahan akhir.

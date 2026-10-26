# Hasil User Acceptance Test (UAT) RoPar

## 1. Informasi Pengujian

| Informasi | Keterangan |
|---|---|
| Aplikasi | Sistem Peminjaman Robot Pacar (RoPar) |
| Tanggal pencatatan | 26 Oktober 2026 |
| Versi kerja | Branch `be-ferdy`, repo kerja `test_codex` |
| Acuan | BRD, PRD, Flowchart, ERD, dan `PROGRESS.md` |
| Status laporan | **Parsial** — automated test telah dijalankan; UAT manual oleh pengguna/asesor belum dilakukan |
| Lingkungan automated test | PHPUnit dengan SQLite in-memory sesuai `phpunit.xml` |

## 2. Ringkasan Hasil Automated Test

Perintah yang dijalankan:

```bash
php artisan test
```

Hasil: **11 test lulus, 45 assertions, 0 gagal**.

Automated test mencakup registrasi dan pembuatan profil, validasi alamat dan nomor telepon internasional, autentikasi dan pembatasan role, batas maksimal dua pinjaman aktif, penolakan unit yang sedang dipinjam, larangan pengembalian oleh anggota, proses pengembalian admin dan perhitungan denda, larangan menghapus kategori yang masih dipakai, pagination daftar unit admin, kode unit, status rusak, serta redirect halaman awal.

Hasil ini membuktikan pemeriksaan otomatis yang tercakup oleh test tersebut. Hasil ini **bukan pengganti UAT manual** untuk seluruh antarmuka, alur CRUD, pencarian, laporan, dan cetak.

## 3. Skenario UAT Manual

Kolom hasil aktual dan status sengaja dibiarkan untuk diisi saat skenario benar-benar dicoba melalui browser. Jangan mengubah status menjadi lulus hanya berdasarkan hasil automated test.

| ID | Role | Skenario | Hasil yang diharapkan | Hasil aktual / bukti | Status |
|---|---|---|---|---|---|
| UAT-01 | Guest | Buka halaman login, masukkan kredensial valid | Login berhasil dan pengguna masuk ke dashboard sesuai role | Belum dijalankan | Belum diuji |
| UAT-02 | Guest | Login dengan kata sandi salah | Login ditolak dan pesan kesalahan ditampilkan | Belum dijalankan | Belum diuji |
| UAT-03 | Guest | Registrasi dengan data valid, lalu coba email duplikat | Akun anggota dan profil terbentuk; email duplikat ditolak dengan pesan validasi | Belum dijalankan | Belum diuji |
| UAT-04 | Anggota | Kosongkan field wajib profil atau masukkan nomor telepon di luar format | Perubahan tidak tersimpan dan validasi ditampilkan | Belum dijalankan | Belum diuji |
| UAT-05 | Anggota | Cari robot dengan nama dan filter kategori; buka detail | Hasil pencarian/filter sesuai; detail menampilkan kategori, unit, dan ketersediaan | Belum dijalankan | Belum diuji |
| UAT-06 | Anggota | Pinjam satu unit tersedia | Transaksi tersimpan; status unit menjadi dipinjam; jatuh tempo lima hari setelah tanggal pinjam | Belum dijalankan | Belum diuji |
| UAT-07 | Anggota | Pinjam unit ketiga saat sudah memiliki dua pinjaman aktif | Permintaan ditolak; transaksi ketiga tidak terbentuk | Belum dijalankan | Belum diuji |
| UAT-08 | Anggota | Coba pinjam unit yang sudah dipinjam | Permintaan ditolak sebagai unit tidak tersedia | Belum dijalankan | Belum diuji |
| UAT-09 | Anggota | Buka Pinjaman Saya dan cari aksi pengembalian | Pinjaman aktif dan riwayat terlihat; anggota diarahkan menghubungi admin untuk pengembalian | Belum dijalankan | Belum diuji |
| UAT-10 | Anggota | Coba buka halaman admin | Akses ditolak | Belum dijalankan | Belum diuji |
| UAT-11 | Admin | Tambah, ubah, dan hapus data master yang tidak memiliki relasi | Perubahan tersimpan; validasi mencegah data tidak valid/duplikat | Belum dijalankan | Belum diuji |
| UAT-12 | Admin | Coba hapus kategori yang masih dipakai robot | Penghapusan ditolak dengan pesan yang jelas | Belum dijalankan | Belum diuji |
| UAT-13 | Admin | Proses pengembalian tepat waktu | Tanggal kembali mengikuti tanggal server; denda Rp0; status pinjaman/unit berubah | Belum dijalankan | Belum diuji |
| UAT-14 | Admin | Proses pengembalian dengan pinjaman terlambat tiga hari | Server menghitung denda Rp15.000; tanggal dari browser tidak mengubah hasil | Belum dijalankan | Belum diuji |
| UAT-15 | Admin | Filter riwayat berdasarkan anggota/periode/status, lalu cetak | Data sesuai filter; tampilan print dapat dicetak atau disimpan sebagai PDF | Belum dijalankan | Belum diuji |
| UAT-16 | Kedua role | Logout lalu coba akses halaman terlindungi | Sesi berakhir dan pengguna diminta login kembali | Belum dijalankan | Belum diuji |

## 4. Catatan Pelaksanaan

- Jalankan skenario dengan akun dan data uji lokal, bukan data produksi.
- Catat tanggal, browser, akun/role (tanpa mencatat kata sandi), hasil aktual, dan bukti screenshot untuk setiap skenario.
- Gunakan anggota demo utama untuk melihat batas dua pinjaman aktif. Gunakan anggota demo kedua atau akun baru untuk menguji peminjaman baru.
- Untuk membuktikan tiga hari keterlambatan, siapkan transaksi dengan jatuh tempo tiga hari sebelum tanggal pengujian; proses pengembalian memakai tanggal server saat itu.
- Pengujian yang mengubah data sebaiknya dilakukan pada database demo yang dapat dipulihkan.

## 5. Persetujuan UAT

Bagian ini diisi setelah UAT manual dilaksanakan bersama pengguna/representatif dan hasilnya disepakati.

| Peran | Nama | Tanggal | Keputusan | Tanda tangan |
|---|---|---|---|---|
| Penguji/pengguna | __________________ | __________________ | __________________ | __________________ |
| Perwakilan tim | __________________ | __________________ | __________________ | __________________ |

# Progress Audit Backend dan Frontend RoPar

**Tanggal audit:** 23 Oktober 2026  
**Branch:** `main` (`e0ca0d6`)  
**Cakupan:** Perbandingan implementasi dengan BRD, ERD, PRD, dan Flowchart. Audit ini hanya membaca kode; belum ada temuan yang diperbaiki.

## Fitur yang sudah tersedia

- Registrasi anggota, login, logout, autentikasi berbasis sesi, dan pembatasan role.
- Profil anggota serta aturan satu profil per akun.
- Katalog robot, pencarian nama/kategori, detail unit, serta foto robot.
- CRUD admin untuk kategori, robot, unit, anggota, dan profil anggota.
- Peminjaman dengan batas dua pinjaman aktif, pengecekan ketersediaan unit, dan durasi lima hari.
- Proses pengembalian admin, kalkulasi denda, riwayat, filter laporan, dan halaman print.
- Data contoh untuk katalog dan riwayat peminjaman.

## Temuan yang perlu ditindaklanjuti

### Prioritas tinggi

- [ ] **Pengembalian anggota tidak sesuai BR-11 dan PRD FR-22.** Halaman Pinjaman Saya menampilkan tombol pengembalian dan backend menerima pengembalian dari anggota. Request juga menerima `return_date` dari browser, sehingga anggota dapat mengirim tanggal lampau dan mengubah perhitungan denda. Tentukan kembali perilaku yang diminta dokumen; bila dokumen tetap berlaku, proses pengembalian harus dilakukan admin saja.
- [ ] **Tanggal simulasi di halaman pengembalian admin tidak konsisten.** Frontend mengirim tanggal pilihan, tetapi backend memproses tanggal hari ini. Hilangkan simulasi tanggal pada proses final atau sepakati aturan tanggal dan implementasikan konsisten pada kedua sisi.

### Prioritas menengah

- [ ] **Validasi profil berbeda dari PRD.** Alamat dibuat opsional, padahal PRD meminta wajib. Format telepon di aplikasi hanya menerima 10–15 digit, sedangkan PRD menyebut 8–15 karakter angka dan tanda `+`.
- [ ] **Aturan kategori dan status unit berbeda dari ERD/PRD.** Penghapusan kategori saat ini menghapus relasi kategori-robot, sementara ERD menyebut pembatasan relasi. Status unit aplikasi memakai `perawatan` dan `hilang`, sedangkan dokumen memakai `rusak`.
- [ ] **Batas panjang field berbeda dari PRD.** Kategori, nama robot, dan kode unit di aplikasi memiliki batas lebih pendek daripada yang didokumentasikan.
- [ ] **Data demo belum sesuai skenario PRD.** Katalog seeder memakai nama robot/kategori berbeda. Seeder pinjaman membuat satu pinjaman aktif normal dan dua pinjaman selesai; PRD meminta contoh satu pinjaman aktif terlambat dan satu selesai berdenda.
- [ ] **Pagination admin menggunakan 25 data per halaman**, sedangkan NFR-05 menyebut 10–15 data.

### Dokumentasi dan verifikasi

- [ ] README memiliki tautan PRD yang salah, keterangan versi teknologi lama, dan instruksi repository GitLab yang belum sesuai repo ini.
- [ ] Dokumen hasil UAT dan manual book belum ditemukan. README memiliki checklist UAT, tetapi itu belum menggantikan hasil uji yang tercatat.
- [ ] Test suite terakhir: **6 lulus, 1 gagal, 27 assertions**. Test bawaan `ExampleTest` mengharapkan `/` berstatus 200, sedangkan route saat ini mengarahkan guest ke login dengan status 302. Perlu putuskan apakah test atau perilaku route yang harus disesuaikan.
- [ ] Label dashboard “Perlu Diselesaikan” untuk denda perlu diperjelas. Dokumen mengecualikan pembayaran online; denda dicatat dan dikonfirmasi admin.

## Langkah berikutnya

1. Sepakati apakah dokumen tetap menjadi acuan untuk pengembalian admin-only dan format data.
2. Benahi temuan prioritas tinggi terlebih dahulu.
3. Selaraskan validasi, status unit, seed demo, pagination, dan dokumentasi.
4. Jalankan ulang test suite dan lakukan UAT untuk skenario PRD sebelum merge/push.

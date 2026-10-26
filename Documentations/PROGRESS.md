# Progress Audit Backend dan Frontend RoPar

**Update:** 26 Oktober 2026
**Branch:** `be-ferdy` pada repo `test_codex` (`2265c1d` sebagai basis)
**Cakupan:** Implementasi tindak lanjut hasil perbandingan BRD, ERD, PRD, dan Flowchart.

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

- [x] **Pengembalian anggota sesuai BR-11 dan PRD FR-22.** Tombol, modal, handler, dan route pengembalian anggota dihapus. Anggota diarahkan menghubungi admin.
- [x] **Tanggal pengembalian admin konsisten.** Form menampilkan tanggal saat ini tanpa simulasi; server menghitung serta mengembalikan tanggal dan denda final. Frontend memakai hasil server.

### Prioritas menengah

- [x] **Validasi profil diselaraskan dengan PRD.** Alamat wajib di form/API dan skema; nomor telepon menerima 8–15 karakter, dengan `+` opsional di depan.
- [x] **Aturan kategori dan status unit diselaraskan.** Kategori yang masih dipakai robot ditolak dengan pesan validasi dan FK restrict. Status unit kini `tersedia/dipinjam/rusak`; migrasi memetakan status lama.
- [x] **Batas field diselaraskan.** Kategori maksimal 100, nama robot 150, dan kode unit 50 karakter. Format kode tidak dibatasi ke `RBT-###`.
- [x] **Data demo diselaraskan.** Seeder memakai katalog Aya/Rio/Luna/Dio, dua anggota contoh, satu pinjaman aktif normal, satu aktif terlambat, serta pinjaman selesai berdenda.
- [x] **Pagination daftar menggunakan 15 data secara default**, sesuai NFR-05. Endpoint tetap mendukung ukuran lebih besar untuk dashboard dan laporan.

### Dokumentasi dan verifikasi

- [x] README diperbarui: tautan PRD, versi stack, alamat repository, dan email anggota demo sudah sesuai.
- [ ] Dokumen hasil UAT dan manual book belum ditemukan. README memiliki checklist UAT, tetapi itu belum menggantikan hasil uji yang tercatat.
- [x] Test root diperbarui untuk mengharapkan redirect guest ke login. Test suite terakhir: **11 lulus, 45 assertions**.
- [x] Label denda di dashboard diperjelas sebagai konfirmasi admin; sistem tidak mengklaim menyediakan pembayaran online.
- [ ] Hasil UAT manual dan manual book belum dibuat. Checklist UAT di README sudah ditandai sebagai skenario untuk dijalankan, bukan hasil uji.

## Langkah berikutnya

1. Jalankan UAT manual untuk skenario PRD dan catat hasilnya.
2. Buat manual book setelah alur UAT disepakati.
3. Review lalu push perubahan dari branch `be-ferdy` jika sudah siap.

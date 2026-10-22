# Product Requirements Document (PRD)

## Sistem Peminjaman Robot Pacar

| | |
|---|---|
| **Nama Kelompok** | _(isi)_ |
| **Nama Team** | _(isi)_ |
| **Versi** | 1.0 |
| **Tanggal** | 22 Oktober 2026 |
| **Dokumen Terkait** | [BRD](BRD.md), [ERD](ERD.md) |

---

## 1. Ringkasan Produk

Aplikasi web untuk mengelola peminjaman Robot Pacar. Anggota dapat mendaftar, mencari robot, meminjam (maks. 2 unit, maks. 5 hari), dan melihat pinjamannya. Admin mengelola data master, memproses pengembalian dengan denda otomatis (Rp5.000/hari keterlambatan), dan mencetak riwayat peminjaman.

## 2. Tech Stack (Asumsi)

| Komponen | Pilihan |
|---|---|
| Framework | Laravel (migration, seeder, validation, Eloquent) |
| Database | MySQL |
| Frontend | Blade + Bootstrap/Tailwind |
| Autentikasi | Laravel Breeze / session auth |
| Cetak laporan | Halaman print-friendly (CSS `@media print`) atau DomPDF |
| Version control | GitLab |

> Jika tim memakai stack lain, struktur fitur dan skema database di bawah tetap berlaku.

## 3. Peran Pengguna dan Hak Akses

| Fitur | Guest | Anggota | Admin |
|---|:-:|:-:|:-:|
| Registrasi, login | ✔ | | |
| Lihat/cari robot | | ✔ | ✔ |
| Pinjam unit | | ✔ | |
| Lihat pinjaman sendiri | | ✔ | |
| Lihat/ubah profil sendiri | | ✔ | ✔ |
| CRUD robot, unit, kategori, anggota | | | ✔ |
| Lihat semua unit yang dipinjam | | | ✔ |
| Proses pengembalian | | | ✔ |
| Lihat dan cetak riwayat | | | ✔ |

## 4. Kebutuhan Fungsional

### 4.1 Autentikasi dan Akses

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-01 | Anggota dapat registrasi mandiri (nama, email, password, data profil) | Tinggi |
| FR-02 | Login dengan email dan password | Tinggi |
| FR-03 | Semua halaman selain login/registrasi memerlukan autentikasi | Tinggi |
| FR-04 | Akses dibatasi berdasarkan role (middleware); anggota yang membuka halaman admin mendapat 403 | Tinggi |
| FR-05 | Logout | Tinggi |

### 4.2 Profil

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-06 | Satu user hanya memiliki satu profil | Tinggi |
| FR-07 | User dapat melihat dan mengubah profilnya sendiri, tidak dapat mengubah profil orang lain | Tinggi |

### 4.3 Katalog dan Pencarian (Anggota & Admin)

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-08 | Menampilkan daftar robot beserta kategori dan jumlah unit tersedia | Tinggi |
| FR-09 | Pencarian robot berdasarkan nama | Tinggi |
| FR-10 | Pencarian/filter robot berdasarkan kategori | Tinggi |
| FR-11 | Detail robot menampilkan daftar unit (kode unit + status) | Sedang |

### 4.4 Peminjaman (Anggota)

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-12 | Anggota dapat meminjam unit berstatus `tersedia` | Tinggi |
| FR-13 | Sistem menolak jika anggota sudah memiliki 2 pinjaman aktif | Tinggi |
| FR-14 | Sistem menetapkan `tanggal_jatuh_tempo` = tanggal pinjam + 5 hari | Tinggi |
| FR-15 | Status unit berubah menjadi `dipinjam` setelah dipinjam | Tinggi |
| FR-16 | Anggota melihat daftar pinjaman miliknya (aktif dan selesai) beserta jatuh tempo dan denda | Tinggi |
| FR-17 | Pinjaman aktif yang melewati jatuh tempo ditandai "Terlambat", dengan estimasi denda berjalan | Sedang |

### 4.5 Pengembalian (Admin)

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-18 | Admin melihat daftar semua unit yang sedang dipinjam (peminjam, kode unit, jatuh tempo) | Tinggi |
| FR-19 | Admin memproses pengembalian dari daftar tersebut | Tinggi |
| FR-20 | Sistem menghitung `hari_terlambat` dan `denda` otomatis saat pengembalian | Tinggi |
| FR-21 | Status unit kembali menjadi `tersedia`, status pinjaman menjadi `dikembalikan`, dan admin pemroses dicatat | Tinggi |
| FR-22 | Anggota tidak memiliki tombol pengembalian | Tinggi |

### 4.6 Manajemen Data Master (Admin)

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-23 | CRUD Robot (nama, deskripsi, foto, pilih beberapa kategori) | Tinggi |
| FR-24 | CRUD Unit Robot (robot, kode unit unik, status) | Tinggi |
| FR-25 | CRUD Kategori | Tinggi |
| FR-26 | CRUD Anggota (akun + profil) | Tinggi |
| FR-27 | Penghapusan ditolak dengan pesan jelas jika data masih memiliki pinjaman aktif/relasi | Tinggi |

### 4.7 Riwayat dan Laporan (Admin)

| ID | Kebutuhan | Prioritas |
|---|---|---|
| FR-28 | Admin melihat riwayat seluruh peminjaman | Tinggi |
| FR-29 | Filter riwayat berdasarkan anggota dan rentang tanggal | Sedang |
| FR-30 | Cetak riwayat (tampilan print-friendly atau PDF) | Tinggi |

## 5. User Stories dan Acceptance Criteria

**US-01 — Registrasi**
Sebagai pengunjung, saya ingin mendaftar sebagai anggota agar dapat meminjam robot.
- Given form valid, When submit, Then akun role `anggota` dan profil terbentuk, lalu diarahkan ke dashboard.
- Given email sudah terdaftar, Then muncul pesan error validasi.

**US-02 — Mencari robot**
Sebagai anggota, saya ingin mencari robot lewat nama atau kategori.
- Given kata kunci "Aya", Then hanya robot bernama mengandung "Aya" yang tampil.
- Given memilih kategori "Romantis", Then hanya robot berkategori tersebut yang tampil.

**US-03 — Meminjam robot**
Sebagai anggota, saya ingin meminjam unit robot.
- Given pinjaman aktif < 2 dan unit tersedia, Then pinjaman tercatat dengan jatuh tempo +5 hari.
- Given sudah 2 pinjaman aktif, Then ditolak dengan pesan "Batas maksimal 2 unit tercapai".
- Given unit sudah dipinjam, Then ditolak.

**US-04 — Mengembalikan robot**
Sebagai admin, saya ingin memproses pengembalian dan denda dihitung otomatis.
- Given dikembalikan sebelum/tepat pada jatuh tempo, Then denda Rp0.
- Given terlambat 3 hari, Then denda Rp15.000 dan ditampilkan pada ringkasan.

**US-05 — Mengelola data**
Sebagai admin, saya ingin menambah, mengubah, dan menghapus robot, unit, kategori, dan anggota.
- Given kode unit sudah ada, Then penyimpanan ditolak.
- Given robot memiliki 3 kategori, Then ketiganya tersimpan dan tampil di detail.

**US-06 — Riwayat**
Sebagai admin, saya ingin mencetak riwayat peminjaman anggota.
- Given memilih anggota dan periode, Then tabel riwayat tampil dan dapat dicetak.

## 6. Aturan Validasi per Field

### Registrasi / Anggota
| Field | Aturan |
|---|---|
| name | required, string, max 100 |
| email | required, email, unique:users |
| password | required, min 8, confirmed (saat registrasi) |
| nama_lengkap | required, string, max 150 |
| no_telepon | required, numeric/regex `^[0-9+]{8,15}$` |
| alamat | required, string |
| tanggal_lahir | nullable, date, before:today |

### Kategori
| Field | Aturan |
|---|---|
| nama | required, string, max 100, unique:categories (ignore saat update) |
| deskripsi | nullable, string |

### Robot
| Field | Aturan |
|---|---|
| nama | required, string, max 150 |
| deskripsi | nullable, string |
| foto | nullable, image, mimes jpg/png, max 2 MB |
| category_ids | required, array, min 1; setiap item exists:categories,id |

### Unit Robot
| Field | Aturan |
|---|---|
| robot_id | required, exists:robots,id |
| kode_unit | required, string, max 50, unique:robot_units (ignore saat update) |
| status | required, in:tersedia,dipinjam,rusak |

### Peminjaman
| Field | Aturan |
|---|---|
| robot_unit_id | required, exists:robot_units,id; status unit = `tersedia` |
| (aturan bisnis) | pinjaman aktif user < 2 |

### Pengembalian
| Field | Aturan |
|---|---|
| loan_id | required, exists:loans,id; status = `dipinjam` |

## 7. Daftar Halaman dan Rute

| Halaman | Rute | Akses |
|---|---|---|
| Login | `/login` | Guest |
| Registrasi | `/register` | Guest |
| Dashboard | `/dashboard` | Semua login |
| Profil saya | `/profile` | Semua login |
| Katalog + pencarian | `/robots` | Anggota, Admin |
| Detail robot | `/robots/{id}` | Anggota, Admin |
| Pinjam unit | `POST /loans` | Anggota |
| Pinjaman saya | `/my-loans` | Anggota |
| Kelola robot | `/admin/robots` | Admin |
| Kelola unit | `/admin/units` | Admin |
| Kelola kategori | `/admin/categories` | Admin |
| Kelola anggota | `/admin/members` | Admin |
| Unit sedang dipinjam | `/admin/loans/active` | Admin |
| Proses pengembalian | `POST /admin/loans/{id}/return` | Admin |
| Riwayat peminjaman | `/admin/loans/history` | Admin |
| Cetak riwayat | `/admin/loans/history/print` | Admin |

## 8. Kebutuhan Non-Fungsional

| ID | Kategori | Kebutuhan |
|---|---|---|
| NFR-01 | Keamanan | Password di-hash; proteksi CSRF; otorisasi role di server |
| NFR-02 | Keamanan | Anggota tidak dapat mengakses data pinjaman/profil user lain (cek kepemilikan) |
| NFR-03 | Integritas | Peminjaman dan pengembalian dalam DB transaction |
| NFR-04 | Usability | Responsif (desktop & mobile), pesan error validasi berbahasa Indonesia |
| NFR-05 | Performa | Halaman daftar memakai pagination (10–15 data/halaman) |
| NFR-06 | Maintainability | Logika denda terpusat pada satu class/service |
| NFR-07 | Format | Mata uang `Rp` dengan pemisah ribuan; tanggal format `dd-mm-yyyy` |

## 9. Data Seed Awal

| Data | Isi |
|---|---|
| **Admin** | admin@robotpacar.test / password `password` (ganti sebelum produksi) |
| **Anggota contoh** | 2–3 anggota lengkap dengan profil |
| **Kategori** | Romantis, Humoris, Penyayang, Pendiam, Petualang |
| **Robot** | Aya (Romantis, Penyayang), Rio (Humoris, Petualang), Luna (Pendiam, Penyayang), Dio (Humoris) |
| **Unit** | Aya: `AYA-001`, `AYA-002`; Rio: `RIO-001`, `RIO-002`, `RIO-003`; Luna: `LUN-001`; Dio: `DIO-001`, `DIO-002` |
| **Pinjaman contoh** | 1 pinjaman aktif normal, 1 aktif terlambat, 1 selesai berdenda (untuk demo) |

## 10. Skenario Uji (Bahan UAT)

| No | Skenario | Hasil yang Diharapkan |
|---|---|---|
| 1 | Akses `/robots` tanpa login | Redirect ke login |
| 2 | Anggota buka `/admin/robots` | 403 Forbidden |
| 3 | Registrasi dengan email duplikat | Gagal, pesan validasi |
| 4 | Tambah unit dengan kode yang sudah ada | Gagal, pesan validasi |
| 5 | Anggota pinjam unit ke-3 | Ditolak |
| 6 | Anggota pinjam unit berstatus `dipinjam` | Ditolak |
| 7 | Pengembalian hari ke-5 | Denda Rp0 |
| 8 | Pengembalian hari ke-8 | Denda Rp15.000 |
| 9 | Hapus kategori yang dipakai robot | Ditolak / ditangani sesuai aturan |
| 10 | Anggota ubah profil milik user lain | 403 |
| 11 | Cetak riwayat | Tampilan cetak muncul dengan data sesuai filter |
| 12 | Pencarian nama dan kategori | Hasil sesuai kata kunci |

## 11. Deliverables (Sesuai Soal)

1. `README.md` berisi: nama kelompok, nama team, nama project, list fitur, screenshot skema database, screen recording demo.
2. Migration dan seeder.
3. Dokumen: BRD, ERD/Flow Chart, PRD, hasil UAT, manual book, dokumentasi project.
4. Seluruh hasil di-push ke GitLab.

## 12. Alur Pengerjaan

`Nama Project → BRD → Flow Chart / ERD → PRD → Design → Pengembangan → UAT → Manual Book → Dokumentasi → Push GitLab`

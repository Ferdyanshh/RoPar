# Flow Chart

## Sistem Peminjaman Robot Pacar

Dokumen ini berisi flow chart proses utama sistem. Dokumen terkait: [BRD](BRD.md), [ERD](ERD.md), [PRD](PRD.md).

---

## 1. Gambaran Umum Sistem

```mermaid
flowchart TD
    Start([Mulai]) --> Akses[Buka website]
    Akses --> Login{Sudah login?}
    Login -- Belum --> Auth[Halaman Login / Registrasi]
    Auth --> Valid{Kredensial valid?}
    Valid -- Tidak --> Auth
    Valid -- Ya --> Role{Role pengguna}
    Login -- Sudah --> Role

    Role -- Anggota --> MA[Dashboard Anggota]
    Role -- Admin --> MB[Dashboard Admin]

    MA --> A1[Cari robot]
    MA --> A2[Pinjam robot]
    MA --> A3[Lihat pinjaman saya]
    MA --> A4[Ubah profil]

    MB --> B1[Kelola robot, unit, kategori, anggota]
    MB --> B2[Lihat unit yang dipinjam]
    MB --> B3[Proses pengembalian]
    MB --> B4[Riwayat dan cetak]

    A1 --> End([Selesai])
    A2 --> End
    A3 --> End
    A4 --> End
    B1 --> End
    B2 --> End
    B3 --> End
    B4 --> End
```

## 2. Registrasi dan Login

```mermaid
flowchart TD
    S([Mulai]) --> P{Punya akun?}

    P -- Tidak --> R1[Buka halaman registrasi]
    R1 --> R2[Isi nama, email, password, data profil]
    R2 --> R3{Validasi form OK?}
    R3 -- Tidak --> R4[Tampilkan pesan error] --> R2
    R3 -- Ya --> R5[Simpan user role anggota]
    R5 --> R6[Simpan profil, satu user satu profil]
    R6 --> L1

    P -- Ya --> L1[Buka halaman login]
    L1 --> L2[Isi email dan password]
    L2 --> L3{Kredensial benar?}
    L3 -- Tidak --> L4[Tampilkan pesan gagal login] --> L2
    L3 -- Ya --> L5{Role?}
    L5 -- Admin --> L6[Dashboard Admin]
    L5 -- Anggota --> L7[Dashboard Anggota]
    L6 --> E([Selesai])
    L7 --> E
```

## 3. Pencarian Robot

```mermaid
flowchart TD
    S([Mulai]) --> A[Buka halaman katalog robot]
    A --> B[Masukkan nama atau pilih kategori]
    B --> C[Sistem query robot + kategori]
    C --> D{Ada hasil?}
    D -- Ya --> E[Tampilkan daftar robot dan jumlah unit tersedia]
    D -- Tidak --> F[Tampilkan pesan data tidak ditemukan]
    E --> G[Pilih robot untuk lihat detail unit]
    G --> H([Selesai])
    F --> H
```

## 4. Peminjaman Robot (Anggota)

```mermaid
flowchart TD
    S([Mulai]) --> A[Anggota buka detail robot]
    A --> B[Pilih unit berstatus tersedia]
    B --> C[Klik Pinjam]
    C --> D{Anggota terdaftar dan login?}
    D -- Tidak --> X1[Redirect ke login]
    D -- Ya --> E{Tanggal mulai mendatang?}
    E -- Ya --> F{Reservasi anggota < kuota reservasi?}
    E -- Tidak --> G{Pinjaman anggota < kuota pinjaman?}
    F -- Tidak --> X2[Tolak: kuota reservasi tercapai]
    G -- Tidak --> X3[Tolak: kuota pinjaman tercapai]
    F -- Ya --> H{Status unit = tersedia?}
    G -- Ya --> H
    H -- Tidak --> X4[Tolak: unit tidak tersedia]
    H -- Ya --> I[Mulai DB transaction]
    I --> J[Buat data loan]
    J --> K[tanggal_jatuh_tempo = tanggal_pinjam + 5 hari]
    K --> L[Ubah status unit menjadi dipinjam]
    L --> M[Commit transaction]
    M --> N[Tampilkan konfirmasi dan jatuh tempo]
    N --> E1([Selesai])
    X1 --> E1
    X2 --> E1
    X3 --> E1
    X4 --> E1
```

## 5. Pengembalian Robot dan Perhitungan Denda (Admin)

```mermaid
flowchart TD
    S([Mulai]) --> A[Anggota menghubungi admin]
    A --> B[Admin buka daftar unit yang dipinjam]
    B --> C[Admin pilih pinjaman]
    C --> D[Klik Proses Pengembalian]
    D --> E{Loan berstatus dipinjam?}
    E -- Tidak --> X[Tolak: sudah dikembalikan]
    E -- Ya --> F[tanggal_kembali = hari ini]
    F --> G[Hitung selisih hari: tanggal_kembali - jatuh_tempo]
    G --> H{Selisih lebih dari 0?}
    H -- Ya --> I[hari_terlambat = selisih]
    I --> J[denda = hari_terlambat x Rp5.000]
    H -- Tidak --> K[hari_terlambat = 0, denda = Rp0]
    J --> L[Mulai DB transaction]
    K --> L
    L --> M[Update loan: status dikembalikan, denda, diproses_oleh]
    M --> N[Update status unit menjadi tersedia]
    N --> O[Commit transaction]
    O --> P[Tampilkan ringkasan pengembalian dan denda]
    P --> E1([Selesai])
    X --> E1
```

## 6. Manajemen Data Master (Admin)

Berlaku untuk robot, unit robot, kategori, dan anggota.

```mermaid
flowchart TD
    S([Mulai]) --> A[Admin pilih menu data master]
    A --> B{Aksi}

    B -- Tambah --> C1[Isi form]
    C1 --> C2{Validasi OK?}
    C2 -- Tidak --> C3[Tampilkan error, mis. kode unit duplikat] --> C1
    C2 -- Ya --> C4[Simpan data]

    B -- Ubah --> D1[Pilih data dan ubah form]
    D1 --> D2{Validasi OK?}
    D2 -- Tidak --> D3[Tampilkan error] --> D1
    D2 -- Ya --> D4[Update data]

    B -- Hapus --> E1[Pilih data dan konfirmasi hapus]
    E1 --> E2{Masih punya pinjaman aktif atau relasi?}
    E2 -- Ya --> E3[Tolak penghapusan dengan pesan]
    E2 -- Tidak --> E4[Hapus data]

    C4 --> F([Selesai])
    D4 --> F
    E3 --> F
    E4 --> F
```

## 7. Daftar Pinjaman

```mermaid
flowchart TD
    S([Mulai]) --> A{Role pengguna}
    A -- Admin --> B[Query semua loan berstatus dipinjam]
    B --> C[Tampilkan peminjam, kode unit, jatuh tempo, status terlambat]
    A -- Anggota --> D[Query loan milik user_id sendiri]
    D --> E[Tampilkan pinjaman aktif dan selesai beserta denda]
    C --> F([Selesai])
    E --> F
```

## 8. Riwayat dan Cetak (Admin)

```mermaid
flowchart TD
    S([Mulai]) --> A[Admin buka halaman riwayat peminjaman]
    A --> B[Pilih filter anggota dan rentang tanggal]
    B --> C[Sistem query riwayat loan]
    C --> D{Ada data?}
    D -- Tidak --> E[Tampilkan pesan data kosong]
    D -- Ya --> F[Tampilkan tabel riwayat]
    F --> G{Cetak?}
    G -- Ya --> H[Buka tampilan cetak / PDF]
    H --> I[Cetak atau simpan]
    G -- Tidak --> J([Selesai])
    E --> J
    I --> J
```

## 9. Ubah Profil

```mermaid
flowchart TD
    S([Mulai]) --> A[User buka halaman profil]
    A --> B[Sistem ambil profil milik user login]
    B --> C[User ubah data]
    C --> D{Validasi OK?}
    D -- Tidak --> E[Tampilkan error] --> C
    D -- Ya --> F[Update profil milik sendiri]
    F --> G[Tampilkan pesan berhasil]
    G --> H([Selesai])
```

## 10. Siklus Status Unit

```mermaid
stateDiagram-v2
    [*] --> tersedia
    tersedia --> dipinjam: Anggota meminjam
    dipinjam --> tersedia: Admin memproses pengembalian
    tersedia --> rusak: Admin menandai rusak
    rusak --> tersedia: Admin memperbaiki status
```

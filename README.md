# Sistem Manajemen Data Interaktif — Manajemen Data Mahasiswa

Aplikasi web dinamis **Sistem Manajemen Data Mahasiswa** yang dibangun untuk memenuhi proyek mandiri **Ujian Tengah Semester (UTS)** mata kuliah **Pemrograman Web 1 (3 SKS)** pada Program Studi Sistem Informasi, Fakultas Teknologi Informasi, **Universitas Adhirajasa Reswara Sanjaya (ARS University)**.

---

## 1. Fitur Utama Aplikasi

1. **Input Data Mahasiswa Baru (`POST`)**:
   - Formulir pendaftaran mahasiswa lengkap dengan input: **NIM**, **Nama Lengkap**, **Email Akademik**, **Program Studi** (`<select>`), **Semester** (`<input type="number">`), **Jenis Kelamin** (`<input type="radio">`), **Minat & Bidang Keahlian** (`<input type="checkbox" name="hobi[]">`), dan **Alamat Domisili** (`<textarea>`).
2. **Daftar, Pencarian, & Filter Data Mahasiswa (`GET`)**:
   - Menampilkan daftar seluruh mahasiswa beserta ringkasan statistik akademik secara real-time.
   - Mendukung pencarian kata kunci (`?q=...`), filter Program Studi (`?prodi=...`), dan filter Semester (`?semester=...`) menggunakan metode **HTTP GET** sehingga URL pencarian dapat disimpan (*bookmark*).
3. **Edit / Pembaruan Data Mahasiswa (`GET` + `POST`)**:
   - Mengambil data mahasiswa yang dipilih melalui parameter `?action=edit&nim=...` untuk mengisi formulir secara otomatis (*pre-filled*), lalu memproses pembaruan data melalui **HTTP POST**.
4. **Hapus Data Mahasiswa (`POST`)**:
   - Menghapus data mahasiswa menggunakan metode **HTTP POST** dilengkapi proteksi token CSRF dan dialog konfirmasi di sisi klien.
5. **Interaktivitas Client-Side (Vanilla JavaScript DOM)**:
   - **Validasi Form Real-Time** pada event `input`, `change`, dan `submit` (`event.preventDefault()`).
   - **Toggle Tema Gelap / Terang (Dark/Light Mode)** dengan `classList.toggle('dark-mode')` dan penyimpanan preferensi permanen di `localStorage`.
   - **Pencarian Cepat (Live DOM Search)** pada tabel mahasiswa dan penghitung karakter alamat secara langsung.
6. **Keamanan & Arsitektur Server-Side (PHP 8)**:
   - **Validasi Server-Side** (`filter_var` untuk email dan integer, pemeriksaan format NIM & duplikasi NIM).
   - **Sanitasi Anti-XSS** menggunakan `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
   - **Pola PRG (*Post-Redirect-Get*)** untuk mencegah duplikasi pengiriman form saat halaman di-*refresh* (F5).
   - **Sticky Form & Flash Message** berbasis `$_SESSION`.
   - **CSRF Token Protection** menggunakan `random_bytes(32)` dan `hash_equals()`.

---

## 2. Teknologi yang Digunakan

| Lapisan | Teknologi | Implementasi Materi |
| :--- | :--- | :--- |
| **Struktur Dokumen** | HTML5 Semantik | `<header>`, `<nav>`, `<main>`, `<section>`, `<article>`, `<footer>`, `<table>`, `<form>` (Pertemuan 2) |
| **Tata Letak & Gaya** | Pure CSS3 (Tanpa Framework) | CSS Custom Properties, Box Model, **Flexbox (1D)**, **CSS Grid (2D)**, **Mobile-First RWD** (Pertemuan 3 & 4) |
| **Interaktivitas Klien** | Vanilla JavaScript (ES6+) | `querySelector`, `addEventListener`, `classList`, `localStorage`, Validasi DOM (Pertemuan 5) |
| **Pemrosesan Server** | PHP 8 Native | Modularisasi `require_once`/`include_once`, `$_GET`, `$_POST`, `$_SESSION`, JSON Storage (Pertemuan 1, 6, & 7) |

---

## 3. Struktur Folder Proyek

```text
pendataan_mahasiswa/
├── assets/
│   ├── css/
│   │   └── style.css          # Stylesheet Pure CSS3 (Mobile-First, Flexbox, Grid, Dark Mode)
│   └── js/
│       └── app.js             # Skrip Vanilla JS (Validasi Real-Time, Dark Mode, Live Filter)
├── data/
│   └── mahasiswa.json         # Penyimpanan data mahasiswa persisten berformat JSON
├── docs/
│   ├── Laporan Project [Nama Mahasiswa].md    # Laporan proyek dalam format Markdown
│   ├── Laporan Project [Nama Mahasiswa].html  # Laporan proyek siap cetak (A4, Times New Roman 12pt, spasi 1.5)
│   └── screenshots/                           # Direktori tangkapan layar Mobile & Desktop
├── includes/
│   ├── functions.php          # Fungsi bisnis: CRUD JSON, validasi, sanitasi XSS, Flash, CSRF
│   ├── header.php             # Template HTML5 <head>, <header>, dan <nav>
│   └── footer.php             # Template HTML5 <footer> dan pemanggilan skrip JS
├── index.php                  # Halaman utama (Statistik, Form Input/Edit, Filter GET, Tabel Data)
├── proses.php                 # Controller pemrosesan request POST (Create, Update, Delete + PRG)
└── README.md                  # Dokumentasi repositori
```

---

## 4. Cara Menjalankan Aplikasi di XAMPP / Laragon

1. Pastikan **XAMPP** atau **Laragon** telah terinstal dan layanan **Apache** sedang berjalan (*Running*).
2. Letakkan folder `pendataan_mahasiswa` di dalam direktori web server:
   - **XAMPP**: `C:\xampp\htdocs\pendataan_mahasiswa`
   - **Laragon**: `C:\laragon\www\pendataan_mahasiswa`
3. Buka browser dan akses alamat berikut:
   ```text
   http://localhost/pendataan_mahasiswa/
   ```
4. Pastikan folder `data/` memiliki izin tulis (*write permission*) agar berkas `data/mahasiswa.json` dapat diperbarui saat menambah, mengubah, atau menghapus data mahasiswa.

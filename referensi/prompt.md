# Prompt Pengembangan Proyek UTS: Sistem Manajemen Data Mahasiswa

> Dokumen ini disusun berdasarkan ketentuan pada [Tugas.md](referensi/Tugas.md) dan materi pembelajaran **Pemrograman Web 1 (Pertemuan 1–7)** di folder `referensi/materi/`.

---

## 1. Peran & Konteks Proyek

Bertindaklah sebagai **Full-Stack Web Developer** yang membangun aplikasi web dinamis berjudul **"Sistem Manajemen Data Interaktif"** dengan tema **Manajemen Data Mahasiswa (Input, Edit, & Daftar Data Mahasiswa)** untuk memenuhi Ujian Tengah Semester (UTS) Mata Kuliah **Pemrograman Web 1 (3 SKS)** — Program Studi Sistem Informasi, Fakultas Teknologi Informasi, Universitas Adhirajasa Reswara Sanjaya (ARS University).

Aplikasi dijalankan di lingkungan server lokal **XAMPP** (`http://localhost/pendataan_mahasiswa/`) tanpa database eksternal (atau menggunakan penyimpanan berkas terstruktur seperti JSON/Session agar fitur Input, Edit, Hapus, Pencarian, dan Daftar Data Mahasiswa berfungsi penuh dan persisten).

---

## 2. Tujuan & Fitur Utama Aplikasi

Bangun aplikasi web **Manajemen Data Mahasiswa** yang mencakup fitur:
1. **Input Data Mahasiswa (Create — `POST`)**:
   - Form input terstruktur: **NIM** (unik, angka/kode), **Nama Lengkap**, **Program Studi** (`<select>`: Sistem Informasi, Teknik Informatika, dll.), **Semester** (`<input type="number">` / `<select>`), **Jenis Kelamin** (`<input type="radio">`), **Minat/Hobi** (`<input type="checkbox" name="hobi[]">` sebagai array), dan **Alamat** (`<textarea>`).
2. **Daftar & Pencarian/Filter Data Mahasiswa (Read & Filter — `GET`)**:
   - Menampilkan daftar mahasiswa dalam bentuk tabel semantik (`<table>`) dan/atau kartu responsif.
   - Fitur pencarian (`?q=...`) dan filter berdasarkan Program Studi (`?prodi=...`) menggunakan metode **HTTP GET** agar URL dapat di-*bookmark*.
3. **Edit Data Mahasiswa (Update — `GET` untuk memuat data ke form + `POST` untuk menyimpan perubahan)**:
   - Klik tombol **Edit** pada baris data (`?action=edit&nim=...`) untuk mengisi form secara otomatis (*pre-filled*), lalu kirim perubahan melalui `POST`.
4. **Hapus Data Mahasiswa (Delete — `POST`)**:
   - Menghapus data mahasiswa menggunakan form `POST` (bukan `GET`) dengan konfirmasi interaktif di sisi klien.
5. **Interaktivitas Client-Side (JavaScript DOM)**:
   - **Validasi Form Real-Time** (`input` & `submit` event dengan `e.preventDefault()`, menampilkan pesan kesalahan langsung di DOM).
   - **Toggle Tema (Dark/Light Mode)** menggunakan `classList.toggle()` dan penyimpanan preferensi di `localStorage`.
   - **Live Filter / Pencarian Cepat** atau **Ringkasan Statistik Mahasiswa** yang diperbarui secara dinamis di DOM.

---

## 3. Ketentuan Teknis Wajib (Mengacu pada `Tugas.md` & Materi Pertemuan 1–7)

### A. Struktur Dokumen HTML5 (Pertemuan 2)
- Gunakan `<!DOCTYPE html>`, `<html lang="id">`, `<meta charset="UTF-8">`, `<meta name="viewport" content="width=device-width, initial-scale=1.0">`, dan `<meta name="description">`.
- Gunakan **HTML5 Semantik** secara konsisten di seluruh halaman:
  - `<header>` (identitas aplikasi & kontrol tema),
  - `<nav>` (menu navigasi / tautan cepat),
  - `<main>` (konten utama),
  - `<section>` (bagian statistik, form input/edit, dan daftar mahasiswa),
  - `<article>` (kartu ringkasan / item informasi),
  - `<footer>` (hak cipta & informasi mata kuliah).
- Pastikan hanya ada **1 tag `<h1>`** per halaman dengan hierarki heading (`<h2>`, `<h3>`) yang terstruktur.
- Gunakan `<label for="...">` pada setiap input form demi aksesibilitas, atribut `name="..."` untuk PHP, dan `id="..."` untuk CSS/JS. Hindari *over-divitis*.

### B. Tata Letak & Desain Responsif CSS3 (Pertemuan 3 & 4)
- **DILARANG menggunakan framework CSS eksternal** (seperti Bootstrap atau Tailwind CSS). Wajib menggunakan **Pure/Vanilla CSS3** (`assets/css/style.css`).
- Terapkan **CSS Modern**:
  - **Flexbox (1D)** untuk navbar, perataan tombol aksi, grup form, dan header.
  - **CSS Grid (2D)** untuk kerangka tata letak utama, grid statistik, dan tata letak kartu/kolom.
  - Gunakan `gap`, CSS Custom Properties (`:root` variables untuk tema Light & Dark Mode), dan Box Model yang rapi.
- Terapkan pendekatan **Mobile-First Design**:
  - Gaya dasar (*default*) ditujukan untuk layar smartphone (`< 768px`, menu vertikal, 1 kolom, tabel *scrollable* secara horizontal agar tidak meluber).
  - Gunakan `@media (min-width: 768px)` dan `@media (min-width: 1024px)` untuk tampilan tablet dan desktop (menu horizontal, layout 2 kolom form + daftar atau grid multi-kolom).

### C. Interaktivitas Client-Side JavaScript (Pertemuan 5)
- Pisahkan kode JavaScript ke file eksternal (`assets/js/app.js`).
- Gunakan sintaks modern ES6+ (`const`, `let`, *arrow function*, strict comparison `===`). Hindari `var`.
- Gunakan `document.querySelector` / `document.querySelectorAll`, `addEventListener`, manipulasi teks (`innerText`), dan manipulasi class (`classList.add`, `classList.remove`, `classList.toggle`).
- Implementasikan minimal fitur berikut:
  1. **Validasi Form Real-Time**: Validasi NIM (hanya angka/panjang minimum), Nama (tidak kosong, minimal karakter), Email (format valid), dan pilihan wajib sebelum form dikirim ke server.
  2. **Dark/Light Mode Toggle**: Menyimpan preferensi tema ke `localStorage` sehingga tetap bertahan saat halaman di-*refresh*.
  3. **Konfirmasi Hapus & Feedback UI**: Dialog konfirmasi sebelum menghapus data dan auto-hide flash message.

### D. Pemrosesan Server-Side PHP & Keamanan Form (Pertemuan 1, 6, & 7)
- Pisahkan logika bisnis, fungsi bantuan, dan template tampilan secara modular menggunakan `require_once` / `include_once` (misal: `includes/functions.php`, `includes/header.php`, `includes/footer.php`).
- Selalu gunakan tag PHP standar (`<?php ... ?>`), jangan gunakan short tag `<?`.
- Gunakan strict comparison (`===`, `!==`), *null coalescing operator* (`??`), dan sintaks alternatif (`<?php if (...): ?> ... <?php endif; ?>`) pada template HTML.
- **Penanganan HTTP Methods**:
  - `$_GET` untuk pencarian (`q`), filter (`prodi`), dan mode edit (`action=edit&nim=...`).
  - `$_POST` untuk aksi tambah, ubah, dan hapus data mahasiswa.
- **Validasi Server-Side Wajib**: Validasi ulang seluruh input di PHP (`empty()`, `filter_var(..., FILTER_VALIDATE_EMAIL)`, `filter_var(..., FILTER_VALIDATE_INT)`, cek duplikasi NIM saat tambah data).
- **Keamanan & Pola Arsitektur**:
  - **Proteksi XSS**: Selalu bungkus setiap output data pengguna dengan `htmlspecialchars($nilai, ENT_QUOTES, 'UTF-8')`.
  - **PRG Pattern (Post-Redirect-Get)**: Setelah memproses `POST`, lakukan `header("Location: index.php"); exit;` untuk mencegah duplikasi pengiriman data saat browser di-*refresh* (F5).
  - **Sticky Form & Flash Message**: Gunakan `$_SESSION` (`session_start()`) untuk menyimpan pesan sukses/error (*flash data*) dan input lama (`$_SESSION['old']`) ketika validasi server gagal, lalu `unset()` setelah ditampilkan.
  - **CSRF Protection**: Sertakan token CSRF unik (`bin2hex(random_bytes(32))`) pada form `POST` dan validasi dengan `hash_equals()`.

---

## 4. Struktur Folder Repositori yang Disarankan

```text
pendataan_mahasiswa/
├── assets/
│   ├── css/
│   │   └── style.css          # Pure CSS3 (Mobile-First, Flexbox, Grid, Dark Mode)
│   └── js/
│       └── app.js             # Vanilla JS (DOM, Event Listener, Validasi, localStorage)
├── data/
│   └── mahasiswa.json         # Penyimpanan data mahasiswa (persisten, auto-created jika belum ada)
├── docs/
│   ├── Laporan Project [Nama Mahasiswa].md   # Draf laporan sesuai sistematika Tugas.md
│   └── screenshots/           # Tempat menyimpan screenshot tampilan Mobile & Desktop
├── includes/
│   ├── functions.php          # Helper: baca/simpan JSON, validasi, sanitasi, flash, CSRF
│   ├── header.php             # Semantik <head>, <header>, <nav>
│   └── footer.php             # Semantik <footer> & pemanggilan script JS
├── index.php                  # Halaman utama (Tampilan daftar, filter GET, & form input/edit)
├── proses.php                 # Controller pemrosesan form POST (Tambah, Edit, Hapus + PRG)
└── README.md                  # Dokumentasi repositori GitHub
```

---

## 5. Ketentuan Dokumentasi & Rubrik Penilaian

Pastikan proyek juga menyiapkan kelengkapan berikut sesuai **Bagian 3, 4, dan 5 `Tugas.md`**:
1. **`README.md` di root repositori**: Berisi deskripsi aplikasi, fitur, teknologi yang digunakan, struktur folder, dan cara menjalankan di XAMPP/Laragon.
2. **Folder `docs/`**:
   - Berisi draf laporan berjudul **`Laporan Project [Nama Mahasiswa]`** (siap dikonversi ke dokumen A4, font Times New Roman 12pt, spasi 1,5).
   - Sistematika isi laporan:
     1. **Identitas Lengkap** (Nama, NIM, Kelas, Mata Kuliah).
     2. **Analisis Kebutuhan Sistem** (Deskripsi singkat, tujuan, dan alur pengguna).
     3. **Dokumentasi Visual** (Placeholder/lampiran screenshot tampilan antarmuka versi **Mobile** & **Desktop**).
     4. **Penjelasan Struktur Kode & Alur Data** (Penjelasan HTML5 semantik, CSS3 Flexbox/Grid & Mobile-First, JavaScript DOM, dan alur PHP GET/POST + PRG + Sanitasi).
     5. **URL GitHub**.
3. **Target Rubrik Penilaian**:
   - Struktur HTML5 & CSS (25%)
   - Interaktivitas JS (25%)
   - Backend Processing PHP (30%)
   - Kelengkapan Dokumentasi (10%)
   - Repositori & Kerapian Kode (10%)

---

## 6. Instruksi Eksekusi

1. Bangun seluruh file aplikasi (`index.php`, `proses.php`, `includes/*`, `assets/css/style.css`, `assets/js/app.js`, `data/mahasiswa.json`) dengan kode yang bersih, terkomentar seperlunya, dan bebas dari error PHP/JS.
2. Isi `data/mahasiswa.json` dengan beberapa data dummy awal mahasiswa agar daftar langsung terlihat saat pertama kali dibuka.
3. Buat `README.md` dan kerangka laporan di `docs/Laporan Project [Nama Mahasiswa].md`.
4. Uji coba seluruh alur:
   - Tampilan responsif Mobile (`< 600px`) dan Desktop (`>= 1024px`).
   - Tambah, Edit, Hapus, Pencarian/Filter (`GET`), Validasi Client-Side & Server-Side, Dark Mode (`localStorage`), dan uji refresh (PRG Pattern).

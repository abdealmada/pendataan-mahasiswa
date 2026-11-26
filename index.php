<?php
declare(strict_types=1);

/**
 * Halaman Utama (index.php) — Sistem Manajemen Data Mahasiswa
 * Mengintegrasikan:
 * - HTML5 Semantik (<header>, <nav>, <main>, <section>, <article>, <footer>)
 * - Penanganan HTTP GET untuk Pencarian (?q=...), Filter (?prodi=...&semester=...), & Mode Edit (?action=edit&nim=...)
 * - Formulir POST dengan Sticky Form, Validasi Server-Side & Client-Side, serta Token CSRF
 */

require_once __DIR__ . '/includes/functions.php';

$allMahasiswa = getAllMahasiswa();
$statistik = hitungStatistikMahasiswa($allMahasiswa);
$daftarProdi = getDaftarProdi();
$daftarHobi = getDaftarHobi();

// Parameter HTTP GET untuk Pencarian & Filter
$searchQuery = trim((string) ($_GET['q'] ?? ''));
$filterProdi = trim((string) ($_GET['prodi'] ?? ''));
$filterSemester = trim((string) ($_GET['semester'] ?? ''));
$isFiltered = ($searchQuery !== '' || $filterProdi !== '' || $filterSemester !== '');

$mahasiswaTampil = filterMahasiswa($allMahasiswa, $searchQuery, $filterProdi, $filterSemester);

// Cek Mode Edit dari parameter HTTP GET (?action=edit&nim=...)
$action = trim((string) ($_GET['action'] ?? ''));
$editNim = trim((string) ($_GET['nim'] ?? ''));
$editingMahasiswa = null;
$isEditMode = false;

if ($action === 'edit' && $editNim !== '') {
    $editingMahasiswa = findMahasiswaByNim($editNim);
    if ($editingMahasiswa !== null) {
        $isEditMode = true;
    } else {
        setFlashMessage(
            'warning',
            'Data Tidak Ditemukan',
            'Mahasiswa dengan NIM ' . e($editNim) . ' tidak ditemukan di dalam sistem.'
        );
    }
}

// Ambil Flash Message & Sticky Form State dari Session
$flash = getFlashMessage();
$formState = consumeFormState();
$oldInput = $formState['old'];
$formErrors = $formState['errors'];

// Tentukan nilai field form (Prioritas: Sticky Form $_SESSION['old'] -> Data Edit -> Default Kosong)
$valNim = (string) ($oldInput['nim'] ?? ($editingMahasiswa['nim'] ?? ''));
$valNama = (string) ($oldInput['nama'] ?? ($editingMahasiswa['nama'] ?? ''));
$valEmail = (string) ($oldInput['email'] ?? ($editingMahasiswa['email'] ?? ''));
$valProdi = (string) ($oldInput['prodi'] ?? ($editingMahasiswa['prodi'] ?? ''));
$valSemester = (string) ($oldInput['semester'] ?? ($editingMahasiswa['semester'] ?? '5'));
$valGender = (string) ($oldInput['jenis_kelamin'] ?? ($editingMahasiswa['jenis_kelamin'] ?? ''));
$valHobi = isset($oldInput['hobi']) && is_array($oldInput['hobi'])
    ? $oldInput['hobi']
    : (isset($editingMahasiswa['hobi']) && is_array($editingMahasiswa['hobi']) ? $editingMahasiswa['hobi'] : []);
$valAlamat = (string) ($oldInput['alamat'] ?? ($editingMahasiswa['alamat'] ?? ''));

$csrfToken = getCsrfToken();

include_once __DIR__ . '/includes/header.php';
?>

<main class="main-content container">

    <?php if ($flash !== null): ?>
        <section class="flash-wrapper" aria-live="polite">
            <div class="alert alert-<?= e($flash['type']) ?>" id="flash-banner" role="status">
                <div class="alert-body">
                    <strong class="alert-title"><?= e($flash['title']) ?></strong>
                    <p class="alert-text"><?= e($flash['message']) ?></p>
                </div>
                <button type="button" class="alert-close" id="close-flash" aria-label="Tutup pesan notifikasi">&times;</button>
            </div>
        </section>
    <?php endif; ?>

    <!-- ===================================================================
         SECTION 1: RINGKASAN STATISTIK DATA MAHASISWA (<section> + <article>)
         =================================================================== -->
    <section id="ringkasan-statistik" class="stats-section" aria-labelledby="heading-statistik">
        <div class="section-bar">
            <div>
                <h2 id="heading-statistik" class="section-title">Ringkasan Akademik Mahasiswa</h2>
                <p class="section-subtitle">Rekapitulasi data mahasiswa aktif Fakultas Teknologi Informasi secara real-time.</p>
            </div>
            <span class="academic-pill">Tahun Akademik 2024/2025 &bull; Semester Ganjil</span>
        </div>

        <div class="stats-grid">
            <article class="stat-card stat-card-gold">
                <header class="stat-card-header">
                    <h3 class="stat-label">Total Mahasiswa</h3>
                    <span class="stat-tag tag-gold">Terdaftar</span>
                </header>
                <p class="stat-value" id="stat-total"><?= e((string) $statistik['total']) ?></p>
                <p class="stat-desc">Mahasiswa aktif di basis data</p>
            </article>

            <article class="stat-card">
                <header class="stat-card-header">
                    <h3 class="stat-label">Sistem Informasi</h3>
                    <span class="stat-tag">Prodi SI</span>
                </header>
                <p class="stat-value" id="stat-si"><?= e((string) $statistik['sistem_informasi']) ?></p>
                <p class="stat-desc">Mahasiswa Program Studi SI</p>
            </article>

            <article class="stat-card">
                <header class="stat-card-header">
                    <h3 class="stat-label">Teknik Informatika</h3>
                    <span class="stat-tag">Prodi TI</span>
                </header>
                <p class="stat-value" id="stat-ti"><?= e((string) $statistik['teknik_informatika']) ?></p>
                <p class="stat-desc">Mahasiswa Program Studi TI</p>
            </article>

            <article class="stat-card">
                <header class="stat-card-header">
                    <h3 class="stat-label">Semester Madya/Akhir</h3>
                    <span class="stat-tag">Smt &ge; 5</span>
                </header>
                <p class="stat-value" id="stat-lanjut"><?= e((string) $statistik['semester_lanjut']) ?></p>
                <p class="stat-desc">Menempuh semester 5 ke atas</p>
            </article>
        </div>
    </section>

    <!-- ===================================================================
         SECTION 2: FORM INPUT / EDIT DATA MAHASISWA (HTTP POST)
         =================================================================== -->
    <section id="form-mahasiswa" class="panel form-panel <?= $isEditMode ? 'panel-editing' : '' ?>" aria-labelledby="heading-form">
        <div class="panel-header">
            <div class="panel-heading-group">
                <span class="mode-badge <?= $isEditMode ? 'badge-edit' : 'badge-create' ?>">
                    <?= $isEditMode ? 'HTTP POST &bull; Mode Edit Data' : 'HTTP POST &bull; Input Data Baru' ?>
                </span>
                <h2 id="heading-form" class="panel-title">
                    <?= $isEditMode ? 'Edit Data Mahasiswa: ' . e((string) ($editingMahasiswa['nama'] ?? '')) : 'Formulir Pendataan Mahasiswa' ?>
                </h2>
                <p class="panel-subtitle">
                    Lengkapi seluruh kolom bertanda bintang (<span class="required-mark">*</span>). Validasi dilakukan secara real-time di browser dan diverifikasi ulang di server.
                </p>
            </div>
            <?php if ($isEditMode): ?>
                <a href="index.php" class="btn btn-outline">Batal Edit</a>
            <?php endif; ?>
        </div>

        <form id="student-form" action="proses.php" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="form_action" value="<?= $isEditMode ? 'update' : 'create' ?>">
            <?php if ($isEditMode): ?>
                <input type="hidden" name="original_nim" value="<?= e((string) $editingMahasiswa['nim']) ?>">
            <?php endif; ?>

            <div class="form-columns">
                <!-- KOLOM KIRI: DATA IDENTITAS & AKADEMIK -->
                <div class="form-col">
                    <h3 class="form-section-heading">1. Identitas &amp; Akademik</h3>

                    <div class="form-row-split">
                        <!-- NIM -->
                        <div class="form-group <?= isset($formErrors['nim']) ? 'has-error' : '' ?>">
                            <label for="nim" class="form-label">
                                Nomor Induk Mahasiswa (NIM) <span class="required-mark" aria-hidden="true">*</span>
                            </label>
                            <input
                                type="text"
                                id="nim"
                                name="nim"
                                class="form-control"
                                inputmode="numeric"
                                pattern="[0-9]{6,12}"
                                minlength="6"
                                maxlength="12"
                                placeholder="Contoh: 17220005"
                                value="<?= e($valNim) ?>"
                                required
                                aria-describedby="error-nim"
                            >
                            <small class="field-error" id="error-nim" role="alert">
                                <?= isset($formErrors['nim']) ? e($formErrors['nim']) : '' ?>
                            </small>
                        </div>

                        <!-- Semester -->
                        <div class="form-group <?= isset($formErrors['semester']) ? 'has-error' : '' ?>">
                            <label for="semester" class="form-label">
                                Semester Aktif <span class="required-mark" aria-hidden="true">*</span>
                            </label>
                            <input
                                type="number"
                                id="semester"
                                name="semester"
                                class="form-control"
                                min="1"
                                max="14"
                                value="<?= e($valSemester) ?>"
                                required
                                aria-describedby="error-semester"
                            >
                            <small class="field-error" id="error-semester" role="alert">
                                <?= isset($formErrors['semester']) ? e($formErrors['semester']) : '' ?>
                            </small>
                        </div>
                    </div>

                    <!-- Nama Lengkap -->
                    <div class="form-group <?= isset($formErrors['nama']) ? 'has-error' : '' ?>">
                        <label for="nama" class="form-label">
                            Nama Lengkap Mahasiswa <span class="required-mark" aria-hidden="true">*</span>
                        </label>
                        <input
                            type="text"
                            id="nama"
                            name="nama"
                            class="form-control"
                            minlength="3"
                            maxlength="100"
                            placeholder="Masukkan nama lengkap sesuai Kartu Tanda Mahasiswa"
                            value="<?= e($valNama) ?>"
                            required
                            aria-describedby="error-nama"
                        >
                        <small class="field-error" id="error-nama" role="alert">
                            <?= isset($formErrors['nama']) ? e($formErrors['nama']) : '' ?>
                        </small>
                    </div>

                    <div class="form-row-equal">
                        <!-- Email Akademik -->
                        <div class="form-group <?= isset($formErrors['email']) ? 'has-error' : '' ?>">
                            <label for="email" class="form-label">
                                Email Akademik / Aktif <span class="required-mark" aria-hidden="true">*</span>
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                placeholder="nama@ars.ac.id"
                                value="<?= e($valEmail) ?>"
                                required
                                aria-describedby="error-email"
                            >
                            <small class="field-error" id="error-email" role="alert">
                                <?= isset($formErrors['email']) ? e($formErrors['email']) : '' ?>
                            </small>
                        </div>

                        <!-- Program Studi -->
                        <div class="form-group <?= isset($formErrors['prodi']) ? 'has-error' : '' ?>">
                            <label for="prodi" class="form-label">
                                Program Studi <span class="required-mark" aria-hidden="true">*</span>
                            </label>
                            <select id="prodi" name="prodi" class="form-control" required aria-describedby="error-prodi">
                                <option value="">-- Pilih Program Studi --</option>
                                <?php foreach ($daftarProdi as $prodiOption): ?>
                                    <option value="<?= e($prodiOption) ?>" <?= $valProdi === $prodiOption ? 'selected' : '' ?>>
                                        <?= e($prodiOption) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="field-error" id="error-prodi" role="alert">
                                <?= isset($formErrors['prodi']) ? e($formErrors['prodi']) : '' ?>
                            </small>
                        </div>
                    </div>

                    <!-- Jenis Kelamin (Radio Button) -->
                    <fieldset class="form-group form-fieldset <?= isset($formErrors['jenis_kelamin']) ? 'has-error' : '' ?>">
                        <legend class="form-label">
                            Jenis Kelamin <span class="required-mark" aria-hidden="true">*</span>
                        </legend>
                        <div class="radio-cards">
                            <label class="choice-card" for="gender-l">
                                <input
                                    type="radio"
                                    id="gender-l"
                                    name="jenis_kelamin"
                                    value="Laki-laki"
                                    <?= $valGender === 'Laki-laki' ? 'checked' : '' ?>
                                    required
                                >
                                <span>Laki-laki</span>
                            </label>
                            <label class="choice-card" for="gender-p">
                                <input
                                    type="radio"
                                    id="gender-p"
                                    name="jenis_kelamin"
                                    value="Perempuan"
                                    <?= $valGender === 'Perempuan' ? 'checked' : '' ?>
                                >
                                <span>Perempuan</span>
                            </label>
                        </div>
                        <small class="field-error" id="error-jenis_kelamin" role="alert">
                            <?= isset($formErrors['jenis_kelamin']) ? e($formErrors['jenis_kelamin']) : '' ?>
                        </small>
                    </fieldset>
                </div>

                <!-- KOLOM KANAN: MINAT KEAHLIAN & ALAMAT DOMISILI -->
                <div class="form-col">
                    <h3 class="form-section-heading">2. Peminatan &amp; Domisili</h3>

                    <!-- Minat / Hobi (Checkbox Array name="hobi[]") -->
                    <fieldset class="form-group form-fieldset <?= isset($formErrors['hobi']) ? 'has-error' : '' ?>">
                        <legend class="form-label">
                            Minat &amp; Bidang Keahlian (Pilih minimal 1) <span class="required-mark" aria-hidden="true">*</span>
                        </legend>
                        <div class="checkbox-pills">
                            <?php foreach ($daftarHobi as $idx => $hobiItem): ?>
                                <?php $hobiId = 'hobi-' . $idx; ?>
                                <label class="choice-pill" for="<?= e($hobiId) ?>">
                                    <input
                                        type="checkbox"
                                        id="<?= e($hobiId) ?>"
                                        name="hobi[]"
                                        value="<?= e($hobiItem) ?>"
                                        <?= in_array($hobiItem, $valHobi, true) ? 'checked' : '' ?>
                                    >
                                    <span><?= e($hobiItem) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <small class="field-error" id="error-hobi" role="alert">
                            <?= isset($formErrors['hobi']) ? e($formErrors['hobi']) : '' ?>
                        </small>
                    </fieldset>

                    <!-- Alamat Lengkap (Textarea) -->
                    <div class="form-group <?= isset($formErrors['alamat']) ? 'has-error' : '' ?>">
                        <div class="label-row">
                            <label for="alamat" class="form-label">
                                Alamat Domisili Lengkap <span class="required-mark" aria-hidden="true">*</span>
                            </label>
                            <span class="char-counter" id="alamat-counter">0 / 250</span>
                        </div>
                        <textarea
                            id="alamat"
                            name="alamat"
                            class="form-control"
                            rows="4"
                            minlength="10"
                            maxlength="250"
                            placeholder="Masukkan nama jalan, nomor rumah, kelurahan/kecamatan, dan kota/kabupaten (minimal 10 karakter)"
                            required
                            aria-describedby="error-alamat"
                        ><?= e($valAlamat) ?></textarea>
                        <small class="field-error" id="error-alamat" role="alert">
                            <?= isset($formErrors['alamat']) ? e($formErrors['alamat']) : '' ?>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Baris Tombol Aksi Form -->
            <div class="form-footer-bar">
                <p class="security-note">
                    Dilindungi dengan Token CSRF, Sanitasi <code>htmlspecialchars()</code>, &amp; Pola <em>Post-Redirect-Get</em>.
                </p>
                <div class="form-actions">
                    <?php if ($isEditMode): ?>
                        <a href="index.php" class="btn btn-outline">Batalkan Edit</a>
                    <?php else: ?>
                        <button type="reset" class="btn btn-outline" id="reset-btn">Kosongkan Form</button>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <?= $isEditMode ? 'Simpan Perubahan Data' : 'Simpan Data Mahasiswa' ?>
                    </button>
                </div>
            </div>
        </form>
    </section>

    <!-- ===================================================================
         SECTION 3: DAFTAR DATA MAHASISWA & PENCARIAN/FILTER (HTTP GET)
         =================================================================== -->
    <section id="daftar-mahasiswa" class="panel data-panel" aria-labelledby="heading-daftar">
        <div class="panel-header">
            <div class="panel-heading-group">
                <span class="mode-badge badge-get">HTTP GET &bull; Pencarian &amp; Filter URL</span>
                <h2 id="heading-daftar" class="panel-title">Daftar Data Mahasiswa</h2>
                <p class="panel-subtitle">
                    Gunakan kotak pencarian untuk filter instan di tabel, atau klik tombol <strong>Terapkan Filter</strong> untuk mengirim parameter query string GET.
                </p>
            </div>
            <div class="record-counter">
                Menampilkan <strong id="visible-count"><?= count($mahasiswaTampil) ?></strong> dari <strong><?= count($allMahasiswa) ?></strong> mahasiswa
            </div>
        </div>

        <!-- Form Pencarian & Filter menggunakan Metode HTTP GET (Pertemuan 7 Slide 4) -->
        <form action="index.php" method="GET" class="filter-bar" id="filter-form">
            <div class="filter-field filter-search">
                <label for="filter-q" class="filter-label">Kata Kunci Pencarian</label>
                <input
                    type="search"
                    id="filter-q"
                    name="q"
                    class="form-control"
                    placeholder="Ketik NIM, nama, email, prodi, atau keahlian..."
                    value="<?= e($searchQuery) ?>"
                    autocomplete="off"
                >
            </div>

            <div class="filter-field">
                <label for="filter-prodi" class="filter-label">Program Studi</label>
                <select id="filter-prodi" name="prodi" class="form-control">
                    <option value="">Semua Program Studi</option>
                    <?php foreach ($daftarProdi as $prodiOpt): ?>
                        <option value="<?= e($prodiOpt) ?>" <?= $filterProdi === $prodiOpt ? 'selected' : '' ?>>
                            <?= e($prodiOpt) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-field">
                <label for="filter-semester" class="filter-label">Semester</label>
                <select id="filter-semester" name="semester" class="form-control">
                    <option value="">Semua Semester</option>
                    <?php for ($smt = 1; $smt <= 8; $smt++): ?>
                        <option value="<?= $smt ?>" <?= $filterSemester === (string) $smt ? 'selected' : '' ?>>
                            Semester <?= $smt ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-secondary">Terapkan Filter</button>
                <?php if ($isFiltered): ?>
                    <a href="index.php#daftar-mahasiswa" class="btn btn-outline">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($isFiltered): ?>
            <div class="active-filter-banner">
                <span>
                    Parameter Query GET Aktif:
                    <?php if ($searchQuery !== ''): ?>
                        Kata kunci <strong>"<?= e($searchQuery) ?>"</strong>
                    <?php endif; ?>
                    <?php if ($filterProdi !== ''): ?>
                        &bull; Program Studi <strong><?= e($filterProdi) ?></strong>
                    <?php endif; ?>
                    <?php if ($filterSemester !== ''): ?>
                        &bull; Semester <strong><?= e($filterSemester) ?></strong>
                    <?php endif; ?>
                </span>
                <a href="index.php#daftar-mahasiswa" class="clear-link">Hapus Filter</a>
            </div>
        <?php endif; ?>

        <?php if (empty($mahasiswaTampil)): ?>
            <article class="empty-state">
                <h3 class="empty-title">Tidak Ada Data Mahasiswa yang Cocok</h3>
                <p class="empty-desc">
                    <?php if ($isFiltered): ?>
                        Pencarian dengan parameter GET yang Anda masukkan tidak menemukan hasil. Silakan atur ulang kata kunci atau filter Program Studi.
                    <?php else: ?>
                        Belum ada data mahasiswa yang tersimpan. Gunakan formulir di atas untuk menambahkan data mahasiswa baru.
                    <?php endif; ?>
                </p>
                <?php if ($isFiltered): ?>
                    <a href="index.php#daftar-mahasiswa" class="btn btn-outline">Tampilkan Semua Mahasiswa</a>
                <?php endif; ?>
            </article>
        <?php else: ?>
            <!-- Tabel Semantik HTML5 (Pertemuan 2 Slide 14) -->
            <div class="table-responsive">
                <table class="data-table" id="mahasiswa-table">
                    <thead>
                        <tr>
                            <th scope="col" class="col-nim">NIM</th>
                            <th scope="col" class="col-nama">Mahasiswa &amp; Kontak</th>
                            <th scope="col" class="col-prodi">Program Studi</th>
                            <th scope="col" class="col-hobi">Minat &amp; Keahlian</th>
                            <th scope="col" class="col-alamat">Alamat Domisili</th>
                            <th scope="col" class="col-actions">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="mahasiswa-tbody">
                        <?php foreach ($mahasiswaTampil as $mhs): ?>
                            <?php
                            $nimRow = (string) ($mhs['nim'] ?? '');
                            $isCurrentEdit = ($isEditMode && $nimRow === $editNim);
                            $hobiList = isset($mhs['hobi']) && is_array($mhs['hobi']) ? $mhs['hobi'] : [];
                            ?>
                            <tr
                                class="student-row <?= $isCurrentEdit ? 'row-editing' : '' ?>"
                                data-search="<?= e(strtolower($nimRow . ' ' . ($mhs['nama'] ?? '') . ' ' . ($mhs['email'] ?? '') . ' ' . ($mhs['prodi'] ?? '') . ' ' . implode(' ', $hobiList))) ?>"
                            >
                                <td class="cell-nim" data-label="NIM">
                                    <span class="nim-badge"><?= e($nimRow) ?></span>
                                </td>
                                <td class="cell-identity" data-label="Mahasiswa">
                                    <div class="student-name"><?= e($mhs['nama'] ?? '') ?></div>
                                    <div class="student-meta">
                                        <span class="gender-text"><?= e($mhs['jenis_kelamin'] ?? '') ?></span>
                                        <span aria-hidden="true">&bull;</span>
                                        <a href="mailto:<?= e($mhs['email'] ?? '') ?>" class="student-email"><?= e($mhs['email'] ?? '') ?></a>
                                    </div>
                                </td>
                                <td class="cell-prodi" data-label="Program Studi">
                                    <span class="prodi-pill"><?= e($mhs['prodi'] ?? '') ?></span>
                                    <div class="semester-text">Semester <?= e((string) ($mhs['semester'] ?? '')) ?></div>
                                </td>
                                <td class="cell-hobi" data-label="Minat &amp; Keahlian">
                                    <div class="tag-list">
                                        <?php foreach ($hobiList as $hobiTag): ?>
                                            <span class="skill-tag"><?= e($hobiTag) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="cell-alamat" data-label="Alamat Domisili">
                                    <p class="address-text"><?= e($mhs['alamat'] ?? '') ?></p>
                                </td>
                                <td class="cell-actions" data-label="Aksi">
                                    <div class="action-group">
                                        <a
                                            href="index.php?action=edit&amp;nim=<?= urlencode($nimRow) ?>#form-mahasiswa"
                                            class="btn btn-sm btn-edit"
                                            title="Edit data <?= e($mhs['nama'] ?? '') ?>"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            action="proses.php"
                                            method="POST"
                                            class="delete-form"
                                            data-nama="<?= e($mhs['nama'] ?? '') ?>"
                                            data-nim="<?= e($nimRow) ?>"
                                        >
                                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                            <input type="hidden" name="form_action" value="delete">
                                            <input type="hidden" name="nim" value="<?= e($nimRow) ?>">
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-delete"
                                                title="Hapus data <?= e($mhs['nama'] ?? '') ?>"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pesan kosong saat live-filter JS menyembunyikan semua baris -->
            <div id="js-no-results" class="empty-state hidden" aria-live="polite">
                <h3 class="empty-title">Pencarian Cepat Tidak Menemukan Hasil</h3>
                <p class="empty-desc">Tidak ada baris pada tabel saat ini yang cocok dengan kata kunci yang Anda ketik.</p>
            </div>
        <?php endif; ?>
    </section>

</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>

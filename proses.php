<?php
declare(strict_types=1);

/**
 * Controller Pemrosesan Data Form (POST) — Sistem Manajemen Data Mahasiswa
 * Menerapkan pola PRG (Post-Redirect-Get), Validasi Server-Side, CSRF Protection,
 * Sticky Form, dan Flash Message (Pertemuan 6 & 7).
 */

require_once __DIR__ . '/includes/functions.php';

// Tolak akses langsung via metode selain POST
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: index.php');
    exit;
}

// Validasi Token CSRF
$csrfToken = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '';
if (!verifyCsrfToken($csrfToken)) {
    setFlashMessage(
        'danger',
        'Akses Ditolak (CSRF Invalid)',
        'Sesi formulir telah kedaluwarsa atau token keamanan tidak valid. Silakan coba lagi.'
    );
    header('Location: index.php');
    exit;
}

$formAction = trim((string) ($_POST['form_action'] ?? ''));

// =========================================================================
// 1. AKSI HAPUS DATA MAHASISWA (DELETE via POST)
// =========================================================================
if ($formAction === 'delete') {
    $nimTarget = trim((string) ($_POST['nim'] ?? ''));
    $allMahasiswa = getAllMahasiswa();
    $found = false;
    $deletedName = '';

    $updatedList = [];
    foreach ($allMahasiswa as $mhs) {
        if ((string) ($mhs['nim'] ?? '') === $nimTarget) {
            $found = true;
            $deletedName = (string) ($mhs['nama'] ?? $nimTarget);
            continue;
        }
        $updatedList[] = $mhs;
    }

    if ($found && saveAllMahasiswa($updatedList)) {
        setFlashMessage(
            'success',
            'Data Berhasil Dihapus',
            "Data mahasiswa {$deletedName} (NIM: {$nimTarget}) telah dihapus dari sistem."
        );
    } else {
        setFlashMessage(
            'danger',
            'Gagal Menghapus Data',
            'Data mahasiswa tidak ditemukan atau terjadi gangguan penyimpanan.'
        );
    }

    header('Location: index.php#daftar-mahasiswa');
    exit;
}

// =========================================================================
// 2. AKSI TAMBAH (CREATE) ATAU UBAH (UPDATE) DATA MAHASISWA
// =========================================================================
$isEdit = ($formAction === 'update');
$originalNim = trim((string) ($_POST['original_nim'] ?? ''));

$rawInput = [
    'nim' => $_POST['nim'] ?? '',
    'nama' => $_POST['nama'] ?? '',
    'email' => $_POST['email'] ?? '',
    'prodi' => $_POST['prodi'] ?? '',
    'semester' => $_POST['semester'] ?? '',
    'jenis_kelamin' => $_POST['jenis_kelamin'] ?? '',
    'hobi' => isset($_POST['hobi']) && is_array($_POST['hobi']) ? $_POST['hobi'] : [],
    'alamat' => $_POST['alamat'] ?? '',
];

$validation = validateMahasiswaInput($rawInput, $isEdit, $originalNim);

if (!$validation['valid']) {
    // Simpan input lama ke Session (Sticky Form) beserta detail error
    setFormState($rawInput, $validation['errors']);
    setFlashMessage(
        'danger',
        'Validasi Data Gagal',
        'Mohon periksa kembali kolom formulir yang ditandai merah di bawah ini.'
    );

    $redirectUrl = $isEdit && $originalNim !== ''
        ? 'index.php?action=edit&nim=' . urlencode($originalNim) . '#form-mahasiswa'
        : 'index.php#form-mahasiswa';

    header('Location: ' . $redirectUrl);
    exit;
}

$cleanedData = $validation['cleaned'];
$allMahasiswa = getAllMahasiswa();

if ($isEdit) {
    $updated = false;
    foreach ($allMahasiswa as $index => $mhs) {
        if ((string) ($mhs['nim'] ?? '') === $originalNim) {
            $allMahasiswa[$index] = $cleanedData;
            $updated = true;
            break;
        }
    }

    if ($updated && saveAllMahasiswa($allMahasiswa)) {
        setFlashMessage(
            'success',
            'Perubahan Tersimpan',
            "Data mahasiswa {$cleanedData['nama']} (NIM: {$cleanedData['nim']}) berhasil diperbarui."
        );
    } else {
        setFlashMessage(
            'danger',
            'Gagal Memperbarui Data',
            'Data mahasiswa yang akan diedit tidak ditemukan di dalam penyimpanan.'
        );
    }
} else {
    // Tambah data baru di urutan teratas agar langsung terlihat
    array_unshift($allMahasiswa, $cleanedData);

    if (saveAllMahasiswa($allMahasiswa)) {
        setFlashMessage(
            'success',
            'Mahasiswa Berhasil Ditambahkan',
            "Data {$cleanedData['nama']} (NIM: {$cleanedData['nim']}) berhasil disimpan ke dalam sistem."
        );
    } else {
        setFlashMessage(
            'danger',
            'Gagal Menyimpan Data',
            'Terjadi kesalahan saat menulis berkas penyimpanan data.'
        );
    }
}

// Pola PRG (Post-Redirect-Get): Redirect ke index.php dengan metode GET
header('Location: index.php#daftar-mahasiswa');
exit;

<?php
declare(strict_types=1);

/**
 * Helper & Business Logic — Sistem Manajemen Data Mahasiswa
 * Mengintegrasikan materi Pertemuan 6 (PHP Dasar, Modularitas, Array, Fungsi)
 * dan Pertemuan 7 (Validasi Server-Side, Sanitasi XSS, Session Flash, Sticky Form, CSRF).
 */

if (session_status() === PHP_SESSION_NONE) {
    $savePath = session_save_path();
    if ($savePath !== '' && !is_writable($savePath)) {
        session_save_path(sys_get_temp_dir());
    }
    session_start();
}

const DATA_FILE = __DIR__ . '/../data/mahasiswa.json';

/**
 * Daftar resmi Program Studi yang tersedia
 *
 * @return array<int, string>
 */
function getDaftarProdi(): array
{
    return [
        'Sistem Informasi',
        'Teknik Informatika',
        'Manajemen Informatika',
        'Rekayasa Perangkat Lunak',
        'Bisnis Digital',
    ];
}

/**
 * Daftar pilihan Minat/Hobi untuk Checkbox Array (name="hobi[]")
 *
 * @return array<int, string>
 */
function getDaftarHobi(): array
{
    return [
        'Web Development',
        'UI/UX Design',
        'Data Analytics',
        'Cyber Security',
        'Open Source',
        'Kewirausahaan Digital',
        'Public Speaking',
    ];
}

/**
 * Sanitasi output string untuk mencegah serangan Cross-Site Scripting (XSS)
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Membaca seluruh data mahasiswa dari file JSON
 *
 * @return array<int, array<string, mixed>>
 */
function getAllMahasiswa(): array
{
    if (!file_exists(DATA_FILE)) {
        return [];
    }

    $content = file_get_contents(DATA_FILE);
    if ($content === false || trim($content) === '') {
        return [];
    }

    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

/**
 * Menyimpan seluruh data mahasiswa ke file JSON
 *
 * @param array<int, array<string, mixed>> $mahasiswaList
 */
function saveAllMahasiswa(array $mahasiswaList): bool
{
    $dir = dirname(DATA_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $json = json_encode(
        array_values($mahasiswaList),
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    if ($json === false) {
        return false;
    }

    return file_put_contents(DATA_FILE, $json, LOCK_EX) !== false;
}

/**
 * Mencari satu data mahasiswa berdasarkan NIM
 *
 * @return array<string, mixed>|null
 */
function findMahasiswaByNim(string $nim): ?array
{
    $all = getAllMahasiswa();
    foreach ($all as $item) {
        if ((string) ($item['nim'] ?? '') === $nim) {
            return $item;
        }
    }
    return null;
}

/**
 * Menyaring daftar mahasiswa berdasarkan parameter GET (?q=...&prodi=...&semester=...)
 *
 * @param array<int, array<string, mixed>> $mahasiswaList
 * @return array<int, array<string, mixed>>
 */
function filterMahasiswa(array $mahasiswaList, string $keyword = '', string $prodi = '', string $semester = ''): array
{
    $keyword = trim($keyword);
    $prodi = trim($prodi);
    $semester = trim($semester);

    return array_values(array_filter(
        $mahasiswaList,
        function (array $mhs) use ($keyword, $prodi, $semester): bool {
            if ($prodi !== '' && ($mhs['prodi'] ?? '') !== $prodi) {
                return false;
            }

            if ($semester !== '' && (string) ($mhs['semester'] ?? '') !== $semester) {
                return false;
            }

            if ($keyword !== '') {
                $haystack = strtolower(
                    ($mhs['nim'] ?? '') . ' ' .
                    ($mhs['nama'] ?? '') . ' ' .
                    ($mhs['email'] ?? '') . ' ' .
                    ($mhs['prodi'] ?? '') . ' ' .
                    ($mhs['alamat'] ?? '') . ' ' .
                    implode(' ', is_array($mhs['hobi'] ?? null) ? $mhs['hobi'] : [])
                );
                if (!str_contains($haystack, strtolower($keyword))) {
                    return false;
                }
            }

            return true;
        }
    ));
}

/**
 * Menghitung ringkasan statistik data mahasiswa untuk kartu dashboard
 *
 * @param array<int, array<string, mixed>> $mahasiswaList
 * @return array<string, int>
 */
function hitungStatistikMahasiswa(array $mahasiswaList): array
{
    $total = count($mahasiswaList);
    $prodiSI = 0;
    $prodiTI = 0;
    $semesterAtas = 0;

    foreach ($mahasiswaList as $mhs) {
        $prodi = (string) ($mhs['prodi'] ?? '');
        if ($prodi === 'Sistem Informasi') {
            $prodiSI++;
        } elseif ($prodi === 'Teknik Informatika') {
            $prodiTI++;
        }

        if ((int) ($mhs['semester'] ?? 0) >= 5) {
            $semesterAtas++;
        }
    }

    return [
        'total' => $total,
        'sistem_informasi' => $prodiSI,
        'teknik_informatika' => $prodiTI,
        'semester_lanjut' => $semesterAtas,
    ];
}

/**
 * Validasi Server-Side untuk data form mahasiswa (Pertemuan 7)
 *
 * @param array<string, mixed> $input
 * @return array{valid: bool, errors: array<string, string>, cleaned: array<string, mixed>}
 */
function validateMahasiswaInput(array $input, bool $isEdit = false, string $originalNim = ''): array
{
    $errors = [];

    $nim = trim((string) ($input['nim'] ?? ''));
    $nama = trim((string) ($input['nama'] ?? ''));
    $email = trim((string) ($input['email'] ?? ''));
    $prodi = trim((string) ($input['prodi'] ?? ''));
    $semesterRaw = trim((string) ($input['semester'] ?? ''));
    $jenisKelamin = trim((string) ($input['jenis_kelamin'] ?? ''));
    $hobiRaw = $input['hobi'] ?? [];
    $alamat = trim((string) ($input['alamat'] ?? ''));

    // 1. Validasi NIM (Wajib angka, 6-12 digit)
    if ($nim === '') {
        $errors['nim'] = 'NIM wajib diisi.';
    } elseif (!preg_match('/^[0-9]{6,12}$/', $nim)) {
        $errors['nim'] = 'NIM harus berupa angka 6 hingga 12 digit.';
    } else {
        $existing = findMahasiswaByNim($nim);
        if (!$isEdit && $existing !== null) {
            $errors['nim'] = "NIM {$nim} sudah terdaftar. Gunakan NIM lain.";
        } elseif ($isEdit && $nim !== $originalNim && $existing !== null) {
            $errors['nim'] = "NIM {$nim} sudah digunakan oleh mahasiswa lain.";
        }
    }

    // 2. Validasi Nama Lengkap
    if ($nama === '') {
        $errors['nama'] = 'Nama lengkap wajib diisi.';
    } elseif (mb_strlen($nama) < 3) {
        $errors['nama'] = 'Nama lengkap minimal terdiri dari 3 karakter.';
    } elseif (mb_strlen($nama) > 100) {
        $errors['nama'] = 'Nama lengkap maksimal 100 karakter.';
    }

    // 3. Validasi Email dengan filter_var (FILTER_VALIDATE_EMAIL)
    if ($email === '') {
        $errors['email'] = 'Alamat email akademik/aktif wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format alamat email tidak valid (contoh: nama@ars.ac.id).';
    }

    // 4. Validasi Program Studi
    $daftarProdi = getDaftarProdi();
    if ($prodi === '' || !in_array($prodi, $daftarProdi, true)) {
        $errors['prodi'] = 'Silakan pilih Program Studi yang valid.';
    }

    // 5. Validasi Semester dengan filter_var (FILTER_VALIDATE_INT)
    $semesterInt = filter_var(
        $semesterRaw,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 14]]
    );
    if ($semesterInt === false) {
        $errors['semester'] = 'Semester harus berupa angka bulat antara 1 hingga 14.';
    }

    // 6. Validasi Jenis Kelamin (Radio)
    if (!in_array($jenisKelamin, ['Laki-laki', 'Perempuan'], true)) {
        $errors['jenis_kelamin'] = 'Silakan pilih Jenis Kelamin.';
    }

    // 7. Validasi Minat/Hobi (Checkbox Array)
    $daftarHobiValid = getDaftarHobi();
    $hobiCleaned = [];
    if (is_array($hobiRaw)) {
        foreach ($hobiRaw as $item) {
            $itemStr = trim((string) $item);
            if (in_array($itemStr, $daftarHobiValid, true)) {
                $hobiCleaned[] = $itemStr;
            }
        }
    }
    if (empty($hobiCleaned)) {
        $errors['hobi'] = 'Pilih minimal 1 minat atau bidang keahlian.';
    }

    // 8. Validasi Alamat (Textarea)
    if ($alamat === '') {
        $errors['alamat'] = 'Alamat lengkap wajib diisi.';
    } elseif (mb_strlen($alamat) < 10) {
        $errors['alamat'] = 'Alamat terlalu singkat (minimal 10 karakter).';
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'cleaned' => [
            'nim' => $nim,
            'nama' => $nama,
            'email' => $email,
            'prodi' => $prodi,
            'semester' => $semesterInt !== false ? $semesterInt : 1,
            'jenis_kelamin' => $jenisKelamin,
            'hobi' => array_values(array_unique($hobiCleaned)),
            'alamat' => $alamat,
            'updated_at' => date('Y-m-d H:i:s'),
        ],
    ];
}

/**
 * Manajemen CSRF Token (Pertemuan 7 Slide 13)
 */
function getCsrfToken(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals((string) $_SESSION['csrf_token'], $token);
}

/**
 * Manajemen Flash Message & Sticky Form via $_SESSION (Pertemuan 7 Slide 12)
 */
function setFlashMessage(string $type, string $title, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'title' => $title,
        'message' => $message,
    ];
}

/**
 * @return array{type: string, title: string, message: string}|null
 */
function getFlashMessage(): ?array
{
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Menyimpan data form lama (Sticky Form) dan daftar pesan error validasi
 *
 * @param array<string, mixed> $oldInput
 * @param array<string, string> $errors
 */
function setFormState(array $oldInput, array $errors): void
{
    $_SESSION['old'] = $oldInput;
    $_SESSION['errors'] = $errors;
}

/**
 * Mengambil dan menghapus state Sticky Form setelah dibaca
 *
 * @return array{old: array<string, mixed>, errors: array<string, string>}
 */
function consumeFormState(): array
{
    $old = isset($_SESSION['old']) && is_array($_SESSION['old']) ? $_SESSION['old'] : [];
    $errors = isset($_SESSION['errors']) && is_array($_SESSION['errors']) ? $_SESSION['errors'] : [];
    unset($_SESSION['old'], $_SESSION['errors']);

    return [
        'old' => $old,
        'errors' => $errors,
    ];
}

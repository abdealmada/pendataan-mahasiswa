/**
 * Client-Side Interactivity — Sistem Manajemen Data Mahasiswa
 * Mengintegrasikan Materi Pertemuan 5 (JavaScript & DOM Manipulation):
 * 1. Pengelolaan Tema Dark/Light Mode dengan classList.toggle & localStorage
 * 2. Validasi Form Real-Time (input, change, & submit event dengan preventDefault)
 * 3. Penghitung Karakter Textarea Alamat Real-Time
 * 4. Pencarian/Filter Cepat (Live DOM Table Search) & Konfirmasi Hapus Data
 */

document.addEventListener('DOMContentLoaded', () => {
    // =========================================================================
    // 1. FITUR TOGGLE TEMA (DARK / LIGHT MODE) + LOCALSTORAGE
    // =========================================================================
    const THEME_KEY = 'ars_mahasiswa_theme';
    const themeToggleBtn = document.querySelector('#theme-toggle');
    const themeIcon = document.querySelector('#theme-icon');
    const themeLabel = document.querySelector('#theme-label');

    const applyTheme = (theme) => {
        const isDark = theme === 'dark';
        document.body.classList.toggle('dark-mode', isDark);

        if (themeToggleBtn) {
            themeToggleBtn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        }
        if (themeIcon) {
            themeIcon.innerText = isDark ? '☀' : '☾';
        }
        if (themeLabel) {
            themeLabel.innerText = isDark ? 'Mode Terang' : 'Mode Gelap';
        }
    };

    const savedTheme = localStorage.getItem(THEME_KEY) || 'light';
    applyTheme(savedTheme);

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const isCurrentlyDark = document.body.classList.contains('dark-mode');
            const nextTheme = isCurrentlyDark ? 'light' : 'dark';
            localStorage.setItem(THEME_KEY, nextTheme);
            applyTheme(nextTheme);
        });
    }

    // =========================================================================
    // 2. PENUTUPAN FLASH MESSAGE BANNER
    // =========================================================================
    const flashBanner = document.querySelector('#flash-banner');
    const closeFlashBtn = document.querySelector('#close-flash');

    if (flashBanner && closeFlashBtn) {
        closeFlashBtn.addEventListener('click', () => {
            flashBanner.classList.add('hidden');
        });
    }

    // =========================================================================
    // 3. PENGHITUNG KARAKTER ALAMAT & VALIDASI FORM REAL-TIME
    // =========================================================================
    const studentForm = document.querySelector('#student-form');
    const inputNim = document.querySelector('#nim');
    const inputNama = document.querySelector('#nama');
    const inputEmail = document.querySelector('#email');
    const inputProdi = document.querySelector('#prodi');
    const inputSemester = document.querySelector('#semester');
    const inputAlamat = document.querySelector('#alamat');
    const alamatCounter = document.querySelector('#alamat-counter');

    const updateAlamatCounter = () => {
        if (!inputAlamat || !alamatCounter) {
            return;
        }
        const length = inputAlamat.value.length;
        alamatCounter.innerText = `${length} / 250`;
    };

    if (inputAlamat) {
        updateAlamatCounter();
        inputAlamat.addEventListener('input', updateAlamatCounter);
    }

    const setFieldFeedback = (fieldId, errorMessage) => {
        const errorEl = document.querySelector(`#error-${fieldId}`);
        const inputEl = document.querySelector(`#${fieldId}`);
        const groupEl = inputEl ? inputEl.closest('.form-group') : (errorEl ? errorEl.closest('.form-group') : null);

        if (errorEl) {
            errorEl.innerText = errorMessage;
        }

        if (groupEl) {
            if (errorMessage !== '') {
                groupEl.classList.add('has-error');
                groupEl.classList.remove('is-valid');
            } else {
                groupEl.classList.remove('has-error');
                groupEl.classList.add('is-valid');
            }
        }

        return errorMessage === '';
    };

    const validateNim = () => {
        if (!inputNim) return true;
        const val = inputNim.value.trim();
        if (val === '') {
            return setFieldFeedback('nim', 'NIM wajib diisi.');
        }
        if (!/^[0-9]{6,12}$/.test(val)) {
            return setFieldFeedback('nim', 'NIM harus berupa angka 6 hingga 12 digit.');
        }
        return setFieldFeedback('nim', '');
    };

    const validateNama = () => {
        if (!inputNama) return true;
        const val = inputNama.value.trim();
        if (val === '') {
            return setFieldFeedback('nama', 'Nama lengkap mahasiswa wajib diisi.');
        }
        if (val.length < 3) {
            return setFieldFeedback('nama', 'Nama lengkap minimal terdiri dari 3 karakter.');
        }
        return setFieldFeedback('nama', '');
    };

    const validateEmail = () => {
        if (!inputEmail) return true;
        const val = inputEmail.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (val === '') {
            return setFieldFeedback('email', 'Email akademik/aktif wajib diisi.');
        }
        if (!emailRegex.test(val)) {
            return setFieldFeedback('email', 'Format email tidak valid (contoh: nama@ars.ac.id).');
        }
        return setFieldFeedback('email', '');
    };

    const validateProdi = () => {
        if (!inputProdi) return true;
        if (inputProdi.value.trim() === '') {
            return setFieldFeedback('prodi', 'Silakan pilih Program Studi.');
        }
        return setFieldFeedback('prodi', '');
    };

    const validateSemester = () => {
        if (!inputSemester) return true;
        const val = Number(inputSemester.value);
        if (!Number.isInteger(val) || val < 1 || val > 14) {
            return setFieldFeedback('semester', 'Semester harus angka 1 s/d 14.');
        }
        return setFieldFeedback('semester', '');
    };

    const validateGender = () => {
        const checked = document.querySelector('input[name="jenis_kelamin"]:checked');
        if (!checked) {
            return setFieldFeedback('jenis_kelamin', 'Silakan pilih Jenis Kelamin.');
        }
        return setFieldFeedback('jenis_kelamin', '');
    };

    const validateHobi = () => {
        const checkedBoxes = document.querySelectorAll('input[name="hobi[]"]:checked');
        if (checkedBoxes.length === 0) {
            return setFieldFeedback('hobi', 'Pilih minimal 1 minat atau bidang keahlian.');
        }
        return setFieldFeedback('hobi', '');
    };

    const validateAlamat = () => {
        if (!inputAlamat) return true;
        const val = inputAlamat.value.trim();
        if (val === '') {
            return setFieldFeedback('alamat', 'Alamat domisili wajib diisi.');
        }
        if (val.length < 10) {
            return setFieldFeedback('alamat', 'Alamat minimal terdiri dari 10 karakter.');
        }
        return setFieldFeedback('alamat', '');
    };

    // Pasang Event Listener Real-Time (input & change)
    if (inputNim) inputNim.addEventListener('input', validateNim);
    if (inputNama) inputNama.addEventListener('input', validateNama);
    if (inputEmail) inputEmail.addEventListener('input', validateEmail);
    if (inputProdi) inputProdi.addEventListener('change', validateProdi);
    if (inputSemester) inputSemester.addEventListener('input', validateSemester);
    if (inputAlamat) inputAlamat.addEventListener('input', validateAlamat);

    document.querySelectorAll('input[name="jenis_kelamin"]').forEach((radio) => {
        radio.addEventListener('change', validateGender);
    });

    document.querySelectorAll('input[name="hobi[]"]').forEach((checkbox) => {
        checkbox.addEventListener('change', validateHobi);
    });

    if (studentForm) {
        studentForm.addEventListener('submit', (event) => {
            const isNimOk = validateNim();
            const isNamaOk = validateNama();
            const isEmailOk = validateEmail();
            const isProdiOk = validateProdi();
            const isSemesterOk = validateSemester();
            const isGenderOk = validateGender();
            const isHobiOk = validateHobi();
            const isAlamatOk = validateAlamat();

            const allValid =
                isNimOk &&
                isNamaOk &&
                isEmailOk &&
                isProdiOk &&
                isSemesterOk &&
                isGenderOk &&
                isHobiOk &&
                isAlamatOk;

            if (!allValid) {
                event.preventDefault();
                const firstErrorGroup = studentForm.querySelector('.has-error');
                if (firstErrorGroup) {
                    firstErrorGroup.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });

        const resetBtn = document.querySelector('#reset-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                setTimeout(() => {
                    studentForm.querySelectorAll('.form-group').forEach((group) => {
                        group.classList.remove('has-error', 'is-valid');
                    });
                    studentForm.querySelectorAll('.field-error').forEach((err) => {
                        err.innerText = '';
                    });
                    updateAlamatCounter();
                }, 10);
            });
        }
    }

    // =========================================================================
    // 4. LIVE FILTER PENCARIAN CEPAT DI DOM & KONFIRMASI HAPUS
    // =========================================================================
    const filterSearchInput = document.querySelector('#filter-q');
    const studentRows = document.querySelectorAll('.student-row');
    const visibleCountEl = document.querySelector('#visible-count');
    const jsNoResultsEl = document.querySelector('#js-no-results');

    if (filterSearchInput && studentRows.length > 0) {
        filterSearchInput.addEventListener('input', () => {
            const query = filterSearchInput.value.trim().toLowerCase();
            let matchCount = 0;

            studentRows.forEach((row) => {
                const haystack = (row.getAttribute('data-search') || '').toLowerCase();
                const matched = query === '' || haystack.includes(query);
                row.classList.toggle('hidden', !matched);
                if (matched) {
                    matchCount++;
                }
            });

            if (visibleCountEl) {
                visibleCountEl.innerText = String(matchCount);
            }

            if (jsNoResultsEl) {
                jsNoResultsEl.classList.toggle('hidden', matchCount > 0);
            }
        });
    }

    // Konfirmasi sebelum menghapus data mahasiswa
    document.querySelectorAll('.delete-form').forEach((delForm) => {
        delForm.addEventListener('submit', (event) => {
            const nama = delForm.getAttribute('data-nama') || 'mahasiswa ini';
            const nim = delForm.getAttribute('data-nim') || '';
            const confirmed = window.confirm(
                `Apakah Anda yakin ingin menghapus data ${nama} (NIM: ${nim})? Tindakan ini tidak dapat dibatalkan.`
            );
            if (!confirmed) {
                event.preventDefault();
            }
        });
    });
});

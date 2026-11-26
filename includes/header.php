<?php
declare(strict_types=1);

/**
 * Template Header Semantik HTML5 (Pertemuan 2)
 * Memuat metadata <head>, <header>, dan <nav> dalam tata letak terpadu yang bersih.
 */
$pageTitle = $pageTitle ?? 'Sistem Manajemen Data Mahasiswa — ARS University';
$currentAction = $_GET['action'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Manajemen Data Interaktif — Aplikasi Web Pengelolaan Data Mahasiswa (Input, Edit, Filter, dan Daftar Mahasiswa) Fakultas Teknologi Informasi ARS University.">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="ars-accent-bar" aria-hidden="true">
            <span class="bar-navy"></span>
            <span class="bar-gold"></span>
            <span class="bar-charcoal"></span>
        </div>

        <div class="container header-inner">
            <div class="brand-block">
                <div class="brand-badge" aria-hidden="true">ARS</div>
                <div class="brand-text">
                    <p class="brand-eyebrow">Fakultas Teknologi Informasi &bull; Sistem Informasi</p>
                    <h1 class="site-title">Sistem Manajemen Data Mahasiswa</h1>
                </div>
            </div>

            <div class="header-right">
                <nav class="site-nav" aria-label="Navigasi Utama">
                    <ul class="nav-list">
                        <li>
                            <a href="#ringkasan-statistik" class="nav-link">Ringkasan</a>
                        </li>
                        <li>
                            <a href="#form-mahasiswa" class="nav-link <?= $currentAction === 'edit' ? 'active' : '' ?>">
                                <?= $currentAction === 'edit' ? 'Edit Data' : 'Input Data' ?>
                            </a>
                        </li>
                        <li>
                            <a href="#daftar-mahasiswa" class="nav-link <?= $currentAction !== 'edit' ? 'active' : '' ?>">
                                Daftar Mahasiswa
                            </a>
                        </li>
                    </ul>
                </nav>

                <button
                    type="button"
                    id="theme-toggle"
                    class="btn btn-outline theme-toggle-btn"
                    aria-label="Ubah tema tampilan gelap atau terang"
                    aria-pressed="false"
                >
                    <span class="theme-icon" id="theme-icon" aria-hidden="true">&#9790;</span>
                    <span class="theme-label" id="theme-label">Mode Gelap</span>
                </button>
            </div>
        </div>
    </header>

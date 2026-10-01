<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

include_once __DIR__ . '/controllers/AdminController.php';
include_once __DIR__ . '/controllers/ParkirController.php';

$action = $_GET['act'] ?? 'login';

$adminController  = new AdminController();
$parkirController = new ParkirController();

switch ($action) {
    case 'login':
        $adminController->index();
        break;
    case 'login-process':
        $adminController->login();
        break;
    case 'dashboard':
        $adminController->dashboard();
        break;
    case 'logout':
        $adminController->logout();
        break;

    case 'parkir-masuk':
        $parkirController->masuk();
        break;
    case 'parkir-masuk-hapus':
        $parkirController->masukHapus();
        break;

    case 'parkir-keluar':
        $parkirController->keluar();
        break;
    case 'parkir-keluar-hitung':
        $parkirController->keluarHitung();
        break;
    case 'parkir-keluar-proses':
        $parkirController->keluarProses();
        break;

    case 'laporan':
        $parkirController->laporan();
        break;
    case 'laporan-pdf':
        $parkirController->laporanPdf();
        break;

    default:
        $adminController->index();
        break;
}

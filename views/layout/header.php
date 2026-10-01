<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($judul); ?> - Sistem Parkir</title>
    <link rel="stylesheet" href="public/css/dashboard.css">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">Sistem Parkir</div>
        <div class="menu">
            <a href="index.php?act=dashboard" class="<?php echo $menu === 'dashboard' ? 'aktif' : ''; ?>">Dashboard</a>
            <a href="index.php?act=parkir-masuk" class="<?php echo $menu === 'parkir-masuk' ? 'aktif' : ''; ?>">Parkir Masuk</a>
            <a href="index.php?act=parkir-keluar" class="<?php echo $menu === 'parkir-keluar' ? 'aktif' : ''; ?>">Parkir Keluar</a>
            <a href="index.php?act=laporan" class="<?php echo $menu === 'laporan' ? 'aktif' : ''; ?>">Laporan</a>
            <a href="index.php?act=logout" class="btn btn-danger" onclick="return confirm('Yakin ingin keluar dari aplikasi?')">Logout</a>
        </div>
    </aside>
    <div class="konten">
        <div class="container-dashboard">

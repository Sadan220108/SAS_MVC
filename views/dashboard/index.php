<?php
$judul = 'Dashboard';
$menu = 'dashboard';
include __DIR__ . '/../layout/header.php';
?>

<div class="card">
    <h3>Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3>
    <p>Ringkasan aktivitas parkir hari ini.</p>
</div>

<div class="grid">
    <div class="stat">Sedang parkir<b><?php echo $stat['sedang']; ?></b></div>
    <div class="stat">Masuk hari ini<b><?php echo $stat['hari_ini']; ?></b></div>
    <div class="stat">Pendapatan hari ini<b>Rp<?php echo number_format($stat['pendapatan'], 0, ',', '.'); ?></b></div>
</div>

<div class="card">
    <h3>Kendaraan Sedang Parkir</h3>
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Plat</th>
                <th>Jenis</th>
                <th>Waktu Masuk</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($dataParkir) > 0): ?>
                <?php $no = 1; foreach ($dataParkir as $row): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['nomor_plat']); ?></td>
                        <td><?php echo htmlspecialchars($row['jenis_kendaraan']); ?></td>
                        <td><?php echo $row['waktu_masuk']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="tengah">Tidak ada kendaraan yang sedang parkir.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

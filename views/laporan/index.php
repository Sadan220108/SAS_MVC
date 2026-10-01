<?php
$judul = 'Laporan';
$menu = 'laporan';
include __DIR__ . '/../layout/header.php';
?>

<div class="card">
    <h3>Laporan Pendapatan Parkir</h3>
    <form action="index.php" method="GET" class="filter no-print">
        <input type="hidden" name="act" value="laporan">
        <label>Tanggal Masuk:</label>
        <input type="date" name="tanggal" class="form-control" value="<?php echo htmlspecialchars($tanggal); ?>">
        <button type="submit" class="btn btn-danger">Filter</button>
        <a href="index.php?act=laporan-pdf&tanggal=<?php echo urlencode($tanggal); ?>"
        target="_blank" class="btn btn-secondary">Cetak PDF</a>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Plat</th>
                <th>Jenis</th>
                <th>Waktu Masuk</th>
                <th>Waktu Keluar</th>
                <th>Total Bayar</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($dataLaporan) > 0): ?>
                <?php $no = 1; foreach ($dataLaporan as $row): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['nomor_plat']); ?></td>
                        <td><?php echo htmlspecialchars($row['jenis_kendaraan']); ?></td>
                        <td><?php echo $row['waktu_masuk']; ?></td>
                        <td><?php echo $row['waktu_keluar']; ?></td>
                        <td>Rp<?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="tengah">Tidak ada data untuk rentang tanggal ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h4 class="total-laporan">Total Pendapatan: Rp<?php echo number_format($totalPendapatan, 0, ',', '.'); ?></h4>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

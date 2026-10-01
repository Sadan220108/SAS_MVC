<?php
$judul = 'Parkir Keluar';
$menu = 'parkir-keluar';
include __DIR__ . '/../layout/header.php';
?>

<div class="card">
    <h3>Kendaraan Keluar</h3>

    <form action="index.php" method="GET" class="form-cari">
        <input type="hidden" name="act" value="parkir-keluar">
        <input type="text" name="q" class="form-control" placeholder="Cari nomor plat..." autofocus
               value="<?php echo htmlspecialchars($q); ?>">
        <button type="submit" class="btn btn-danger">Cari</button>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Plat</th>
                <th>Jenis</th>
                <th>Waktu Masuk</th>
                <th>Aksi</th>
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
                        <td>
                            <button type="button" class="btn btn-danger" onclick="bukaKeluar(<?php echo (int) $row['id']; ?>)">Keluar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="tengah">Kendaraan tidak ditemukan.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal" id="modalKeluar">
    <div class="isi">
        <h3>Total Biaya Parkir</h3>
        <div id="mPlat" class="redup"></div>
        <div id="mTotal" class="total"></div>
        <div id="mRincian" class="redup rincian"></div>
        <form action="index.php?act=parkir-keluar-proses" method="POST">
            <input type="hidden" name="id" id="mId">
            <button type="submit" class="btn btn-success">Bayar dan Simpan</button>
            <button type="button" class="btn btn-secondary" onclick="tutupKeluar()">Batal</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

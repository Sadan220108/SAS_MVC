<?php
$judul = 'Parkir Masuk';
$menu = 'parkir-masuk';
$mode_edit = $dataEdit !== null;
include __DIR__ . '/../layout/header.php';
?>

<div class="card">
    <h3><?php echo $mode_edit ? 'Edit' : 'Input'; ?> Kendaraan Masuk</h3>

    <form action="index.php?act=parkir-masuk" method="POST" class="form-baris">
        <?php if ($mode_edit): ?>
            <input type="hidden" name="id" value="<?php echo (int) $dataEdit['id']; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Nomor Plat</label>
            <input type="text" name="nomor_plat" class="form-control" placeholder="Contoh: L 1234 AB" required
                   value="<?php echo htmlspecialchars($dataEdit['nomor_plat'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Jenis Kendaraan</label>
            <select name="jenis_kendaraan" class="form-control" required>
                <?php foreach (['Roda 2', 'Roda 4'] as $jenis): ?>
                    <option value="<?php echo $jenis; ?>" <?php echo ($dataEdit['jenis_kendaraan'] ?? '') === $jenis ? 'selected' : ''; ?>><?php echo $jenis; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-success">Simpan</button>
            <?php if ($mode_edit): ?>
                <a href="index.php?act=parkir-masuk" class="btn btn-secondary">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <h3>Data Parkir</h3>
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor Plat</th>
                <th>Jenis</th>
                <th>Waktu Masuk</th>
                <th>Status</th>
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
                        <td><span class="badge <?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                        <td>
                            <?php if ($row['status'] === 'Parkir'): ?>
                                <a href="index.php?act=parkir-masuk&id=<?php echo (int) $row['id']; ?>" class="btn btn-secondary">Edit</a>
                            <?php endif; ?>
                            <a href="index.php?act=parkir-masuk-hapus&id=<?php echo (int) $row['id']; ?>" class="btn btn-danger"
                               onclick="return confirm('Hapus data ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="tengah">Belum ada data parkir.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

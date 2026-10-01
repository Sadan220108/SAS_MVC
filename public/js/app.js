function rupiah(angka) {
    return 'Rp' + Number(angka).toLocaleString('id-ID');
}

function formatDurasi(totalDetik) {
    var jam = Math.floor(totalDetik / 3600);
    var menit = Math.floor((totalDetik % 3600) / 60);
    var detik = totalDetik % 60;

    var bagian = [];
    if (jam > 0) bagian.push(jam + ' jam');
    if (menit > 0) bagian.push(menit + ' menit');
    if (detik > 0 || bagian.length === 0) bagian.push(detik + ' detik');

    return bagian.join(' ');
}

function bukaKeluar(id) {
    fetch('index.php?act=parkir-keluar-hitung&id=' + encodeURIComponent(id))
        .then(function (res) { return res.json(); })
        .then(function (d) {
            if (d.error) {
                alert(d.error);
                return;
            }
            document.getElementById('mId').value = id;
            document.getElementById('mPlat').textContent = d.plat;
            document.getElementById('mTotal').textContent = rupiah(d.total);
            document.getElementById('mRincian').textContent =
                'Durasi ' + formatDurasi(d.detik) +
                ' | Tarif Akan Menambah Rp.1.000 Jika Parkir Lebih Dari 2 Jam' +
                ' | Tarif awal ' + rupiah(d.tarif_awal) +
                ' | Tambahan ' + rupiah(d.tambahan);
            document.getElementById('modalKeluar').classList.add('buka');
        })
        .catch(function () { alert('Gagal menghitung biaya parkir.'); });
}

function tutupKeluar() {
    document.getElementById('modalKeluar').classList.remove('buka');
}
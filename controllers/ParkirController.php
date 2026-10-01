<?php
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/ParkirModel.php';

class ParkirController {

    private $ParkirModel;
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->ParkirModel = new ParkirModel($this->db);
    }

    private function cekLogin() {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: index.php');
            exit;
        }
    }

    private function redirect($act, $extra = '') {
        header('Location: index.php?act=' . $act . $extra);
        exit;
    }

    private function flash($pesan) {
        $_SESSION['flash'] = $pesan;
    }

    private function idValid($id) {
        return $id !== null && ctype_digit((string) $id);
    }

    // halaman parkir masuk: tambah, edit, dan daftar data dalam satu halaman
    public function masuk() {
        $this->cekLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->masukSimpan();
        }

        $dataEdit = null;
        if (isset($_GET['id'])) {
            $dataEdit = $this->idValid($_GET['id']) ? $this->ParkirModel->getById($_GET['id']) : null;
            if (!$dataEdit || $dataEdit['status'] !== 'Parkir') {
                $this->flash('Data tidak ditemukan atau kendaraan sudah keluar.');
                $this->redirect('parkir-masuk');
            }
        }

        $dataParkir = $this->ParkirModel->getAll();
        include __DIR__ . '/../views/masuk/index.php';
    }

    // simpan data baru atau perbarui data (jika ada id)
    private function masukSimpan() {
        $id = $_POST['id'] ?? '';
        $nomor_plat = $this->ParkirModel->rapikanPlat($_POST['nomor_plat'] ?? '');
        $jenis_kendaraan = $_POST['jenis_kendaraan'] ?? '';
        $kembali = $id !== '' ? '&id=' . (int) $id : '';

        if ($nomor_plat === '' || !in_array($jenis_kendaraan, ['Roda 2', 'Roda 4'], true)) {
            $this->flash('Nomor plat dan jenis kendaraan wajib diisi!');
            $this->redirect('parkir-masuk', $kembali);
        }

        if ($this->ParkirModel->platAktif($nomor_plat, $id !== '' ? $id : null)) {
            $this->flash("Gagal! Kendaraan $nomor_plat masih tercatat parkir.");
            $this->redirect('parkir-masuk', $kembali);
        }

        if ($id !== '') {
            $this->ParkirModel->ubahKendaraan($id, $nomor_plat, $jenis_kendaraan);
            $this->flash('Data parkir berhasil diperbarui!');
        } else {
            $this->ParkirModel->kendaraanMasuk($nomor_plat, $jenis_kendaraan);
            $this->flash('Data parkir masuk berhasil disimpan!');
        }

        $this->redirect('parkir-masuk');
    }

    public function masukHapus() {
        $this->cekLogin();
        $id = $_GET['id'] ?? null;

        if ($this->idValid($id)) {
            $this->ParkirModel->hapus($id);
            $this->flash('Data parkir berhasil dihapus!');
        }

        $this->redirect('parkir-masuk');
    }

    // pencarian kendaraan yang akan keluar berdasarkan nomor plat
    public function keluar() {
        $this->cekLogin();

        $q = trim($_GET['q'] ?? '');
        $dataParkir = $q !== '' ? $this->ParkirModel->cariPlat($q) : $this->ParkirModel->getAktif();

        include __DIR__ . '/../views/keluar/index.php';
    }

    // data JSON untuk pop up total biaya
    public function keluarHitung() {
        $this->cekLogin();
        header('Content-Type: application/json');

        $id = $_GET['id'] ?? null;
        $data = $this->idValid($id) ? $this->ParkirModel->getById($id) : null;

        if (!$data || $data['status'] !== 'Parkir') {
            echo json_encode(['error' => 'Data kendaraan tidak ditemukan.']);
            exit;
        }

        $tarif = $this->ParkirModel->hitungTarif($data['jenis_kendaraan'], $data['waktu_masuk'], date('Y-m-d H:i:s'));
        echo json_encode(['plat' => $data['nomor_plat']] + $tarif);
        exit;
    }

    // simpan transaksi saat kendaraan keluar
    public function keluarProses() {
        $this->cekLogin();

        $id = $_POST['id'] ?? null;
        $total = $this->idValid($id) ? $this->ParkirModel->kendaraanKeluar($id) : false;

        if ($total === false) {
            $this->flash('Data kendaraan tidak ditemukan atau sudah keluar.');
        } else {
            $this->flash('Transaksi berhasil disimpan! Total bayar Rp' . number_format($total, 0, ',', '.'));
        }

        $this->redirect('parkir-keluar');
    }

    // laporan pendapatan
    public function laporan() {
        $this->cekLogin();

        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');

        $dataLaporan = $this->ParkirModel->getLaporan($tanggal);
        $totalPendapatan = array_sum(array_column($dataLaporan, 'total_bayar'));

        include __DIR__ . '/../views/laporan/index.php';
    }

    // laporan dalam bentuk PDF, langsung dibuka di browser
    public function laporanPdf() {
        $this->cekLogin();

        $tanggal_awal = $_GET['awal'] ?? date('Y-m-d');
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');

        $dataLaporan = $this->ParkirModel->getLaporan($tanggal);
        $totalPendapatan = array_sum(array_column($dataLaporan, 'total_bayar'));

        include_once __DIR__ . '/../helpers/PdfSederhana.php';
        $pdf = new PdfSederhana();

        $kolom = ['No' => 30, 'Nomor Plat' => 90, 'Jenis' => 55, 'Waktu Masuk' => 115, 'Waktu Keluar' => 115, 'Total Bayar' => 80];
        $x0 = 55;
        $tinggiBaris = 20;
        $lebarTabel = array_sum($kolom);

        $gambarHeader = function ($y) use ($pdf, $kolom, $x0, $tinggiBaris) {
            $x = $x0;
            foreach ($kolom as $nama => $lebar) {
                $pdf->kotak($x, $y, $lebar, $tinggiBaris, [0.17, 0.24, 0.31]);
                $pdf->teks($x + 5, $y + 6, $nama, 9, true, true);
                $x += $lebar;
            }
        };

        $pdf->teks($x0, 790, 'Laporan Pendapatan Parkir', 15, true);
        $pdf->teks($x0, 770, 'Tanggal Masuk: ' . $tanggal, 10);

        $y = 725;
        $gambarHeader($y);

        foreach ($dataLaporan as $i => $row) {
            $y -= $tinggiBaris;
            if ($y < 60) {
                $pdf->tambahHalaman();
                $y = 800;
                $gambarHeader($y);
                $y -= $tinggiBaris;
            }

            $sel = [
                $i + 1,
                $row['nomor_plat'],
                $row['jenis_kendaraan'],
                $row['waktu_masuk'],
                $row['waktu_keluar'],
                'Rp' . number_format($row['total_bayar'], 0, ',', '.')
            ];

            $x = $x0;
            $k = 0;
            foreach ($kolom as $lebar) {
                $pdf->kotak($x, $y, $lebar, $tinggiBaris);
                $pdf->teks($x + 5, $y + 6, $sel[$k], 9);
                $x += $lebar;
                $k++;
            }
        }

        if (count($dataLaporan) === 0) {
            $y -= $tinggiBaris;
            $pdf->kotak($x0, $y, $lebarTabel, $tinggiBaris);
            $pdf->teks($x0 + 5, $y + 6, 'Tidak ada data untuk rentang tanggal ini.', 9);
        }

        $y -= 30;
        if ($y < 40) {
            $pdf->tambahHalaman();
            $y = 790;
        }
        $pdf->teks($x0, $y, 'Total Pendapatan: Rp' . number_format($totalPendapatan, 0, ',', '.'), 11, true);

        $pdf->tampilkan('laporan-parkir.pdf');
    }
}

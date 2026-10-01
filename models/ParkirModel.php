<?php
class ParkirModel {
    private $conn;
    private $table_name = "parkir";

    // tarif awal (untuk 2 jam pertama) per jenis kendaraan
    private $tarif = [
        "Roda 2" => 2000,
        "Roda 4" => 5000
    ];
    private $batas_jam = 2;
    private $tambahan_per_jam = 1000;

    public function __construct($db) {
        $this->conn = $db;
    }

    // rapikan nomor plat: huruf besar, spasi tunggal
    public function rapikanPlat($plat) {
        return strtoupper(trim(preg_replace('/\s+/', ' ', $plat)));
    }

    // kendaraan masuk
    public function kendaraanMasuk($nomor_plat, $jenis_kendaraan) {
        $query = "INSERT INTO " . $this->table_name . "
                  (nomor_plat, jenis_kendaraan, waktu_masuk, status, total_bayar)
                  VALUES (:nomor_plat, :jenis_kendaraan, :waktu_masuk, 'Parkir', 0)";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':nomor_plat'      => $nomor_plat,
            ':jenis_kendaraan' => $jenis_kendaraan,
            ':waktu_masuk'     => date('Y-m-d H:i:s')
        ]);
    }

    // ubah data kendaraan yang masih parkir
    public function ubahKendaraan($id, $nomor_plat, $jenis_kendaraan) {
        $query = "UPDATE " . $this->table_name . "
                  SET nomor_plat = :nomor_plat, jenis_kendaraan = :jenis_kendaraan
                  WHERE id = :id AND status = 'Parkir'";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':nomor_plat'      => $nomor_plat,
            ':jenis_kendaraan' => $jenis_kendaraan,
            ':id'              => $id
        ]);
    }

    public function hapus($id) {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $stmt = $this->conn->query("SELECT * FROM " . $this->table_name . " ORDER BY waktu_masuk DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // kendaraan yang masih parkir (belum keluar)
    public function getAktif() {
        $stmt = $this->conn->query("SELECT * FROM " . $this->table_name . " WHERE status = 'Parkir' ORDER BY waktu_masuk DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // cari kendaraan yang masih parkir berdasarkan nomor plat
    public function cariPlat($plat) {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE status = 'Parkir' AND nomor_plat LIKE :plat
                  ORDER BY waktu_masuk DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':plat' => '%' . $plat . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // apakah nomor plat ini masih tercatat parkir (abaikan id tertentu saat edit)
    public function platAktif($plat, $kecuali_id = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . "
                  WHERE status = 'Parkir' AND nomor_plat = :plat AND id <> :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':plat' => $plat, ':id' => $kecuali_id ?? -1]);
        return $stmt->fetchColumn() > 0;
    }

    // hitung tarif: tarif awal untuk 2 jam pertama, lalu tambahan per jam (dibulatkan ke atas)
    public function hitungTarif($jenis_kendaraan, $waktu_masuk, $waktu_keluar) {
        $detik = max(1, strtotime($waktu_keluar) - strtotime($waktu_masuk)); // untuk tampilan durasi
        $jam = (int) ceil($detik / 3600);                                     // untuk tagihan

        $tarif_awal = $this->tarif[$jenis_kendaraan] ?? 0;
        $tambahan = max(0, $jam - $this->batas_jam) * $this->tambahan_per_jam;

        return [
            'detik'      => $detik,
            'jam'        => $jam,
            'tarif_awal' => $tarif_awal,
            'tambahan'   => $tambahan,
            'total'      => $tarif_awal + $tambahan
        ];
    }

    // kendaraan keluar: hitung tarif otomatis, update status. Mengembalikan total bayar atau false
    public function kendaraanKeluar($id) {
        $data = $this->getById($id);
        if (!$data || $data['status'] !== 'Parkir') return false;

        $waktu_keluar = date('Y-m-d H:i:s');
        $tarif = $this->hitungTarif($data['jenis_kendaraan'], $data['waktu_masuk'], $waktu_keluar);

        $query = "UPDATE " . $this->table_name . "
                  SET waktu_keluar = :waktu_keluar, total_bayar = :total_bayar, status = 'Selesai'
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':waktu_keluar' => $waktu_keluar,
            ':total_bayar'  => $tarif['total'],
            ':id'           => $id
        ]);

        return $tarif['total'];
    }

    // laporan: kendaraan yang sudah keluar, filter rentang tanggal masuk
    public function getLaporan($tanggal) {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE status = 'Selesai'
                AND DATE(waktu_masuk) = :tanggal
                ORDER BY waktu_masuk ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':tanggal' => $tanggal]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ringkasan untuk dashboard
    public function statistik() {
        $hari_ini = date('Y-m-d');

        $sedang = $this->conn->query("SELECT COUNT(*) FROM " . $this->table_name . " WHERE status = 'Parkir'")->fetchColumn();

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM " . $this->table_name . " WHERE DATE(waktu_masuk) = :t");
        $stmt->execute([':t' => $hari_ini]);
        $masuk_hari_ini = $stmt->fetchColumn();

        $stmt = $this->conn->prepare("SELECT COALESCE(SUM(total_bayar), 0) FROM " . $this->table_name . " WHERE status = 'Selesai' AND DATE(waktu_keluar) = :t");
        $stmt->execute([':t' => $hari_ini]);
        $pendapatan = $stmt->fetchColumn();

        return [
            'sedang'     => (int) $sedang,
            'hari_ini'   => (int) $masuk_hari_ini,
            'pendapatan' => (int) $pendapatan
        ];
    }
}
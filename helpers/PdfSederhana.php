<?php
// Pembuat PDF sederhana (tanpa library), cukup untuk teks dan tabel
class PdfSederhana {
    private $lebar = 595;   // A4 portrait (satuan poin)
    private $tinggi = 842;
    private $halaman = [''];
    private $sekarang = 0;

    public function tambahHalaman() {
        $this->halaman[] = '';
        $this->sekarang = count($this->halaman) - 1;
    }

    private function tulis($perintah) {
        $this->halaman[$this->sekarang] .= $perintah . "\n";
    }

    private function bersihkan($teks) {
        $teks = preg_replace('/[^\x20-\x7E]/', '?', (string) $teks);
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $teks);
    }

    public function teks($x, $y, $teks, $ukuran = 10, $tebal = false, $putih = false) {
        $font = $tebal ? 'F2' : 'F1';
        $warna = $putih ? '1 1 1 rg' : '0 0 0 rg';
        $this->tulis(sprintf('%s BT /%s %d Tf %F %F Td (%s) Tj ET',
            $warna, $font, $ukuran, $x, $y, $this->bersihkan($teks)));
    }

    // kotak dengan garis tepi, opsional diisi warna [r, g, b] (0 sampai 1)
    public function kotak($x, $y, $lebar, $tinggi, $isi = null) {
        if ($isi !== null) {
            $this->tulis(sprintf('%F %F %F rg %F %F %F %F re f', $isi[0], $isi[1], $isi[2], $x, $y, $lebar, $tinggi));
        }
        $this->tulis(sprintf('0.5 w 0.6 0.6 0.6 RG %F %F %F %F re S', $x, $y, $lebar, $tinggi));
    }

    // tampilkan langsung di browser (Chrome membukanya di penampil PDF, tanpa diunduh)
    public function tampilkan($namaFile) {
        $jumlah = count($this->halaman);
        $obj = [];

        $kids = [];
        for ($i = 0; $i < $jumlah; $i++) {
            $kids[] = (5 + 2 * $i) . ' 0 R';
        }

        $obj[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $obj[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . $jumlah . ' >>';
        $obj[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $obj[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        foreach ($this->halaman as $i => $isi) {
            $noHalaman = 5 + 2 * $i;
            $noIsi = 6 + 2 * $i;
            $obj[$noHalaman] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %d %d] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>',
                $this->lebar, $this->tinggi, $noIsi
            );
            $obj[$noIsi] = '<< /Length ' . strlen($isi) . " >>\nstream\n" . $isi . "\nendstream";
        }

        ksort($obj);

        $pdf = "%PDF-1.4\n";
        $posisi = [];
        foreach ($obj as $no => $isi) {
            $posisi[$no] = strlen($pdf);
            $pdf .= $no . " 0 obj\n" . $isi . "\nendobj\n";
        }

        $mulaiXref = strlen($pdf);
        $total = count($obj) + 1;
        $pdf .= "xref\n0 " . $total . "\n0000000000 65535 f \n";
        foreach ($obj as $no => $isi) {
            $pdf .= sprintf("%010d 00000 n \n", $posisi[$no]);
        }
        $pdf .= "trailer\n<< /Size " . $total . " /Root 1 0 R >>\nstartxref\n" . $mulaiXref . "\n%%EOF";

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $namaFile . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }
}

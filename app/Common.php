<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
/**
 * Deteksi daerah asal (Kota/Kabupaten) berdasarkan nama sekolah dan alamatnya.
 * Mengutamakan pencarian kota/kabupaten sekitar Karesidenan Pekalongan dan Jawa Tengah.
 */
if (!function_exists('detect_kota_kabupaten')) {
    function detect_kota_kabupaten(?string $namaSekolah = '', ?string $alamat = ''): string
    {
        // 0. Prioritaskan jika alamat sudah diisi secara valid di database
        if (!empty($alamat)) {
            $alm = strtolower(trim($alamat));
            if (strpos($alm, 'kota pekalongan') !== false) {
                return 'KOTA_PEKALONGAN';
            }
            if (strpos($alm, 'kabupaten pekalongan') !== false || strpos($alm, 'kab. pekalongan') !== false) {
                return 'KAB_PEKALONGAN';
            }
            if (strpos($alm, 'batang') !== false) {
                return 'BATANG';
            }
            if (strpos($alm, 'pemalang') !== false) {
                return 'PEMALANG';
            }
            if (strpos($alm, 'kota tegal') !== false) {
                return 'TEGAL';
            }
            if (strpos($alm, 'tegal') !== false || strpos($alm, 'slawi') !== false) {
                return 'KAB_TEGAL';
            }
            if (strpos($alm, 'brebes') !== false) {
                return 'BREBES';
            }
            if (strpos($alm, 'kendal') !== false) {
                return 'KENDAL';
            }
            if (strpos($alm, 'semarang') !== false) {
                return 'SEMARANG';
            }
        }

        $text = strtolower(trim(($namaSekolah ?? '') . ' ' . ($alamat ?? '')));
        if ($text === '') {
            return 'KOTA_PEKALONGAN';
        }

        // Special case: Kramat Sari / Pasirkratonkramat is in Kota Pekalongan
        if (preg_match('/\b(kramatsari|kramat\s+sari|pasirkratonkramat)\b/i', $text)) {
            return 'KOTA_PEKALONGAN';
        }

        // 1. Brebes
        if (preg_match('/\b(brebes|bumiayu|jatibarang|losari|tanjung|bulakamba|wanasari|larangan|banjarharjo|ketanggungan|songgom|tonjong|sirampog|salem|bantarkawung|paguyangan)\b/i', $text)) {
            return 'BREBES';
        }

        // 2. Kota Tegal & Kab Tegal / Slawi
        if (preg_match('/\b(slawi|adiwerna|dukuhturi|talang|tarub|kramat(?!\s+sari)|suradadi|warureja|lebaksiu|balapulang|pangkah|kedungbanteng|jatinegara|margasari|bumijawa|tembok\s+kidul|bregas)\b/i', $text)) {
            return 'KAB_TEGAL';
        }
        if (preg_match('/\btegal\b/i', $text)) {
            if (preg_match('/\b(kab|kabupaten)\b.*tegal/i', $text) || preg_match('/tegal.*\b(kab|kabupaten)\b/i', $text)) {
                return 'KAB_TEGAL';
            }
            return 'TEGAL';
        }

        // 3. Pemalang
        if (preg_match('/\b(pemalang|moga|randudongkal|comal|petarukan|ulujami|taman|bodeh|bantarbolang|belik|pulosari|watukumpul|ampelgading|warungpring)\b/i', $text)) {
            return 'PEMALANG';
        }

        // 4. Batang
        if (preg_match('/\b(batang|warungasem|proyonanggan|bandar|blado|reban|bawang|tersono|gringsing|limpung|subah|tulis|kandeman|pecalungan|banyuputih|wonotunggal)\b/i', $text)) {
            return 'BATANG';
        }

        // 5. Kab Pekalongan (Check explicit Kab or known Kab subdistricts)
        if (preg_match('/\b(kab\b|kabupaten)\s*\.?\s*pekalongan\b/i', $text) || preg_match('/pekalongan\s*(kab\b|kabupaten)/i', $text)) {
            return 'KAB_PEKALONGAN';
        }
        if (preg_match('/\b(kajen|kedungwuni|wiradesa|tirto|bojong|wonokerto|siwalan|sragi|kesesi|doro|karanganyar|paninggaran|kandangserang|petungkriyono|lebakbarang|talun)\b/i', $text)) {
            return 'KAB_PEKALONGAN';
        }

        // 6. Other specific cities
        if (preg_match('/\b(semarang|kendal|cirebon|banyumas|purwokerto|kudus|solo|surakarta|yogyakarta|jogja|demak|pati|rembang|jepara|salatiga|magelang)\b/i', $text, $m)) {
            return strtoupper($m[1]);
        }

        // 7. Kota Pekalongan (Explicit or known subdistricts / urban kelurahan)
        if (preg_match('/\b(kota\s+pekalongan|pekalongan\s+kota|pringlangu|banyurip|kauman|podosugih|bendan|kramatsari|pasirkratonkramat|poncol|medono|panjang\s+wetan|panjang\s+baru|degayu|krapyak|kuripan|kertoharjo|buaran|gamer|setono|noyontaan|klego|sampangan|sugihwaras|kandangsari|duwet|jenggot|kradenan|sokoduwet|landungsari|yosorejo|pasirsari|bandengan)\b/i', $text)) {
            return 'KOTA_PEKALONGAN';
        }

        if (preg_match('/\bpekalongan\b/i', $text)) {
            return 'KOTA_PEKALONGAN';
        }

        // Default untuk turnamen Kejurwil Pekalongan: Kota Pekalongan
        return 'KOTA_PEKALONGAN';
    }
}

/**
 * Mendapatkan label tampilan dan styling badge untuk daerah asal Kota/Kabupaten
 */
if (!function_exists('get_label_kota_kabupaten')) {
    function get_label_kota_kabupaten(?string $namaSekolah = '', ?string $alamat = ''): array
    {
        $code = detect_kota_kabupaten($namaSekolah, $alamat);
        $map = [
            'KOTA_PEKALONGAN' => ['label' => 'Kota Pekalongan', 'badge' => 'badge-primary', 'icon' => 'bi-geo-alt-fill'],
            'KAB_PEKALONGAN'  => ['label' => 'Kab. Pekalongan', 'badge' => 'badge-indigo', 'icon' => 'bi-geo-alt'],
            'BATANG'          => ['label' => 'Kab. Batang', 'badge' => 'badge-amber', 'icon' => 'bi-pin-map-fill'],
            'PEMALANG'        => ['label' => 'Kab. Pemalang', 'badge' => 'badge-emerald', 'icon' => 'bi-pin-map'],
            'TEGAL'           => ['label' => 'Kota Tegal', 'badge' => 'badge-rose', 'icon' => 'bi-geo'],
            'KAB_TEGAL'       => ['label' => 'Kab. Tegal', 'badge' => 'badge-rose', 'icon' => 'bi-geo'],
            'BREBES'          => ['label' => 'Kab. Brebes', 'badge' => 'badge-purple', 'icon' => 'bi-geo-alt-fill'],
            'KENDAL'          => ['label' => 'Kab. Kendal', 'badge' => 'badge-blue', 'icon' => 'bi-geo-alt'],
            'SEMARANG'        => ['label' => 'Kota Semarang', 'badge' => 'badge-cyan', 'icon' => 'bi-geo-alt'],
            'CIREBON'         => ['label' => 'Cirebon', 'badge' => 'badge-secondary', 'icon' => 'bi-geo-alt'],
            'BANYUMAS'        => ['label' => 'Kab. Banyumas', 'badge' => 'badge-secondary', 'icon' => 'bi-geo-alt'],
            'PURWOKERTO'      => ['label' => 'Purwokerto', 'badge' => 'badge-secondary', 'icon' => 'bi-geo-alt'],
        ];

        if (isset($map[$code])) {
            return $map[$code] + ['code' => $code];
        }

        return [
            'code'  => $code,
            'label' => ucwords(strtolower(str_replace('_', ' ', $code))),
            'badge' => 'badge-secondary',
            'icon'  => 'bi-geo-alt',
        ];
    }
}

/**
 * Format Prestasi Seluruh Nomor Lomba agar seragam menjadi format waktu / catatan menit:detik.seperseratus (0:00.00).
 * Contoh: 10.61 -> 0:10.61, 8.93 -> 0:08.93, 6.48 -> 0:06.48, 0.00 / kosong / - -> 0:00.00.
 */
if (!function_exists('formatPrestasiPiagam')) {
    function formatPrestasiPiagam($rawPrestasi, $nomorLomba = ''): string
    {
        $p = trim((string)$rawPrestasi);
        if ($p === '') {
            return '0:00.00';
        }

        $upper = strtoupper($p);
        if (in_array($upper, ['DQ', 'DNS', 'DNF', 'NM', 'DISK', 'DISQ'])) {
            return $upper;
        }

        if (in_array($p, ['-', '0', '0.0', '0.00', '00.00', '0:00', '00:00', '0:00.00', '00:00.00'])) {
            return '0:00.00';
        }

        // Hapus satuan di ujung seperti m, meter, jarak, dtk, detik, s
        $cleaned = trim(preg_replace('/\s*(m|meter|jarak|dtk|detik|s)$/i', '', $p));

        // Format waktu yang sudah memiliki menit:detik misal 23:28.00 atau 3:44.00 atau 0:06.48
        if (preg_match('/^(\d+):(\d{2})(\.\d{1,2})?$/', $cleaned, $m)) {
            $dec = isset($m[3]) ? $m[3] : '.00';
            if (strlen($dec) === 2) {
                $dec .= '0';
            }
            return $m[1] . ':' . $m[2] . $dec;
        }

        // Format angka desimal biasa (misal 10.61, 8.93, 6.48, 44.64, 3.54) -> ubah ke 0:00.00
        if (preg_match('/^(\d+)\.(\d{1,2})$/', $cleaned, $m)) {
            $sec = (int)$m[1];
            $dec = $m[2];
            if (strlen($dec) === 1) {
                $dec .= '0';
            }
            return sprintf('0:%02d.%s', $sec, $dec);
        }

        // Format angka bulat (misal 10 atau 8) -> ubah ke 0:10.00
        if (ctype_digit($cleaned)) {
            return sprintf('0:%02d.00', (int)$cleaned);
        }

        return $p;
    }
}


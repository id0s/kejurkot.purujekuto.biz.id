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
        $text = strtolower(trim(($namaSekolah ?? '') . ' ' . ($alamat ?? '')));
        if ($text === '') {
            return 'KOTA_PEKALONGAN';
        }

        // 1. Brebes
        if (preg_match('/\b(brebes|bumiayu|jatibarang|losari|tanjung|bulakamba|wanasari|larangan|banjarharjo|ketanggungan|songgom|tonjong|sirampog|salem|bantarkawung|paguyangan)\b/i', $text)) {
            return 'BREBES';
        }

        // 2. Kota Tegal & Kab Tegal / Slawi
        if (preg_match('/\b(slawi|adiwerna|dukuhturi|talang|tarub|kramat|suradadi|warureja|lebaksiu|balapulang|pangkah|kedungbanteng|jatinegara|margasari|bumijawa|tembok\s+kidul|bregas)\b/i', $text)) {
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


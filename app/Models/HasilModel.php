<?php

namespace App\Models;

use CodeIgniter\Model;

class HasilModel extends Model
{
    protected $table            = 'hasil';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'peserta_event_id',
        'prestasi',
        'nilai_angka',
        'peringkat',
        'medali',
        'keterangan',
        'status_prestasi',
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Konversi teks prestasi menjadi nilai angka (misal '19.45' -> 19.45, '5.33m' -> 5.33)
     */
    public function parseNilaiAngka(?string $prestasi): ?float
    {
        if (empty($prestasi)) {
            return null;
        }

        $clean = trim($prestasi);
        // Tangani status khusus
        if (in_array(strtoupper($clean), ['DNS', 'DNF', 'DQ', 'NM', 'DISH', '-'])) {
            return null;
        }

        // Ambil angka dan titik/koma desimal
        $clean = str_replace(',', '.', $clean);

        // Format waktu jam:menit:detik.milidetik (misal 00:01:12.54 atau 01:12.54)
        if (strpos($clean, ':') !== false) {
            $parts = explode(':', $clean);
            if (count($parts) === 3) {
                $hours = (float)$parts[0];
                $mins  = (float)$parts[1];
                $secs  = (float)$parts[2];
                return ($hours * 3600) + ($mins * 60) + $secs;
            } elseif (count($parts) === 2) {
                $mins = (float)$parts[0];
                $secs = (float)$parts[1];
                return ($mins * 60) + $secs;
            }
        }

        if (preg_match('/([0-9]+(\.[0-9]+)?)/', $clean, $matches)) {
            return (float)$matches[1];
        }

        return null;
    }

    /**
     * Simpan atau update hasil untuk peserta event
     */
    public function simpanHasil(int $pesertaEventId, array $data): bool
    {
        $existing = $this->where('peserta_event_id', $pesertaEventId)->first();

        // Hitung nilai_angka otomatis jika belum diset
        if (isset($data['prestasi']) && !isset($data['nilai_angka'])) {
            $data['nilai_angka'] = $this->parseNilaiAngka($data['prestasi']);
        }

        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            $data['peserta_event_id'] = $pesertaEventId;
            return (bool)$this->insert($data);
        }
    }

    /**
     * Auto ranking hasil peserta dalam satu event berdasarkan jenis lomba (Waktu / Jarak)
     */
    public function autoRankingEvent(int $eventId): bool
    {
        $eventModel = new EventModel();
        $event = $eventModel->getEventWithDetails($eventId);
        if (!$event) return false;

        $satuan = $event['satuan_hasil'] ?? 'Waktu';

        // Ambil semua hasil pada event ini yang memiliki nilai_angka valid
        $builder = $this->db->table('hasil h')
            ->select('h.id, h.nilai_angka, h.prestasi, h.status_prestasi')
            ->join('peserta_event pe', 'pe.id = h.peserta_event_id')
            ->where('pe.event_id', $eventId)
            ->where('h.status_prestasi', 'Valid')
            ->where('h.nilai_angka IS NOT NULL');

        // Jika waktu: semakin kecil semakin cepat (ASC). Jika jarak/poin: semakin besar semakin jauh (DESC)
        if ($satuan === 'Waktu') {
            $builder->orderBy('h.nilai_angka', 'ASC');
        } else {
            $builder->orderBy('h.nilai_angka', 'DESC');
        }

        $results = $builder->get()->getResultArray();

        $rank = 1;
        foreach ($results as $row) {
            $medali = 'None';
            if ($rank === 1) $medali = 'Emas';
            elseif ($rank === 2) $medali = 'Perak';
            elseif ($rank === 3) $medali = 'Perunggu';

            $this->update($row['id'], [
                'peringkat' => $rank,
                'medali'    => $medali,
            ]);
            $rank++;
        }

        return true;
    }
}

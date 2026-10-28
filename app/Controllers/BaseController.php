<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    protected $session;
    protected ?array $activeKejuaraan = null;
    protected array $data = [];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Helpers
        $this->helpers = ['form', 'url', 'number', 'text'];

        parent::initController($request, $response, $logger);

        $this->session = service('session');

        // Ambil kejuaraan aktif
        $kejuaraanModel = new \App\Models\KejuaraanModel();
        $this->activeKejuaraan = $kejuaraanModel->getActive();

        // Data default untuk seluruh view
        $this->data['activeKejuaraan'] = $this->activeKejuaraan;
        $this->data['allKejuaraans']   = $kejuaraanModel->orderBy('tahun', 'DESC')->orderBy('id', 'DESC')->findAll();
        $this->data['session']         = $this->session;

        // Standarisasi otomatis nama nomor lomba dan nama acara jika ada yang belum seragam
        $this->autoStandardizeNames();
    }

    /**
     * Standarisasi otomatis nama nomor lomba dan nama acara di seluruh sistem
     */
    protected function autoStandardizeNames(): void
    {
        static $executed = false;
        if ($executed) {
            return;
        }
        $executed = true;

        try {
            $db = \Config\Database::connect();

            // 1. Cek & standarisasi tabel nomor_lomba
            $lombas = $db->table('nomor_lomba')->get()->getResultArray();
            $changed = 0;

            foreach ($lombas as $l) {
                $nama    = trim($l['nama']);
                $newNama = $nama;
                $newKat  = $l['kategori'];

                // Jika nama diawali/berupa angka jarak, misal "80 m", "80 M", "80m", "1000 m", "1000M", "100 m", "40 m"
                if (preg_match('/^(\d+)\s*m?$/i', $nama, $m)) {
                    $newNama = 'Lari ' . $m[1] . ' m';
                    $newKat  = 'Lari';
                } elseif (preg_match('/^lari\s*(\d+)\s*m?$/i', $nama, $m)) {
                    $newNama = 'Lari ' . $m[1] . ' m';
                    $newKat  = 'Lari';
                } elseif ($l['kategori'] === 'Lari' && !preg_match('/^lari/i', $nama)) {
                    $newNama = 'Lari ' . $nama;
                }

                if ($newNama !== $nama || $newKat !== $l['kategori']) {
                    $db->table('nomor_lomba')->where('id', $l['id'])->update([
                        'nama'     => $newNama,
                        'kategori' => $newKat,
                    ]);
                    $changed++;
                }
            }

            // 2. Cek & standarisasi tabel event
            $events = $db->table('event')->get()->getResultArray();

            foreach ($events as $ev) {
                $evNama    = trim($ev['nama_acara']);
                $newEvNama = $evNama;

                // Jika nama acara diawali angka jarak, misal "80 M KU 12-13 PUTRA", "1000 M KU 10-11 PUTRI", "80m ...", dll.
                if (preg_match('/^(\d+)\s*(?:m|meter)?\s*(.*)$/i', $evNama, $m)) {
                    $jarak     = $m[1];
                    $sisa      = trim($m[2]);
                    $newEvNama = 'Lari ' . $jarak . ' m' . ($sisa !== '' ? ' ' . $sisa : '');
                } elseif (!preg_match('/^(lari|lompat|lempar|estafet|jalan|tolak)/i', $evNama)) {
                    $newEvNama = 'Lari ' . $evNama;
                }

                // Bersihkan jika ada duplikasi "Lari Lari" atau spasi berlebih
                $newEvNama = preg_replace('/^lari\s+lari\s+/i', 'Lari ', $newEvNama);
                $newEvNama = preg_replace('/\s+/', ' ', trim($newEvNama));

                if ($newEvNama !== $evNama) {
                    $db->table('event')->where('id', $ev['id'])->update(['nama_acara' => $newEvNama]);
                    $changed++;
                }
            }

            // 3. Multi-event ready (nama kejuaraan dikelola manual melalui modul Kejuaraan)

            // 4. Update nama user admin jika masih bertuliskan Kejurkot
            $db->table('users')->where('nama_lengkap', 'Administrator Kejurkot')->update(['nama_lengkap' => 'Administrator Kejurwil']);

            if ($changed > 0) {
                @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurwil_Atletik.xlsx');
                @unlink(FCPATH . 'downloads' . DIRECTORY_SEPARATOR . 'Buku_Acara_Kejurkot_Atletik.xlsx');
            }
        } catch (\Throwable $e) {
            // Abaikan jika DB belum siap
        }
    }
}


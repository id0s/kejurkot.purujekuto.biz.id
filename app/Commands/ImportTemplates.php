<?php

namespace App\Commands;

use App\Libraries\ExcelImporter;
use App\Models\AtletModel;
use App\Models\EventModel;
use App\Models\KejuaraanModel;
use App\Models\PesertaEventModel;
use App\Models\SekolahModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ImportTemplates extends BaseCommand
{
    protected $group       = 'Kejurwil';
    protected $name        = 'import:templates';
    protected $description = 'Import data atlet dan no dada dari file template Excel kejurwil';

    public function run(array $params)
    {
        CLI::write('Memulai import template Excel...', 'yellow');

        $base = ROOTPATH;
        $file1 = $base . 'template-kejurkot-excel/mentahan template siswa.xlsx';
        $file2 = $base . 'template-kejurkot-excel/hasil template(buku acara).xlsx';

        if (!file_exists($file1)) {
            CLI::error("File mentahan tidak ditemukan di: $file1");
            return;
        }

        $kejuaraanModel = new KejuaraanModel();
        $active = $kejuaraanModel->getActive();
        $kejuaraanId = $active['id'] ?? 1;

        CLI::write("Menggunakan Kejuaraan ID: $kejuaraanId ({$active['nama']})", 'green');

        $importer = new ExcelImporter();

        // 1. Import Mentahan
        CLI::write("1. Memproses file mentahan atlet: $file1 ...", 'cyan');
        $res1 = $importer->importMentahan($file1, $kejuaraanId);
        CLI::write("   Total baris diproses: {$res1['total_rows']}", 'green');
        CLI::write("   Atlet baru: {$res1['atlet_baru']}", 'green');
        CLI::write("   Atlet update: {$res1['atlet_terdaftar']}", 'green');
        CLI::write("   Pendaftaran nomor lomba: {$res1['pendaftaran_event']}", 'green');

        // 2. Import No Dada
        if (file_exists($file2)) {
            CLI::write("2. Memproses mapping nomor dada: $file2 ...", 'cyan');
            $res2 = $importer->importNomorDada($file2, $kejuaraanId);
            CLI::write("   Nomor dada berhasil disematkan ke: {$res2['total_updated']} atlet", 'green');
        }

        // Summary
        $atletModel   = new AtletModel();
        $sekolahModel = new SekolahModel();
        $eventModel   = new EventModel();
        $pesertaModel = new PesertaEventModel();

        CLI::newLine();
        CLI::write('=== REKAP DATA SETELAH IMPORT ===', 'yellow');
        CLI::write("Total Atlet Terdaftar: " . $atletModel->where('kejuaraan_id', $kejuaraanId)->countAllResults(), 'white');
        CLI::write("Total Sekolah / Kontingen: " . $sekolahModel->countAllResults(), 'white');
        CLI::write("Total Nomor Acara: " . $eventModel->where('kejuaraan_id', $kejuaraanId)->countAllResults(), 'white');
        CLI::write("Total Entri Perlombaan: " . $pesertaModel->countAllResults(), 'white');
        CLI::write('Import template selesai dengan sukses!', 'green');
    }
}

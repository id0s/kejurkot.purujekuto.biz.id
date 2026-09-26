<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Auth Routes (Public)
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::processLogin');
$routes->get('logout', 'Auth::logout');
$routes->get('auth/login', 'Auth::login');
$routes->post('auth/login', 'Auth::processLogin');
$routes->get('auth/logout', 'Auth::logout');

// Landing Page Publik & Download Excel
$routes->get('/', 'Home::index');
$routes->get('downloads/Buku_Acara_Kejurwil_Atletik.xlsx', 'BukuAcara::exportExcel');
$routes->get('downloads/Buku_Acara_Kejurkot_Atletik.xlsx', 'BukuAcara::exportExcel');
$routes->get('buku-acara/export-excel', 'BukuAcara::exportExcel');
$routes->get('buku-acara/export-excel/(:any)', 'BukuAcara::exportExcel/$1');

// Verifikasi Piagam (Publik — tanpa login, diakses via QR Code)
$routes->get('verifikasi/piagam/(:any)', 'Piagam::verifikasi/$1');

// Dashboard Admin (Protected by Auth)
$routes->get('dashboard', 'Dashboard::index');

// Kejuaraan
$routes->group('kejuaraan', static function ($routes) {
    $routes->get('', 'Kejuaraan::index');
    $routes->get('create', 'Kejuaraan::create');
    $routes->get('edit/(:num)', 'Kejuaraan::edit/$1');
    $routes->post('save', 'Kejuaraan::save');
    $routes->post('save/(:num)', 'Kejuaraan::save/$1');
    $routes->get('activate/(:num)', 'Kejuaraan::activate/$1');
    $routes->post('delete/(:num)', 'Kejuaraan::delete/$1');
});

// Sekolah
$routes->group('sekolah', static function ($routes) {
    $routes->get('', 'Sekolah::index');
    $routes->get('create', 'Sekolah::create');
    $routes->get('edit/(:num)', 'Sekolah::edit/$1');
    $routes->post('save', 'Sekolah::save');
    $routes->post('save/(:num)', 'Sekolah::save/$1');
    $routes->post('delete/(:num)', 'Sekolah::delete/$1');
});

// Kelompok Umur
$routes->group('kelompok-umur', static function ($routes) {
    $routes->get('', 'KelompokUmur::index');
    $routes->post('save', 'KelompokUmur::save');
    $routes->post('delete/(:num)', 'KelompokUmur::delete/$1');
});

// Nomor Lomba
$routes->group('nomor-lomba', static function ($routes) {
    $routes->get('', 'NomorLomba::index');
    $routes->post('save', 'NomorLomba::save');
    $routes->post('delete/(:num)', 'NomorLomba::delete/$1');
});

// Atlet
$routes->group('atlet', static function ($routes) {
    $routes->get('', 'Atlet::index');
    $routes->get('create', 'Atlet::create');
    $routes->get('edit/(:num)', 'Atlet::edit/$1');
    $routes->post('save', 'Atlet::save');
    $routes->post('save/(:num)', 'Atlet::save/$1');
    $routes->post('delete/(:num)', 'Atlet::delete/$1');
    $routes->get('import', 'Atlet::import');
    $routes->post('import', 'Atlet::processImport');
    $routes->post('update-no-dada', 'Atlet::updateNoDada');
    $routes->post('generate-no-dada', 'Atlet::generateNoDada');
    $routes->get('cetak-nomor-lomba', 'Atlet::cetakNomorLomba');
    $routes->post('upload-template-no-dada', 'Atlet::uploadTemplateNoDada');
});

// Event
$routes->group('event', static function ($routes) {
    $routes->get('', 'Event::index');
    $routes->get('create', 'Event::create');
    $routes->get('edit/(:num)', 'Event::edit/$1');
    $routes->post('save', 'Event::save');
    $routes->post('save/(:num)', 'Event::save/$1');
    $routes->post('delete/(:num)', 'Event::delete/$1');
    $routes->get('peserta/(:num)', 'Event::peserta/$1');
    $routes->post('add-peserta/(:num)', 'Event::addPeserta/$1');
    $routes->post('update-peserta/(:num)', 'Event::updatePeserta/$1');
    $routes->match(['GET', 'POST'], 'remove-peserta/(:num)/(:num)', 'Event::removePeserta/$1/$2');
    $routes->post('save-team-estafet/(:num)', 'Event::saveTeamEstafet/$1');
    $routes->post('auto-arrange/(:num)', 'Event::autoArrange/$1');
    $routes->post('auto-arrange-all', 'Event::autoArrangeAll');
});

// Hasil
$routes->group('hasil', static function ($routes) {
    $routes->get('', 'Hasil::index');
    $routes->get('input/(:num)', 'Hasil::input/$1');
    $routes->post('save/(:num)', 'Hasil::save/$1');
    $routes->post('reset/(:num)', 'Hasil::reset/$1');
    $routes->get('auto-rank/(:num)', 'Hasil::autoRank/$1');
    $routes->get('form-lapangan/(:num)', 'Hasil::formLapangan/$1');
    $routes->get('cetak-hasil/(:num)', 'Hasil::cetakHasil/$1');
    $routes->match(['GET', 'POST'], 'qualify-to-final/(:num)', 'Hasil::qualifyToFinal/$1');
});

// Buku Acara
$routes->group('buku-acara', static function ($routes) {
    $routes->get('', 'BukuAcara::index');
    $routes->post('update-jadwal', 'BukuAcara::updateJadwal');
    $routes->post('sync-official', 'BukuAcara::syncOfficial');
    $routes->get('cetak', 'BukuAcara::cetak');
    $routes->get('export-excel', 'BukuAcara::exportExcel');
    $routes->get('export-excel/(:any)', 'BukuAcara::exportExcel');
});

// Laporan
$routes->group('laporan', static function ($routes) {
    $routes->get('klasemen', 'Laporan::klasemen');
});

// Piagam Penghargaan
$routes->group('piagam', static function ($routes) {
    $routes->get('', 'Piagam::index');
    $routes->get('cetak-event/(:num)', 'Piagam::cetakEvent/$1');
    $routes->get('cetak-all', 'Piagam::cetakAll');
    $routes->match(['GET', 'POST'], 'cetak-custom', 'Piagam::cetakCustom');
    $routes->get('templates', 'Piagam::templates');
    $routes->post('save-template', 'Piagam::saveTemplate');
    $routes->post('update-template-position', 'Piagam::updateTemplatePosition');
    $routes->get('set-default/(:num)', 'Piagam::setDefaultTemplate/$1');
    $routes->get('toggle-active/(:num)', 'Piagam::toggleActiveTemplate/$1');
    $routes->get('delete-template/(:num)', 'Piagam::deleteTemplate/$1');
});

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEventTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kejuaraan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nomor_lomba_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'kelompok_umur_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'gender' => [
                'type'       => 'ENUM',
                'constraint' => ['L', 'P', 'Campuran'],
                'default'    => 'L',
            ],
            'nomor_acara' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 1,
            ],
            'nama_acara' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'babak' => [
                'type'       => 'ENUM',
                'constraint' => ['Penyisihan', 'Semifinal', 'Final'],
                'default'    => 'Final',
            ],
            'jadwal_hari' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'jadwal_waktu' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Belum Dimulai', 'Berlangsung', 'Selesai'],
                'default'    => 'Belum Dimulai',
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kejuaraan_id');
        $this->forge->addKey('nomor_lomba_id');
        $this->forge->addKey('kelompok_umur_id');
        $this->forge->addKey('nomor_acara');
        $this->forge->createTable('event', true);
    }

    public function down()
    {
        $this->forge->dropTable('event', true);
    }
}

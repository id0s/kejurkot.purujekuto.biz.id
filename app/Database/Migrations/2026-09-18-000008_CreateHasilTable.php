<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHasilTable extends Migration
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
            'peserta_event_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'prestasi' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'nilai_angka' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'peringkat' => [
                'type'       => 'INT',
                'constraint' => 5,
                'null'       => true,
            ],
            'medali' => [
                'type'       => 'ENUM',
                'constraint' => ['Emas', 'Perak', 'Perunggu', 'None'],
                'default'    => 'None',
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'status_prestasi' => [
                'type'       => 'ENUM',
                'constraint' => ['Valid', 'DNS', 'DNF', 'DQ', 'NM'],
                'default'    => 'Valid',
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
        $this->forge->addUniqueKey('peserta_event_id');
        $this->forge->addKey('peringkat');
        $this->forge->addKey('medali');
        $this->forge->createTable('hasil', true);
    }

    public function down()
    {
        $this->forge->dropTable('hasil', true);
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKelompokUmurTable extends Migration
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
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'usia_min' => [
                'type'       => 'INT',
                'constraint' => 3,
            ],
            'usia_max' => [
                'type'       => 'INT',
                'constraint' => 3,
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
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
        $this->forge->createTable('kelompok_umur', true);
    }

    public function down()
    {
        $this->forge->dropTable('kelompok_umur', true);
    }
}

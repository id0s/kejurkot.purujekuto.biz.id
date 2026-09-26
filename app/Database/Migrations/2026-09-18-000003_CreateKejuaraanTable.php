<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKejuaraanTable extends Migration
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
                'constraint' => '200',
            ],
            'tahun' => [
                'type'       => 'INT',
                'constraint' => 4,
            ],
            'tempat' => [
                'type'       => 'VARCHAR',
                'constraint' => '200',
            ],
            'tanggal_mulai' => [
                'type' => 'DATE',
            ],
            'tanggal_selesai' => [
                'type' => 'DATE',
            ],
            'penyelenggara' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Draft', 'Aktif', 'Selesai'],
                'default'    => 'Aktif',
            ],
            'deskripsi' => [
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
        $this->forge->createTable('kejuaraan', true);
    }

    public function down()
    {
        $this->forge->dropTable('kejuaraan', true);
    }
}

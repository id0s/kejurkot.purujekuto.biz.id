<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNomorLombaTable extends Migration
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
                'constraint' => '100',
            ],
            'kategori' => [
                'type'       => 'ENUM',
                'constraint' => ['Lari', 'Lompat', 'Lempar', 'Estafet', 'Lainnya'],
                'default'    => 'Lari',
            ],
            'satuan_hasil' => [
                'type'       => 'ENUM',
                'constraint' => ['Waktu', 'Jarak', 'Poin'],
                'default'    => 'Waktu',
            ],
            'keterangan' => [
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
        $this->forge->createTable('nomor_lomba', true);
    }

    public function down()
    {
        $this->forge->dropTable('nomor_lomba', true);
    }
}

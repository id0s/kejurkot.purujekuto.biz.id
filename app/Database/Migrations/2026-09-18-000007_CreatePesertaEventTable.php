<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePesertaEventTable extends Migration
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
            'event_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'atlet_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'heat' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 1,
            ],
            'lane' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
            ],
            'no_urut' => [
                'type'       => 'INT',
                'constraint' => 5,
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
        $this->forge->addKey('event_id');
        $this->forge->addKey('atlet_id');
        $this->forge->addUniqueKey(['event_id', 'atlet_id']);
        $this->forge->createTable('peserta_event', true);
    }

    public function down()
    {
        $this->forge->dropTable('peserta_event', true);
    }
}

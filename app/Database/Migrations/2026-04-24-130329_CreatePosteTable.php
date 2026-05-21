<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePosteTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_poste' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nom_poste' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'salaire' => [
                'type' => 'DOUBLE',
                'constraint' => '12,5'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);
        $this->forge->addPrimaryKey('id_poste');
        $this->forge->createTable('postes');
    }

    public function down()
    {
        $this->forge->dropTable('postes');
    }
}
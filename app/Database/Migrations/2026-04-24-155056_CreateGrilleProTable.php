<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGrilleProTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_grille' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nom_grille' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id_grille', true);
        $this->forge->createTable('grilles_tarifaires');
    }

    public function down()
    {
        $this->forge->dropTable('grilles_tarifaires');
    }
}

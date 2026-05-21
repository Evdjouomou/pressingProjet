<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLibelleTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_libelle' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'categorie' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'nom_libelle' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'code_court' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'unique' => true,
            ],
            'code_barre' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true,
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_libelle', true);
        $this->forge->createTable('libelles');
    }

    public function down()
    {
        $this->forge->dropTable('libelles');
    }
}
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Createtypeabonnement extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_type_abonnement' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'libelle' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'nb_pieces' => [
                'type' => 'INT'
            ],
            'prix' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2'
            ]
        ]);
        $this->forge->addKey('id_type_abonnement', true);
        $this->forge->createTable('type_abon');
    }

    public function down()
    {
        $this->forge->dropTable('type_abon');
    }
}

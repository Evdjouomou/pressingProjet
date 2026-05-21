<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateServicesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_service' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'libelle_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'type_prestation' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'prix_unitaire_base' => [ 
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'taux_tva' => [ 
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 19.25,
            ],
            'delai_standard' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 72,
            ],
            'majoration_express' => [ 
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 25.00,
            ],
            'points_fidelite' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'statut' => [
                'type'       => 'ENUM',
                'constraint' => ['actif', 'inactif'],
                'default'    => 'actif',
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id_service', true);
        $this->forge->addForeignKey('libelle_id', 'libelles', 'id_libelle', 'CASCADE', 'CASCADE');
        $this->forge->createTable('services');
    }

    public function down()
    {
        $this->forge->dropTable('services');
    }
}

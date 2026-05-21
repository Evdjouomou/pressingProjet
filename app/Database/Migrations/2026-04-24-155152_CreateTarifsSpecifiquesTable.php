<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTarifsSpecifiquesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_tarif_spec' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'grille_id' => [ 
                'type'       => 'INT',
                'unsigned'   => true,
            ],
            'service_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
            ],
            'prix_unitaire' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id_tarif_spec', true);
        
        $this->forge->addUniqueKey(['grille_id', 'service_id']);

        $this->forge->addForeignKey('grille_id', 'grilles_tarifaires', 'id_grille', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('service_id', 'services', 'id_service', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('tarifs_specifiques');
    }

    public function down()
    {
        $this->forge->dropTable('tarifs_specifiques');
    }
}

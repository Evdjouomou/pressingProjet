<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDepotPrestationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_depot_prestation' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'article_depose_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'service_id' => [ 
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'prix_applique' => [ 
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'options_express' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
        ]);

        $this->forge->addKey('id_depot_prestation', true);
        $this->forge->addForeignKey('article_depose_id', 'depot_articles', 'id_article_depose', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('service_id', 'services', 'id_service', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('depot_prestations');
    }

    public function down()
    {
        $this->forge->dropTable('depot_prestations');
    }
}
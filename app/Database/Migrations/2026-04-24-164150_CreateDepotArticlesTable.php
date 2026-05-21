<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDepotArticlesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_article_depose' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'depot_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'libelle_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'designation_libre' => [ 
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'matiere' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'observations' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'barcode_unique' => [  
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
                'null'       => true,
            ],
            'statut_article' => [
                'type'       => 'ENUM',
                'constraint' => ['recu','en_traitement','traite','pret','livre'],
                'default'    => 'recu',
            ],
        ]);
        $this->forge->addKey('id_article_depose', true);
        $this->forge->addForeignKey('depot_id',   'depots',   'id_depot',   'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('libelle_id', 'libelles', 'id_libelle', 'CASCADE', 'CASCADE');
        $this->forge->createTable('depot_articles');
    }

    public function down()
    {
        $this->forge->dropTable('depot_articles');
    }
}
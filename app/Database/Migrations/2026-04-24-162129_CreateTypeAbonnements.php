<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateTypeAbonnements extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_type_abon'    => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nom'             => ['type' => 'VARCHAR', 'constraint' => 100],
            'description'     => ['type' => 'TEXT', 'null' => true],
            'prix'            => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'nb_articles'     => ['type' => 'INT', 'default' => 0,
                                  'comment' => 'Nb articles lavage inclus'],
            'duree_jours'     => ['type' => 'INT', 'default' => 30,
                                  'comment' => 'Durée en jours (30 = 1 mois)'],
            'actif'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_type_abon', true);
        $this->forge->createTable('type_abon');
    }

    public function down() { 
        $this->forge->dropTable('type_abon'); 
    }
}
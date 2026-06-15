<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddOffreSnapshotToAbonnements extends Migration
{
    public function up()
    {
        $this->forge->addColumn('abonnements', [
            'offre_nom_snapshot' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'type_abon_id',
                'comment'    => 'Nom de l\'offre au moment de la souscription',
            ],
            'offre_description_snapshot' => [
                'type' => 'TEXT',
                'null' => true,
                'after'=> 'offre_nom_snapshot',
            ],
            'offre_prix_snapshot' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
                'after'      => 'offre_description_snapshot',
            ],
            'offre_nb_articles_snapshot' => [
                'type'    => 'INT',
                'default' => 0,
                'after'   => 'offre_prix_snapshot',
                'comment' => 'Nb articles de l\'offre au moment de la souscription',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('abonnements', [
            'offre_nom_snapshot',
            'offre_description_snapshot',
            'offre_prix_snapshot',
            'offre_nb_articles_snapshot',
        ]);
    }
}
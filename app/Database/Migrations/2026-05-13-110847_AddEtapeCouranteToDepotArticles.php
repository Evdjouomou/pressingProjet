<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEtapeCouranteToDepotArticles extends Migration
{
    public function up()
    {
        $this->forge->addColumn('depot_articles', [
            'etape_courante_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'statut_article',
                'constraint' => 11,
            ],
        ]);

        // Tous les articles existants démarrent à l'étape 1 (Déposé)
        $this->db->query('UPDATE depot_articles SET etape_courante_id = 1');
    }

    public function down()
    {
        $this->forge->dropColumn('depot_articles', 'etape_courante_id');
    }
}

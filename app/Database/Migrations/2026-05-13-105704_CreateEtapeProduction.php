<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEtapeProduction extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_etape'        => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'libelle'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'ordre'           => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'duree_prevue_h'  => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'couleur'         => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '#6b7280'],
            'icone'           => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'fa-circle'],
            'est_actif'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_etape', true);
        $this->forge->createTable('etapes_production');

        // Données par défaut
        $this->db->table('etapes_production')->insertBatch([
            ['libelle' => 'Déposé',           'ordre' => 1, 'duree_prevue_h' => 0,   'couleur' => '#6b7280', 'icone' => 'fa-inbox'],
            ['libelle' => 'Trié',             'ordre' => 2, 'duree_prevue_h' => 1,   'couleur' => '#f59e0b', 'icone' => 'fa-layer-group'],
            ['libelle' => 'En traitement',    'ordre' => 3, 'duree_prevue_h' => 4,   'couleur' => '#3b82f6', 'icone' => 'fa-soap'],
            ['libelle' => 'Séchage',          'ordre' => 4, 'duree_prevue_h' => 2,   'couleur' => '#06b6d4', 'icone' => 'fa-wind'],
            ['libelle' => 'Repassage',        'ordre' => 5, 'duree_prevue_h' => 2,   'couleur' => '#8b5cf6', 'icone' => 'fa-tshirt'],
            ['libelle' => 'Contrôle qualité', 'ordre' => 6, 'duree_prevue_h' => 1,   'couleur' => '#f97316', 'icone' => 'fa-clipboard-check'],
            ['libelle' => 'Prêt à retirer',   'ordre' => 7, 'duree_prevue_h' => 0,   'couleur' => '#10b981', 'icone' => 'fa-check-circle'],
            ['libelle' => 'Livré',            'ordre' => 8, 'duree_prevue_h' => 0,   'couleur' => '#166534', 'icone' => 'fa-truck'],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('etapes_production');
    }
}

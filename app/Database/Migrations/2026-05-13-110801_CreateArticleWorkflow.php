<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateArticleWorkflow extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_workflow'       => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'article_depose_id' => ['type' => 'INT', 'unsigned' => true],
            'etape_id'          => ['type' => 'INT', 'unsigned' => true],
            'employe_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'date_entree'       => ['type' => 'DATETIME', 'null' => true],
            'date_sortie'       => ['type' => 'DATETIME', 'null' => true],
            'duree_reelle_min'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'commentaire'       => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id_workflow', true);
        $this->forge->addForeignKey('article_depose_id', 'depot_articles', 'id_article_depose', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('etape_id', 'etapes_production', 'id_etape', 'CASCADE', 'CASCADE');
        $this->forge->createTable('article_workflow');
    }

    public function down()
    {
        $this->forge->dropTable('article_workflow');
    }
}

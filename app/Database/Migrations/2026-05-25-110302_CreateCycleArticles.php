<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateCycleArticles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_cycle_article'  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'cycle_id'          => ['type' => 'INT', 'unsigned' => true],
            'article_depose_id' => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addKey('id_cycle_article', true);
        $this->forge->addForeignKey('cycle_id',          'cycles_machine',  'id_cycle',           'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('article_depose_id', 'depot_articles',  'id_article_depose',  'CASCADE', 'CASCADE');
        $this->forge->createTable('cycle_articles');
    }

    public function down() { $this->forge->dropTable('cycle_articles'); }
}
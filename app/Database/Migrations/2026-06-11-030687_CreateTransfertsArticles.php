<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateTransfertsArticles extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_transfert'      => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'article_depose_id' => ['type'=>'INT','unsigned'=>true],
            'shop_source_id'    => ['type'=>'INT','unsigned'=>true],
            'shop_dest_id'      => ['type'=>'INT','unsigned'=>true],
            'employe_id'        => ['type'=>'INT','unsigned'=>true,'null'=>true],
            'motif'             => ['type'=>'TEXT','null'=>true],
            'statut'            => [
                'type'       => 'ENUM',
                'constraint' => ['en_attente','confirme','annule'],
                'default'    => 'en_attente',
            ],
            'created_at'        => ['type'=>'DATETIME','null'=>true],
            'confirmed_at'      => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id_transfert', true);
        $this->forge->addForeignKey('article_depose_id','depot_articles','id_article_depose','CASCADE','CASCADE');
        $this->forge->addForeignKey('shop_source_id','shops','id_shop','CASCADE','CASCADE');
        $this->forge->addForeignKey('shop_dest_id','shops','id_shop','CASCADE','CASCADE');
        $this->forge->addForeignKey('employe_id','employes','id_employe','SET NULL','CASCADE');
        $this->forge->createTable('transferts_articles');
    }

    public function down() { $this->forge->dropTable('transferts_articles'); }
}
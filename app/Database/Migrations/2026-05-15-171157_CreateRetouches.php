<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateRetouches extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_retouche'       => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'client_id'         => ['type' => 'INT', 'unsigned' => true],
            'depot_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => true,
                                    'comment' => 'NULL si retouche sans dépôt'],
            'article_depose_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true,
                                    'comment' => 'NULL si retouche indépendante'],
            'employe_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true,
                                    'comment' => 'Retoucheur assigné'],
            'code_retouche'     => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'type_retouche'     => [
                'type'       => 'ENUM',
                'constraint' => ['ourlet','fermeture_eclair','bouton','couture',
                                 'teinture','restauration','broderie','autre'],
                'default'    => 'autre',
            ],
            'description'       => ['type' => 'TEXT'],
            'prix'              => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'acompte_verse'     => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'delai_estime'      => ['type' => 'DATE', 'null' => true],
            'statut'            => [
                'type'       => 'ENUM',
                'constraint' => ['en_attente','en_cours','fait','livre','annule'],
                'default'    => 'en_attente',
            ],
            'observations'      => ['type' => 'TEXT', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_retouche', true);
        $this->forge->addForeignKey('client_id',         'clients',         'id_client',          'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('depot_id',          'depots',          'id_depot',           'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('article_depose_id', 'depot_articles',  'id_article_depose',  'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('employe_id',        'employes',        'id_employe',         'SET NULL', 'CASCADE');
        $this->forge->createTable('retouches');
    }

    public function down() { $this->forge->dropTable('retouches'); }
}
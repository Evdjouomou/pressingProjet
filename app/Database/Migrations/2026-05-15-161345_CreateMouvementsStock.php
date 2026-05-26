<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateMouvementsStock extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_mouvement'   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'produit_id'     => ['type' => 'INT', 'unsigned' => true],
            'employe_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'type_mouvement' => [
                'type'       => 'ENUM',
                'constraint' => ['entree', 'sortie', 'ajustement', 'vente_pos', 'consommation'],
                'default'    => 'entree',
            ],
            'quantite'       => ['type' => 'INT'],
            'stock_avant'    => ['type' => 'INT'],
            'stock_apres'    => ['type' => 'INT'],
            'motif'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'reference_doc'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true,
                                 'comment' => 'ex: BON-123, TX-456'],
            'prix_unitaire'  => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_mouvement', true);
        $this->forge->addForeignKey('produit_id',  'produits_annexes', 'id_produit',  'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('employe_id',  'employes',         'id_employe',  'SET NULL', 'CASCADE');
        $this->forge->createTable('mouvements_stock');
    }

    public function down() { $this->forge->dropTable('mouvements_stock'); }
}
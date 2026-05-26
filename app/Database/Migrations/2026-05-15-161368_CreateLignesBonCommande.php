<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateLignesBonCommande extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_ligne'      => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'bon_id'        => ['type' => 'INT', 'unsigned' => true],
            'produit_id'    => ['type' => 'INT', 'unsigned' => true],
            'quantite'      => ['type' => 'INT', 'default' => 1],
            'prix_unitaire' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'total_ligne'   => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
        ]);
        $this->forge->addKey('id_ligne', true);
        $this->forge->addForeignKey('bon_id',     'bons_commande',   'id_bon',    'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('produit_id', 'produits_annexes','id_produit','CASCADE', 'CASCADE');
        $this->forge->createTable('lignes_bon_commande');
    }

    public function down() { $this->forge->dropTable('lignes_bon_commande'); }
}
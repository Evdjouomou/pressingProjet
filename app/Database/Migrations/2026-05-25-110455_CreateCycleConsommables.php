<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateCycleConsommables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_conso'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'cycle_id'          => ['type' => 'INT', 'unsigned' => true],
            'produit_id'        => ['type' => 'INT', 'unsigned' => true],
            'quantite_totale'   => ['type' => 'DECIMAL', 'constraint' => '10,3'],
            'quantite_par_article' => ['type' => 'DECIMAL', 'constraint' => '10,3', 'default' => 0,
                                       'comment' => 'Calculée = totale / nb_articles'],
        ]);
        $this->forge->addKey('id_conso', true);
        $this->forge->addForeignKey('cycle_id',   'cycles_machine',  'id_cycle',   'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('produit_id', 'produits_annexes','id_produit', 'CASCADE', 'CASCADE');
        $this->forge->createTable('cycle_consommables');
    }

    public function down() { $this->forge->dropTable('cycle_consommables'); }
}
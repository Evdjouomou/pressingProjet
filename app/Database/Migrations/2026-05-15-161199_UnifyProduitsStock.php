<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class UnifyProduitsStock extends Migration
{
    public function up()
    {
        $this->forge->addColumn('produits_annexes', [
            'type_produit' => [
                'type'       => 'ENUM',
                'constraint' => ['boutique', 'production', 'les_deux'],
                'default'    => 'boutique',
                'after'      => 'actif',
            ],
            'reference'        => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true, 'after' => 'nom'],
            'categorie'        => ['type' => 'VARCHAR', 'constraint' => 100,  'null' => true, 'after' => 'reference'],
            'unite'            => ['type' => 'VARCHAR', 'constraint' => 30,   'default' => 'unité', 'after' => 'categorie'],
            'fournisseur'      => ['type' => 'VARCHAR', 'constraint' => 255,  'null' => true, 'after' => 'unite'],
            'prix_achat'       => ['type' => 'DECIMAL', 'constraint' => '15,2','default' => 0, 'after' => 'prix'],
            'updated_at'       => ['type' => 'DATETIME','null' => true, 'after' => 'created_at'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('produits_annexes', [
            'type_produit','reference','categorie','unite',
            'fournisseur','prix_achat','updated_at',
        ]);
    }
}
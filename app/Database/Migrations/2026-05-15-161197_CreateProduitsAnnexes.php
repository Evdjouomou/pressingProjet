<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateProduitsAnnexes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_produit'   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nom'          => ['type' => 'VARCHAR', 'constraint' => 255],
            'description'  => ['type' => 'TEXT', 'null' => true],
            'prix'         => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'stock'        => ['type' => 'INT', 'default' => 0],
            'stock_alerte' => ['type' => 'INT', 'default' => 5],
            'actif'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_produit', true);
        $this->forge->createTable('produits_annexes');
    }

    public function down() { $this->forge->dropTable('produits_annexes'); }
}
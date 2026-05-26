<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateBonsCommande extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_bon'       => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'reference'    => ['type' => 'VARCHAR', 'constraint' => 50],
            'fournisseur'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'statut'       => [
                'type'       => 'ENUM',
                'constraint' => ['brouillon', 'envoye', 'recu', 'annule'],
                'default'    => 'brouillon',
            ],
            'total_ht'     => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'note'         => ['type' => 'TEXT', 'null' => true],
            'employe_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'date_envoi'   => ['type' => 'DATETIME', 'null' => true],
            'date_reception'=> ['type' => 'DATETIME', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_bon', true);
        $this->forge->addForeignKey('employe_id', 'employes', 'id_employe', 'SET NULL', 'CASCADE');
        $this->forge->createTable('bons_commande');
    }

    public function down() { $this->forge->dropTable('bons_commande'); }
}
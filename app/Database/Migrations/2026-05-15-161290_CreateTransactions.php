<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateTransactions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_transaction'   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'depot_id'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'caisse_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'employe_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'client_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'type'             => [
                'type'       => 'ENUM',
                'constraint' => ['encaissement', 'remboursement', 'avoir', 'vente_annexe'],
                'default'    => 'encaissement',
            ],
            'montant'          => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'mode_paiement'    => [
                'type'       => 'ENUM',
                'constraint' => ['especes', 'carte', 'mobile_money', 'avoir', 'fidelite', 'mixte'],
                'default'    => 'especes',
            ],
            'montant_especes'  => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'montant_carte'    => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'montant_mobile'   => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'montant_avoir'    => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'montant_fidelite' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'rendu_monnaie'    => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'motif'            => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'reference'        => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'statut'           => [
                'type'       => 'ENUM',
                'constraint' => ['valide', 'annule', 'rembourse'],
                'default'    => 'valide',
            ],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_transaction', true);
        $this->forge->addForeignKey('depot_id',   'depots',   'id_depot',   'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('employe_id', 'employes', 'id_employe', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('client_id',  'clients',  'id_client',  'SET NULL', 'CASCADE');
        $this->forge->createTable('transactions');
    }

    public function down() { $this->forge->dropTable('transactions'); }
}
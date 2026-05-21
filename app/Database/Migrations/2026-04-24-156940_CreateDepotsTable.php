<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDepotsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_depot' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'code_commande' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'unique'     => true,
            ],
            'client_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'total_ttc' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'acompte_verse' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'statut_global' => [
                'type'       => 'ENUM',
                'constraint' => ['depot','en_cours','pret','livre','annule'],
                'default'    => 'depot',
            ],
            'date_livraison_prevue' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_depot', true);
        $this->forge->addForeignKey('client_id', 'clients', 'id_client', 'CASCADE', 'CASCADE');
        $this->forge->createTable('depots');
    }

    public function down()
    {
        $this->forge->dropTable('depots');
    }
}
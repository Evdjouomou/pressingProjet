<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Createabonnement extends Migration
{
    public function up()
    {
        $this->forge->addField(([
            'id_souscription' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'client_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],
            'id_type_abon' => [
                'type' => 'INT',
                'unsigned' => true
            ],
            'pieces_restantes' => [
                'type' => 'INT'
            ],
            'date_achat' => [
                'type' => 'DATETIME'
            ],
            'date_expiration' => [
                'type' => 'DATETIME'
            ],
            'statut' => [
                'type' => 'ENUM',
                'constraint' => ['actif', 'epuise', 'expire'], 
                'default' => 'actif'
            ]
        ]));
        $this->forge->addKey('id_souscription', true);
        $this->forge->addForeignKey('client_id', 'clients', 'id_client', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_type_abon', 'type_abon', 'id_type_abonnement', 'CASCADE', 'CASCADE');
        $this->forge->createTable('abonnement');
    }

    public function down()
    {
        $this->forge->dropTable('abonnement');
    }
}

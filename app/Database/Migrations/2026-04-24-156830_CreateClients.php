<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClients extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_client' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nomclient' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'telephone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'adresse' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'journaissance' => [
                'type' => 'VARCHAR',
                'constraint' => 25,
            ],
            'typeclient' => [
                'type' => 'ENUM',
                'constraint' => ['particulier', 'professionnel'],
                'default' => 'particulier',
            ],
            'grille_id' => [
                'type' => 'INT',
                'contraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'preferences' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'solde_fidelite' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'solde_prepaye' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
            ],
            'dateajout' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_client', true);
        $this->forge->addKey('telephone');
        $this->forge->addForeignKey('grille_id', 'grilles_tarifaires', 'id_grille', 'SET NULL', 'CASCADE');
        $this->forge->createTable('clients');
    }

    public function down()
    {
        $this->forge->dropTable('clients');
    }
}

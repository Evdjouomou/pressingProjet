<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmployeTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_employe' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'matricule' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'nom_complet' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'num_cni' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'photo' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'telephone' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'lieu_residence' => [
                'type' => 'VARCHAR',
                'constraint' => 256
            ],
            'num_urgence' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'shop_id' => [
                'type' => 'INT',
                'constraint'=> 11,
                'unsigned' => true
            ],
            'poste_id' => [
                'type' => 'INT',
                'constraint'=> 11,
                'unsigned' => true
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['Actif', 'Conge', 'Inactif', 'Renvoye'],
                'default' => 'Actif'
            ],
            'role' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);
        $this->forge->addKey('id_employe', true);
        $this->forge->addUniqueKey('matricule');
        $this->forge->addForeignKey('shop_id', 'shops', 'id_shop', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('poste_id', 'postes', 'id_poste', 'CASCADE', 'CASCADE');
        $this->forge->createTable('employes');
    }

    public function down()
    {
        $this->forge->dropTable('employes');
    }
}
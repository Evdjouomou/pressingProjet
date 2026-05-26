<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateCyclesMachine extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_cycle'      => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'machine_id'    => ['type' => 'INT', 'unsigned' => true],
            'employe_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'reference'     => ['type' => 'VARCHAR', 'constraint' => 50],
            'statut'        => [
                'type'       => 'ENUM',
                'constraint' => ['en_cours', 'termine', 'annule'],
                'default'    => 'en_cours',
            ],
            'nb_articles'   => ['type' => 'INT', 'default' => 0],
            'observations'  => ['type' => 'TEXT', 'null' => true],
            'date_debut'    => ['type' => 'DATETIME', 'null' => true],
            'date_fin'      => ['type' => 'DATETIME', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_cycle', true);
        $this->forge->addForeignKey('machine_id',  'machines', 'id_machine',  'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('employe_id',  'employes', 'id_employe',  'SET NULL', 'CASCADE');
        $this->forge->createTable('cycles_machine');
    }

    public function down() { $this->forge->dropTable('cycles_machine'); }
}
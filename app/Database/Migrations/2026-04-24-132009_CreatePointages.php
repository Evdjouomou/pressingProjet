<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreatePointages extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_pointage'   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'employe_id'    => ['type' => 'INT', 'unsigned' => true],
            'date_pointage' => ['type' => 'DATE'],
            'heure_arrivee' => ['type' => 'DATETIME', 'null' => true],
            'heure_depart'  => ['type' => 'DATETIME', 'null' => true],
            'duree_minutes' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'type_pointage' => [
                'type'       => 'ENUM',
                'constraint' => ['bouton', 'qrcode'],
                'default'    => 'bouton',
            ],
            'statut'        => [
                'type'       => 'ENUM',
                'constraint' => ['present', 'absent', 'en_cours'],
                'default'    => 'en_cours',
            ],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_pointage', true);
        $this->forge->addForeignKey('employe_id', 'employes', 'id_employe', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pointages');
    }

    public function down() { $this->forge->dropTable('pointages'); }
}
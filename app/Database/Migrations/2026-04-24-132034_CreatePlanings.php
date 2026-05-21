<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreatePlannings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_planning'  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'employe_id'   => ['type' => 'INT', 'unsigned' => true],
            'semaine'      => ['type' => 'DATE', 'comment' => 'Lundi de la semaine'],
            'jour'         => [
                'type'       => 'ENUM',
                'constraint' => ['lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche'],
            ],
            'heure_debut'  => ['type' => 'TIME'],
            'heure_fin'    => ['type' => 'TIME'],
            'note'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_planning', true);
        $this->forge->addForeignKey('employe_id', 'employes', 'id_employe', 'CASCADE', 'CASCADE');
        $this->forge->createTable('plannings');
    }

    public function down() { $this->forge->dropTable('plannings'); }
}
<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateIncidentPhotos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_photo'    => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'incident_id' => ['type' => 'INT', 'unsigned' => true],
            'nom_fichier' => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_photo', true);
        $this->forge->addForeignKey('incident_id', 'incidents', 'id_incident', 'CASCADE', 'CASCADE');
        $this->forge->createTable('incident_photos');
    }

    public function down() { $this->forge->dropTable('incident_photos'); }
}
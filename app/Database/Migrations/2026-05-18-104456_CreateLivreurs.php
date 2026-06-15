<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateLivreurs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_livreur'   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nom_complet'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'telephone'    => ['type' => 'VARCHAR', 'constraint' => 20],
            'telephone2'   => ['type' => 'VARCHAR', 'constraint' => 20,  'null' => true],
            'email'        => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'adresse'      => ['type' => 'TEXT',    'null' => true],
            'zone_livraison'=> ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true,
                                'comment' => 'Quartiers/zones couverts'],
            'vehicule'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true,
                                'comment' => 'Ex: Moto, Voiture, Vélo'],
            'numero_plaque'=> ['type' => 'VARCHAR', 'constraint' => 50,  'null' => true],
            'photo'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'tarif_base'   => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0,
                                'comment' => 'Tarif de base par livraison'],
            'statut'       => [
                'type'       => 'ENUM',
                'constraint' => ['actif', 'inactif', 'suspendu'],
                'default'    => 'actif',
            ],
            'note'         => ['type' => 'TEXT', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_livreur', true);
        $this->forge->createTable('livreurs');
    }

    public function down() { $this->forge->dropTable('livreurs'); }
}
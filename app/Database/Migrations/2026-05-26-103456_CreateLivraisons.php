<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateLivraisons extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_livraison'      => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'depot_id'          => ['type' => 'INT', 'unsigned' => true],
            'client_id'         => ['type' => 'INT', 'unsigned' => true],
            'livreur_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'enregistre_par'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'code_livraison'    => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'adresse_livraison' => ['type' => 'TEXT'],
            'date_livraison'    => ['type' => 'DATE', 'null' => true],
            'heure_livraison'   => ['type' => 'TIME', 'null' => true],
            'statut'            => [
                'type'       => 'ENUM',
                'constraint' => ['en_attente','assignee','en_cours','livree','echec','annulee'],
                'default'    => 'en_attente',
            ],
            'note_client'       => ['type' => 'TEXT', 'null' => true],
            'note_livreur'      => ['type' => 'TEXT', 'null' => true],
            'montant_livraison' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'date_livree'       => ['type' => 'DATETIME', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_livraison', true);
        $this->forge->addForeignKey('depot_id',       'depots',   'id_depot',   'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('client_id',      'clients',  'id_client',  'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('livreur_id',     'employes', 'id_employe', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('enregistre_par', 'employes', 'id_employe', 'SET NULL', 'CASCADE');
        $this->forge->createTable('livraisons');
    }

    public function down() { $this->forge->dropTable('livraisons'); }
}
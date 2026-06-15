<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateAbonnements extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_abonnement'   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'client_id'       => ['type' => 'INT', 'unsigned' => true],
            'type_abon_id'    => ['type' => 'INT', 'unsigned' => true],
            'employe_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'code_abonnement' => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'date_debut'      => ['type' => 'DATE'],
            'date_fin'        => ['type' => 'DATE'],
            'nb_articles_total'   => ['type' => 'INT', 'default' => 0,
                                      'comment' => 'Total articles droit (report + nouveaux)'],
            'nb_articles_utilises'=> ['type' => 'INT', 'default' => 0],
            'nb_articles_restants'=> ['type' => 'INT', 'default' => 0],
            'montant_paye'    => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'statut'          => [
                'type'       => 'ENUM',
                'constraint' => ['actif','expire','annule','suspendu'],
                'default'    => 'actif',
            ],
            'note'            => ['type' => 'TEXT', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_abonnement', true);
        $this->forge->addForeignKey('client_id',    'clients',  'id_client',    'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('type_abon_id', 'type_abon','id_type_abon', 'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('employe_id',   'employes', 'id_employe',   'SET NULL', 'CASCADE');
        $this->forge->createTable('abonnements');
    }

    public function down() { 
        $this->forge->dropTable('abonnements'); 
    }
}
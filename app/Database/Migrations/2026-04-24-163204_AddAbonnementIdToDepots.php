<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddAbonnementIdToDepots extends Migration
{
    public function up()
    {
        $this->forge->addColumn('depots', [
            'abonnement_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'statut_global',
                'comment'  => 'NULL si dépôt normal, sinon ID abonnement utilisé',
            ],
        ]);
        $this->db->query("
            ALTER TABLE depots
            ADD CONSTRAINT fk_depots_abonnement
            FOREIGN KEY (abonnement_id)
            REFERENCES abonnements(id_abonnement)
            ON DELETE SET NULL ON UPDATE CASCADE
        ");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE depots DROP FOREIGN KEY fk_depots_abonnement");
        $this->forge->dropColumn('depots', 'abonnement_id');
    }
}
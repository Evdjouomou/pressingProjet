<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddModeRetraitToDepots extends Migration
{
    public function up()
    {
        $this->forge->addColumn('depots', [
            'mode_retrait' => [
                'type'       => 'ENUM',
                'constraint' => ['non_defini','boutique','livraison'],
                'default'    => 'non_defini',
                'after'      => 'statut_global',
            ],
            'notif_pret_envoyee' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after'   => 'mode_retrait',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('depots', ['mode_retrait', 'notif_pret_envoyee']);
    }
}
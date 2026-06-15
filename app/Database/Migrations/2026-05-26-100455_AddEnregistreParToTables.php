<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddEnregistreParToTables extends Migration
{
    public function up()
    {
        $tables = [
            'clients'   => 'dateajout',
            'depots'    => 'created_at',
            'retouches' => 'created_at',
            'incidents' => 'created_at',
        ];

        foreach ($tables as $table => $after) {
            $this->forge->addColumn($table, [
                'enregistre_par' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                    'after'    => $after,
                ],
            ]);

            // FK vers employes
            $this->db->query("
                ALTER TABLE `{$table}`
                ADD CONSTRAINT `fk_{$table}_enregistre_par`
                FOREIGN KEY (`enregistre_par`)
                REFERENCES `employes`(`id_employe`)
                ON DELETE SET NULL ON UPDATE CASCADE
            ");
        }
    }

    public function down()
    {
        $tables = ['clients', 'depots', 'retouches', 'incidents'];
        foreach ($tables as $table) {
            $this->db->query("ALTER TABLE `{$table}` DROP FOREIGN KEY `fk_{$table}_enregistre_par`");
            $this->forge->dropColumn($table, 'enregistre_par');
        }
    }
}
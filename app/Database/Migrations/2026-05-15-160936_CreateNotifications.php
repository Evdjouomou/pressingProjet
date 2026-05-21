<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotifications extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_notification' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'client_id'       => ['type' => 'INT', 'unsigned' => true],
            'depot_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'type'            => [
                'type'       => 'ENUM',
                'constraint' => ['depot_confirme', 'commande_prete', 'rappel_retrait', 'retrait_confirme', 'campagne'],
                'default'    => 'depot_confirme',
            ],
            'canal'           => [
                'type'       => 'ENUM',
                'constraint' => ['interne', 'email', 'sms'],
                'default'    => 'interne',
            ],
            'sujet'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'message'         => ['type' => 'TEXT'],
            'statut'          => [
                'type'       => 'ENUM',
                'constraint' => ['en_attente', 'envoye', 'echec'],
                'default'    => 'en_attente',
            ],
            'lu'              => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'date_envoi'      => ['type' => 'DATETIME', 'null' => true],
            'erreur_detail'   => ['type' => 'TEXT', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_notification', true);
        $this->forge->addForeignKey('client_id', 'clients', 'id_client', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('depot_id',  'depots',  'id_depot',  'CASCADE', 'SET NULL');
        $this->forge->createTable('notifications');
    }

    public function down()
    {
        $this->forge->dropTable('notifications');
    }
}

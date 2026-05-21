<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCampagnes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_campagne'  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'titre'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'message'      => ['type' => 'TEXT'],
            'type_cible'   => [
                'type'       => 'ENUM',
                'constraint' => ['tous', 'inactifs', 'anniversaire', 'manuel'],
                'default'    => 'tous',
            ],
            'canal'        => [
                'type'       => 'ENUM',
                'constraint' => ['interne', 'email', 'sms', 'tous'],
                'default'    => 'interne',
            ],
            'jours_inactivite' => ['type' => 'INT', 'null' => true, 'comment' => 'Pour type inactifs'],
            'statut'       => [
                'type'       => 'ENUM',
                'constraint' => ['brouillon', 'planifiee', 'envoyee'],
                'default'    => 'brouillon',
            ],
            'date_envoi_prevue' => ['type' => 'DATETIME', 'null' => true],
            'date_envoi_reel'   => ['type' => 'DATETIME', 'null' => true],
            'nb_envoyes'        => ['type' => 'INT', 'default' => 0],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_campagne', true);
        $this->forge->createTable('campagnes');
    }

    public function down()
    {
        $this->forge->dropTable('campagnes');
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Override;

class CreateShopTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_shop' => [
                'type' => 'INT',
                'constraint' => 100,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'nom_shop' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'adresse' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'created_at' => [
                'type' => 'DATETIME'
            ]
        ]);
        $this->forge->addKey('id_shop', true);
        $this->forge->createTable('shops');
    }

    public function down()
    {
        $this->forge->dropTable('shops');
    }
}
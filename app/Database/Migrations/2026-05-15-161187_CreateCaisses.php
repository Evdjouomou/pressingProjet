<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateCaisses extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_caisse'        => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'employe_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'shop_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'date_ouverture'   => ['type' => 'DATETIME'],
            'date_cloture'     => ['type' => 'DATETIME', 'null' => true],
            'fond_ouverture'   => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'total_especes'    => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'total_carte'      => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'total_mobile'     => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'total_avoir'      => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'total_ca'         => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'total_rembourse'  => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'fond_reel'        => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'ecart'            => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'statut'           => [
                'type'       => 'ENUM',
                'constraint' => ['ouverte', 'cloturee'],
                'default'    => 'ouverte',
            ],
            'note_cloture'     => ['type' => 'TEXT', 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_caisse', true);
        $this->forge->addForeignKey('employe_id', 'employes', 'id_employe', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('shop_id',    'shops',    'id_shop',    'SET NULL', 'CASCADE');
        $this->forge->createTable('caisses');
    }

    public function down() { $this->forge->dropTable('caisses'); }
}
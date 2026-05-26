<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateMachines extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_machine'   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nom'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'type_machine' => [
                'type'       => 'ENUM',
                'constraint' => ['lavage', 'sechage', 'repassage', 'detachage', 'autre'],
                'default'    => 'lavage',
            ],
            'capacite_max' => ['type' => 'INT', 'default' => 10,
                               'comment' => 'Nb max articles par cycle'],
            'actif'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_machine', true);
        $this->forge->createTable('machines');

        // Données par défaut
        $this->db->table('machines')->insertBatch([
            ['nom' => 'Machine lavage 1',  'type_machine' => 'lavage',    'capacite_max' => 15],
            ['nom' => 'Machine lavage 2',  'type_machine' => 'lavage',    'capacite_max' => 10],
            ['nom' => 'Tunnel de séchage', 'type_machine' => 'sechage',   'capacite_max' => 20],
            ['nom' => 'Table repassage',   'type_machine' => 'repassage', 'capacite_max' => 5],
        ]);
    }

    public function down() { $this->forge->dropTable('machines'); }
}
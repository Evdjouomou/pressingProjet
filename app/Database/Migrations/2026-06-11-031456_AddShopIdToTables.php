<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddShopIdToTables extends Migration
{
    public function up()
    {
        // 1. Ajouter shop_id sur depots si elle n'existe pas
        if (!$this->db->fieldExists('shop_id', 'depots')) {
            $this->forge->addColumn('depots', [
                'shop_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                    'after'    => 'enregistre_par',
                ],
            ]);
            $this->db->query("
                ALTER TABLE depots
                ADD CONSTRAINT fk_depots_shop
                FOREIGN KEY (shop_id) REFERENCES shops(id_shop)
                ON DELETE SET NULL ON UPDATE CASCADE
            ");
        }

        // 2. Ajouter shop_id sur clients si elle n'existe pas
        if (!$this->db->fieldExists('shop_id', 'clients')) {
            $this->forge->addColumn('clients', [
                'shop_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                    'after'    => 'enregistre_par',
                    'comment'  => 'Shop principal du client',
                ],
            ]);
            $this->db->query("
                ALTER TABLE clients
                ADD CONSTRAINT fk_clients_shop
                FOREIGN KEY (shop_id) REFERENCES shops(id_shop)
                ON DELETE SET NULL ON UPDATE CASCADE
            ");
        }

        // 3. Ajouter shop_id sur caisses si elle n'existe pas (C'est ici que ça plantait)
        if (!$this->db->fieldExists('shop_id', 'caisses')) {
            $this->forge->addColumn('caisses', [
                'shop_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                    'after'    => 'employe_id',
                ],
            ]);
            $this->db->query("
                ALTER TABLE caisses
                ADD CONSTRAINT fk_caisses_shop
                FOREIGN KEY (shop_id) REFERENCES shops(id_shop)
                ON DELETE SET NULL ON UPDATE CASCADE
            ");
        }
    }

    public function down()
    {
        foreach (['depots','clients','caisses'] as $table) {
            $fk = $this->getFkName($table, 'shop_id');
            if ($fk) $this->db->query("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk}`");
            $this->forge->dropColumn($table, 'shop_id');
        }
    }

    private function getFkName(string $table, string $col): ?string
    {
        $r = $this->db->query("
            SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?
              AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1
        ", [$this->db->getDatabase(), $table, $col])->getRow();
        return $r ? $r->CONSTRAINT_NAME : null;
    }
}
<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class MakeTypeAbonIdNullableInAbonnements extends Migration
{
    public function up()
    {
        // 1. Chercher le vrai nom de la FK existante sur type_abon_id
        $fkName = $this->getForeignKeyName('abonnements', 'type_abon_id');

        if ($fkName) {
            $this->db->query("
                ALTER TABLE abonnements
                DROP FOREIGN KEY `{$fkName}`
            ");
        }

        // 2. Rendre la colonne nullable
        $this->db->query("
            ALTER TABLE abonnements
            MODIFY COLUMN type_abon_id INT UNSIGNED NULL
        ");

        // 3. Recréer la FK avec SET NULL
        $this->db->query("
            ALTER TABLE abonnements
            ADD CONSTRAINT fk_abonnements_type_abon
            FOREIGN KEY (type_abon_id)
            REFERENCES type_abon(id_type_abon)
            ON DELETE SET NULL ON UPDATE CASCADE
        ");
    }

    public function down()
    {
        $fkName = $this->getForeignKeyName('abonnements', 'type_abon_id');

        if ($fkName) {
            $this->db->query("
                ALTER TABLE abonnements
                DROP FOREIGN KEY `{$fkName}`
            ");
        }

        $this->db->query("
            ALTER TABLE abonnements
            MODIFY COLUMN type_abon_id INT UNSIGNED NOT NULL
        ");

        $this->db->query("
            ALTER TABLE abonnements
            ADD CONSTRAINT fk_abonnements_type_abon
            FOREIGN KEY (type_abon_id)
            REFERENCES type_abon(id_type_abon)
            ON DELETE CASCADE ON UPDATE CASCADE
        ");
    }

    
    private function getForeignKeyName(string $table, string $column): ?string
    {
        $dbName = $this->db->getDatabase();

        $result = $this->db->query(
            "SELECT CONSTRAINT_NAME
             FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA    = ?
               AND TABLE_NAME      = ?
               AND COLUMN_NAME     = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1",
            [$dbName, $table, $column]
        )->getRow();

        return $result ? $result->CONSTRAINT_NAME : null;
    }
}
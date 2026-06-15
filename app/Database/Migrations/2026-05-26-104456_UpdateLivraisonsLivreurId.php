<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class UpdateLivraisonsLivreurId extends Migration
{
    public function up()
    {
        // 1. Supprimer l'ancienne FK vers employes
        $fkName = $this->getForeignKeyName('livraisons', 'livreur_id');
        if ($fkName) {
            $this->db->query("ALTER TABLE livraisons DROP FOREIGN KEY `{$fkName}`");
        }

        // 2. Modifier la colonne (même type, même nom)
        $this->db->query("
            ALTER TABLE livraisons
            MODIFY COLUMN livreur_id INT UNSIGNED NULL
        ");

        // 3. Recréer la FK vers livreurs
        $this->db->query("
            ALTER TABLE livraisons
            ADD CONSTRAINT fk_livraisons_livreur
            FOREIGN KEY (livreur_id)
            REFERENCES livreurs(id_livreur)
            ON DELETE SET NULL ON UPDATE CASCADE
        ");
    }

    public function down()
    {
        $fkName = $this->getForeignKeyName('livraisons', 'livreur_id');
        if ($fkName) {
            $this->db->query("ALTER TABLE livraisons DROP FOREIGN KEY `{$fkName}`");
        }
        $this->db->query("
            ALTER TABLE livraisons
            ADD CONSTRAINT fk_livraisons_livreur_emp
            FOREIGN KEY (livreur_id)
            REFERENCES employes(id_employe)
            ON DELETE SET NULL ON UPDATE CASCADE
        ");
    }

    private function getForeignKeyName(string $table, string $column): ?string
    {
        $result = $this->db->query("
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
              AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ", [$this->db->getDatabase(), $table, $column])->getRow();
        return $result ? $result->CONSTRAINT_NAME : null;
    }
}
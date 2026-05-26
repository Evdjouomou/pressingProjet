<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateIncidents extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_incident'       => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'client_id'         => ['type' => 'INT', 'unsigned' => true],
            'depot_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'article_depose_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'responsable_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'declare_par_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'code_incident'     => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'type_incident'     => [
                'type'       => 'ENUM',
                'constraint' => ['article_endommage','article_perdu','retard',
                                 'qualite_insuffisante','mauvais_traitement','autre'],
                'default'    => 'autre',
            ],
            'description'       => ['type' => 'TEXT'],
            'gravite'           => [
                'type'       => 'ENUM',
                'constraint' => ['faible','moyen','eleve','critique'],
                'default'    => 'moyen',
            ],
            'statut'            => [
                'type'       => 'ENUM',
                'constraint' => ['ouvert','en_traitement','resolu','cloture'],
                'default'    => 'ouvert',
            ],
            'type_resolution'   => [
                'type'       => 'ENUM',
                'constraint' => ['avoir','remboursement','compensation','aucune'],
                'null'       => true,
            ],
            'montant_resolution'=> ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'note_resolution'   => ['type' => 'TEXT', 'null' => true],
            'delai_resolution'  => ['type' => 'DATE', 'null' => true],
            'date_cloture'      => ['type' => 'DATETIME', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_incident', true);
        $this->forge->addForeignKey('client_id',         'clients',        'id_client',          'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('depot_id',          'depots',         'id_depot',           'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('article_depose_id', 'depot_articles', 'id_article_depose',  'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('responsable_id',    'employes',       'id_employe',         'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('declare_par_id',    'employes',       'id_employe',         'SET NULL', 'CASCADE');
        $this->forge->createTable('incidents');
    }

    public function down() { $this->forge->dropTable('incidents'); }
}
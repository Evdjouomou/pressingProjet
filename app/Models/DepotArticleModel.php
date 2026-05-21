<?php

namespace App\Models;

use CodeIgniter\Model;

class DepotArticleModel extends Model
{
    protected $table            = 'depot_articles';
    protected $primaryKey       = 'id_article_depose';
    protected $allowedFields    = [
        'depot_id', 
        'libelle_id', 
        'barcode_unique', 
        'designation_libre', 
        'observations', 
        'statut_article'
    ];

    public function getArticleWithLibelle($id_article)
    {
        return $this->select('depot_articles.*, libelles.nom_libelle')
                    ->join('libelles', 'libelles.id_libelle = depot_articles.libelle_id')
                    ->where('id_article_depose', $id_article)
                    ->first();
    }
}
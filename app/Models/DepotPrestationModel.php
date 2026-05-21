<?php

namespace App\Models;

use CodeIgniter\Model;

class DepotPrestationModel extends Model
{
    protected $table            = 'depot_prestations';
    protected $primaryKey       = 'id_depot_prestation';
    protected $allowedFields    = [
        'article_depose_id', 
        'service_id', 
        'prix_applique', 
        'options_express'
    ];

    public function getPrestationsByArticle($article_id)
    {
        return $this->select('depot_prestations.*, services.type_prestation')
                    ->join('services', 'services.id_service = depot_prestations.service_id')
                    ->where('article_depose_id', $article_id)
                    ->findAll();
    }
}
<?php 

namespace App\Models;

use CodeIgniter\Model;

class ServiceModel extends Model
{
    protected $table = 'services';
    protected $primaryKey = 'id_service';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    
    protected $allowedFields = [
        'libelle_id', 'type_prestation', 'prix_unitaire_base', 
        'taux_tva', 'delai_standard', 'majoration_express', 
        'points_fidelite', 'statut', 'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}  
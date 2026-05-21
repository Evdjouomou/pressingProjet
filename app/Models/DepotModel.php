<?php

namespace App\Models;

use CodeIgniter\Model;

class DepotModel extends Model
{
    protected $table            = 'depots';
    protected $primaryKey       = 'id_depot';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'code_commande', 
        'client_id', 
        'total_ttc', 
        'acompte_verse', 
        'statut_global', 
        'date_livraison_prevue'
    ];

    protected $useTimestamps = true;
}
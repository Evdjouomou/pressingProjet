<?php

namespace App\Models;

use CodeIgniter\Model;

class TarifSpecifiqueModel extends Model
{
    protected $table = 'tarifs_specifiques';
    protected $primaryKey = 'id_tarif_spec';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'grille_id',
        'service_id',
        'prix_unitaire',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
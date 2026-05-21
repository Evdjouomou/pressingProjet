<?php

namespace App\Models;

use CodeIgniter\Model;

class GrilleProModel extends Model
{
    protected $table = 'grilles_tarifaires';
    protected $primaryKey = 'id_grille';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'nom_grille',
        'description',
        'created_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';
}

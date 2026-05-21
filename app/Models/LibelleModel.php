<?php

namespace App\Models;

use CodeIgniter\Model;

class LibelleModel extends Model
{
    protected $table = 'libelles';
    protected $primaryKey = 'id_libelle';
    protected $allowedFields = ['categorie', 'nom_libelle', 'code_court', 'code_barre'];
}
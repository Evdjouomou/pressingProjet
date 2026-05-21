<?php

namespace App\Models;

use CodeIgniter\Model;

class TypeAbonnementModel extends Model
{
    protected $table = 'type_abon';
    protected $primaryKey = 'id_type_abon';
    protected $allowedFields = [
        'libelle', 'nb_prieces', 'prix'
    ]; 

    public function getTypeAbonnementById($id_type_abon) {
        return $this->where('id_type_abon', $id_type_abon)->first();
    }
}
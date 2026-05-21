<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model {
    protected $table = 'clients';
    protected $primaryKey = 'id_client';
    protected $allowedFields = [
        'nomclient', 
        'email', 
        'telephone', 
        'adresse', 
        'journaissance', 
        'dateajout',
        'typeclient',
        'grille_id',
        'preferences',
        'solde_fidelite',
        'solde_prepaye'
    ];
    protected $useTimestamps = false;

    public function getClientById($id_client) {
        return $this->where('id_client', $id_client)->first();
    }
}
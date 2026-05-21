<?php

namespace App\Models;

use CodeIgniter\Model;

class AbonnementModel extends Model
{
    protected $table = 'abonnement';
    protected $primaryKey = 'id_souscription';
    protected $allowedFields = [
        'client_id',
        'id_type_abon',
        'pieces_restantes',
        'date_achat',
        'date_expiration',
        'statut'
    ];

    public function getAllAbonnements() {
        return $this->select('abonnement.*, type_abon.*, clients.*')
            ->join('type_abon', 'type_abon.id_type_abonnement = abonnement.id_type_abon')
            ->join('clients', 'clients.id_client = abonnement.client_id')
            ->findAll();
    }
}
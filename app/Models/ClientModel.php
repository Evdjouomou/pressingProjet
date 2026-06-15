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

    public function getAllAvecDetails(): array
    {
        return $this->db->table('employes e')
            ->select('e.*, p.nom_poste, p.salaire, s.nom_shop, s.adresse,
                    ep.nom_complet AS enregistre_par_nom') // ← AJOUT
            ->join('postes p',   'p.id_poste = e.poste_id',     'left')
            ->join('shops s',    's.id_shop = e.shop_id',        'left')
            ->join('employes ep','ep.id_employe = e.enregistre_par', 'left') // ← AJOUT
            ->orderBy('e.nom_complet', 'ASC')
            ->get()->getResultArray();
    }
}
<?php
namespace App\Models;
use CodeIgniter\Model;

class EmployeModel extends Model
{
    protected $table         = 'employes';
    protected $primaryKey    = 'id_employe';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'matricule', 'password', 'nom_complet', 'email',
        'num_cni', 'photo', 'telephone', 'lieu_residence',
        'num_urgence', 'shop_id', 'poste_id', 'status',
        'role', 'created_at',
    ];

    // Récupère tous les employés avec leur poste et shop joints
    public function getAllAvecDetails(): array
    {
        return $this->db->table('employes e')
            ->select('
                e.*,
                p.nom_poste,
                p.salaire,
                s.nom_shop,
                s.adresse
            ')
            ->join('postes p', 'p.id_poste = e.poste_id', 'left')
            ->join('shops s',  's.id_shop = e.shop_id',   'left')
            ->orderBy('e.nom_complet', 'ASC')
            ->get()->getResultArray();
    }
}
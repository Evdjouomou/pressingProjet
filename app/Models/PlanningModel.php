<?php
namespace App\Models;
use CodeIgniter\Model;

class PlanningModel extends Model
{
    protected $table         = 'plannings';
    protected $primaryKey    = 'id_planning';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'employe_id', 'semaine', 'jour',
        'heure_debut', 'heure_fin', 'note', 'created_at',
    ];

    public function getParSemaine(string $semaine): array
    {
        return $this->db->table('plannings pl')
            ->select('pl.*, e.nom_complet, e.photo, e.matricule, s.nom_shop')
            ->join('employes e', 'e.id_employe = pl.employe_id')
            ->join('shops s',    's.id_shop = e.shop_id', 'left')
            ->where('pl.semaine', $semaine)
            ->orderBy('pl.jour')
            ->orderBy('pl.heure_debut')
            ->get()->getResultArray();
    }
}
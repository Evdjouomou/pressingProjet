<?php
namespace App\Models;
use CodeIgniter\Model;

class PointageModel extends Model
{
    protected $table         = 'pointages';
    protected $primaryKey    = 'id_pointage';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'employe_id', 'date_pointage', 'heure_arrivee',
        'heure_depart', 'duree_minutes', 'type_pointage',
        'statut', 'created_at',
    ];

    public function getPointageEnCours(int $employeId): ?array
    {
        return $this->where('employe_id', $employeId)
                    ->where('statut', 'en_cours')
                    ->orderBy('created_at', 'DESC')
                    ->first();
    }

    public function getParDate(string $date): array
    {
        return $this->db->table('pointages p')
            ->select('p.*, e.nom_complet, e.matricule, e.photo, p2.nom_poste, s.nom_shop')
            ->join('employes e',  'e.id_employe = p.employe_id')
            ->join('postes p2',   'p2.id_poste = e.poste_id',  'left')
            ->join('shops s',     's.id_shop = e.shop_id',     'left')
            ->where('p.date_pointage', $date)
            ->orderBy('p.heure_arrivee', 'DESC')
            ->get()->getResultArray();
    }
}
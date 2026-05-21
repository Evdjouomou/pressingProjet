<?php
namespace App\Controllers;

use App\Models\EmployeModel;
use App\Models\PosteModel;
use App\Models\ShopModel;
use App\Models\PointageModel;
use App\Models\PlanningModel;

class EmployeController extends BaseController
{
    // ═══════════════════════════════════════════
    // LISTE PRINCIPALE
    // ═══════════════════════════════════════════
    public function index()
    {
        return view('pages/personnel/employe', [
            'title'     => 'Gestion du Personnel',
            'employees' => (new EmployeModel())->getAllAvecDetails(),
            'postes'    => (new PosteModel())->orderBy('nom_poste')->findAll(),
            'shops'     => (new ShopModel())->orderBy('nom_shop')->findAll(),
        ]);
    }

    // ═══════════════════════════════════════════
    // CRÉER
    // ═══════════════════════════════════════════
    public function store()
    {
        $model    = new EmployeModel();
        $matricule = 'EMP-' . strtoupper(substr(uniqid(), -6));

        $data = [
            'matricule'      => $matricule,
            'password'       => password_hash('pressing2024', PASSWORD_DEFAULT),
            'nom_complet'    => $this->request->getPost('nom_complet'),
            'email'          => $this->request->getPost('email'),
            'telephone'      => $this->request->getPost('telephone'),
            'num_cni'        => $this->request->getPost('num_cni'),
            'num_urgence'    => $this->request->getPost('num_urgence'),
            'lieu_residence' => $this->request->getPost('lieu_residence'),
            'shop_id'        => $this->request->getPost('shop_id'),
            'poste_id'       => $this->request->getPost('poste_id'),
            'role'           => $this->request->getPost('role'),
            'status'         => $this->request->getPost('status') ?: 'Actif',
            'created_at'     => date('Y-m-d H:i:s'),
        ];

        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $nom = $photo->getRandomName();
            $photo->move(ROOTPATH . 'public/uploads/photos', $nom);
            $data['photo'] = $nom;
        }

        $model->insert($data);
        return redirect()->to('personnel')
                         ->with('success', 'Employé enregistré. Matricule : ' . $matricule . ' | Mot de passe par défaut : pressing2024');
    }

    // ═══════════════════════════════════════════
    // MODIFIER
    // ═══════════════════════════════════════════
    public function update(int $id)
    {
        $model = new EmployeModel();
        $data  = [
            'status'    => $this->request->getPost('status'),
            'telephone' => $this->request->getPost('telephone'),
            'email'     => $this->request->getPost('email'),
            'poste_id'  => $this->request->getPost('poste_id'),
            'shop_id'   => $this->request->getPost('shop_id'),
            'role'      => $this->request->getPost('role'),
        ];

        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $nom = $photo->getRandomName();
            $photo->move(ROOTPATH . 'public/uploads/photos', $nom);
            $data['photo'] = $nom;
        }

        $model->update($id, $data);
        return redirect()->to('personnel')->with('success', 'Profil mis à jour.');
    }

    // ═══════════════════════════════════════════
    // SUPPRIMER
    // ═══════════════════════════════════════════
    public function delete(int $id)
    {
        (new EmployeModel())->delete($id);
        return redirect()->to('personnel')->with('success', 'Employé supprimé.');
    }

    // ═══════════════════════════════════════════
    // POINTAGE — BOUTON
    // ═══════════════════════════════════════════
    public function pointer()
    {
        $model     = new PointageModel();
        $employeId = (int) $this->request->getPost('employe_id');
        $now       = date('Y-m-d H:i:s');

        $enCours = $model->getPointageEnCours($employeId);

        if ($enCours) {
            $duree = (int) round((strtotime($now) - strtotime($enCours['heure_arrivee'])) / 60);
            $model->update($enCours['id_pointage'], [
                'heure_depart'  => $now,
                'duree_minutes' => $duree,
                'statut'        => 'present',
            ]);
            return redirect()->to('personnel/pointages')
                             ->with('success', 'Départ enregistré — Durée : ' . intdiv($duree, 60) . 'h' . str_pad($duree % 60, 2, '0', STR_PAD_LEFT));
        }

        $model->insert([
            'employe_id'    => $employeId,
            'date_pointage' => date('Y-m-d'),
            'heure_arrivee' => $now,
            'type_pointage' => 'bouton',
            'statut'        => 'en_cours',
            'created_at'    => $now,
        ]);

        return redirect()->to('personnel/pointages')->with('success', 'Arrivée enregistrée à ' . date('H:i'));
    }

    // ═══════════════════════════════════════════
    // POINTAGE — QR CODE (JSON)
    // ═══════════════════════════════════════════
    public function pointerQr()
    {
        $model     = new PointageModel();
        $db        = \Config\Database::connect();
        $matricule = trim($this->request->getPost('matricule') ?? '');
        $now       = date('Y-m-d H:i:s');

        $employe = $db->table('employes')
                      ->where('matricule', $matricule)
                      ->get()->getRowArray();

        if (!$employe) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Matricule inconnu : ' . $matricule,
            ]);
        }

        $enCours = $model->getPointageEnCours($employe['id_employe']);

        if ($enCours) {
            $duree = (int) round((strtotime($now) - strtotime($enCours['heure_arrivee'])) / 60);
            $model->update($enCours['id_pointage'], [
                'heure_depart'  => $now,
                'duree_minutes' => $duree,
                'statut'        => 'present',
            ]);
            return $this->response->setJSON([
                'success' => true,
                'action'  => 'depart',
                'message' => '👋 Au revoir ' . $employe['nom_complet'] . ' — ' . intdiv($duree,60) . 'h' . str_pad($duree%60,2,'0',STR_PAD_LEFT),
            ]);
        }

        $model->insert([
            'employe_id'    => $employe['id_employe'],
            'date_pointage' => date('Y-m-d'),
            'heure_arrivee' => $now,
            'type_pointage' => 'qrcode',
            'statut'        => 'en_cours',
            'created_at'    => $now,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'action'  => 'arrivee',
            'message' => '✅ Bonjour ' . $employe['nom_complet'] . ' — Arrivée : ' . date('H:i'),
        ]);
    }

    // ═══════════════════════════════════════════
    // PAGE POINTAGES
    // ═══════════════════════════════════════════
    public function pointages()
    {
        $pointageModel = new PointageModel();
        $employeModel  = new EmployeModel();
        $filtre        = $this->request->getGet('date') ?? date('Y-m-d');

        $pointages  = $pointageModel->getParDate($filtre);
        $idsPointes = array_column($pointages, 'employe_id');

        $db = \Config\Database::connect();
        $absentsQ = $db->table('employes e')
            ->select('e.*, p.nom_poste, s.nom_shop')
            ->join('postes p', 'p.id_poste = e.poste_id', 'left')
            ->join('shops s',  's.id_shop = e.shop_id',   'left')
            ->where('e.status', 'Actif');

        if (!empty($idsPointes)) {
            $absentsQ->whereNotIn('e.id_employe', $idsPointes);
        }

        return view('pages/personnel/pointages', [
            'title'     => 'Pointages — ' . date('d/m/Y', strtotime($filtre)),
            'pointages' => $pointages,
            'absents'   => $absentsQ->get()->getResultArray(),
            'employees' => $employeModel->getAllAvecDetails(),
            'filtre'    => $filtre,
        ]);
    }

    // ═══════════════════════════════════════════
    // PLANNING
    // ═══════════════════════════════════════════
    public function planning()
    {
        $semaine = $this->request->getGet('semaine')
                   ?? date('Y-m-d', strtotime('monday this week'));

        return view('pages/personnel/planning', [
            'title'     => 'Planning — semaine du ' . date('d/m/Y', strtotime($semaine)),
            'plannings' => (new PlanningModel())->getParSemaine($semaine),
            'employees' => (new EmployeModel())->getAllAvecDetails(),
            'semaine'   => $semaine,
            'jours'     => ['lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche'],
        ]);
    }

    public function sauvegarderPlanning()
    {
        (new PlanningModel())->insert([
            'employe_id'  => $this->request->getPost('employe_id'),
            'semaine'     => $this->request->getPost('semaine'),
            'jour'        => $this->request->getPost('jour'),
            'heure_debut' => $this->request->getPost('heure_debut'),
            'heure_fin'   => $this->request->getPost('heure_fin'),
            'note'        => $this->request->getPost('note'),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('personnel/planning?semaine=' . $this->request->getPost('semaine'))
                         ->with('success', 'Créneau ajouté.');
    }

    public function supprimerPlanning(int $id)
    {
        (new PlanningModel())->delete($id);
        return redirect()->back()->with('success', 'Créneau supprimé.');
    }

    // ═══════════════════════════════════════════
    // PRODUCTIVITÉ
    // ═══════════════════════════════════════════
    public function productivite()
    {
        $mois = $this->request->getGet('mois') ?? date('Y-m');

        $stats = \Config\Database::connect()->query("
            SELECT
                e.id_employe, e.nom_complet, e.matricule, e.photo, e.role,
                p.nom_poste,
                s.nom_shop,
                COUNT(DISTINCT pt.id_pointage)                          AS jours_travailles,
                COALESCE(SUM(pt.duree_minutes), 0)                      AS total_minutes,
                COALESCE(AVG(pt.duree_minutes), 0)                      AS moy_minutes_jour,
                COUNT(DISTINCT aw.id_workflow)                          AS articles_traites,
                COALESCE(AVG(aw.duree_reelle_min), 0)                   AS moy_min_article
            FROM employes e
            LEFT JOIN postes p   ON p.id_poste = e.poste_id
            LEFT JOIN shops s    ON s.id_shop = e.shop_id
            LEFT JOIN pointages pt ON pt.employe_id = e.id_employe
                                  AND DATE_FORMAT(pt.date_pointage, '%Y-%m') = ?
                                  AND pt.statut = 'present'
            LEFT JOIN article_workflow aw ON aw.employe_id = e.id_employe
                                         AND DATE_FORMAT(aw.date_entree, '%Y-%m') = ?
            WHERE e.status = 'Actif'
            GROUP BY e.id_employe
            ORDER BY articles_traites DESC
        ", [$mois, $mois])->getResultArray();

        return view('pages/personnel/productivite', [
            'title' => 'Productivité — ' . date('F Y', strtotime($mois . '-01')),
            'stats' => $stats,
            'mois'  => $mois,
        ]);
    }
}
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
    $passwordClair = 'pressing2024'; 

    $data = [
        'matricule'      => $matricule,
        'password'       => password_hash($passwordClair, PASSWORD_DEFAULT),
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
        $photo->move(ROOTPATH . 'public/img', $nom);
        $data['photo'] = $nom;
    }

    if ($model->insert($data)) {
        
        // ─── CONFIGURATION DIRECTE DE L'EMAIL ────────────────
        $config = [
            'protocol'   => 'smtp',
            'SMTPHost'   => 'smtp.gmail.com',            // Modifie si ce n'est pas Gmail
            'SMTPUser'   => 'kemadjouyann11@gmail.com', // METS TON EMAIL ICI
            'SMTPPass'   => 'ydup uucz tewq zlln',// METS TON MOT DE PASSE ICI
            'SMTPPort'   => 465,
            'SMTPCrypto' => 'ssl',
            'mailType'   => 'html',                      // Pour accepter les balises HTML du message
            'charset'    => 'UTF-8',
            'newline'    => "\r\n"
        ];

        $emailService = \Config\Services::email();
        $emailService->initialize($config); // On applique la configuration
        // ─────────────────────────────────────────────────────

        $emailDestination = $data['email'];
        $nomComplet       = $data['nom_complet'];
        $posteId          = $data['poste_id'];

        $emailService->setFrom($config['SMTPUser'], 'Pressing Pro');
        $emailService->setTo($emailDestination);
        $emailService->setSubject('Création de votre compte - Pressing Pro');

       $message = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <h2 style='color: #4B6BFB;'>Bienvenue dans l'équipe, " . esc($nomComplet) . " !</h2>
                <p>Votre compte employé a été créé avec succès dans le système de gestion du pressing.</p>
                <p>Voici vos identifiants personnels pour vous connecter à l'application :</p>
                <table style='background: #f4f6f9; padding: 15px; border-radius: 5px; width: 100%;'>
                    <tr>
                        <td><strong>Identifiant / Matricule :</strong></td>
                        <td><code style='background: #fff; padding: 2px 6px; border: 1px solid #ddd;'>" . esc($matricule) . "</code></td>
                    </tr>
                    <tr>
                        <td><strong>Mot de passe par défaut :</strong></td>
                        <td><code style='background: #fff; padding: 2px 6px; border: 1px solid #ddd;'>" . esc($passwordClair) . "</code></td>
                    </tr>
                    <tr>
                        <td><strong>Identifiant Poste (ID) :</strong></td>
                        <td>" . esc($posteId) . "</td>
                    </tr>
                </table>
                <p style='margin-top: 15px;'>⚠️ <em>Par mesure de sécurité, nous vous recommandons vivement de modifier ce mot de passe dès votre première connexion.</em></p>
                <br>
                <p>Cordialement,<br><strong>L'équipe de Direction</strong></p>
            </div>
        ";

        $emailService->setMessage($message);

        if ($emailService->send()) {
            $statutEmail = " et e-mail de bienvenue envoyé à " . $emailDestination;
        } else {
            $statutEmail = " mais l'envoi de l'e-mail a échoué. Erreur : " . $emailService->printDebugger(['headers']);
        }

        return redirect()->to('personnel')
                         ->with('success', 'Employé enregistré avec succès. Matricule : ' . $matricule . $statutEmail);
    }

    return redirect()->to('personnel')->with('error', 'Impossible d\'enregistrer l\'employé.');
}
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
         public function changementMotDePasse()
       {
             // Sécurité : Si l'ID temporaire n'est pas en session, on le renvoie au login
            if (!session()->has('temp_employe_id')) {
             return redirect()->to('/');
                                                    }

            return view('pages/personnel/changement_password'); // On va créer cette vue à l'étape 4
       }

            public function updatePremierPassword()
{
    // Sécurité : On vérifie si la session temporaire existe toujours
    if (!session()->has('temp_employe_id')) {
        return redirect()->to('/')->with('error', 'Votre session a expiré. Veuillez vous reconnecter.');
    }

    $idEmploye = session()->get('temp_employe_id');
    $password  = $this->request->getPost('new_password');
    $confirm   = $this->request->getPost('confirm_password');

    // 1. Validation des champs
    if (empty($password) || strlen($password) < 6) {
        return redirect()->back()->with('error', 'Le mot de passe doit contenir au moins 6 caractères.');
    }

    if ($password !== $confirm) {
        return redirect()->back()->with('error', 'Les deux mots de passe ne correspondent pas.');
    }

    // 2. Mise à jour sécurisée en base de données
    $model = new \App\Models\EmployeModel();
    
    $updated = $model->update($idEmploye, [
        'password'           => password_hash($password, PASSWORD_DEFAULT),
        'premiere_connexion' => 0
    ]);

    if ($updated) {
        // Nettoyage de la session temporaire uniquement si la mise à jour a réussi
        session()->remove('temp_employe_id');
        
        // Redirection propre vers la page de connexion avec message de succès
        return redirect()->to('/')->with('success', 'Votre mot de passe a été configuré avec succès ! Connectez-vous avec votre nouveau mot de passe.');
    } else {
        return redirect()->back()->with('error', 'Impossible de mettre à jour le mot de passe en base de données.');
    }
}
}
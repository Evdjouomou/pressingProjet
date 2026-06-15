<?php
namespace App\Controllers;

class LivreurController extends BaseController
{
    // ═══════════════════════════════════════════
    // LISTE
    // ═══════════════════════════════════════════
    public function index()
    {
        $db = \Config\Database::connect();

        $livreurs = $db->table('livreurs l')
            ->select('l.*,
                      COUNT(liv.id_livraison)  AS total_livraisons,
                      SUM(CASE WHEN liv.statut = "livree" THEN 1 ELSE 0 END)
                          AS livraisons_terminees,
                      SUM(CASE WHEN liv.statut IN ("assignee","en_cours") THEN 1 ELSE 0 END)
                          AS livraisons_encours')
            ->join('livraisons liv', 'liv.livreur_id = l.id_livreur', 'left')
            ->groupBy('l.id_livreur')
            ->orderBy('l.nom_complet', 'ASC')
            ->get()->getResultArray();

        $stats = [
            'total'   => count($livreurs),
            'actifs'  => count(array_filter($livreurs, fn($l) => $l['statut'] === 'actif')),
            'encours' => array_sum(array_column($livreurs, 'livraisons_encours')),
        ];

        return view('pages/livreurs/index', [
            'title'    => 'Gestion des livreurs',
            'livreurs' => $livreurs,
            'stats'    => $stats,
        ]);
    }

    // ═══════════════════════════════════════════
    // CRÉER
    // ═══════════════════════════════════════════
    public function store()
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $data = [
            'nom_complet'   => $this->request->getPost('nom_complet'),
            'telephone'     => $this->request->getPost('telephone'),
            'telephone2'    => $this->request->getPost('telephone2')    ?: null,
            'email'         => $this->request->getPost('email')         ?: null,
            'adresse'       => $this->request->getPost('adresse')       ?: null,
            'zone_livraison'=> $this->request->getPost('zone_livraison') ?: null,
            'vehicule'      => $this->request->getPost('vehicule')      ?: null,
            'numero_plaque' => $this->request->getPost('numero_plaque') ?: null,
            'tarif_base'    => (float) ($this->request->getPost('tarif_base') ?? 0),
            'statut'        => 'actif',
            'note'          => $this->request->getPost('note') ?: null,
            'created_at'    => $now,
            'updated_at'    => $now,
        ];

        // Photo
        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $nom         = $photo->getRandomName();
            $photo->move(ROOTPATH . 'public/uploads/livreurs', $nom);
            $data['photo'] = $nom;
        }

        $db->table('livreurs')->insert($data);

        return redirect()->to('livreurs')
            ->with('success', 'Livreur créé avec succès.');
    }

    // ═══════════════════════════════════════════
    // MODIFIER
    // ═══════════════════════════════════════════
    public function update(int $id)
    {
        $db  = \Config\Database::connect();

        $data = [
            'nom_complet'   => $this->request->getPost('nom_complet'),
            'telephone'     => $this->request->getPost('telephone'),
            'telephone2'    => $this->request->getPost('telephone2')    ?: null,
            'email'         => $this->request->getPost('email')         ?: null,
            'adresse'       => $this->request->getPost('adresse')       ?: null,
            'zone_livraison'=> $this->request->getPost('zone_livraison') ?: null,
            'vehicule'      => $this->request->getPost('vehicule')      ?: null,
            'numero_plaque' => $this->request->getPost('numero_plaque') ?: null,
            'tarif_base'    => (float) ($this->request->getPost('tarif_base') ?? 0),
            'statut'        => $this->request->getPost('statut'),
            'note'          => $this->request->getPost('note') ?: null,
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            // Supprimer l'ancienne photo
            $ancien = $db->table('livreurs')
                ->select('photo')
                ->where('id_livreur', $id)
                ->get()->getRowArray();
            if ($ancien && $ancien['photo']) {
                $fichier = ROOTPATH . 'public/uploads/livreurs/' . $ancien['photo'];
                if (file_exists($fichier)) unlink($fichier);
            }
            $nom = $photo->getRandomName();
            $photo->move(ROOTPATH . 'public/uploads/livreurs', $nom);
            $data['photo'] = $nom;
        }

        $db->table('livreurs')->where('id_livreur', $id)->update($data);

        return redirect()->to('livreurs')
            ->with('success', 'Livreur mis à jour.');
    }

    // ═══════════════════════════════════════════
    // SUPPRIMER
    // ═══════════════════════════════════════════
    public function delete(int $id)
    {
        $db = \Config\Database::connect();

        // Vérifier s'il a des livraisons en cours
        $enCours = $db->table('livraisons')
            ->where('livreur_id', $id)
            ->whereIn('statut', ['assignee', 'en_cours'])
            ->countAllResults();

        if ($enCours > 0) {
            return redirect()->to('livreurs')
                ->with('error',
                    'Impossible de supprimer : ce livreur a '
                    . $enCours . ' livraison(s) en cours.'
                );
        }

        // Supprimer la photo
        $livreur = $db->table('livreurs')
            ->where('id_livreur', $id)
            ->get()->getRowArray();
        if ($livreur && $livreur['photo']) {
            $fichier = ROOTPATH . 'public/uploads/livreurs/' . $livreur['photo'];
            if (file_exists($fichier)) unlink($fichier);
        }

        // Mettre livreur_id à NULL dans livraisons historiques
        $db->table('livraisons')
            ->where('livreur_id', $id)
            ->update(['livreur_id' => null]);

        $db->table('livreurs')->where('id_livreur', $id)->delete();

        return redirect()->to('livreurs')
            ->with('success', 'Livreur supprimé.');
    }

    // ═══════════════════════════════════════════
    // DÉTAIL
    // ═══════════════════════════════════════════
    public function detail(int $id)
    {
        $db      = \Config\Database::connect();
        $livreur = $db->table('livreurs')
            ->where('id_livreur', $id)
            ->get()->getRowArray();

        if (!$livreur) {
            return redirect()->to('livreurs')
                ->with('error', 'Livreur introuvable.');
        }

        $livraisons = $db->table('livraisons l')
            ->select('l.*, d.code_commande, c.nomclient, c.telephone AS tel_client')
            ->join('depots d',  'd.id_depot = l.depot_id')
            ->join('clients c', 'c.id_client = l.client_id')
            ->where('l.livreur_id', $id)
            ->orderBy('l.created_at', 'DESC')
            ->get()->getResultArray();

        return view('pages/livreurs/detail', [
            'title'      => $livreur['nom_complet'],
            'livreur'    => $livreur,
            'livraisons' => $livraisons,
        ]);
    }
}
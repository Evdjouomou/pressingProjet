<?php
namespace App\Controllers;

class IncidentController extends BaseController
{
    private function getClients(): array
    {
        return \Config\Database::connect()
            ->table('clients')->orderBy('nomclient')->get()->getResultArray();
    }

    private function getEmployes(): array
    {
        return \Config\Database::connect()
            ->table('employes')->where('status', 'Actif')->orderBy('nom_complet')->get()->getResultArray();
    }

    // ═══════════════════════════════════════════
    // LISTE
    // ═══════════════════════════════════════════
    public function index()
    {
        $db     = \Config\Database::connect();
        $statut = $this->request->getGet('statut') ?? '';

        $q = $db->table('incidents i')
            ->select('i.*, c.nomclient, c.telephone,
                      r.nom_complet AS responsable,
                      d.code_commande,
                      l.nom_libelle AS article_nom')
            ->join('clients c',         'c.id_client = i.client_id')
            ->join('employes r',         'r.id_employe = i.responsable_id',          'left')
            ->join('depots d',           'd.id_depot = i.depot_id',                  'left')
            ->join('depot_articles da',  'da.id_article_depose = i.article_depose_id','left')
            ->join('libelles l',         'l.id_libelle = da.libelle_id',             'left')
            ->orderBy('i.created_at', 'DESC');

        if ($statut) $q->where('i.statut', $statut);

        $incidents = $q->get()->getResultArray();

        $stats = [
            'ouvert'       => count(array_filter($incidents, fn($i) => $i['statut'] === 'ouvert')),
            'en_traitement'=> count(array_filter($incidents, fn($i) => $i['statut'] === 'en_traitement')),
            'critique'     => count(array_filter($incidents, fn($i) => $i['gravite'] === 'critique')),
        ];

        return view('pages/incidents/index', [
            'title'     => 'Incidents',
            'incidents' => $incidents,
            'stats'     => $stats,
            'statut'    => $statut,
        ]);
    }

    // ═══════════════════════════════════════════
    // NOUVEAU
    // ═══════════════════════════════════════════
    public function nouveau()
    {
        return view('pages/incidents/nouveau', [
            'title'    => 'Déclarer un incident',
            'clients'  => $this->getClients(),
            'employes' => $this->getEmployes(),
        ]);
    }

    // ═══════════════════════════════════════════
    // CRÉER
    // ═══════════════════════════════════════════
    public function store()
    {
        $db   = \Config\Database::connect();
        $code = 'INC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $now  = date('Y-m-d H:i:s');

        $db->table('incidents')->insert([
            'client_id'         => $this->request->getPost('client_id'),
            'depot_id'          => $this->request->getPost('depot_id') ?: null,
            'article_depose_id' => $this->request->getPost('article_depose_id') ?: null,
            'responsable_id'    => $this->request->getPost('responsable_id') ?: null,
            'declare_par_id'    => session()->get('employe_id'),
            'code_incident'     => $code,
            'type_incident'     => $this->request->getPost('type_incident'),
            'description'       => $this->request->getPost('description'),
            'gravite'           => $this->request->getPost('gravite'),
            'delai_resolution'  => $this->request->getPost('delai_resolution') ?: null,
            'statut'            => 'ouvert',
            'declare_par'    => employe_connecte_id(),
            'enregistre_par'    => employe_connecte_id(),
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
        $idIncident = $db->insertID();

        // Upload photos si présentes
        $photos = $this->request->getFiles('photos');
        if ($photos && isset($photos['photos'])) {
            foreach ($photos['photos'] as $photo) {
                if ($photo->isValid() && !$photo->hasMoved()) {
                    $nom = $photo->getRandomName();
                    $photo->move(ROOTPATH . 'public/uploads/incidents', $nom);
                    $db->table('incident_photos')->insert([
                        'incident_id' => $idIncident,
                        'nom_fichier' => $nom,
                        'created_at'  => $now,
                    ]);
                }
            }
        }

        // Notification client
        $client = $db->table('clients')
            ->where('id_client', $this->request->getPost('client_id'))
            ->get()->getRowArray();

        if ($client) {
            $delai = $this->request->getPost('delai_resolution')
                ? date('d/m/Y', strtotime($this->request->getPost('delai_resolution')))
                : 'À définir';

            $notif = new \App\Services\NotificationService();
            $notif->envoyer(
                $client['id_client'],
                'campagne',
                'Incident signalé — ' . $code,
                "Bonjour {$client['nomclient']},<br><br>
                 Un incident concernant votre commande a été signalé
                 et est en cours de traitement.<br><br>
                 <strong>Référence :</strong> {$code}<br>
                 <strong>Délai de résolution prévu :</strong> {$delai}<br><br>
                 Notre équipe vous contactera très prochainement.
                 Nous nous excusons pour la gêne occasionnée.",
                null,
                ['interne', 'email']
            );
        }

        return redirect()->to('incidents/' . $idIncident)
            ->with('success', 'Incident ' . $code . ' déclaré.');
    }

    // ═══════════════════════════════════════════
    // DÉTAIL
    // ═══════════════════════════════════════════
    public function detail(int $id)
    {
        $db       = \Config\Database::connect();
        $incident = $db->table('incidents i')
            ->select('i.*, c.nomclient, c.telephone, c.email,
                      r.nom_complet AS responsable,
                      dp.nom_complet AS declare_par,
                      d.code_commande,
                      da.barcode_unique, da.designation_libre,
                      l.nom_libelle AS article_nom')
            ->join('clients c',         'c.id_client = i.client_id')
            ->join('employes r',         'r.id_employe = i.responsable_id',           'left')
            ->join('employes dp',        'dp.id_employe = i.declare_par_id',          'left')
            ->join('depots d',           'd.id_depot = i.depot_id',                   'left')
            ->join('depot_articles da',  'da.id_article_depose = i.article_depose_id','left')
            ->join('libelles l',         'l.id_libelle = da.libelle_id',              'left')
            ->where('i.id_incident', $id)
            ->get()->getRowArray();

        if (!$incident) return redirect()->to('incidents')->with('error', 'Incident introuvable.');

        $photos = $db->table('incident_photos')
            ->where('incident_id', $id)
            ->get()->getResultArray();

        return view('pages/incidents/detail', [
            'title'    => 'Incident ' . $incident['code_incident'],
            'incident' => $incident,
            'photos'   => $photos,
            'employes' => $this->getEmployes(),
        ]);
    }

    // ═══════════════════════════════════════════
    // MODIFIER
    // ═══════════════════════════════════════════
    public function update(int $id)
    {
        \Config\Database::connect()->table('incidents')->where('id_incident', $id)->update([
            'type_incident'    => $this->request->getPost('type_incident'),
            'description'      => $this->request->getPost('description'),
            'gravite'          => $this->request->getPost('gravite'),
            'statut'           => $this->request->getPost('statut'),
            'responsable_id'   => $this->request->getPost('responsable_id') ?: null,
            'delai_resolution' => $this->request->getPost('delai_resolution') ?: null,
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('incidents/' . $id)->with('success', 'Incident mis à jour.');
    }

    // ═══════════════════════════════════════════
    // CLÔTURER AVEC RÉSOLUTION
    // ═══════════════════════════════════════════
    public function cloturer(int $id)
    {
        $db       = \Config\Database::connect();
        $incident = $db->table('incidents i')
            ->select('i.*, c.nomclient, c.id_client, c.email')
            ->join('clients c', 'c.id_client = i.client_id')
            ->where('i.id_incident', $id)
            ->get()->getRowArray();

        if (!$incident) return redirect()->back()->with('error', 'Incident introuvable.');

        $typeResolution   = $this->request->getPost('type_resolution');
        $montantResolution = (float) $this->request->getPost('montant_resolution') ?: 0;

        $db->transStart();

        // Mettre à jour l'incident
        $db->table('incidents')->where('id_incident', $id)->update([
            'statut'             => 'cloture',
            'type_resolution'    => $typeResolution,
            'montant_resolution' => $montantResolution,
            'note_resolution'    => $this->request->getPost('note_resolution'),
            'date_cloture'       => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);

        // Si avoir → créditer solde_prepaye client
        if ($typeResolution === 'avoir' && $montantResolution > 0) {
            $db->table('clients')
               ->where('id_client', $incident['client_id'])
               ->set('solde_prepaye', 'solde_prepaye + ' . $montantResolution, false)
               ->update();
        }

        $db->transComplete();

        // Notifier le client de la résolution
        $notif = new \App\Services\NotificationService();
        $resolutionLabel = [
            'avoir'         => 'un avoir de ' . number_format($montantResolution, 0, ',', ' ') . ' FCFA',
            'remboursement' => 'un remboursement de ' . number_format($montantResolution, 0, ',', ' ') . ' FCFA',
            'compensation'  => 'une compensation',
            'aucune'        => 'une résolution sans compensation financière',
        ][$typeResolution] ?? 'une résolution';

        $notif->envoyer(
            $incident['id_client'],
            'campagne',
            '✅ Incident ' . $incident['code_incident'] . ' résolu',
            "Bonjour {$incident['nomclient']},<br><br>
             L'incident <strong>{$incident['code_incident']}</strong>
             a été clôturé avec <strong>{$resolutionLabel}</strong>.<br><br>
             " . ($this->request->getPost('note_resolution')
                 ? '<em>' . esc($this->request->getPost('note_resolution')) . '</em><br><br>'
                 : '') . "
             Merci de votre compréhension.",
            null,
            ['interne', 'email']
        );

        return redirect()->to('incidents/' . $id)
            ->with('success', 'Incident clôturé avec résolution : ' . $typeResolution . '.');
    }

    // ═══════════════════════════════════════════
    // PHOTOS
    // ═══════════════════════════════════════════
    public function ajouterPhoto(int $id)
    {
        $photos = $this->request->getFiles('photos');
        if ($photos && isset($photos['photos'])) {
            $db  = \Config\Database::connect();
            $now = date('Y-m-d H:i:s');
            foreach ($photos['photos'] as $photo) {
                if ($photo->isValid() && !$photo->hasMoved()) {
                    $nom = $photo->getRandomName();
                    $photo->move(ROOTPATH . 'public/uploads/incidents', $nom);
                    $db->table('incident_photos')->insert([
                        'incident_id' => $id,
                        'nom_fichier' => $nom,
                        'created_at'  => $now,
                    ]);
                }
            }
        }
        return redirect()->to('incidents/' . $id)->with('success', 'Photo(s) ajoutée(s).');
    }

    public function supprimerPhoto(int $idPhoto)
    {
        $db    = \Config\Database::connect();
        $photo = $db->table('incident_photos')->where('id_photo', $idPhoto)->get()->getRowArray();
        if ($photo) {
            $fichier = ROOTPATH . 'public/uploads/incidents/' . $photo['nom_fichier'];
            if (file_exists($fichier)) unlink($fichier);
            $db->table('incident_photos')->where('id_photo', $idPhoto)->delete();
        }
        return redirect()->back()->with('success', 'Photo supprimée.');
    }
}
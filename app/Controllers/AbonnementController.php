<?php
namespace App\Controllers;

class AbonnementController extends BaseController
{
    // ═══════════════════════════════════════════
    // LISTE TOUS LES ABONNEMENTS
    // ═══════════════════════════════════════════
    public function index()
    {
        $db = \Config\Database::connect();

        $db->query("
            UPDATE abonnements
            SET statut = 'expire', updated_at = NOW()
            WHERE statut = 'actif' AND date_fin < CURDATE()
        ");

        $abonnements = $db->table('abonnements a')
            ->select("a.*,
                    c.nomclient, c.telephone,
                    COALESCE(a.offre_nom_snapshot, t.nom, 'Offre supprimée') AS offre_nom,
                    COALESCE(a.offre_nb_articles_snapshot, t.nb_articles, 0) AS offre_articles,
                    e.nom_complet AS enregistre_par")
            ->join('clients c',   'c.id_client = a.client_id')
            ->join('type_abon t','t.id_type_abon = a.type_abon_id', 'left')
            ->join('employes e', 'e.id_employe = a.employe_id', 'left')
            ->orderBy('a.created_at', 'DESC')
            ->get()->getResultArray();

        $stats = [
            'actifs'   => count(array_filter($abonnements, fn($a) => $a['statut'] === 'actif')),
            'expires'  => count(array_filter($abonnements, fn($a) => $a['statut'] === 'expire')),
            'ca_total' => array_sum(array_column($abonnements, 'montant_paye')),
        ];

        return view('pages/abonnements/index', [
            'title'       => 'Abonnements',
            'abonnements' => $abonnements,
            'stats'       => $stats,
        ]);
    }

    // ═══════════════════════════════════════════
    // GESTION DES OFFRES (admin)
    // ═══════════════════════════════════════════
    public function offres()
    {
        $db = \Config\Database::connect();

        $offres = $db->table('type_abon')
            ->orderBy('prix', 'ASC')
            ->get()->getResultArray();

        return view('pages/abonnements/offres', [
            'title'  => 'Offres d\'abonnement',
            'offres' => $offres,
        ]);
    }

    public function storeOffre()
    {
        \Config\Database::connect()->table('type_abon')->insert([
            'nom'         => $this->request->getPost('nom'),
            'description' => $this->request->getPost('description'),
            'prix'        => (float) $this->request->getPost('prix'),
            'nb_articles' => (int)   $this->request->getPost('nb_articles'),
            'duree_jours' => (int)   ($this->request->getPost('duree_jours') ?? 30),
            'actif'       => 1,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('abonnements/offres')
            ->with('success', 'Offre créée.');
    }

    public function updateOffre(int $id)
    {
        \Config\Database::connect()->table('type_abon')
            ->where('id_type_abon', $id)->update([
                'nom'         => $this->request->getPost('nom'),
                'description' => $this->request->getPost('description'),
                'prix'        => (float) $this->request->getPost('prix'),
                'nb_articles' => (int)   $this->request->getPost('nb_articles'),
                'duree_jours' => (int)   ($this->request->getPost('duree_jours') ?? 30),
                'actif'       => (int)   $this->request->getPost('actif'),
            ]);
        return redirect()->to('abonnements/offres')
            ->with('success', 'Offre mise à jour.');
    }

    public function deleteOffre(int $id)
    {
        $db = \Config\Database::connect();

        // Vérifier si des abonnements actifs sont liés à cette offre
        $nbActifs = $db->table('abonnements')
            ->where('type_abon_id', $id)
            ->whereIn('statut', ['actif'])
            ->countAllResults();

        if ($nbActifs > 0) {
            // Ne pas supprimer — désactiver uniquement
            $db->table('type_abon')
                ->where('id_type_abon', $id)
                ->update(['actif' => 0]);

            return redirect()->to('abonnements/offres')
                ->with('warning',
                    'Cette offre a ' . $nbActifs . ' abonnement(s) actif(s). '
                    . 'Elle a été <strong>désactivée</strong> au lieu d\'être supprimée '
                    . 'pour ne pas impacter les clients abonnés.'
                );
        }

        // Vérifier s'il y a des abonnements historiques (expirés/annulés)
        $nbHistorique = $db->table('abonnements')
            ->where('type_abon_id', $id)
            ->countAllResults();

        if ($nbHistorique > 0) {
            // Mettre type_abon_id à NULL dans les abonnements historiques
            // grâce au snapshot, les données sont préservées
            $db->query("
                UPDATE abonnements
                SET type_abon_id = NULL
                WHERE type_abon_id = ?
                AND statut IN ('expire', 'annule', 'suspendu')
            ", [$id]);
        }

        // Supprimer l'offre
        $db->table('type_abon')->where('id_type_abon', $id)->delete();

        return redirect()->to('abonnements/offres')
            ->with('success', 'Offre supprimée.');
    }

    // ═══════════════════════════════════════════
    // NOUVELLE SOUSCRIPTION
    // ═══════════════════════════════════════════
    public function nouveau(int $clientId)
    {
        $db     = \Config\Database::connect();
        $client = $db->table('clients')
            ->where('id_client', $clientId)
            ->get()->getRowArray();

        if (!$client) {
            return redirect()->to('client')
                ->with('error', 'Client introuvable.');
        }

        $offres = $db->table('type_abon')
            ->where('actif', 1)
            ->orderBy('prix', 'ASC')
            ->get()->getResultArray();

        // Abonnement actif existant
        $abonActif = $db->table('abonnements')
            ->where('client_id', $clientId)
            ->where('statut', 'actif')
            ->where('date_fin >=', date('Y-m-d'))
            ->orderBy('date_fin', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        return view('pages/abonnements/nouveau', [
            'title'      => 'Nouvel abonnement',
            'client'     => $client,
            'offres'     => $offres,
            'abon_actif' => $abonActif,
        ]);
    }

    // ═══════════════════════════════════════════
    // SOUSCRIRE / RENOUVELER
    // ═══════════════════════════════════════════
    public function souscrire()
    {
        $db         = \Config\Database::connect();
        $clientId   = (int) $this->request->getPost('client_id');
        $typeAbonId = (int) $this->request->getPost('type_abon_id');
        $now        = date('Y-m-d H:i:s');
        $today      = date('Y-m-d');

        $offre = $db->table('type_abon')
            ->where('id_type_abon', $typeAbonId)
            ->get()->getRowArray();

        if (!$offre) {
            return redirect()->back()->with('error', 'Offre introuvable.');
        }

        // Report des articles restants d'un abonnement précédent
        $abonPrecedent = $db->table('abonnements')
            ->where('client_id', $clientId)
            ->whereIn('statut', ['actif', 'expire'])
            ->where('nb_articles_restants >', 0)
            ->orderBy('date_fin', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        $reportArticles = $abonPrecedent
            ? (int) $abonPrecedent['nb_articles_restants']
            : 0;

        $totalArticles = $offre['nb_articles'] + $reportArticles;
        $code          = 'ABN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $dateFin       = date('Y-m-d', strtotime('+' . $offre['duree_jours'] . ' days'));

        $db->transStart();

        // Expirer l'ancien abonnement actif
        $db->query("
            UPDATE abonnements
            SET statut = 'expire', updated_at = ?
            WHERE client_id = ? AND statut = 'actif'
        ", [$now, $clientId]);

        // Créer le nouvel abonnement avec SNAPSHOT de l'offre
        $db->table('abonnements')->insert([
            'client_id'                  => $clientId,
            'type_abon_id'               => $typeAbonId,
            'employe_id'                 => session()->get('employe_id'),
            'code_abonnement'            => $code,

            // ── Snapshot de l'offre au moment de la souscription ──
            'offre_nom_snapshot'         => $offre['nom'],
            'offre_description_snapshot' => $offre['description'],
            'offre_prix_snapshot'        => $offre['prix'],
            'offre_nb_articles_snapshot' => $offre['nb_articles'],
            // ───────────────────────────────────────────────────────

            'date_debut'                 => $today,
            'date_fin'                   => $dateFin,
            'nb_articles_total'          => $totalArticles,
            'nb_articles_utilises'       => 0,
            'nb_articles_restants'       => $totalArticles,
            'montant_paye'               => $offre['prix'],
            'statut'                     => 'actif',
            'note'                       => $reportArticles > 0
                ? "Inclut le report de {$reportArticles} article(s) de l'abonnement précédent."
                : null,
            'created_at'                 => $now,
            'updated_at'                 => $now,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Erreur lors de la souscription.');
        }

        // Notification client
        try {
            $client = $db->table('clients')
                ->where('id_client', $clientId)
                ->get()->getRowArray();

            $notif = new \App\Services\NotificationService();
            $notif->envoyer(
                $clientId,
                'campagne',
                '🎉 Abonnement ' . $offre['nom'] . ' activé !',
                "Bonjour {$client['nomclient']},<br><br>
                Votre abonnement <strong>{$offre['nom']}</strong> est activé.<br>
                <strong>Valable du :</strong> " . date('d/m/Y') . "
                au " . date('d/m/Y', strtotime($dateFin)) . "<br>
                <strong>Articles inclus :</strong> {$totalArticles} articles"
                . ($reportArticles > 0
                    ? " (dont {$reportArticles} reportés)"
                    : "") . "<br><br>
                Référence : <strong>{$code}</strong><br><br>
                Merci de votre fidélité !<br><strong>Pressing Pro</strong>",
                null,
                ['interne', 'email']
            );
        } catch (\Exception $e) {}

        return redirect()->to('ficheclient/' . $clientId)
            ->with('success', 'Abonnement ' . $code . ' activé — '
                . $totalArticles . ' articles disponibles.'
                . ($reportArticles > 0 ? ' (dont ' . $reportArticles . ' reportés)' : ''));
    }

    // ═══════════════════════════════════════════
    // DÉTAIL ABONNEMENT
    // ═══════════════════════════════════════════
    // ═══════════════════════════════════════════
    // DÉTAIL ABONNEMENT
    // ═══════════════════════════════════════════
    public function detail(int $id)
    {
        $db   = \Config\Database::connect();
        $abon = $db->table('abonnements a')
            ->select("a.*,
                    c.nomclient, c.telephone, c.email,
                    COALESCE(a.offre_nom_snapshot, t.nom, 'Offre supprimée') AS offre_nom,
                    COALESCE(a.offre_description_snapshot, t.description, '') AS offre_description,
                    COALESCE(a.offre_nb_articles_snapshot, t.nb_articles, 0) AS offre_articles,
                    e.nom_complet AS enregistre_par")
            ->join('clients c',   'c.id_client = a.client_id')
            ->join('type_abon t','t.id_type_abon = a.type_abon_id', 'left')
            ->join('employes e', 'e.id_employe = a.employe_id', 'left')
            ->where('a.id_abonnement', $id)
            ->get()->getRowArray();

        if (!$abon) {
            return redirect()->to('abonnements')->with('error', 'Abonnement introuvable.');
        }

        $depots = $db->table('depots d')
            ->select('d.code_commande, d.created_at, d.statut_global,
                    COUNT(da.id_article_depose) AS nb_articles')
            ->join('depot_articles da','da.depot_id = d.id_depot', 'left')
            ->where('d.abonnement_id', $id)
            ->groupBy('d.id_depot')
            ->orderBy('d.created_at', 'DESC')
            ->get()->getResultArray();

        return view('pages/abonnements/detail', [
            'title'  => 'Abonnement ' . $abon['code_abonnement'],
            'abon'   => $abon,
            'depots' => $depots,
        ]);
    }

    // ═══════════════════════════════════════════
    // ANNULER
    // ═══════════════════════════════════════════
    public function annuler(int $id)
    {
        \Config\Database::connect()->table('abonnements')
            ->where('id_abonnement', $id)
            ->update([
                'statut'     => 'annule',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        return redirect()->to('abonnements')
            ->with('success', 'Abonnement annulé.');
    }

    // ═══════════════════════════════════════════
    // API — Abonnement actif d'un client
    // ═══════════════════════════════════════════
    public function apiAbonnementActif(int $clientId)
    {
        $db = \Config\Database::connect();

        $abon = $db->table('abonnements a')
            ->select('a.*, t.nom AS offre_nom, t.nb_articles AS offre_articles')
            ->join('type_abon t', 't.id_type_abon = a.type_abon_id')
            ->where('a.client_id', $clientId)
            ->where('a.statut', 'actif')
            ->where('a.date_fin >=', date('Y-m-d'))
            ->where('a.nb_articles_restants >', 0)
            ->orderBy('a.date_fin', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        if (!$abon) {
            return $this->response->setJSON([
                'actif'   => false,
                'message' => 'Aucun abonnement actif.',
            ]);
        }

        return $this->response->setJSON([
            'actif'        => true,
            'abonnement'   => $abon,
            'restants'     => $abon['nb_articles_restants'],
            'date_fin'     => date('d/m/Y', strtotime($abon['date_fin'])),
            'offre_nom'    => $abon['offre_nom'],
        ]);
    }
}
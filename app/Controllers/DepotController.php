<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\LibelleModel;
use App\Models\DepotModel;
use App\Models\DepotArticleModel;
use App\Models\DepotPrestationModel;
use CodeIgniter\API\ResponseTrait;

class DepotController extends BaseController
{
    use ResponseTrait;
    // ════════════════════════════════════════════
    // PAGE PRINCIPALE
    // ════════════════════════════════════════════
    public function index()
    {
        $db           = \Config\Database::connect();
        $clientModel  = new ClientModel();
        $libelleModel = new LibelleModel();

        // ← AJOUT : vérifier si une caisse est ouverte
        $caissePourVue = $db->table('caisses')
            ->where('statut', 'ouverte')
            ->orderBy('date_ouverture', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        return view('pages/depots/depotarticle', [
            'title'        => 'Nouveau Dépôt',
            'libelles'     => $libelleModel->findAll(),
            'clients'      => $clientModel->findAll(),
            'caissePourVue'=> $caissePourVue, // ← AJOUT
        ]);
    }


    public function liste()
    {
        $depotModel = new DepotModel();
        // On récupère les dépôts avec le nom du client joint
        $data['depots'] = $depotModel->select('depots.*, clients.nom as client_nom')
                                     ->join('clients', 'clients.id_client = depots.client_id')
                                     ->findAll();

        return view('depot/liste', $data);
    }

    // ════════════════════════════════════════════
    // AJAX : Prestations par libellé
    // ════════════════════════════════════════════
    public function getPrestationsByArticle($idLibelle)
    {
        $db = \Config\Database::connect();

        $services = $db->table('services')
            ->select('id_service, type_prestation, prix_unitaire_base, points_fidelite, majoration_express')
            ->where('libelle_id', $idLibelle)
            ->where('statut', 'actif')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($services);
    }

    // ════════════════════════════════════════════
    // ENREGISTREMENT DU DÉPÔT
    // ════════════════════════════════════════════


    public function valider()
    {
        $db = \Config\Database::connect();

        $idClient        = $this->request->getPost('id_client');
        $dateRetrait     = $this->request->getPost('date_retrait');
        $numBon          = $this->request->getPost('numero_bon') ?: 'BON-' . time();
        $acompte         = (float) ($this->request->getPost('acompte') ?: 0);
        $modePaiement    = $this->request->getPost('mode_paiement') ?: 'especes';
        $libellesIds     = $this->request->getPost('articles_libelle_id');
        $articlesPrix    = $this->request->getPost('articles_prix');
        $prestaIds       = $this->request->getPost('articles_presta_id');
        $articlesExpress = $this->request->getPost('articles_express');
        $articlesCouleur = $this->request->getPost('articles_couleur');
        $articlesMarque  = $this->request->getPost('articles_marque');
        $articlesMatiere = $this->request->getPost('articles_matiere');
        $articlesObs     = $this->request->getPost('articles_obs');

        if (empty($idClient) || empty($libellesIds)) {
            return redirect()->back()->with('error', 'Le panier est vide ou le client n\'est pas sélectionné.');
        }

        // ── Vérification caisse ouverte si acompte > 0 ──
        $caisseCourante = null;
        if ($acompte > 0) {
            $caisseCourante = $db->table('caisses')
                ->where('statut', 'ouverte')
                ->orderBy('date_ouverture', 'DESC')
                ->limit(1)
                ->get()->getRowArray();

            if (!$caisseCourante) {
                return redirect()->back()->with('error',
                    'Aucune caisse ouverte. Ouvrez la caisse avant d\'encaisser un acompte.'
                );
            }
        }

        $db->transStart();

        // 1. Insertion du dépôt
        $depotModel = new \App\Models\DepotModel();
        $idDepot = $depotModel->insert([
            'code_commande'         => $numBon,
            'client_id'             => $idClient,
            'shop_id'               => shop_actif_id(),
            'total_ttc'             => array_sum($articlesPrix),
            'acompte_verse'         => $acompte,
            'date_livraison_prevue' => $dateRetrait ?: null,
            'statut_global'         => 'depot',
            'enregistre_par'        => employe_connecte_id(),
        ]);

        // 2. Récupérer étape 1 pour le workflow
        $etape1 = $db->table('etapes_production')
                    ->where('ordre', 1)
                    ->where('est_actif', 1)
                    ->get()->getRowArray();

        $artModel    = new \App\Models\DepotArticleModel();
        $prestaModel = new \App\Models\DepotPrestationModel();
        $totalPointsCalcules = 0;

        foreach ($libellesIds as $index => $libelleId) {
            $designation = trim(($articlesMarque[$index] ?? '') . ' ' . ($articlesCouleur[$index] ?? ''));

            $idArtDepose = $artModel->insert([
                'depot_id'          => $idDepot,
                'libelle_id'        => $libelleId,
                'designation_libre' => $designation,
                'matiere'           => $articlesMatiere[$index] ?? null,
                'observations'      => $articlesObs[$index] ?? null,
                'barcode_unique'    => 'BC-' . time() . '-' . $index,
                'statut_article'    => 'recu',
                'etape_courante_id' => $etape1['id_etape'] ?? 1,
            ]);

            $prestaModel->insert([
                'article_depose_id' => $idArtDepose,
                'service_id'        => $prestaIds[$index],
                'prix_applique'     => $articlesPrix[$index],
                'options_express'   => ($articlesExpress[$index] == '1') ? 1 : 0,
            ]);

            if ($etape1) {
                $db->table('article_workflow')->insert([
                    'article_depose_id' => $idArtDepose,
                    'etape_id'          => $etape1['id_etape'],
                    'date_entree'       => date('Y-m-d H:i:s'),
                ]);
            }

            $service = $db->table('services')
                        ->select('points_fidelite')
                        ->where('id_service', $prestaIds[$index])
                        ->get()->getRowArray();

            if ($service) {
                $totalPointsCalcules += (int) $service['points_fidelite'];
            }
        }

        // 3. Créer transaction si acompte > 0
        if ($acompte > 0 && $caisseCourante) {
            $db->table('transactions')->insert([
                'depot_id'        => $idDepot,
                'caisse_id'       => $caisseCourante['id_caisse'],
                'employe_id'      => session()->get('employe_id'),
                'client_id'       => $idClient,
                'type'            => 'encaissement',
                'montant'         => $acompte,
                'mode_paiement'   => $modePaiement,
                'montant_especes' => $modePaiement === 'especes'      ? $acompte : 0,
                'montant_mobile'  => $modePaiement === 'mobile_money' ? $acompte : 0,
                'montant_carte'   => $modePaiement === 'carte'        ? $acompte : 0,
                'statut'          => 'valide',
                'motif'           => 'Acompte à la réception — ' . $numBon,
                'created_at'      => date('Y-m-d H:i:s'),
            ]);

            // Mettre à jour les totaux de la caisse
            $db->table('caisses')
            ->where('id_caisse', $caisseCourante['id_caisse'])
            ->update([
                'total_especes' => $caisseCourante['total_especes'] + ($modePaiement === 'especes'      ? $acompte : 0),
                'total_mobile'  => $caisseCourante['total_mobile']  + ($modePaiement === 'mobile_money' ? $acompte : 0),
                'total_carte'   => $caisseCourante['total_carte']   + ($modePaiement === 'carte'        ? $acompte : 0),
                'total_ca'      => $caisseCourante['total_ca']      + $acompte,
            ]);
        }

        // ── Vérifier si le client a un abonnement actif ────────
        $abonnement = null;
        $nbArticlesDepot = count($libellesIds);

        $abonnement = $db->table('abonnements')
            ->where('client_id', $idClient)
            ->where('statut', 'actif')
            ->where('date_fin >=', date('Y-m-d'))
            ->where('nb_articles_restants >=', $nbArticlesDepot)
            ->orderBy('date_fin', 'ASC')
            ->limit(1)
            ->get()->getRowArray();

        if ($abonnement) {
            // Lier le dépôt à l'abonnement
            $db->table('depots')->where('id_depot', $idDepot)->update([
                'abonnement_id' => $abonnement['id_abonnement'],
                'total_ttc'     => 0, // Gratuit car abonnement
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);

            // Décrémenter les articles restants
            $db->table('abonnements')
                ->where('id_abonnement', $abonnement['id_abonnement'])
                ->update([
                    'nb_articles_utilises' => $abonnement['nb_articles_utilises'] + $nbArticlesDepot,
                    'nb_articles_restants' => $abonnement['nb_articles_restants'] - $nbArticlesDepot,
                    'updated_at'           => date('Y-m-d H:i:s'),
                ]);
        }

        // 4. Créditer points fidélité
        if ($totalPointsCalcules > 0) {
            $db->table('clients')
            ->where('id_client', $idClient)
            ->set('solde_fidelite', 'solde_fidelite + ' . $totalPointsCalcules, false)
            ->update();
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Erreur lors de la sauvegarde en base de données.');
        }

        // 5. Notification dépôt confirmé
        $notif = new \App\Services\NotificationService();
        $notif->depotConfirme($idDepot);

        return redirect()->to('/depot')->with('success', 'Dépôt enregistré avec succès ! Bon n° ' . $numBon);
    }

    public function listedepot()
    {
        $db = \Config\Database::connect();

        $recherche = $this->request->getGet('q') ?? '';
        $page      = (int) ($this->request->getGet('page') ?? 1);
        $parPage   = 10;
        $offset    = ($page - 1) * $parPage;

        $builder = $db->table('depots d')
            ->select('
                d.id_depot,
                d.code_commande,
                d.created_at,
                d.date_livraison_prevue,
                d.total_ttc,
                d.acompte_verse,
                d.statut_global,
                c.nomclient,
                c.telephone,
                COUNT(DISTINCT da.id_article_depose) AS nb_articles,
                COALESCE(SUM(s.points_fidelite), 0)  AS total_points
            ')
            ->join('clients c',          'c.id_client = d.client_id')
            ->join('depot_articles da',  'da.depot_id = d.id_depot',                        'left')
            ->join('depot_prestations dp','dp.article_depose_id = da.id_article_depose',    'left')
            ->join('services s',         's.id_service = dp.service_id',                    'left')
            ->groupBy('d.id_depot')
            ->orderBy('d.created_at', 'DESC');

        filtrer_par_shop($builder, 'd');

        if ($recherche) {
            $builder->groupStart()
                ->like('d.code_commande', $recherche)
                ->orLike('c.nomclient',   $recherche)
                ->orLike('c.telephone',   $recherche)
            ->groupEnd();
        }

        // Total pour la pagination
        $total   = $db->table('depots d')
            ->join('clients c', 'c.id_client = d.client_id')
            ->groupStart()
                ->like('d.code_commande', $recherche)
                ->orLike('c.nomclient',   $recherche)
                ->orLike('c.telephone',   $recherche)
            ->groupEnd()
            ->countAllResults();

        $depots     = $builder->limit($parPage, $offset)->get()->getResultArray();
        $totalPages = (int) ceil($total / $parPage);

        return view('pages/depots/listedepot', [
            'title'       => 'Liste des dépôts',
            'depots'      => $depots,
            'recherche'   => $recherche,
            'page'        => $page,
            'totalPages'  => $totalPages,
            'total'       => $total,
            'parPage'     => $parPage,
        ]);
    }

    // ════════════════════════════════════════════
    // HELPERS PRIVÉS
    // ════════════════════════════════════════════
    private function getDepotComplet(int $id): ?array
    {
        $db = \Config\Database::connect();

        $depot = $db->table('depots d')
                    ->select('d.*, c.nomclient, c.telephone, c.email,
                            e.nom_complet AS enregistre_par_nom,
                            e.matricule   AS enregistre_par_matricule')
                    ->join('clients c',  'c.id_client = d.client_id')
                    ->join('employes e', 'e.id_employe = d.enregistre_par', 'left')
                    ->where('d.id_depot', $id)
                    ->get()->getRowArray();

        if (!$depot) return null;

        $articles = $db->table('depot_articles da')
            ->select('da.*, l.nom_libelle, l.categorie,
                    dp.article_depose_id as presta_art_id,
                    dp.service_id, dp.prix_applique, dp.options_express,
                    s.type_prestation, s.points_fidelite')
            ->join('libelles l', 'l.id_libelle = da.libelle_id')
            ->join('depot_prestations dp', 'dp.article_depose_id = da.id_article_depose', 'left')
            ->join('services s', 's.id_service = dp.service_id', 'left')
            ->where('da.depot_id', $id)
            ->get()->getResultArray();

        $depot['articles'] = $articles;
        $depot['nb_articles'] = count($articles);
        $depot['total_points'] = array_sum(array_column($articles, 'points_fidelite'));

        return $depot;
    }

    // ════════════════════════════════════════════
    // PAGE DÉTAIL
    // ════════════════════════════════════════════
    public function detail(int $id)
    {
        $depot = $this->getDepotComplet($id);
        if (!$depot) return redirect()->to('/depot')->with('error', 'Dépôt introuvable.');

        return view('pages/depots/detail', ['depot' => $depot, 'title' => 'Dépôt ' . $depot['code_commande']]);
    }

    // ════════════════════════════════════════════
    // IMPRESSION BON CLIENT
    // ════════════════════════════════════════════
    public function imprimerBon(int $id)
    {
        $depot = $this->getDepotComplet($id);
        if (!$depot) return redirect()->to('/depot')->with('error', 'Dépôt introuvable.');

        return view('pages/depots/printbon', ['depot' => $depot]);
    }

    // ════════════════════════════════════════════
    // IMPRESSION FICHE PRODUCTION
    // ════════════════════════════════════════════
    public function imprimerFiche(int $id)
    {
        $depot = $this->getDepotComplet($id);
        if (!$depot) return redirect()->to('/depot')->with('error', 'Dépôt introuvable.');

        return view('pages/depots/printfiche', ['depot' => $depot]);
    }

    // ════════════════════════════════════════════
    // TICKET ARTICLE INDIVIDUEL
    // ════════════════════════════════════════════
    public function ticket(int $idDepot, int $idArticle)
    {
        $db = \Config\Database::connect();

        $article = $db->table('depot_articles da')
            ->select('da.*, l.nom_libelle, l.categorie,
                    dp.prix_applique, dp.options_express,
                    s.type_prestation,
                    d.code_commande, d.date_livraison_prevue,
                    c.nomclient, c.telephone')
            ->join('libelles l', 'l.id_libelle = da.libelle_id')
            ->join('depot_prestations dp', 'dp.article_depose_id = da.id_article_depose', 'left')
            ->join('services s', 's.id_service = dp.service_id', 'left')
            ->join('depots d', 'd.id_depot = da.depot_id')
            ->join('clients c', 'c.id_client = d.client_id')
            ->where('da.id_article_depose', $idArticle)
            ->where('da.depot_id', $idDepot)
            ->get()->getRowArray();

        if (!$article) return redirect()->back()->with('error', 'Article introuvable.');

        return view('pages/depots/printticket', ['article' => $article]);
    }

    public function marquerPaye(int $id)
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $depot = $db->table('depots')
            ->where('id_depot', $id)
            ->get()->getRowArray();

        if (!$depot) {
            return redirect()->to('depot/detail/' . $id)
                ->with('error', 'Dépôt introuvable.');
        }

        $montantEncaisse = (float) ($this->request->getPost('montant_encaisse') ?? 0);
        $modeReglement   = $this->request->getPost('mode_reglement') ?? 'especes';

        if ($montantEncaisse <= 0) {
            return redirect()->to('depot/detail/' . $id)
                ->with('error', 'Le montant doit être supérieur à 0.');
        }

        // ── Caisse ouverte ────────────────────────────────────────
        $caisse = $db->table('caisses')
            ->where('statut', 'ouverte')
            ->orderBy('date_ouverture', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        if (!$caisse) {
            return redirect()->to('depot/detail/' . $id)
                ->with('error', 'Aucune caisse ouverte. Ouvrez la caisse avant d\'encaisser.');
        }

        // ── Total réellement encaissé depuis transactions ─────────
        $dejaEncaisse = (float) $db->query("
            SELECT COALESCE(SUM(montant), 0) AS total
            FROM transactions
            WHERE depot_id  = ?
            AND type      = 'encaissement'
            AND statut    = 'valide'
        ", [$id])->getRowArray()['total'];

        // ── Ne pas encaisser plus que ce qui reste ────────────────
        $resteReel = max(0, $depot['total_ttc'] - $dejaEncaisse);
        if ($montantEncaisse > $resteReel) {
            $montantEncaisse = $resteReel;
        }

        if ($montantEncaisse <= 0) {
            return redirect()->to('depot/detail/' . $id)
                ->with('error', 'Ce dépôt est déjà entièrement soldé.');
        }

        $db->transStart();

        // 1. Créer la transaction
        $db->table('transactions')->insert([
            'depot_id'        => $id,
            'caisse_id'       => $caisse['id_caisse'],
            'employe_id'      => session()->get('employe_id'),
            'client_id'       => $depot['client_id'],
            'type'            => 'encaissement',
            'montant'         => $montantEncaisse,
            'mode_paiement'   => $modeReglement,
            'montant_especes' => ($modeReglement === 'especes')      ? $montantEncaisse : 0,
            'montant_mobile'  => ($modeReglement === 'mobile_money') ? $montantEncaisse : 0,
            'montant_carte'   => ($modeReglement === 'carte')        ? $montantEncaisse : 0,
            'montant_avoir'   => 0,
            'montant_fidelite'=> 0,
            'rendu_monnaie'   => 0,
            'statut'          => 'valide',
            'motif'           => 'Règlement solde — ' . $depot['code_commande'],
            'created_at'      => $now,
        ]);

        // 2. Synchroniser acompte_verse avec la réalité des transactions
        $nouveauTotal = $dejaEncaisse + $montantEncaisse;
        $db->table('depots')
            ->where('id_depot', $id)
            ->update([
                'acompte_verse' => $nouveauTotal,
                'updated_at'    => $now,
            ]);

        // 3. Mettre à jour les totaux de la caisse
        $db->table('caisses')
            ->where('id_caisse', $caisse['id_caisse'])
            ->update([
                'total_especes' => $caisse['total_especes']
                    + ($modeReglement === 'especes'      ? $montantEncaisse : 0),
                'total_mobile'  => $caisse['total_mobile']
                    + ($modeReglement === 'mobile_money' ? $montantEncaisse : 0),
                'total_carte'   => $caisse['total_carte']
                    + ($modeReglement === 'carte'        ? $montantEncaisse : 0),
                'total_ca'      => $caisse['total_ca'] + $montantEncaisse,
            ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('depot/detail/' . $id)
                ->with('error', 'Erreur base de données. Réessayez.');
        }

        // ── Notification si soldé ─────────────────────────────────
        $estSolde = ($nouveauTotal >= $depot['total_ttc']);
        if ($estSolde) {
            try {
                $notif = new \App\Services\NotificationService();
                $notif->retraitConfirme($id);
            } catch (\Exception $e) {
                // Ne pas bloquer si la notif échoue
            }
        }

        $message = $estSolde
            ? 'Dépôt entièrement soldé. ✅'
            : 'Paiement enregistré. Reste : '
            . number_format($depot['total_ttc'] - $nouveauTotal, 0, ',', ' ')
            . ' FCFA.';

        return redirect()->to(base_url('depot/detail/' . $id))
            ->with('success', $message);
    }

    // ═══════════════════════════════════════════
    // LISTE DES DÉPÔTS PRÊTS
    // ═══════════════════════════════════════════
    public function prets()
    {
        $db = \Config\Database::connect();

        $depots = $db->table('depots d')
            ->select('d.*, c.nomclient, c.telephone, c.email,
                    e.nom_complet AS enregistre_par_nom,
                    COUNT(da.id_article_depose) AS nb_articles,
                    l.id_livraison, l.statut AS statut_livraison,
                    l.code_livraison')
            ->join('clients c',         'c.id_client = d.client_id')
            ->join('employes e',         'e.id_employe = d.enregistre_par',   'left')
            ->join('depot_articles da',  'da.depot_id = d.id_depot',          'left')
            ->join('livraisons l',       'l.depot_id = d.id_depot',           'left')
            ->where('d.statut_global', 'pret')
            ->groupBy('d.id_depot')
            ->orderBy('d.date_livraison_prevue', 'ASC')
            ->get()->getResultArray();

        // Livreurs disponibles pour le modal
        $livreurs = $db->table('employes e')
            ->join('postes p', 'p.id_poste = e.poste_id', 'left')
            ->where('e.status', 'Actif')
            ->get()->getResultArray();

        return view('pages/depots/prets', [
            'title'    => 'Commandes prêtes',
            'depots'   => $depots,
            'livreurs' => $livreurs,
        ]);
    }

    // ═══════════════════════════════════════════
    // NOTIFIER LE CLIENT (auto ou manuel)
    // ═══════════════════════════════════════════
    public function notifierClient(int $id)
    {
        $db    = \Config\Database::connect();
        $depot = $db->table('depots d')
            ->select('d.*, c.nomclient, c.telephone, c.email, c.id_client')
            ->join('clients c', 'c.id_client = d.client_id')
            ->where('d.id_depot', $id)
            ->get()->getRowArray();

        if (!$depot) {
            return redirect()->back()->with('error', 'Dépôt introuvable.');
        }

        $notif = new \App\Services\NotificationService();

        $message = "
            Bonjour <strong>{$depot['nomclient']}</strong>,<br><br>
            🎉 Votre commande <strong>{$depot['code_commande']}</strong> est <strong>prête</strong> !<br><br>
            Vous avez deux options pour la récupérer :<br><br>
            <strong>Option 1 — Passer en boutique</strong><br>
            Venez récupérer vos vêtements directement chez nous pendant nos heures d'ouverture.<br><br>
            <strong>Option 2 — Livraison à domicile</strong><br>
            Nous pouvons vous livrer à l'adresse de votre choix.
            Contactez-nous pour organiser la livraison.<br><br>
            <em>Merci de nous indiquer votre choix dès que possible.</em><br><br>
            À bientôt,<br>
            <strong>L'équipe Pressing Pro</strong>
        ";

        $notif->envoyer(
            $depot['id_client'],
            'commande_prete',
            '✅ Votre commande ' . $depot['code_commande'] . ' est prête !',
            $message,
            $id,
            ['interne', 'email', 'sms']
        );

        // Marquer la notification comme envoyée
        $db->table('depots')->where('id_depot', $id)->update([
            'notif_pret_envoyee' => 1,
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('depot/prets')
            ->with('success', 'Notification envoyée à ' . $depot['nomclient'] . '.');
    }

    // ═══════════════════════════════════════════
    // DÉFINIR LE MODE DE RETRAIT
    // ═══════════════════════════════════════════
    public function definirModeRetrait(int $id)
    {
        $db   = \Config\Database::connect();
        $mode = $this->request->getPost('mode_retrait');
        $now  = date('Y-m-d H:i:s');

        $depot = $db->table('depots d')
            ->select('d.*, c.nomclient, c.telephone, c.email, c.id_client, c.adresse')
            ->join('clients c', 'c.id_client = d.client_id')
            ->where('d.id_depot', $id)
            ->get()->getRowArray();

        if (!$depot) {
            return redirect()->back()->with('error', 'Dépôt introuvable.');
        }

        $db->table('depots')->where('id_depot', $id)->update([
            'mode_retrait' => $mode,
            'updated_at'   => $now,
        ]);

        if ($mode === 'boutique') {
            // ── Retrait en boutique → passer directement à "livré" ──
            $db->table('depots')->where('id_depot', $id)->update([
                'statut_global' => 'livre',
                'updated_at'    => $now,
            ]);

            // Notifier le client
            $notif = new \App\Services\NotificationService();
            $notif->retraitConfirme($id);

            return redirect()->to('depot/prets')
                ->with('success', 'Retrait en boutique confirmé pour ' . $depot['nomclient'] . '.');
        }

        if ($mode === 'livraison') {
            // ── Livraison à domicile → créer la livraison ───────────
            $adresse = $this->request->getPost('adresse_livraison')
                    ?: ($depot['adresse'] ?? 'À préciser');

            $codeLiv = 'LIV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $db->table('livraisons')->insert([
                'depot_id'          => $id,
                'client_id'         => $depot['id_client'],
                'enregistre_par'    => employe_connecte_id(),
                'code_livraison'    => $codeLiv,
                'adresse_livraison' => $adresse,
                'date_livraison'    => $this->request->getPost('date_livraison') ?: null,
                'heure_livraison'   => $this->request->getPost('heure_livraison') ?: null,
                'note_client'       => $this->request->getPost('note_client'),
                'montant_livraison' => (float)($this->request->getPost('montant_livraison') ?? 0),
                'statut'            => 'en_attente',
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            // Notifier le client
            $notif = new \App\Services\NotificationService();
            $notif->envoyer(
                $depot['id_client'],
                'campagne',
                '🚚 Livraison programmée — ' . $depot['code_commande'],
                "Bonjour {$depot['nomclient']},<br><br>
                Votre demande de livraison pour la commande
                <strong>{$depot['code_commande']}</strong> a bien été enregistrée.<br>
                Référence : <strong>{$codeLiv}</strong><br><br>
                Notre livreur vous contactera avant le passage.<br><br>
                <strong>Pressing Pro</strong>",
                $id,
                ['interne', 'email']
            );

            return redirect()->to('livraison')
                ->with('success', 'Livraison ' . $codeLiv . ' créée pour ' . $depot['nomclient'] . '.');
        }

        return redirect()->to('depot/prets')->with('success', 'Mode retrait enregistré.');
    }
}
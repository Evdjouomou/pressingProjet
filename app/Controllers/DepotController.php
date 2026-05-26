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
        $clientModel  = new ClientModel();
        $libelleModel = new LibelleModel();

        $data = [
            'title'    => 'Nouveau Dépôt',
            'libelles' => $libelleModel->findAll(),
            'clients'  => $clientModel->findAll(),
        ];

        return view('pages/depots/depotarticle', $data);
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
            'total_ttc'             => array_sum($articlesPrix),
            'acompte_verse'         => $acompte,
            'date_livraison_prevue' => $dateRetrait ?: null,
            'statut_global'         => 'depot',
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
            ->select('d.*, c.nomclient, c.telephone, c.email')
            ->join('clients c', 'c.id_client = d.client_id')
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
        $db = \Config\Database::connect();

        $depot = $db->table('depots')->where('id_depot', $id)->get()->getRowArray();

        if (!$depot) {
            return redirect()->back()->with('error', 'Dépôt introuvable.');
        }

        $montantEncaisse = (float) ($this->request->getPost('montant_encaisse') ?? 0);
        $nouveauSolde    = min($depot['total_ttc'], $depot['acompte_verse'] + $montantEncaisse);

        $db->table('depots')->where('id_depot', $id)->update([
            'acompte_verse' => $nouveauSolde,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        $notif = new \App\Services\NotificationService();
        $notif->retraitConfirme($id);

        $estSolde = $nouveauSolde >= $depot['total_ttc'];
        $message  = $estSolde
            ? 'Paiement complet enregistré. Le dépôt est entièrement soldé.'
            : 'Paiement partiel enregistré. Reste : ' . number_format($depot['total_ttc'] - $nouveauSolde, 0, ',', ' ') . ' FCFA.';

        return redirect()->to(base_url('depot/detail/' . $id))->with('success', $message);
    }
}
<?php
namespace App\Controllers;

class PosController extends BaseController
{
    // ═══════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════
    private function getCaisseCourante(): ?array
    {
        return \Config\Database::connect()
            ->table('caisses')
            ->where('statut', 'ouverte')
            ->orderBy('date_ouverture', 'DESC')
            ->limit(1)
            ->get()->getRowArray() ?: null;
    }

    private function getDepotCompletPOS(int $id): ?array
    {
        $db = \Config\Database::connect();

        $depot = $db->table('depots d')
            ->select('d.*, c.nomclient, c.telephone, c.email,
                    c.solde_fidelite, c.solde_prepaye, c.id_client')
            ->join('clients c', 'c.id_client = d.client_id')
            ->where('d.id_depot', $id)
            ->get()->getRowArray();

        if (!$depot) return null;

        $articles = $db->table('depot_articles da')
            ->select('da.*, l.nom_libelle, dp.prix_applique, dp.options_express,
                    s.type_prestation, ep.libelle AS etape_libelle')
            ->join('libelles l',           'l.id_libelle = da.libelle_id')
            ->join('depot_prestations dp', 'dp.article_depose_id = da.id_article_depose', 'left')
            ->join('services s',           's.id_service = dp.service_id', 'left')
            ->join('etapes_production ep', 'ep.id_etape = da.etape_courante_id', 'left')
            ->where('da.depot_id', $id)
            ->get()->getResultArray();

        $transactions = $db->table('transactions')
            ->where('depot_id', $id)
            ->where('statut', 'valide')
            ->orderBy('created_at', 'ASC')
            ->get()->getResultArray();

        // ── Calcul fiable depuis les transactions ──────────
        $dejaPaye = array_sum(array_column(
            array_filter($transactions, fn($t) => $t['type'] === 'encaissement'),
            'montant'
        ));

        // Synchroniser acompte_verse avec la réalité des transactions
        if ((float)$depot['acompte_verse'] !== $dejaPaye) {
            $db->table('depots')
            ->where('id_depot', $id)
            ->update(['acompte_verse' => $dejaPaye]);
            $depot['acompte_verse'] = $dejaPaye;
        }

        $depot['articles']      = $articles;
        $depot['transactions']  = $transactions;
        $depot['deja_paye']     = $dejaPaye;
        $depot['reste_a_payer'] = max(0, $depot['total_ttc'] - $dejaPaye);

        return $depot;
    }

    // ═══════════════════════════════════════════
    // PAGE PRINCIPALE POS
    // ═══════════════════════════════════════════
    public function index()
    {
        $db      = \Config\Database::connect();
        $caisse  = $this->getCaisseCourante();

        $commandesPrêtes = $db->table('depots d')
            ->select('d.id_depot, d.code_commande, d.total_ttc, d.acompte_verse,
                      d.date_livraison_prevue, c.nomclient, c.telephone,
                      COUNT(da.id_article_depose) AS nb_articles')
            ->join('clients c',         'c.id_client = d.client_id')
            ->join('depot_articles da', 'da.depot_id = d.id_depot', 'left')
            ->where('d.statut_global', 'pret')
            ->groupBy('d.id_depot')
            ->orderBy('d.date_livraison_prevue', 'ASC')
            ->get()->getResultArray();

        // Calcul reste réel via transactions
        foreach ($commandesPrêtes as &$cmd) {
            $dejaPaye = $db->table('transactions')
                ->selectSum('montant')
                ->where('depot_id', $cmd['id_depot'])
                ->where('type', 'encaissement')
                ->where('statut', 'valide')
                ->get()->getRowArray()['montant'] ?? 0;
            $cmd['reste'] = max(0, $cmd['total_ttc'] - $dejaPaye);
        }

        $produits = $db->table('produits_annexes')
            ->where('actif', 1)
            ->where('stock >', 0)
            ->orderBy('nom')
            ->get()->getResultArray();

        return view('pages/pos/index', [
            'title'           => 'Point de Vente',
            'caisse'          => $caisse,
            'commandes'       => $commandesPrêtes,
            'produits'        => $produits,
        ]);
    }

    // ═══════════════════════════════════════════
    // CHARGER UNE COMMANDE (AJAX)
    // ═══════════════════════════════════════════
    public function chargerCommande(int $id)
    {
        $depot = $this->getDepotCompletPOS($id);
        if (!$depot) {
            return $this->response->setJSON(['success' => false, 'message' => 'Commande introuvable.']);
        }
        return $this->response->setJSON(['success' => true, 'depot' => $depot]);
    }

    // ═══════════════════════════════════════════
    // ENCAISSER
    // ═══════════════════════════════════════════
    public function encaisser()
    {
        $db      = \Config\Database::connect();
        $caisse  = $this->getCaisseCourante();

        if (!$caisse) {
            return $this->response->setJSON(['success' => false, 'message' => 'Aucune caisse ouverte. Ouvrez la caisse avant d\'encaisser.']);
        }

        $depotId       = (int) $this->request->getPost('depot_id');
        $clientId      = (int) $this->request->getPost('client_id');
        $montantTotal  = (float) $this->request->getPost('montant_total');
        $modeGlobal    = $this->request->getPost('mode_paiement');
        $especes       = (float) ($this->request->getPost('montant_especes') ?? 0);
        $carte         = (float) ($this->request->getPost('montant_carte') ?? 0);
        $mobile        = (float) ($this->request->getPost('montant_mobile') ?? 0);
        $avoir         = (float) ($this->request->getPost('montant_avoir') ?? 0);
        $fidelite      = (float) ($this->request->getPost('montant_fidelite') ?? 0);
        $rendu         = (float) ($this->request->getPost('rendu_monnaie') ?? 0);
        $pointsUtilises = (int) ($this->request->getPost('points_utilises') ?? 0);
        $produitVendu = $this->request->getPost('produit_annexe_id');
        $qteVendue    = (int) ($this->request->getPost('produit_qte') ?? 1);

        $db->transStart();

        // 1. Enregistrer la transaction
        $db->table('transactions')->insert([
            'depot_id'         => $depotId ?: null,
            'caisse_id'        => $caisse['id_caisse'],
            'employe_id'       => session()->get('employe_id'),
            'client_id'        => $clientId ?: null,
            'type'             => 'encaissement',
            'montant'          => $montantTotal,
            'mode_paiement'    => $modeGlobal,
            'montant_especes'  => $especes,
            'montant_carte'    => $carte,
            'montant_mobile'   => $mobile,
            'montant_avoir'    => $avoir,
            'montant_fidelite' => $fidelite,
            'rendu_monnaie'    => $rendu,
            'statut'           => 'valide',
            'created_at'       => date('Y-m-d H:i:s'),
        ]);
        $idTransaction = $db->insertID();

        // 2. Mettre à jour acompte_verse et statut dépôt
        if ($depotId) {
            $depot = $db->table('depots')->where('id_depot', $depotId)->get()->getRowArray();
            $nouveauAcompte = min($depot['total_ttc'], $depot['acompte_verse'] + $montantTotal);
            $nouveauStatut  = $nouveauAcompte >= $depot['total_ttc'] ? 'livre' : 'pret';

            $db->table('depots')->where('id_depot', $depotId)->update([
                'acompte_verse'  => $nouveauAcompte,
                'statut_global'  => $nouveauStatut,
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
        }

        // 3. Déduire points fidélité si utilisés
        if ($pointsUtilises > 0 && $clientId) {
            $db->table('clients')
               ->where('id_client', $clientId)
               ->set('solde_fidelite', 'solde_fidelite - ' . $pointsUtilises, false)
               ->update();
        }

        // 4. Mettre à jour totaux caisse
        $db->table('caisses')->where('id_caisse', $caisse['id_caisse'])->update([
            'total_especes' => $caisse['total_especes'] + $especes,
            'total_carte'   => $caisse['total_carte']   + $carte,
            'total_mobile'  => $caisse['total_mobile']  + $mobile,
            'total_avoir'   => $caisse['total_avoir']   + $avoir,
            'total_ca'      => $caisse['total_ca']      + $montantTotal,
        ]);

        // 5. Notification retrait confirmé
        if ($depotId && isset($nouveauStatut) && $nouveauStatut === 'livre') {
            $notif = new \App\Services\NotificationService();
            $notif->retraitConfirme($depotId);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Erreur lors de l\'encaissement.']);
        }

        // Décrémentation stock si vente produit annexe

        if ($produitVendu) {
            // On réutilise la logique du StockController
            $stockCtrl = new \App\Controllers\StockController();
            // Appel direct via la méthode privée — on crée un helper statique
            \App\Controllers\StockController::mouvementerStatique(
                (int) $produitVendu,
                'vente_pos',
                $qteVendue,
                'Vente POS — Transaction #' . $idTransaction,
                'TX-' . $idTransaction
            );
        }

        return $this->response->setJSON([
            'success'        => true,
            'id_transaction' => $idTransaction,
            'message'        => 'Encaissement enregistré.',
        ]);
    }

    // ═══════════════════════════════════════════
    // REMBOURSER
    // ═══════════════════════════════════════════
    public function rembourser()
    {
        $db     = \Config\Database::connect();
        $caisse = $this->getCaisseCourante();

        if (!$caisse) {
            return redirect()->back()->with('error', 'Aucune caisse ouverte.');
        }

        $depotId  = (int) $this->request->getPost('depot_id');
        $montant  = (float) $this->request->getPost('montant_remboursement');
        $motif    = $this->request->getPost('motif');
        $typeRemb = $this->request->getPost('type_remboursement'); // 'especes' ou 'avoir'
        $clientId = (int) $this->request->getPost('client_id');

        $db->transStart();

        $db->table('transactions')->insert([
            'depot_id'      => $depotId ?: null,
            'caisse_id'     => $caisse['id_caisse'],
            'employe_id'    => session()->get('employe_id'),
            'client_id'     => $clientId ?: null,
            'type'          => $typeRemb === 'avoir' ? 'avoir' : 'remboursement',
            'montant'       => $montant,
            'mode_paiement' => $typeRemb,
            'motif'         => $motif,
            'statut'        => 'valide',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        // Si avoir → créditer solde_prepaye du client
        if ($typeRemb === 'avoir' && $clientId) {
            $db->table('clients')
               ->where('id_client', $clientId)
               ->set('solde_prepaye', 'solde_prepaye + ' . $montant, false)
               ->update();
        }

        // Mise à jour total remboursé caisse
        $db->table('caisses')->where('id_caisse', $caisse['id_caisse'])->update([
            'total_rembourse' => $caisse['total_rembourse'] + $montant,
        ]);

        $db->transComplete();

        return redirect()->to('pos')->with('success', 'Remboursement de ' . number_format($montant, 0, ',', ' ') . ' FCFA enregistré.');
    }

    // ═══════════════════════════════════════════
    // REÇU THERMIQUE (window.print)
    // ═══════════════════════════════════════════
    public function recu(int $idTransaction)
    {
        $db   = \Config\Database::connect();
        $tx   = $db->table('transactions t')
            ->select('t.*, c.nomclient, c.telephone, c.email, d.code_commande')
            ->join('clients c', 'c.id_client = t.client_id', 'left')
            ->join('depots d',  'd.id_depot = t.depot_id',   'left')
            ->where('t.id_transaction', $idTransaction)
            ->get()->getRowArray();

        $depot = $tx['depot_id'] ? $this->getDepotCompletPOS($tx['depot_id']) : null;

        return view('pages/pos/recu', [
            'tx'    => $tx,
            'depot' => $depot,
        ]);
    }

    // ═══════════════════════════════════════════
    // FACTURE DÉTAILLÉE
    // ═══════════════════════════════════════════
    public function facture(int $idTransaction)
    {
        $db = \Config\Database::connect();
        $tx = $db->table('transactions t')
            ->select('t.*, c.nomclient, c.telephone, c.email, d.code_commande, d.total_ttc')
            ->join('clients c', 'c.id_client = t.client_id', 'left')
            ->join('depots d',  'd.id_depot = t.depot_id',   'left')
            ->where('t.id_transaction', $idTransaction)
            ->get()->getRowArray();

        $depot = $tx['depot_id'] ? $this->getDepotCompletPOS($tx['depot_id']) : null;

        return view('pages/pos/facture', [
            'tx'    => $tx,
            'depot' => $depot,
        ]);
    }

    // ═══════════════════════════════════════════
    // CAISSE
    // ═══════════════════════════════════════════
    public function caisse()
    {
        $db     = \Config\Database::connect();
        $caisse = $this->getCaisseCourante();

        $historique = $db->table('caisses c')
            ->select('c.*, e.nom_complet, s.nom_shop')
            ->join('employes e', 'e.id_employe = c.employe_id', 'left')
            ->join('shops s',    's.id_shop = c.shop_id',       'left')
            ->orderBy('c.date_ouverture', 'DESC')
            ->limit(30)
            ->get()->getResultArray();

        $shops = $db->table('shops')->get()->getResultArray();

        return view('pages/pos/caisse', [
            'title'      => 'Gestion de Caisse',
            'caisse'     => $caisse,
            'historique' => $historique,
            'shops'      => $shops,
        ]);
    }

    public function ouvrirCaisse()
    {
        $caisse = $this->getCaisseCourante();
        if ($caisse) {
            return redirect()->to('pos/caisse')->with('error', 'Une caisse est déjà ouverte.');
        }

        \Config\Database::connect()->table('caisses')->insert([
            'employe_id'    => session()->get('employe_id'),
            'shop_id'       => $this->request->getPost('shop_id'),
            'date_ouverture'=> date('Y-m-d H:i:s'),
            'fond_ouverture'=> (float) $this->request->getPost('fond_ouverture'),
            'statut'        => 'ouverte',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('pos')->with('success', 'Caisse ouverte avec un fond de ' . number_format($this->request->getPost('fond_ouverture'), 0, ',', ' ') . ' FCFA.');
    }

    public function cloturerCaisse()
    {
        $db     = \Config\Database::connect();
        $caisse = $this->getCaisseCourante();

        if (!$caisse) {
            return redirect()->to('pos/caisse')->with('error', 'Aucune caisse ouverte.');
        }

        $fondReel = (float) $this->request->getPost('fond_reel');
        $ecart    = $fondReel - ($caisse['fond_ouverture'] + $caisse['total_especes'] - $caisse['total_rembourse']);

        $db->table('caisses')->where('id_caisse', $caisse['id_caisse'])->update([
            'date_cloture'  => date('Y-m-d H:i:s'),
            'fond_reel'     => $fondReel,
            'ecart'         => $ecart,
            'statut'        => 'cloturee',
            'note_cloture'  => $this->request->getPost('note_cloture'),
        ]);

        return redirect()->to('pos/caisse/rapport/' . $caisse['id_caisse'])
                         ->with('success', 'Caisse clôturée.');
    }

    public function rapportCaisse(int $id)
    {
        $db     = \Config\Database::connect();
        $caisse = $db->table('caisses c')
            ->select('c.*, e.nom_complet, s.nom_shop')
            ->join('employes e', 'e.id_employe = c.employe_id', 'left')
            ->join('shops s',    's.id_shop = c.shop_id',       'left')
            ->where('c.id_caisse', $id)
            ->get()->getRowArray();

        $transactions = $db->table('transactions t')
            ->select('t.*, cl.nomclient, d.code_commande')
            ->join('clients cl', 'cl.id_client = t.client_id', 'left')
            ->join('depots d',   'd.id_depot = t.depot_id',    'left')
            ->where('t.caisse_id', $id)
            ->orderBy('t.created_at', 'ASC')
            ->get()->getResultArray();

        return view('pages/pos/rapport_caisse', [
            'caisse'       => $caisse,
            'transactions' => $transactions,
        ]);
    }

    // ═══════════════════════════════════════════
    // PRODUITS ANNEXES
    // ═══════════════════════════════════════════
    public function produits()
    {
        return view('pages/pos/produits', [
            'title'    => 'Produits Annexes',
            'produits' => \Config\Database::connect()
                ->table('produits_annexes')
                ->orderBy('nom')
                ->get()->getResultArray(),
        ]);
    }

    public function storeProduit()
    {
        \Config\Database::connect()->table('produits_annexes')->insert([
            'nom'          => $this->request->getPost('nom'),
            'description'  => $this->request->getPost('description'),
            'prix'         => (float) $this->request->getPost('prix'),
            'stock'        => (int) $this->request->getPost('stock'),
            'stock_alerte' => (int) ($this->request->getPost('stock_alerte') ?? 5),
            'actif'        => 1,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('pos/produits')->with('success', 'Produit ajouté.');
    }

    public function updateProduit(int $id)
    {
        \Config\Database::connect()->table('produits_annexes')->where('id_produit', $id)->update([
            'nom'          => $this->request->getPost('nom'),
            'description'  => $this->request->getPost('description'),
            'prix'         => (float) $this->request->getPost('prix'),
            'stock'        => (int) $this->request->getPost('stock'),
            'stock_alerte' => (int) $this->request->getPost('stock_alerte'),
            'actif'        => (int) $this->request->getPost('actif'),
        ]);
        return redirect()->to('pos/produits')->with('success', 'Produit mis à jour.');
    }

    public function deleteProduit(int $id)
    {
        \Config\Database::connect()->table('produits_annexes')->where('id_produit', $id)->delete();
        return redirect()->to('pos/produits')->with('success', 'Produit supprimé.');
    }

    // ═══════════════════════════════════════════
    // API RECHERCHE COMMANDES
    // ═══════════════════════════════════════════
    public function apiRecherche()
    {
        $q  = $this->request->getGet('q') ?? '';
        $db = \Config\Database::connect();

        $results = $db->table('depots d')
            ->select('d.id_depot, d.code_commande, d.total_ttc, d.acompte_verse,
                      c.nomclient, c.telephone')
            ->join('clients c', 'c.id_client = d.client_id')
            ->where('d.statut_global', 'pret')
            ->groupStart()
                ->like('d.code_commande', $q)
                ->orLike('c.nomclient',   $q)
                ->orLike('c.telephone',   $q)
            ->groupEnd()
            ->limit(8)
            ->get()->getResultArray();

        return $this->response->setJSON($results);
    }

    public function apiCaisseCourante()
    {
        return $this->response->setJSON($this->getCaisseCourante());
    }
}
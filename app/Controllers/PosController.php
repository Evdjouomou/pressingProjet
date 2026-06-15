<?php
namespace App\Controllers;

class PosController extends BaseController
{
    // ═══════════════════════════════════════════
    // PAGE PRINCIPALE POS
    // ═══════════════════════════════════════════
    public function index()
    {
        $db = \Config\Database::connect();

        // Caisse ouverte ?
        $caisse = $db->table('caisses')
            ->where('statut', 'ouverte')
            ->orderBy('date_ouverture', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        if (!$caisse) {
            return redirect()->to('pos/caisse')
                ->with('info', 'Ouvrez la caisse avant de commencer.');
        }

        // Commandes prêtes
        $commandesPrêtes = $db->table('depots d')
            ->select('d.id_depot, d.code_commande, d.total_ttc,
                      d.statut_global,
                      c.nomclient, c.telephone,
                      COUNT(da.id_article_depose) AS nb_articles,
                      COALESCE(SUM(CASE WHEN t.type="encaissement"
                        AND t.statut="valide" THEN t.montant ELSE 0 END),0)
                        AS total_encaisse')
            ->join('clients c',         'c.id_client = d.client_id')
            ->join('depot_articles da', 'da.depot_id = d.id_depot', 'left')
            ->join('transactions t',    't.depot_id = d.id_depot',  'left')
            ->where('d.statut_global', 'pret')
            ->groupBy('d.id_depot')
            ->orderBy('d.date_livraison_prevue', 'ASC')
            ->get()->getResultArray();

        // Produits boutique en stock
        $produits = $db->table('produits_annexes')
            ->whereIn('type_produit', ['boutique', 'les_deux'])
            ->where('actif', 1)
            ->where('stock >', 0)
            ->orderBy('nom')
            ->get()->getResultArray();

        return view('pages/pos/index', [
            'title'            => 'Point de Vente',
            'caisse'           => $caisse,
            'commandesPrêtes'  => $commandesPrêtes,
            'produits'         => $produits,
        ]);
    }

    // ═══════════════════════════════════════════
    // API RECHERCHE COMMANDE
    // ═══════════════════════════════════════════
    public function apiRecherche()
    {
        $db  = \Config\Database::connect();
        $q   = trim($this->request->getGet('q') ?? '');

        if (strlen($q) < 2) {
            return $this->response->setJSON([]);
        }

        $depots = $db->table('depots d')
            ->select('d.id_depot, d.code_commande, d.total_ttc,
                      d.statut_global, d.date_livraison_prevue,
                      c.nomclient, c.telephone,
                      COUNT(DISTINCT da.id_article_depose) AS nb_articles,
                      COALESCE(SUM(CASE WHEN t.type="encaissement"
                        AND t.statut="valide" THEN t.montant ELSE 0 END),0)
                        AS total_encaisse')
            ->join('clients c',         'c.id_client = d.client_id')
            ->join('depot_articles da', 'da.depot_id = d.id_depot', 'left')
            ->join('transactions t',    't.depot_id = d.id_depot',  'left')
            ->groupStart()
                ->like('d.code_commande', $q)
                ->orLike('c.nomclient',   $q)
                ->orLike('c.telephone',   $q)
            ->groupEnd()
            ->whereNotIn('d.statut_global', ['annule'])
            ->groupBy('d.id_depot')
            ->orderBy('d.statut_global = "pret"', 'DESC')
            ->orderBy('d.created_at', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        return $this->response->setJSON($depots);
    }

    // ═══════════════════════════════════════════
    // API DÉTAIL COMMANDE
    // ═══════════════════════════════════════════
    public function apiCommande(int $id)
    {
        $db    = \Config\Database::connect();

        $depot = $db->table('depots d')
            ->select('d.*, c.nomclient, c.telephone, c.email')
            ->join('clients c', 'c.id_client = d.client_id')
            ->where('d.id_depot', $id)
            ->get()->getRowArray();

        if (!$depot) {
            return $this->response->setJSON(['success' => false]);
        }

        $articles = $db->table('depot_articles da')
            ->select('da.barcode_unique, da.designation_libre,
                      l.nom_libelle, dp.prix_applique, dp.options_express,
                      s.type_prestation')
            ->join('libelles l',          'l.id_libelle = da.libelle_id')
            ->join('depot_prestations dp','dp.article_depose_id = da.id_article_depose','left')
            ->join('services s',          's.id_service = dp.service_id', 'left')
            ->where('da.depot_id', $id)
            ->get()->getResultArray();

        $totalEncaisse = (float) $db->query("
            SELECT COALESCE(SUM(montant),0) AS total
            FROM transactions
            WHERE depot_id = ? AND type = 'encaissement' AND statut = 'valide'
        ", [$id])->getRowArray()['total'];

        $depot['articles']       = $articles;
        $depot['total_encaisse'] = $totalEncaisse;
        $depot['reste']          = max(0, $depot['total_ttc'] - $totalEncaisse);

        return $this->response->setJSON(['success' => true, 'depot' => $depot]);
    }

    // ═══════════════════════════════════════════
    // ENCAISSER UNE COMMANDE
    // ═══════════════════════════════════════════
    public function encaisser()
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $idDepot         = (int)   $this->request->getPost('depot_id');
        $montant         = (float) $this->request->getPost('montant');
        $mode            = $this->request->getPost('mode_paiement') ?? 'especes';
        $montantEspeces  = (float) ($this->request->getPost('montant_especes')  ?? 0);
        $montantMobile   = (float) ($this->request->getPost('montant_mobile')   ?? 0);
        $montantCarte    = (float) ($this->request->getPost('montant_carte')    ?? 0);

        if (!$idDepot || $montant <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Données invalides.'
            ]);
        }

        $depot = $db->table('depots')->where('id_depot', $idDepot)->get()->getRowArray();
        if (!$depot) {
            return $this->response->setJSON(['success' => false, 'message' => 'Commande introuvable.']);
        }

        $caisse = $db->table('caisses')
            ->where('statut', 'ouverte')
            ->orderBy('date_ouverture', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        if (!$caisse) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Aucune caisse ouverte.'
            ]);
        }

        // Calcul du reste réel
        $dejaEncaisse = (float) $db->query("
            SELECT COALESCE(SUM(montant),0) AS total
            FROM transactions
            WHERE depot_id = ? AND type = 'encaissement' AND statut = 'valide'
        ", [$idDepot])->getRowArray()['total'];

        $resteReel = max(0, $depot['total_ttc'] - $dejaEncaisse);
        if ($montant > $resteReel) $montant = $resteReel;

        if ($montant <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cette commande est déjà soldée.'
            ]);
        }

        // Rendu monnaie (espèces uniquement)
        $renduMonnaie = ($mode === 'especes' && $montantEspeces > $resteReel)
            ? $montantEspeces - $resteReel
            : 0;

        $db->transStart();

        // 1. Transaction
        $db->table('transactions')->insert([
            'depot_id'        => $idDepot,
            'caisse_id'       => $caisse['id_caisse'],
            'employe_id'      => session()->get('employe_id'),
            'client_id'       => $depot['client_id'],
            'type'            => 'encaissement',
            'montant'         => $montant,
            'mode_paiement'   => $mode,
            'montant_especes' => $mode === 'especes' ? $montant
                                 : ($mode === 'mixte' ? $montantEspeces : 0),
            'montant_mobile'  => $mode === 'mobile_money' ? $montant
                                 : ($mode === 'mixte' ? $montantMobile : 0),
            'montant_carte'   => $mode === 'carte' ? $montant
                                 : ($mode === 'mixte' ? $montantCarte : 0),
            'rendu_monnaie'   => $renduMonnaie,
            'statut'          => 'valide',
            'motif'           => 'Encaissement POS — ' . $depot['code_commande'],
            'created_at'      => $now,
        ]);
        $idTransaction = $db->insertID();

        // 2. Synchroniser acompte_verse
        $nouveauTotal = $dejaEncaisse + $montant;
        $db->table('depots')->where('id_depot', $idDepot)->update([
            'acompte_verse' => min($depot['total_ttc'], $nouveauTotal),
            'updated_at'    => $now,
        ]);

        // 3. Si soldé → passer à "livre"
        $estSolde = ($nouveauTotal >= $depot['total_ttc']);
        if ($estSolde && $depot['statut_global'] === 'pret') {
            $db->table('depots')->where('id_depot', $idDepot)->update([
                'statut_global' => 'livre',
                'updated_at'    => $now,
            ]);
        }

        // 4. Mettre à jour la caisse
        $esp = $mode === 'especes'      ? $montant : ($mode === 'mixte' ? $montantEspeces : 0);
        $mob = $mode === 'mobile_money' ? $montant : ($mode === 'mixte' ? $montantMobile  : 0);
        $car = $mode === 'carte'        ? $montant : ($mode === 'mixte' ? $montantCarte   : 0);

        $db->table('caisses')->where('id_caisse', $caisse['id_caisse'])->update([
            'total_especes' => $caisse['total_especes'] + $esp,
            'total_mobile'  => $caisse['total_mobile']  + $mob,
            'total_carte'   => $caisse['total_carte']   + $car,
            'total_ca'      => $caisse['total_ca']      + $montant,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erreur base de données.'
            ]);
        }

        // 5. Notification
        if ($estSolde) {
            try {
                $notif = new \App\Services\NotificationService();
                $notif->retraitConfirme($idDepot);
            } catch (\Exception $e) {}
        }

        return $this->response->setJSON([
            'success'      => true,
            'solde'        => $estSolde,
            'rendu'        => $renduMonnaie,
            'id_transaction'=> $idTransaction,
            'message'      => $estSolde
                ? '✅ Commande soldée !'
                : 'Paiement enregistré. Reste : '
                  . number_format($depot['total_ttc'] - $nouveauTotal, 0, ',', ' ')
                  . ' FCFA',
        ]);
    }

    // ═══════════════════════════════════════════
    // VENTE PRODUIT ANNEXE
    // ═══════════════════════════════════════════
    public function vendreProduit()
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $produitId = (int)   $this->request->getPost('produit_id');
        $quantite  = (int)   $this->request->getPost('quantite') ?: 1;
        $mode      = $this->request->getPost('mode_paiement') ?? 'especes';

        $produit = $db->table('produits_annexes')
            ->where('id_produit', $produitId)
            ->get()->getRowArray();

        if (!$produit || $produit['stock'] < $quantite) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Produit indisponible ou stock insuffisant.'
            ]);
        }

        $caisse = $db->table('caisses')
            ->where('statut', 'ouverte')
            ->orderBy('date_ouverture', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        if (!$caisse) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Aucune caisse ouverte.'
            ]);
        }

        $montant = $produit['prix'] * $quantite;

        $db->transStart();

        // Transaction
        $db->table('transactions')->insert([
            'depot_id'        => null,
            'caisse_id'       => $caisse['id_caisse'],
            'employe_id'      => session()->get('employe_id'),
            'client_id'       => null,
            'type'            => 'encaissement',
            'montant'         => $montant,
            'mode_paiement'   => $mode,
            'montant_especes' => $mode === 'especes'      ? $montant : 0,
            'montant_mobile'  => $mode === 'mobile_money' ? $montant : 0,
            'montant_carte'   => $mode === 'carte'        ? $montant : 0,
            'statut'          => 'valide',
            'motif'           => 'Vente boutique — ' . $produit['nom'] . ' x' . $quantite,
            'created_at'      => $now,
        ]);

        // Décrémenter stock
        \App\Controllers\StockController::mouvementerStatique(
            $produitId,
            'vente_pos',
            $quantite,
            'Vente POS — ' . $produit['nom'],
            'POS-' . date('YmdHis')
        );

        // Mettre à jour caisse
        $db->table('caisses')->where('id_caisse', $caisse['id_caisse'])->update([
            'total_especes' => $caisse['total_especes'] + ($mode === 'especes'      ? $montant : 0),
            'total_mobile'  => $caisse['total_mobile']  + ($mode === 'mobile_money' ? $montant : 0),
            'total_carte'   => $caisse['total_carte']   + ($mode === 'carte'        ? $montant : 0),
            'total_ca'      => $caisse['total_ca']      + $montant,
        ]);

        $db->transComplete();

        return $this->response->setJSON([
            'success' => $db->transStatus() !== false,
            'message' => '✅ ' . $produit['nom'] . ' vendu — '
                       . number_format($montant, 0, ',', ' ') . ' FCFA',
        ]);
    }

    // ═══════════════════════════════════════════
    // CAISSE
    // ═══════════════════════════════════════════
    public function caisse()
    {
        $db     = \Config\Database::connect();
        $caisse = $db->table('caisses')
            ->where('statut', 'ouverte')
            ->where('shop_id', shop_actif_id())
            ->orderBy('date_ouverture', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        return view('pages/pos/caisse', [
            'title'  => 'Caisse',
            'caisse' => $caisse,
        ]);
    }

    public function ouvrirCaisse()
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        // Vérifier qu'il n'y a pas déjà une caisse ouverte
        $existe = $db->table('caisses')->where('statut', 'ouverte')->get()->getRowArray();
        if ($existe) {
            return redirect()->to('pos')->with('error', 'Une caisse est déjà ouverte.');
        }

        $fond = (float) ($this->request->getPost('fond_ouverture') ?? 0);

        $db->table('caisses')->insert([
            'employe_id'     => session()->get('employe_id'),
            'shop_id'        => shop_actif_id(),
            'fond_ouverture' => $fond,
            'total_especes'  => 0,
            'total_mobile'   => 0,
            'total_carte'    => 0,
            'total_ca'       => 0,
            'total_rembourse'=> 0,
            'statut'         => 'ouverte',
            'date_ouverture' => $now,
            'created_at'     => $now,
        ]);

        return redirect()->to('pos')->with('success', 'Caisse ouverte — Fond : '
            . number_format($fond, 0, ',', ' ') . ' FCFA');
    }

    public function cloturerCaisse()
    {
        $db     = \Config\Database::connect();
        $now    = date('Y-m-d H:i:s');
        $caisse = $db->table('caisses')
            ->where('statut', 'ouverte')
            ->orderBy('date_ouverture', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        if (!$caisse) {
            return redirect()->to('pos/caisse')->with('error', 'Aucune caisse ouverte.');
        }

        $fondReel = (float) ($this->request->getPost('fond_reel') ?? 0);
        $ecart    = $fondReel - ($caisse['fond_ouverture'] + $caisse['total_especes']);

        $db->table('caisses')->where('id_caisse', $caisse['id_caisse'])->update([
            'statut'         => 'cloturee',
            'fond_reel'      => $fondReel,
            'ecart'          => $ecart,
            'date_cloture'   => $now,
        ]);

        return redirect()->to('pos/caisse')
            ->with('success', 'Caisse clôturée. Écart : '
                . number_format($ecart, 0, ',', ' ') . ' FCFA');
    }

    // ═══════════════════════════════════════════
    // REMBOURSEMENT
    // ═══════════════════════════════════════════
    public function rembourser()
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $idDepot  = (int)   $this->request->getPost('depot_id');
        $montant  = (float) $this->request->getPost('montant');
        $motif    = $this->request->getPost('motif') ?: 'Remboursement';
        $mode     = $this->request->getPost('mode_paiement') ?? 'especes';

        if (!$idDepot || $montant <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Données invalides.'
            ]);
        }

        $depot = $db->table('depots')->where('id_depot', $idDepot)->get()->getRowArray();
        if (!$depot) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Commande introuvable.'
            ]);
        }

        $caisse = $db->table('caisses')
            ->where('statut', 'ouverte')
            ->orderBy('date_ouverture', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        if (!$caisse) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Aucune caisse ouverte.'
            ]);
        }

        $db->transStart();

        // Créer la transaction de remboursement
        $db->table('transactions')->insert([
            'depot_id'        => $idDepot,
            'caisse_id'       => $caisse['id_caisse'],
            'employe_id'      => session()->get('employe_id'),
            'client_id'       => $depot['client_id'],
            'type'            => 'remboursement',
            'montant'         => $montant,
            'mode_paiement'   => $mode,
            'montant_especes' => $mode === 'especes'      ? $montant : 0,
            'montant_mobile'  => $mode === 'mobile_money' ? $montant : 0,
            'montant_carte'   => $mode === 'carte'        ? $montant : 0,
            'statut'          => 'valide',
            'motif'           => $motif,
            'created_at'      => $now,
        ]);

        // Décrémenter la caisse
        $db->table('caisses')
            ->where('id_caisse', $caisse['id_caisse'])
            ->update([
                'total_rembourse' => $caisse['total_rembourse'] + $montant,
                'total_especes'   => $caisse['total_especes']
                    - ($mode === 'especes' ? $montant : 0),
            ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erreur base de données.'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => '↩ Remboursement de '
                    . number_format($montant, 0, ',', ' ')
                    . ' FCFA enregistré.',
        ]);
    }

    // ═══════════════════════════════════════════
    // RAPPORT CAISSE (rapport Z)
    // ═══════════════════════════════════════════
    public function rapportCaisse(int $idCaisse)
    {
        $db     = \Config\Database::connect();
        $caisse = $db->table('caisses c')
            ->select('c.*, e.nom_complet AS caissier')
            ->join('employes e', 'e.id_employe = c.employe_id', 'left')
            ->where('c.id_caisse', $idCaisse)
            ->get()->getRowArray();

        if (!$caisse) {
            return redirect()->to('pos/historique-caisses')
                ->with('error', 'Caisse introuvable.');
        }

        // Transactions de cette caisse
        $transactions = $db->table('transactions t')
            ->select('t.*, d.code_commande, c.nomclient')
            ->join('depots d',  'd.id_depot = t.depot_id',   'left')
            ->join('clients c', 'c.id_client = t.client_id', 'left')
            ->where('t.caisse_id', $idCaisse)
            ->where('t.statut', 'valide')
            ->orderBy('t.created_at', 'ASC')
            ->get()->getResultArray();

        // Totaux par type
        $totalEnc  = array_sum(array_column(
            array_filter($transactions, fn($t) => $t['type'] === 'encaissement'),
            'montant'
        ));
        $totalRmb  = array_sum(array_column(
            array_filter($transactions, fn($t) => $t['type'] === 'remboursement'),
            'montant'
        ));

        // Totaux par mode
        $parMode = [];
        foreach ($transactions as $tx) {
            if ($tx['type'] !== 'encaissement') continue;
            $m = $tx['mode_paiement'];
            $parMode[$m] = ($parMode[$m] ?? 0) + $tx['montant'];
        }

        return view('pages/pos/rapport_caisse', [
            'title'        => 'Rapport caisse',
            'caisse'       => $caisse,
            'transactions' => $transactions,
            'total_enc'    => $totalEnc,
            'total_rmb'    => $totalRmb,
            'par_mode'     => $parMode,
        ]);
    }

    // ═══════════════════════════════════════════
    // HISTORIQUE DES CAISSES
    // ═══════════════════════════════════════════
    public function historiqueCaisses()
    {
        $db = \Config\Database::connect();

        $caisses = $db->table('caisses c')
            ->select('c.*, e.nom_complet AS caissier,
                    COUNT(t.id_transaction) AS nb_transactions')
            ->join('employes e',    'e.id_employe = c.employe_id', 'left')
            ->join('transactions t','t.caisse_id = c.id_caisse',   'left')
            ->groupBy('c.id_caisse')
            ->orderBy('c.date_ouverture', 'DESC')
            ->get()->getResultArray();

        return view('pages/pos/historique_caisses', [
            'title'   => 'Historique des caisses',
            'caisses' => $caisses,
        ]);
    }

    // ═══════════════════════════════════════════
    // REÇU THERMIQUE
    // ═══════════════════════════════════════════
    public function recu(int $idTransaction)
    {
        $db = \Config\Database::connect();
        $tx = $db->table('transactions t')
            ->select('t.*, d.code_commande, d.total_ttc,
                    c.nomclient, c.telephone,
                    e.nom_complet AS caissier,
                    s.nom_shop')
            ->join('depots d',   'd.id_depot = t.depot_id',       'left')
            ->join('clients c',  'c.id_client = t.client_id',     'left')
            ->join('employes e', 'e.id_employe = t.employe_id',   'left')
            ->join('shops s',    's.id_shop = e.shop_id',          'left')
            ->where('t.id_transaction', $idTransaction)
            ->get()->getRowArray();

        if (!$tx) {
            return redirect()->to('pos')->with('error', 'Transaction introuvable.');
        }

        // Historique paiements du dépôt
        $historique = [];
        if ($tx['depot_id']) {
            $totalDepot = (float) $db->query("
                SELECT COALESCE(SUM(montant),0) AS total
                FROM transactions
                WHERE depot_id = ? AND type='encaissement' AND statut='valide'
            ", [$tx['depot_id']])->getRowArray()['total'];

            $depot = $db->table('depots')
                ->where('id_depot', $tx['depot_id'])
                ->get()->getRowArray();

            $tx['total_encaisse'] = $totalDepot;
            $tx['reste']          = max(0, ($depot['total_ttc'] ?? 0) - $totalDepot);
        }

        return view('pages/pos/recu', ['tx' => $tx]);
    }
}
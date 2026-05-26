<?php
namespace App\Controllers;

class StockController extends BaseController
{
    // ═══════════════════════════════════════════
    // HELPERS PRIVÉS
    // ═══════════════════════════════════════════
    private function getProduits(string $filtre = ''): array
    {
        $db = \Config\Database::connect();
        $q  = $db->table('produits_annexes');
        if ($filtre) $q->where('type_produit', $filtre);
        return $q->orderBy('nom')->get()->getResultArray();
    }

    private function mouvementer(
        int    $produitId,
        string $type,
        int    $quantite,
        string $motif       = '',
        string $refDoc      = '',
        ?float $prixUnitaire = null
    ): void {
        $db      = \Config\Database::connect();
        $produit = $db->table('produits_annexes')
                      ->where('id_produit', $produitId)
                      ->get()->getRowArray();

        if (!$produit) return;

        $stockAvant = (int) $produit['stock'];
        $stockApres = match ($type) {
            'entree'       => $stockAvant + $quantite,
            'sortie',
            'vente_pos',
            'consommation' => max(0, $stockAvant - $quantite),
            'ajustement'   => $quantite, // quantite = nouveau stock absolu
            default        => $stockAvant,
        };

        $db->table('mouvements_stock')->insert([
            'produit_id'    => $produitId,
            'employe_id'    => session()->get('employe_id'),
            'type_mouvement'=> $type,
            'quantite'      => $quantite,
            'stock_avant'   => $stockAvant,
            'stock_apres'   => $stockApres,
            'motif'         => $motif,
            'reference_doc' => $refDoc,
            'prix_unitaire' => $prixUnitaire,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $db->table('produits_annexes')
           ->where('id_produit', $produitId)
           ->update(['stock' => $stockApres, 'updated_at' => date('Y-m-d H:i:s')]);

        // Déclencher alerte si stock sous le seuil
        if ($stockApres <= $produit['stock_alerte'] && $stockAvant > $produit['stock_alerte']) {
            $this->envoyerAlerteStock($produit, $stockApres);
        }
    }

    private function envoyerAlerteStock(array $produit, int $stockActuel): void
    {
        // Notifier les employés ayant le rôle gérant
        $db      = \Config\Database::connect();
        $gerants = $db->table('employes')
                      ->where('role', 'admin')
                      ->where('status', 'Actif')
                      ->get()->getResultArray();

        foreach ($gerants as $g) {
            $db->table('notifications')->insert([
                'client_id'  => null,
                'depot_id'   => null,
                'type'       => 'campagne', // on réutilise pour les alertes internes
                'canal'      => 'interne',
                'sujet'      => '⚠️ Stock bas : ' . $produit['nom'],
                'message'    => 'Le stock de <strong>' . esc($produit['nom']) . '</strong> '
                              . 'est passé à <strong>' . $stockActuel . ' ' . $produit['unite'] . '</strong>. '
                              . 'Seuil d\'alerte : ' . $produit['stock_alerte'] . ' ' . $produit['unite'] . '.',
                'statut'     => 'envoye',
                'lu'         => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    // ═══════════════════════════════════════════
    // PAGE PRINCIPALE
    // ═══════════════════════════════════════════
    public function index()
    {
        $db       = \Config\Database::connect();
        $produits = $this->getProduits();

        // Stats globales
        $enAlerte  = array_filter($produits, fn($p) => $p['stock'] <= $p['stock_alerte']);
        $rupture   = array_filter($produits, fn($p) => $p['stock'] == 0);
        $valeurTotal = array_sum(array_map(fn($p) => $p['stock'] * $p['prix_achat'], $produits));

        return view('pages/stocks/index', [
            'title'       => 'Gestion des Stocks',
            'produits'    => $produits,
            'nb_alerte'   => count($enAlerte),
            'nb_rupture'  => count($rupture),
            'valeur_total'=> $valeurTotal,
            'nb_produits' => count($produits),
        ]);
    }

    // ═══════════════════════════════════════════
    // DÉTAIL PRODUIT + HISTORIQUE
    // ═══════════════════════════════════════════
    public function detail(int $id)
    {
        $db      = \Config\Database::connect();
        $produit = $db->table('produits_annexes')->where('id_produit', $id)->get()->getRowArray();

        if (!$produit) return redirect()->to('stocks')->with('error', 'Produit introuvable.');

        $mouvements = $db->table('mouvements_stock m')
            ->select('m.*, e.nom_complet')
            ->join('employes e', 'e.id_employe = m.employe_id', 'left')
            ->where('m.produit_id', $id)
            ->orderBy('m.created_at', 'DESC')
            ->limit(50)
            ->get()->getResultArray();

        return view('pages/stocks/detail', [
            'title'      => 'Stock — ' . $produit['nom'],
            'produit'    => $produit,
            'mouvements' => $mouvements,
        ]);
    }

    // ═══════════════════════════════════════════
    // CRUD PRODUITS
    // ═══════════════════════════════════════════
    public function storeProduit()
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $db->table('produits_annexes')->insert([
            'nom'          => $this->request->getPost('nom'),
            'reference'    => $this->request->getPost('reference'),
            'categorie'    => $this->request->getPost('categorie'),
            'description'  => $this->request->getPost('description'),
            'unite'        => $this->request->getPost('unite') ?: 'unité',
            'type_produit' => $this->request->getPost('type_produit'),
            'prix'         => (float) $this->request->getPost('prix_vente') ?: 0,
            'prix_achat'   => (float) $this->request->getPost('prix_achat') ?: 0,
            'stock'        => (int) $this->request->getPost('stock_initial') ?: 0,
            'stock_alerte' => (int) $this->request->getPost('stock_alerte') ?: 5,
            'fournisseur'  => $this->request->getPost('fournisseur'),
            'actif'        => 1,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);

        // Enregistrer le stock initial comme première entrée
        $id = $db->insertID();
        $stockInit = (int) $this->request->getPost('stock_initial');
        if ($stockInit > 0) {
            $this->mouvementer($id, 'entree', $stockInit, 'Stock initial à la création');
        }

        return redirect()->to('stocks')->with('success', 'Produit créé avec succès.');
    }

    public function updateProduit(int $id)
    {
        \Config\Database::connect()->table('produits_annexes')
            ->where('id_produit', $id)
            ->update([
                'nom'          => $this->request->getPost('nom'),
                'reference'    => $this->request->getPost('reference'),
                'categorie'    => $this->request->getPost('categorie'),
                'description'  => $this->request->getPost('description'),
                'unite'        => $this->request->getPost('unite') ?: 'unité',
                'type_produit' => $this->request->getPost('type_produit'),
                'prix'         => (float) $this->request->getPost('prix_vente') ?: 0,
                'prix_achat'   => (float) $this->request->getPost('prix_achat') ?: 0,
                'stock_alerte' => (int) $this->request->getPost('stock_alerte') ?: 5,
                'fournisseur'  => $this->request->getPost('fournisseur'),
                'actif'        => (int) $this->request->getPost('actif'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);

        return redirect()->to('stocks')->with('success', 'Produit mis à jour.');
    }

    public function deleteProduit(int $id)
    {
        \Config\Database::connect()->table('produits_annexes')
            ->where('id_produit', $id)->delete();
        return redirect()->to('stocks')->with('success', 'Produit supprimé.');
    }

    // ═══════════════════════════════════════════
    // MOUVEMENTS
    // ═══════════════════════════════════════════
    public function entree()
    {
        $this->mouvementer(
            (int)   $this->request->getPost('produit_id'),
            'entree',
            (int)   $this->request->getPost('quantite'),
            $this->request->getPost('motif') ?: 'Entrée manuelle',
            $this->request->getPost('reference_doc') ?: '',
            (float) $this->request->getPost('prix_unitaire') ?: null
        );
        return redirect()->back()->with('success', 'Entrée de stock enregistrée.');
    }

    public function sortie()
    {
        $this->mouvementer(
            (int) $this->request->getPost('produit_id'),
            'sortie',
            (int) $this->request->getPost('quantite'),
            $this->request->getPost('motif') ?: 'Sortie manuelle',
            $this->request->getPost('reference_doc') ?: ''
        );
        return redirect()->back()->with('success', 'Sortie de stock enregistrée.');
    }

    public function ajustement()
    {
        $nouveauStock = (int) $this->request->getPost('nouveau_stock');
        $this->mouvementer(
            (int) $this->request->getPost('produit_id'),
            'ajustement',
            $nouveauStock,
            $this->request->getPost('motif') ?: 'Ajustement inventaire'
        );
        return redirect()->back()->with('success', 'Stock ajusté.');
    }

    // ═══════════════════════════════════════════
    // JOURNAL COMPLET
    // ═══════════════════════════════════════════
    public function journal()
    {
        $db      = \Config\Database::connect();
        $filtre  = $this->request->getGet('type') ?? '';
        $debut   = $this->request->getGet('debut') ?? date('Y-m-01');
        $fin     = $this->request->getGet('fin')   ?? date('Y-m-d');

        $q = $db->table('mouvements_stock m')
            ->select('m.*, p.nom AS produit_nom, p.unite, e.nom_complet')
            ->join('produits_annexes p', 'p.id_produit = m.produit_id')
            ->join('employes e',         'e.id_employe = m.employe_id', 'left')
            ->where('DATE(m.created_at) >=', $debut)
            ->where('DATE(m.created_at) <=', $fin)
            ->orderBy('m.created_at', 'DESC');

        if ($filtre) $q->where('m.type_mouvement', $filtre);

        return view('pages/stocks/journal', [
            'title'      => 'Journal des mouvements',
            'mouvements' => $q->get()->getResultArray(),
            'filtre'     => $filtre,
            'debut'      => $debut,
            'fin'        => $fin,
        ]);
    }

    public static function mouvementerStatique(
        int $produitId, string $type, int $quantite,
        string $motif = '', string $refDoc = ''
    ): void {
        $db      = \Config\Database::connect();
        $produit = $db->table('produits_annexes')
                    ->where('id_produit', $produitId)
                    ->get()->getRowArray();
        if (!$produit) return;

        $stockAvant = (int) $produit['stock'];
        $stockApres = max(0, $stockAvant - $quantite);

        $db->table('mouvements_stock')->insert([
            'produit_id'     => $produitId,
            'employe_id'     => session()->get('employe_id'),
            'type_mouvement' => $type,
            'quantite'       => $quantite,
            'stock_avant'    => $stockAvant,
            'stock_apres'    => $stockApres,
            'motif'          => $motif,
            'reference_doc'  => $refDoc,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $db->table('produits_annexes')
        ->where('id_produit', $produitId)
        ->update(['stock' => $stockApres, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    // ═══════════════════════════════════════════
    // BONS DE COMMANDE
    // ═══════════════════════════════════════════
    public function bons()
    {
        $db = \Config\Database::connect();

        $bons = $db->table('bons_commande b')
            ->select('b.*, e.nom_complet,
                      COUNT(l.id_ligne) AS nb_lignes')
            ->join('employes e',          'e.id_employe = b.employe_id', 'left')
            ->join('lignes_bon_commande l','l.bon_id = b.id_bon',        'left')
            ->groupBy('b.id_bon')
            ->orderBy('b.created_at', 'DESC')
            ->get()->getResultArray();

        $produits = $this->getProduits();

        return view('pages/stocks/bons', [
            'title'    => 'Bons de commande',
            'bons'     => $bons,
            'produits' => $produits,
        ]);
    }

    public function storeBon()
    {
        $db  = \Config\Database::connect();
        $ref = 'BC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $produits = $this->request->getPost('produits');   // tableau
        $qtés     = $this->request->getPost('quantites');
        $prix     = $this->request->getPost('prix');

        if (empty($produits)) {
            return redirect()->back()->with('error', 'Ajoutez au moins un produit.');
        }

        $totalHT = 0;
        foreach ($produits as $i => $pid) {
            $totalHT += ((float)($prix[$i] ?? 0)) * ((int)($qtés[$i] ?? 0));
        }

        $db->table('bons_commande')->insert([
            'reference'  => $ref,
            'fournisseur'=> $this->request->getPost('fournisseur'),
            'note'       => $this->request->getPost('note'),
            'total_ht'   => $totalHT,
            'employe_id' => session()->get('employe_id'),
            'statut'     => 'brouillon',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $idBon = $db->insertID();

        foreach ($produits as $i => $pid) {
            $qte   = (int)   ($qtés[$i] ?? 0);
            $pu    = (float) ($prix[$i] ?? 0);
            if ($qte <= 0) continue;
            $db->table('lignes_bon_commande')->insert([
                'bon_id'       => $idBon,
                'produit_id'   => $pid,
                'quantite'     => $qte,
                'prix_unitaire'=> $pu,
                'total_ligne'  => $qte * $pu,
            ]);
        }

        return redirect()->to('stocks/bons')->with('success', 'Bon de commande ' . $ref . ' créé.');
    }

    public function detailBon(int $id)
    {
        $db  = \Config\Database::connect();
        $bon = $db->table('bons_commande b')
            ->select('b.*, e.nom_complet')
            ->join('employes e', 'e.id_employe = b.employe_id', 'left')
            ->where('b.id_bon', $id)
            ->get()->getRowArray();

        if (!$bon) return redirect()->to('stocks/bons')->with('error', 'Bon introuvable.');

        $lignes = $db->table('lignes_bon_commande l')
            ->select('
                l.id_ligne,
                l.quantite,
                l.prix_unitaire,
                l.total_ligne,
                p.nom,
                p.unite,
                p.stock      AS stock_actuel,
                p.reference  AS ref_produit
            ')
            ->join('produits_annexes p', 'p.id_produit = l.produit_id')
            ->where('l.bon_id', $id)
            ->get()->getResultArray();

        return view('pages/stocks/detail_bon', [
            'title'  => 'Bon ' . $bon['reference'],
            'bon'    => $bon,
            'lignes' => $lignes,
        ]);
    }

    public function recevoirBon(int $id)
    {
        $db  = \Config\Database::connect();
        $bon = $db->table('bons_commande')->where('id_bon', $id)->get()->getRowArray();

        if (!$bon || $bon['statut'] === 'recu') {
            return redirect()->back()->with('error', 'Bon déjà reçu ou introuvable.');
        }

        $lignes = $db->table('lignes_bon_commande l')
            ->join('produits_annexes p', 'p.id_produit = l.produit_id')
            ->where('l.bon_id', $id)
            ->get()->getResultArray();

        // Entrée en stock pour chaque ligne
        foreach ($lignes as $ligne) {
            $this->mouvementer(
                $ligne['produit_id'],
                'entree',
                $ligne['quantite'],
                'Réception bon commande ' . $bon['reference'],
                $bon['reference'],
                $ligne['prix_unitaire']
            );
        }

        $db->table('bons_commande')->where('id_bon', $id)->update([
            'statut'         => 'recu',
            'date_reception' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('stocks/bons')->with('success', 'Réception enregistrée — stocks mis à jour.');
    }

    public function imprimerBon(int $id)
    {
        $db  = \Config\Database::connect();
        $bon = $db->table('bons_commande b')
            ->select('b.*, e.nom_complet')
            ->join('employes e', 'e.id_employe = b.employe_id', 'left')
            ->where('b.id_bon', $id)
            ->get()->getRowArray();

        $lignes = $db->table('lignes_bon_commande l')
            ->select('l.*, p.nom, p.unite, p.reference AS ref_produit')
            ->join('produits_annexes p', 'p.id_produit = l.produit_id')
            ->where('l.bon_id', $id)
            ->get()->getResultArray();

        return view('pages/stocks/print_bon', ['bon' => $bon, 'lignes' => $lignes]);
    }

    public function deleteBon(int $id)
    {
        $db  = \Config\Database::connect();
        $bon = $db->table('bons_commande')->where('id_bon', $id)->get()->getRowArray();
        if ($bon && $bon['statut'] === 'recu') {
            return redirect()->back()->with('error', 'Impossible de supprimer un bon déjà reçu.');
        }
        $db->table('lignes_bon_commande')->where('bon_id', $id)->delete();
        $db->table('bons_commande')->where('id_bon', $id)->delete();
        return redirect()->to('stocks/bons')->with('success', 'Bon supprimé.');
    }

    // ═══════════════════════════════════════════
    // API ALERTES
    // ═══════════════════════════════════════════
    public function apiAlertes()
    {
        $db = \Config\Database::connect();
        $alertes = $db->table('produits_annexes')
            ->where('stock <=', 'stock_alerte', false)
            ->where('actif', 1)
            ->get()->getResultArray();

        return $this->response->setJSON([
            'total'   => count($alertes),
            'alertes' => $alertes,
        ]);
    }
}
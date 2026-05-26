<?php
namespace App\Controllers;

class CycleController extends BaseController
{
    // ═══════════════════════════════════════════
    // LISTE DES CYCLES
    // ═══════════════════════════════════════════
    public function index()
    {
        $db = \Config\Database::connect();

        $cycles = $db->table('cycles_machine c')
            ->select('c.*, m.nom AS machine_nom, m.type_machine,
                      e.nom_complet AS operateur')
            ->join('machines m',  'm.id_machine = c.machine_id')
            ->join('employes e',  'e.id_employe = c.employe_id', 'left')
            ->orderBy('c.created_at', 'DESC')
            ->limit(50)
            ->get()->getResultArray();

        $machines = $db->table('machines')->where('actif', 1)->get()->getResultArray();

        return view('pages/production/cycles/index', [
            'title'    => 'Cycles Machine',
            'cycles'   => $cycles,
            'machines' => $machines,
        ]);
    }

    // ═══════════════════════════════════════════
    // NOUVEAU CYCLE — formulaire
    // ═══════════════════════════════════════════
    public function nouveau()
    {
        $db       = \Config\Database::connect();
        $machines = $db->table('machines')->where('actif', 1)->get()->getResultArray();
        $consommables = $db->table('produits_annexes')
            ->whereIn('type_produit', ['production', 'les_deux'])
            ->where('actif', 1)
            ->where('stock >', 0)
            ->orderBy('nom')
            ->get()->getResultArray();

        return view('pages/production/cycles/nouveau', [
            'title'        => 'Nouveau cycle',
            'machines'     => $machines,
            'consommables' => $consommables,
        ]);
    }

    // ═══════════════════════════════════════════
    // CRÉER LE CYCLE + articles + consommables
    // ═══════════════════════════════════════════
    public function store()
    {
        $db  = \Config\Database::connect();
        $ref = 'CYC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        // 1. Récupérer les barcodes saisis
        $barcodes     = array_filter(array_map('trim',
            explode("\n", $this->request->getPost('barcodes') ?? '')
        ));

        if (empty($barcodes)) {
            return redirect()->back()->with('error', 'Aucun article saisi.');
        }

        // 2. Résoudre les articles depuis les barcodes
        $articles = [];
        $inconnus = [];
        foreach ($barcodes as $bc) {
            $art = $db->table('depot_articles da')
                ->select('da.id_article_depose, da.barcode_unique,
                          da.designation_libre, l.nom_libelle,
                          d.code_commande, c.nomclient')
                ->join('libelles l', 'l.id_libelle = da.libelle_id')
                ->join('depots d',   'd.id_depot = da.depot_id')
                ->join('clients c',  'c.id_client = d.client_id')
                ->where('da.barcode_unique', $bc)
                ->get()->getRowArray();

            if ($art) {
                $articles[] = $art;
            } else {
                $inconnus[] = $bc;
            }
        }

        if (!empty($inconnus)) {
            return redirect()->back()
                ->with('error', 'Codes-barres introuvables : ' . implode(', ', $inconnus));
        }

        $nbArticles = count($articles);

        // 3. Vérifier consommables
        $produits = $this->request->getPost('produits') ?? [];
        $quantites = $this->request->getPost('quantites') ?? [];
        $consommablesValides = [];
        foreach ($produits as $i => $pid) {
            $qte = (float) ($quantites[$i] ?? 0);
            if ($pid && $qte > 0) {
                $consommablesValides[] = ['produit_id' => $pid, 'quantite' => $qte];
            }
        }

        $db->transStart();

        // 4. Créer le cycle
        $db->table('cycles_machine')->insert([
            'machine_id'   => $this->request->getPost('machine_id'),
            'employe_id'   => session()->get('employe_id'),
            'reference'    => $ref,
            'statut'       => 'en_cours',
            'nb_articles'  => $nbArticles,
            'observations' => $this->request->getPost('observations'),
            'date_debut'   => date('Y-m-d H:i:s'),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        $idCycle = $db->insertID();

        // 5. Lier les articles au cycle
        foreach ($articles as $art) {
            $db->table('cycle_articles')->insert([
                'cycle_id'          => $idCycle,
                'article_depose_id' => $art['id_article_depose'],
            ]);
        }

        // 6. Enregistrer consommables + décrémenter stock
        foreach ($consommablesValides as $conso) {
            $qtaParArticle = $nbArticles > 0
                ? round($conso['quantite'] / $nbArticles, 3)
                : 0;

            $db->table('cycle_consommables')->insert([
                'cycle_id'             => $idCycle,
                'produit_id'           => $conso['produit_id'],
                'quantite_totale'      => $conso['quantite'],
                'quantite_par_article' => $qtaParArticle,
            ]);

            // Décrémenter le stock via mouvements_stock
            \App\Controllers\StockController::mouvementerStatique(
                (int) $conso['produit_id'],
                'consommation',
                (int) ceil($conso['quantite']),
                'Cycle machine ' . $ref . ' — ' . $nbArticles . ' article(s)',
                $ref
            );
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Erreur lors de la création du cycle.');
        }

        return redirect()->to('production/cycles/' . $idCycle)
            ->with('success', 'Cycle ' . $ref . ' créé avec ' . $nbArticles . ' article(s).');
    }

    // ═══════════════════════════════════════════
    // DÉTAIL D'UN CYCLE
    // ═══════════════════════════════════════════
    public function detail(int $id)
    {
        $db    = \Config\Database::connect();
        $cycle = $db->table('cycles_machine c')
            ->select('c.*, m.nom AS machine_nom, m.type_machine,
                      m.capacite_max, e.nom_complet AS operateur')
            ->join('machines m', 'm.id_machine = c.machine_id')
            ->join('employes e', 'e.id_employe = c.employe_id', 'left')
            ->where('c.id_cycle', $id)
            ->get()->getRowArray();

        if (!$cycle) return redirect()->to('production/cycles')->with('error', 'Cycle introuvable.');

        $articles = $db->table('cycle_articles ca')
            ->select('ca.*, da.barcode_unique, da.designation_libre,
                      da.observations, l.nom_libelle,
                      d.code_commande, d.id_depot,
                      cl.nomclient, cl.telephone,
                      ep.libelle AS etape_libelle, ep.couleur AS etape_couleur,
                      dp.options_express')
            ->join('depot_articles da',   'da.id_article_depose = ca.article_depose_id')
            ->join('libelles l',          'l.id_libelle = da.libelle_id')
            ->join('depots d',            'd.id_depot = da.depot_id')
            ->join('clients cl',          'cl.id_client = d.client_id')
            ->join('etapes_production ep','ep.id_etape = da.etape_courante_id', 'left')
            ->join('depot_prestations dp','dp.article_depose_id = da.id_article_depose', 'left')
            ->where('ca.cycle_id', $id)
            ->get()->getResultArray();

        $consommables = $db->table('cycle_consommables cc')
            ->select('cc.*, p.nom, p.unite, p.stock AS stock_actuel')
            ->join('produits_annexes p', 'p.id_produit = cc.produit_id')
            ->where('cc.cycle_id', $id)
            ->get()->getResultArray();

        return view('pages/production/cycles/detail', [
            'title'        => 'Cycle ' . $cycle['reference'],
            'cycle'        => $cycle,
            'articles'     => $articles,
            'consommables' => $consommables,
        ]);
    }

    // ═══════════════════════════════════════════
    // TERMINER UN CYCLE
    // ═══════════════════════════════════════════
    public function terminer(int $id)
    {
        $db = \Config\Database::connect();
        $db->table('cycles_machine')->where('id_cycle', $id)->update([
            'statut'   => 'termine',
            'date_fin' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('production/cycles/' . $id)
            ->with('success', 'Cycle terminé.');
    }

    // ═══════════════════════════════════════════
    // ANNULER UN CYCLE
    // ═══════════════════════════════════════════
    public function annuler(int $id)
    {
        $db    = \Config\Database::connect();
        $cycle = $db->table('cycles_machine')->where('id_cycle', $id)->get()->getRowArray();

        if (!$cycle || $cycle['statut'] === 'termine') {
            return redirect()->back()->with('error', 'Impossible d\'annuler ce cycle.');
        }

        $db->table('cycles_machine')->where('id_cycle', $id)->update(['statut' => 'annule']);
        return redirect()->to('production/cycles')->with('success', 'Cycle annulé.');
    }

    // ═══════════════════════════════════════════
    // API — Rechercher article par barcode
    // ═══════════════════════════════════════════
    public function apiArticleParBarcode(string $barcode)
    {
        $db  = \Config\Database::connect();
        $art = $db->table('depot_articles da')
            ->select('da.id_article_depose, da.barcode_unique,
                      da.designation_libre, da.observations,
                      l.nom_libelle,
                      d.code_commande, d.id_depot,
                      c.nomclient,
                      ep.libelle AS etape_libelle,
                      dp.options_express')
            ->join('libelles l',          'l.id_libelle = da.libelle_id')
            ->join('depots d',            'd.id_depot = da.depot_id')
            ->join('clients c',           'c.id_client = d.client_id')
            ->join('etapes_production ep','ep.id_etape = da.etape_courante_id', 'left')
            ->join('depot_prestations dp','dp.article_depose_id = da.id_article_depose', 'left')
            ->where('da.barcode_unique', $barcode)
            ->get()->getRowArray();

        if (!$art) {
            return $this->response->setJSON(['success' => false, 'message' => 'Article introuvable : ' . $barcode]);
        }

        return $this->response->setJSON(['success' => true, 'article' => $art]);
    }

    // ═══════════════════════════════════════════
    // MACHINES — GESTION
    // ═══════════════════════════════════════════
    public function machines()
    {
        $db       = \Config\Database::connect();
        $machines = $db->table('machines m')
            ->select('m.*, COUNT(c.id_cycle) AS nb_cycles')
            ->join('cycles_machine c', 'c.machine_id = m.id_machine', 'left')
            ->groupBy('m.id_machine')
            ->get()->getResultArray();

        return view('pages/production/cycles/machines', [
            'title'    => 'Gestion des machines',
            'machines' => $machines,
        ]);
    }

    public function storeMachine()
    {
        \Config\Database::connect()->table('machines')->insert([
            'nom'          => $this->request->getPost('nom'),
            'type_machine' => $this->request->getPost('type_machine'),
            'capacite_max' => (int) $this->request->getPost('capacite_max'),
            'actif'        => 1,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('production/machines')->with('success', 'Machine ajoutée.');
    }

    public function updateMachine(int $id)
    {
        \Config\Database::connect()->table('machines')->where('id_machine', $id)->update([
            'nom'          => $this->request->getPost('nom'),
            'type_machine' => $this->request->getPost('type_machine'),
            'capacite_max' => (int) $this->request->getPost('capacite_max'),
            'actif'        => (int) $this->request->getPost('actif'),
        ]);
        return redirect()->to('production/machines')->with('success', 'Machine mise à jour.');
    }

    public function deleteMachine(int $id)
    {
        \Config\Database::connect()->table('machines')->where('id_machine', $id)->delete();
        return redirect()->to('production/machines')->with('success', 'Machine supprimée.');
    }
}
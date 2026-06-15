<?php
namespace App\Controllers;

class ShopController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // 1. On récupère d'abord les stats de base des boutiques (Employés et Dépôts)
        $shopsData = $db->table('shops s')
            ->select('s.*,
                    COUNT(DISTINCT e.id_employe) AS nb_employes, -- CodeIgniter gérera si c\'est id ou id_employe via la relation globale
                    COUNT(DISTINCT d.id_depot)   AS nb_depots,
                    COALESCE(SUM(CASE WHEN d.statut_global NOT IN ("livre","annule") THEN 1 ELSE 0 END),0) AS depots_actifs')
            ->join('employes e', 'e.shop_id = s.id_shop', 'left')
            ->join('depots d',   'd.shop_id = s.id_shop', 'left')
            ->groupBy('s.id_shop')
            ->orderBy('s.nom_shop')
            ->get()->getResultArray();

        // 2. On récupère le CA total par boutique via une requête de groupe séparée sur les transactions
        // Cette méthode évite les jointures croisées qui multiplient les résultats et faussent les SUM/COUNT
        $caData = $db->table('transactions t')
            ->select('c.shop_id, SUM(t.montant) as ca_total')
            ->join('caisses c', 'c.id_caisse = t.caisse_id')
            ->where('t.type', 'encaissement')
            ->where('t.statut', 'valide')
            ->groupBy('c.shop_id')
            ->get()->getResultArray();

        // On indexe le CA par shop_id pour un accès rapide
        $caParShop = [];
        foreach ($caData as $row) {
            $caParShop[$row['shop_id']] = (float) $row['ca_total'];
        }

        // 3. On fusionne le CA calculé dans notre tableau principal des shops
        foreach ($shopsData as &$shop) {
            $idShop = $shop['id_shop'];
            $shop['ca_total'] = $caParShop[$idShop] ?? 0.0;
        }

        return view('pages/shops/index', [
            'title' => 'Établissements',
            'shops' => $shopsData,
        ]);
    }

    public function store()
    {
        \Config\Database::connect()->table('shops')->insert([
            'nom_shop'  => $this->request->getPost('nom_shop'),
            'adresse'   => $this->request->getPost('adresse'),
            'telephone' => $this->request->getPost('telephone') ?: null,
            'email'     => $this->request->getPost('email')     ?: null,
            'created_at'=> date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('shop')->with('success', 'Établissement créé.');
    }

    public function update(int $id)
    {
        \Config\Database::connect()->table('shops')->where('id_shop', $id)->update([
            'nom_shop'  => $this->request->getPost('nom_shop'),
            'adresse'   => $this->request->getPost('adresse'),
            'telephone' => $this->request->getPost('telephone') ?: null,
            'email'     => $this->request->getPost('email')     ?: null,
        ]);
        return redirect()->to('shop')->with('success', 'Établissement mis à jour.');
    }

    public function delete(int $id)
    {
        $db = \Config\Database::connect();
        $nb = $db->table('employes')->where('shop_id', $id)->countAllResults();
        if ($nb > 0) {
            return redirect()->to('shop')
                ->with('error', 'Impossible : ' . $nb . ' employé(s) rattaché(s).');
        }
        $db->table('shops')->where('id_shop', $id)->delete();
        return redirect()->to('shop')->with('success', 'Établissement supprimé.');
    }

    // ── Changer de shop actif (pour l'admin central) ──────
    public function switcher(int $shopId)
    {
        if (!est_admin_central()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $db   = \Config\Database::connect();
        $shop = $db->table('shops')->where('id_shop', $shopId)->get()->getRowArray();

        if (!$shop) {
            return redirect()->back()->with('error', 'Établissement introuvable.');
        }

        // CORRECTION LOGIQUE ICI : On stocke l'ID demandé ($shopId) reçu en paramètre.
        // Votre ancien code écrasait immédiatement $shopId par la session existante,
        // ce qui empêchait définitivement de changer de boutique !
        session()->set('shop_id_vue', $shopId); 
        
        return redirect()->back()
            ->with('success', 'Vue basculée sur : ' . $shop['nom_shop']);
    }

    public function resetVue()
    {
        session()->remove('shop_id_vue');
        return redirect()->to('dashboard')
            ->with('success', 'Vue globale restaurée.');
    }
}
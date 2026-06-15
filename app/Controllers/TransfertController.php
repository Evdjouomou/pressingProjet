<?php
namespace App\Controllers;

class TransfertController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $transferts = $db->table('transferts_articles tr')
            ->select('tr.*,
                      da.barcode_unique, l.nom_libelle,
                      d.code_commande, c.nomclient,
                      ss.nom_shop AS shop_source,
                      sd.nom_shop AS shop_dest,
                      e.nom_complet AS demandeur')
            ->join('depot_articles da','da.id_article_depose = tr.article_depose_id')
            ->join('libelles l',       'l.id_libelle = da.libelle_id')
            ->join('depots d',         'd.id_depot = da.depot_id')
            ->join('clients c',        'c.id_client = d.client_id')
            ->join('shops ss',         'ss.id_shop = tr.shop_source_id')
            ->join('shops sd',         'sd.id_shop = tr.shop_dest_id')
            ->join('employes e',       'e.id_employe = tr.employe_id','left')
            ->orderBy('tr.created_at','DESC')
            ->get()->getResultArray();

        $shops = $db->table('shops')->orderBy('nom_shop')->get()->getResultArray();

        return view('pages/transferts/index', [
            'title'      => 'Transferts inter-shops',
            'transferts' => $transferts,
            'shops'      => $shops,
        ]);
    }

    public function store()
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $barcode    = trim($this->request->getPost('barcode'));
        $shopDestId = (int) $this->request->getPost('shop_dest_id');
        $motif      = $this->request->getPost('motif');

        // Trouver l'article
        $article = $db->table('depot_articles da')
            ->select('da.*, d.shop_id AS shop_source_id, d.code_commande')
            ->join('depots d','d.id_depot = da.depot_id')
            ->where('da.barcode_unique', $barcode)
            ->get()->getRowArray();

        if (!$article) {
            return redirect()->back()
                ->with('error', 'Article introuvable : ' . $barcode);
        }

        if ($article['shop_source_id'] == $shopDestId) {
            return redirect()->back()
                ->with('error', 'L\'article est déjà dans cet établissement.');
        }

        // Créer le transfert
        $db->table('transferts_articles')->insert([
            'article_depose_id' => $article['id_article_depose'],
            'shop_source_id'    => $article['shop_source_id'],
            'shop_dest_id'      => $shopDestId,
            'employe_id'        => employe_connecte_id(),
            'motif'             => $motif,
            'statut'            => 'en_attente',
            'created_at'        => $now,
        ]);

        return redirect()->to('transferts')
            ->with('success', 'Transfert créé — en attente de confirmation du shop destinataire.');
    }

    public function confirmer(int $id)
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $transfert = $db->table('transferts_articles')
            ->where('id_transfert', $id)
            ->where('statut', 'en_attente')
            ->get()->getRowArray();

        if (!$transfert) {
            return redirect()->back()->with('error', 'Transfert introuvable.');
        }

        $db->transStart();

        // Mettre à jour le shop du dépôt
        $article = $db->table('depot_articles')
            ->where('id_article_depose', $transfert['article_depose_id'])
            ->get()->getRowArray();

        $db->table('depots')
            ->where('id_depot', $article['depot_id'])
            ->update([
                'shop_id'    => $transfert['shop_dest_id'],
                'updated_at' => $now,
            ]);

        // Confirmer le transfert
        $db->table('transferts_articles')
            ->where('id_transfert', $id)
            ->update([
                'statut'       => 'confirme',
                'confirmed_at' => $now,
            ]);

        $db->transComplete();

        return redirect()->to('transferts')
            ->with('success', 'Transfert confirmé — article déplacé vers le nouveau shop.');
    }

    public function annuler(int $id)
    {
        \Config\Database::connect()->table('transferts_articles')
            ->where('id_transfert', $id)
            ->update(['statut' => 'annule']);
        return redirect()->to('transferts')->with('success', 'Transfert annulé.');
    }
}
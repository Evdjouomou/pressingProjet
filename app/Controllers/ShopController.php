<?php
namespace App\Controllers;
use App\Models\ShopModel;

class ShopController extends BaseController
{
    public function index()
    {
        $shopModel = new ShopModel();
        $db        = \Config\Database::connect();

        $shops = $shopModel->orderBy('nom_shop')->findAll();
        foreach ($shops as &$s) {
            $s['nb_employes'] = $db->table('employes')
                ->where('shop_id', $s['id_shop'])
                ->countAllResults();
        }

        return view('pages/personnel/shop', [
            'title' => 'Gestion des Boutiques',
            'shops' => $shops,
        ]);
    }

    public function store()
    {
        (new ShopModel())->insert([
            'nom_shop'   => $this->request->getPost('nom_shop'),
            'adresse'    => $this->request->getPost('adresse'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('shop')->with('success', 'Boutique créée avec succès.');
    }

    public function update(int $id)
    {
        (new ShopModel())->update($id, [
            'nom_shop' => $this->request->getPost('nom_shop'),
            'adresse'  => $this->request->getPost('adresse'),
        ]);
        return redirect()->to('shop')->with('success', 'Boutique mise à jour.');
    }

    public function delete(int $id)
    {
        $db = \Config\Database::connect();
        $nb = $db->table('employes')->where('shop_id', $id)->countAllResults();
        if ($nb > 0) {
            return redirect()->to('shop')
                             ->with('error', 'Impossible : ' . $nb . ' employé(s) sont affectés à cette boutique.');
        }
        (new ShopModel())->delete($id);
        return redirect()->to('shop')->with('success', 'Boutique supprimée.');
    }
}
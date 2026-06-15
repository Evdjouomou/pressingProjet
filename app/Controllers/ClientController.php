<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\GrilleProModel;

class ClientController extends BaseController
{
    public function index()
    {
        $db           = \Config\Database::connect();
        $grillesModel = new GrilleProModel();

        $builder = $db->table('clients c')
            ->select('c.*, s.nom_shop')
            ->join('shops s', 's.id_shop = c.shop_id', 'left')
            ->orderBy('c.dateajout', 'DESC');

        $shopId = shop_actif_id();
        if ($shopId !== null) {
            $builder->groupStart()
                ->where('c.shop_id', $shopId)
                ->orWhere('c.shop_id IS NULL') // Sécurité : affiche aussi les clients globaux ou sans shop assigné
                ->orWhere('c.id_client IN (
                    SELECT DISTINCT client_id FROM depots WHERE shop_id = ' . (int)$shopId . '
                )')
            ->groupEnd();
        }

        $clients = $builder->get()->getResultArray();

        return view('pages/client', [
            'title'   => 'Gestion des Clients',
            'clients' => $clients,
            'grilles' => $grillesModel->findAll(),
        ]);
    }

    public function saveclient()
    {
        $clientModel = new ClientModel();

        if($this->request->is('post')){
            $rules = [
                'nomclient'     => 'required|min_length[3]|is_unique[clients.nomclient]',
                'email'         => 'permit_empty|valid_email|is_unique[clients.email]',
                'telephone'     => 'required|min_length[9]|is_unique[clients.telephone]',
                'adresse'       => 'required',
                'jour'          => 'required',
                'mois'          => 'required',
                'typeclient'    => 'required|in_list[particulier,professionnel]',
                'grille_id'     => 'permit_empty|is_not_unique[grilles_tarifaires.id_grille]'
            ];

            $messages = [
                'nomclient' => [
                    'is_unique' => 'Ce nom de client existe déjà dans la base de données.'
                ],
                'email' => [
                    'is_unique'   => 'Cet email est déjà utilisé par un autre client.',
                    'valid_email' => 'Veuillez entrer une adresse email valide.'
                ],
                'telephone' => [
                    'is_unique' => 'Ce numéro de téléphone est déjà enregistré.'
                ]
            ];

            if($this->validate($rules, $messages)){
                $jour = $this->request->getPost('jour');
                $mois = $this->request->getPost('mois');
                $fullBirthday = $jour . ' ' . $mois;

                $data = [
                    'nomclient'      => $this->request->getPost('nomclient'),
                    'email'          => $this->request->getPost('email') ?: null,
                    'telephone'      => $this->request->getPost('telephone'),
                    'adresse'        => $this->request->getPost('adresse'),
                    'journaissance'  => $fullBirthday,
                    'typeclient'     => $this->request->getPost('typeclient'),
                    'grille_id'      => $this->request->getPost('grille_id') ?: null,
                    'preferences'    => $this->request->getPost('preferences'),
                    'dateajout'      => date('Y-m-d H:i:s'),
                    'enregistre_par' => employe_connecte_id(),
                    // CORRECTION CRITIQUE : On lie le client au shop actif lors de sa création
                    'shop_id'        => shop_actif_id() 
                ];

                if ($clientModel->save($data)) {
                    return redirect()->to(base_url('client'))->with('success', 'Client ajouté avec succès');
                } else {
                    return redirect()->back()->with('validation', $clientModel->errors());
                }
            } else {
                return redirect()->back()->withInput()->with('validation', $this->validator->getErrors());
            }
        }
        return redirect()->back();    
    }

    public function ficheclient(int $id)
    {
        $db = \Config\Database::connect();

        $client = $db->table('clients c')
            ->select('c.*,
                    e.nom_complet AS enregistre_par_nom,
                    e.matricule   AS enregistre_par_matricule,
                    s.nom_shop    AS shop_nom')
            ->join('employes e', 'e.id_employe = c.enregistre_par', 'left')
            ->join('shops s',    's.id_shop = c.shop_id',           'left')
            ->where('c.id_client', $id)
            ->get()->getRowArray();

        if (!$client) {
            return redirect()->to('client')
                ->with('error', 'Client introuvable.');
        }

        // Points fidélité
        $solde  = $client['solde_fidelite'] ?? 0;
        $nbDep  = $db->table('depots')
            ->where('client_id', $id)
            ->countAllResults();

        // Dépôts du client
        $depots = $db->table('depots d')
            ->select('d.id_depot, d.code_commande, d.created_at,
                    d.date_livraison_prevue, d.total_ttc,
                    d.statut_global, d.abonnement_id,
                    s.nom_shop,
                    COUNT(da.id_article_depose) AS nb_articles,
                    COALESCE(SUM(CASE WHEN t.type="encaissement"
                        AND t.statut="valide" THEN t.montant ELSE 0 END),0)
                        AS total_encaisse')
            ->join('shops s',           's.id_shop = d.shop_id',    'left')
            ->join('depot_articles da', 'da.depot_id = d.id_depot', 'left')
            ->join('transactions t',    't.depot_id = d.id_depot',  'left')
            ->where('d.client_id', $id)
            ->groupBy('d.id_depot')
            ->orderBy('d.created_at', 'DESC')
            ->get()->getResultArray();

        // Abonnement actif
        $abonActif = $db->table('abonnements a')
            ->select('a.*, t.nom AS offre_nom')
            ->join('type_abon t', 't.id_type_abon = a.type_abon_id', 'left')
            ->where('a.client_id', $id)
            ->where('a.statut', 'actif')
            ->where('a.date_fin >=', date('Y-m-d'))
            ->orderBy('a.date_fin', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        return view('pages/ficheclient', [
            'title'     => $client['nomclient'],
            'client'    => $client,
            'solde'     => $solde,
            'nb_depots' => $nbDep,
            'depots'    => $depots,
            'abon_actif'=> $abonActif,
        ]);
    }

    public function updateclient($id)
    {
        $rules = [
            'nomclient'  => "required|min_length[3]|is_unique[clients.nomclient,id_client,$id]",
            'telephone'  => "required|min_length[9]|is_unique[clients.telephone,id_client,$id]",
            'typeclient' => 'required'
        ];

        if ($this->validate($rules)) {
            $data = [
                'nomclient'     => $this->request->getPost('nomclient'),
                'email'         => $this->request->getPost('email'),
                'telephone'     => $this->request->getPost('telephone'),
                'adresse'       => $this->request->getPost('adresse'),
                'typeclient'    => $this->request->getPost('typeclient'),
                'grille_id'     => $this->request->getPost('grille_id') ?: null,
                'preferences'   => $this->request->getPost('preferences'),
                'journaissance' => $this->request->getPost('jour') . ' ' . $this->request->getPost('mois'),
            ];

            $clientModel = new ClientModel();
            $clientModel->update($id, $data);
            return redirect()->to('client')->with('success', 'Client mis à jour.');
        } else {
            return redirect()->back()->withInput()->with('validation', $this->validator->getErrors());
        }
    }

    public function deleteclient($id)
    {
        $clientModel = new ClientModel();
        if ($clientModel->delete($id)) {
            return redirect()->to('client')->with('success', 'Client supprimé.');
        } else {
            return redirect()->to('client')->with('error', 'Impossible de supprimer ce client.');
        }
    }
}
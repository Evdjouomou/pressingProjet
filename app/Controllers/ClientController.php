<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\GrilleProModel;

class ClientController extends BaseController
{
    public function index()
    {
        $clientModel = new ClientModel();
        $grillesModel = new GrilleProModel();
        $data = [
            'title' => 'Gestion des Clients',
            'clients' => $clientModel->orderBy('dateajout', 'DESC')->findAll(),
            'grilles' => $grillesModel->findAll()
        ];
        return view('pages/client', $data);
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
                    'email'          => $this->request->getPost('email'),
                    'telephone'      => $this->request->getPost('telephone'),
                    'adresse'        => $this->request->getPost('adresse'),
                    'journaissance'  => $fullBirthday,
                    'typeclient'     => $this->request->getPost('typeclient'),
                    'grille_id'      => $this->request->getPost('grille_id') ?: null,
                    'preferences'    => $this->request->getPost('preferences'),
                    'dateajout'      => date('Y-m-d H:i:s'),
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

    public function ficheclient($id_client)
    {
        $clientModel = new ClientModel();
        $db = \Config\Database::connect();

        // 1. Recalcul du solde fidélité réel depuis les dépôts
        $soldeReel = $db->table('depots d')
            ->selectSum('s.points_fidelite', 'total')
            ->join('depot_articles da', 'da.depot_id = d.id_depot', 'left')
            ->join('depot_prestations dp', 'dp.article_depose_id = da.id_article_depose', 'left')
            ->join('services s', 's.id_service = dp.service_id', 'left')
            ->where('d.client_id', $id_client)
            ->get()->getRowArray();

        $soldeReel = (int) ($soldeReel['total'] ?? 0);

        // 2. Mise à jour du solde en base si désynchronisé
        $clientActuel = $clientModel->getClientById($id_client);
        if ((int) $clientActuel['solde_fidelite'] !== $soldeReel) {
            $db->table('clients')
            ->where('id_client', $id_client)
            ->update(['solde_fidelite' => $soldeReel]);

            // Recharge le client avec le bon solde
            $clientActuel['solde_fidelite'] = $soldeReel;
        }

        $data['client']  = $clientActuel;
        $data['forfaits'] = $db->table('type_abon')->get()->getResultArray();

        // 3. Dépôts avec nb articles et points par dépôt
        $data['depots'] = $db->table('depots d')
            ->select('
                d.id_depot,
                d.code_commande,
                d.created_at,
                d.date_livraison_prevue,
                d.total_ttc,
                d.acompte_verse,
                d.statut_global,
                COUNT(DISTINCT da.id_article_depose) AS nb_articles,
                COALESCE(SUM(s.points_fidelite), 0) AS total_points
            ')
            ->join('depot_articles da', 'da.depot_id = d.id_depot', 'left')
            ->join('depot_prestations dp', 'dp.article_depose_id = da.id_article_depose', 'left')
            ->join('services s', 's.id_service = dp.service_id', 'left')
            ->where('d.client_id', $id_client)
            ->groupBy('d.id_depot')
            ->orderBy('d.created_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('pages/ficheclient', $data);
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
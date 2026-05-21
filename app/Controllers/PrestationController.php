<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LibelleModel;
use App\Models\ServiceModel;

class PrestationController extends BaseController
{
    public function index()
    {
        $prestationModel = new ServiceModel();
        $libelleModel = new LibelleModel();
        $data = [
            'Title' => 'Gestion des Prestations',
            'categories' => $libelleModel->select('categorie')->distinct()->findAll(),
            'libelles' => $libelleModel->findAll(),
            'services' => $prestationModel->select('services.*, libelles.*')
                                        ->join('libelles', 'services.libelle_id = libelles.id_libelle', 'left')
                                        ->findAll()
        ];
        return view('pages/prestation', $data);
    }

    public function save()
    {
        $model = new ServiceModel();
        $data = [
            'libelle_id'         => $this->request->getPost('libelle_id'),
            'type_prestation'    => $this->request->getPost('prestation'),
            'prix_unitaire_base' => $this->request->getPost('prix_unitaire_base'),
            'taux_tva'           => $this->request->getPost('taux_tva') ?: 19.25,
            'delai_standard'     => $this->request->getPost('delai_standard') ?: 72,
            'majoration_express' => $this->request->getPost('majoration_express') ?: 25.00,
            'points_fidelite'    => $this->request->getPost('points_fidelite') ?: 0,
            'statut'             => $this->request->getPost('statut'),
            'created_at'         => date('Y-m-d H:i:s'),
        ];

        if (empty($data['libelle_id'])) {
            return redirect()->back()->withInput()->with('error', 'Veuillez sélectionner un article (libellé) valide.');
        }

        if($model->insert($data)) {
            return redirect()->to(base_url('/prestation'))->with('success', 'Prestation créée avec succès');
        } else {
            return redirect()->back()->withInput()->with('error', $model->errors());
        }
    }

    public function delete($id)
    {
        $model = new ServiceModel();
        if($model->find($id)){
            $model->delete($id);
            return redirect()->to(base_url('/prestation'))->with('success', 'Service supprime avec succes.');
        }

        return redirect()->to(base_url('/prestation'))->with('error', 'Service non trouve.');
    }

    public function update($id)
    {
        $model = new ServiceModel();
        
        $data = [
            'libelle_id'         => $this->request->getPost('libelle_id'), 
            'type_prestation'    => $this->request->getPost('prestation'),
            'prix_unitaire_base' => $this->request->getPost('prix_unitaire_base'),
            'taux_tva'           => $this->request->getPost('taux_tva') ?: 19.25,
            'delai_standard'     => $this->request->getPost('delai_standard') ?: 72,
            'majoration_express' => $this->request->getPost('majoration_express') ?: 25.00,
            'points_fidelite'    => $this->request->getPost('points_fidelite') ?: 0,
            'statut'             => $this->request->getPost('statut'),
            'updated_at'         => date('Y-m-d H:i:s'), 
        ];

        if (empty($data['libelle_id'])) {
            return redirect()->back()->withInput()->with('error', 'Le libellé sélectionné est invalide.');
        }

        if($model->update($id, $data))
        {
            return redirect()->to(base_url('/prestation'))->with('success', 'Prestation mise à jour avec succès.');
        }

        return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour.');
    }

    
}
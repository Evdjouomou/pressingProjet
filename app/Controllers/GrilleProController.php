<?php

namespace App\Controllers;

use App\Models\GrilleProModel;
use App\Models\TarifSpecifiqueModel;

class GrilleProController extends BaseController
{
    protected $grilleModel;

    public function __construct()
    {
        $this->grilleModel = new GrilleProModel();
    }

    public function index()
    {
        
        $data = [
            'title' => 'Gestion des Grilles Tarifaires',
            'grilles' => $this->grilleModel->findAll()
        ];
        return view('pages/grillepro', $data);
    }

    public function save() 
    {
        $rules = [
            'nom_grille' => 'required|min_length[3]|max_length[255]',
            'description' => 'max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', 'Veuillez corriger les erreurs du formulaire');
        }
        $this->grilleModel->save([
            'nom_grille' => $this->request->getPost('nom_grille'),
            'description' => $this->request->getPost('description')
        ]);

        return redirect()->to(base_url('/grillepro'))->with('success', 'Grille ajoutee avec succes');
    }

    public function update($id = null)
    {
        if(!$id) {
            return redirect()->to(base_url('/grillepro'));
        }

        $rules = [
            'nom_grille' => 'required|min_length[3]|max_length[255]',
            'description' => 'max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', 'Veuillez corriger les erreurs du formulaire');
        }

        $this->grilleModel->update($id, [
            'nom_grille'  => $this->request->getPost('nom_grille'),
            'description' => $this->request->getPost('description')
        ]);

        return redirect()->to(base_url('/grillepro'))->with('success', 'Grille mise à jour');
    }

    public function delete($id = null)
    {
        if(!$id) {
            return redirect()->to(base_url('/grillepro'));
        }

        $this->grilleModel->delete($id);
        return redirect()->to(base_url('/grillepro'))->with('success', 'Grille supprimée');
    }

    public function tarifs($id_grille)
    {
        $db = \Config\Database::connect();
        $grille = $this->grilleModel->find($id_grille);
        
        if (!$grille) return redirect()->to('/grilles')->with('error', 'Grille introuvable.');

        // 1. On récupère UNIQUEMENT les services qui ont un prix spécial pour cette grille
        $builder = $db->table('tarifs_specifiques ts');
        $builder->select('ts.*, s.libelle, s.prix_unitaire_base');
        $builder->join('services s', 's.id_service = ts.service_id');
        $builder->where('ts.grille_id', $id_grille);
        $tarifs_existants = $builder->get()->getResultArray();

        // 2. On récupère TOUS les services pour le menu déroulant du Modal
        $serviceModel = new \App\Models\ServiceModel(); // Assure-toi d'avoir ce modèle
        $tous_les_services = $serviceModel->where('statut', 'actif')->findAll();

        $data = [
            'title'      => 'Tarifs : ' . $grille['nom_grille'],
            'grille'     => $grille,
            'tarifs'     => $tarifs_existants,
            'services'   => $tous_les_services,
        ];

        return view('pages/grillestarif', $data);
    }

   public function save_tarif_specifique()
    {
        $model = new \App\Models\TarifSpecifiqueModel();

        $grilleId  = $this->request->getPost('grille_id');
        $serviceId = $this->request->getPost('service_id');
        $prix      = $this->request->getPost('prix_unitaire');

        if (empty($grilleId) || empty($serviceId) || empty($prix)) {
            return redirect()->back()->with('error', 'Tous les champs sont obligatoires.');
        }

        $existant = $model->where([
            'grille_id'  => $grilleId, 
            'service_id' => $serviceId
        ])->first();

        try {
            if ($existant) {
                $model->update($existant['id_tarif_spec'], ['prix_unitaire' => $prix]);
            } else {
                $model->insert([
                    'grille_id'     => $grilleId,
                    'service_id'    => $serviceId,
                    'prix_unitaire' => $prix
                ]);
            }
        
            return redirect()->to(base_url('grilles/tarifs/' . $grilleId))->with('success', 'Prix enregistré');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }
    }

    public function delete_tarif($id)
    {
        $model = new \App\Models\TarifSpecifiqueModel();
        $model->delete($id);
        return redirect()->back()->with('success', 'Le service repasse au tarif public.');
    }
}
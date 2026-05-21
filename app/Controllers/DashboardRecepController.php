<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AbonnementModel;
use App\Models\ReceptionnisteModel;
use App\Models\TypeAbonnementModel;
use App\Models\GrilleProModel;

class DashboardRecepController extends BaseController
{
    protected $receptionnisteModel;
    protected $typeAbonnementModel;
    protected $abonnementModel;
    protected $grilleModel;

    public function __construct()
    {
        $this->receptionnisteModel = new ReceptionnisteModel();
        $this->typeAbonnementModel = new TypeAbonnementModel();
        $this->abonnementModel = new AbonnementModel();
        $this->grilleModel = new GrilleProModel();
    }
    
    public function fichedepot()
    {
        return view('pages/fichedepot');
    }
    public function commande()
    {
        return view('pages/commande');
    }
    public function detailcommande()
    {
        return view('pages/detailcommande');
    }
    

    //-------------------CRUD CLIENT-------------------

    public function saveabonnement($id_client)
    {
        $id_type_abon = $this->request->getPost('id_type_abonnement');
        $date_debut = $this->request->getPost('date_debut');
        $jour_duree = $this->request->getPost('type_abonnement');

        $forfait = $this->typeAbonnementModel
                      ->where('id_type_abonnement', $id_type_abon)
                      ->get()
                      ->getRowArray();
        
        if(!$forfait) {
            return redirect()->back()->with('error', 'Type d\'abonnement invalide.');
        }

        try {
            $dateStart = new \DateTime($date_debut);
            $dateEnd = clone $dateStart;
            $dateEnd->modify("+$jour_duree days");

            $data = [
                'client_id' => $id_client,
                'id_type_abon' => $id_type_abon,
                'pieces_restantes' => $forfait['nb_pieces'],
                'date_achat' => $dateStart->format('Y-m-d H:i:s'),
                'date_expiration' => $dateEnd->format('Y-m-d H:i:s'),
                'statut' => 'actif'
            ];
            
            if($this->abonnementModel->insert($data))
            {
                return redirect()->to(base_url('receptionniste/ficheclient/' . $id_client))
                                 ->with('success', 'Abonnement enregistrer avec succes.');
            } else {
                return redirect()->back()->with('error', 'Impossible d\'enregistrer l\'abonnement.');
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Format de date invalide.');
        }
    }

    public function ficheabonnement()
    {
        $data['clients'] = $this->abonnementModel->getAllAbonnements();
        return view('pages/ficheabonnement', $data);
    }
}

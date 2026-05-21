<?php

namespace App\Controllers;

use App\Models\LibelleModel;

class LibelleController extends BaseController
{
    protected $libelleModel;

    public function __construct() {
        $this->libelleModel = new LibelleModel();
    }

    public function index()
    {
        $data = [
            'title'    => 'Gestion des Libellés',
            'libelles' => $this->libelleModel->findAll()
        ];
        return view('pages/libelle', $data);
    }

    public function savelibelle()
    {
        $data = [
            'categorie'   => $this->request->getPost('categorie'),
            'nom_libelle' => $this->request->getPost('nom_libelle'),
            'code_court'  => strtoupper($this->request->getPost('code_court')),
            'code_barre'  => $this->request->getPost('code_barre'),
        ];

        if ($this->libelleModel->insert($data)) {
            return redirect()->to(base_url('libelle'))->with('success', 'Nouveau libellé ajouté.');
        }
        return redirect()->back()->with('error', 'Erreur lors de l\'ajout.');
    }
}
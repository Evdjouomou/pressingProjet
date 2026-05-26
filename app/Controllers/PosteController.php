<?php
namespace App\Controllers;
use App\Models\PosteModel;
use App\Models\EmployeModel;

class PosteController extends BaseController
{
    public function index()
    {
        $posteModel  = new PosteModel();
        $employeModel = new EmployeModel();
        $db = \Config\Database::connect();

        // Nombre d'employés par poste
        $postes = $posteModel->orderBy('nom_poste')->findAll();
        foreach ($postes as &$p) {
            $p['nb_employes'] = $db->table('employes')
                ->where('poste_id', $p['id_poste'])
                ->countAllResults();
        }

        return view('pages/personnel/poste', [
            'title'  => 'Gestion des Postes',
            'postes' => $postes,
        ]);
    }

    public function store()
    {
        $model = new PosteModel();
        $model->insert([
            'nom_poste'  => $this->request->getPost('nom_poste'),
            'salaire'    => $this->request->getPost('salaire'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('poste')->with('success', 'Poste créé avec succès.');
    }

    public function update(int $id)
    {
        $model = new PosteModel();
        $model->update($id, [
            'nom_poste' => $this->request->getPost('nom_poste'),
            'salaire'   => $this->request->getPost('salaire'),
        ]);
        return redirect()->to('poste')->with('success', 'Poste mis à jour.');
    }

    public function delete(int $id)
    {
        $db = \Config\Database::connect();
        $nb = $db->table('employes')->where('poste_id', $id)->countAllResults();
        if ($nb > 0) {
            return redirect()->to('poste')
                             ->with('error', 'Impossible : ' . $nb . ' employé(s) sont affectés à ce poste.');
        }
        (new PosteModel())->delete($id);
        return redirect()->to('poste')->with('success', 'Poste supprimé.');
    }
}
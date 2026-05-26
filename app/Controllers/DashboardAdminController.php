<?php

namespace App\Controllers;

class DashboardAdminController extends BaseController
{
    public function index()
    {
        // Sécurité : On vérifie si l'utilisateur est bien connecté
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/')->with('error', 'Veuillez vous connecter pour accéder au tableau de bord.');
        }

        // On prépare les données de la session à envoyer à la vue
        $data = [
            'nom_complet' => session()->get('nom_complet'),
            'role'        => session()->get('role'),
            'matricule'   => session()->get('matricule')
        ];

        // On charge enfin ta vue en lui passant les données
        return view('pages/tableauboard', $data);
    }
}
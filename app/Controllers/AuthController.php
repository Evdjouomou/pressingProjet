<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\EmployeModel; // Importation du modèle des employés

class AuthController extends BaseController
{
    // Affiche la page de connexion
    public function index()
    {
        return view('auth/login');
    }

    // Traite la tentative de connexion (Soumission du formulaire)
    public function login()
    {
        $session = session();
        $model   = new EmployeModel();

        // Récupération des champs du formulaire
        // (Assure-toi que les attributs "name" de ton formulaire correspondent à 'identifiant' et 'password')
        $identifiant = $this->request->getPost('identifiant'); 
        $password    = $this->request->getPost('password');

        // On cherche l'employé soit par son matricule, soit par son e-mail
        $employe = $model->where('matricule', $identifiant)
                         ->orWhere('email', $identifiant)
                         ->first();

        if ($employe) {
            // Vérification du mot de passe haché
            if (password_verify($password, $employe['password'])) {
                
                // 🔐 INTERCEPTION : Vérification s'il s'agit de la première connexion
                if (isset($employe['premiere_connexion']) && (int)$employe['premiere_connexion'] === 1) {
                    
                    // On stocke temporairement son ID unique en session
                    $session->set('temp_employe_id', $employe['id_employe']);
                    
                    // Redirection forcée vers la page de changement de mot de passe
                    return redirect()->to('/personnel/changer-mot-de-passe')
                                     ->with('info', 'Pour votre sécurité, veuillez définir un mot de passe personnel avant de continuer.');
                }

                // 🏢 CONNEXION NORMALE : Si ce n'est pas sa première connexion
                $dataSession = [
                    'id_employe'  => $employe['id_employe'],
                    'matricule'   => $employe['matricule'],
                    'nom_complet' => $employe['nom_complet'],
                    'role'        => $employe['role'],
                    'shop_id'     => $employe['shop_id'],
                    'isLoggedIn'  => true
                ];
                $session->set($dataSession);

                // Redirection vers le tableau de bord principal
                return redirect()->to('dashboard');

            } else {
                // Mauvais mot de passe
                return redirect()->back()->with('error', 'Mot de passe incorrect.');
            }
        } else {
            // Aucun employé trouvé avec cet identifiant
            return redirect()->back()->with('error', 'Identifiant ou Matricule introuvable.');
        }
    }

    // Gère la déconnexion de l'utilisateur
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
}

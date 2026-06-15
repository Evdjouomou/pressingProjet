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
        $session     = session();
        $model       = new EmployeModel();
        $identifiant = trim($this->request->getPost('identifiant') ?? '');
        $password    = $this->request->getPost('password') ?? '';

        // Cherche par matricule d'abord, puis par email
        $employe = $model->where('matricule', $identifiant)->first();

        if (!$employe) {
            $employe = $model->where('email', $identifiant)->first();
        }

        if (!$employe) {
            return redirect()->back()->with('error', 'Identifiant ou Matricule introuvable.')->withInput();
        }

        // Vérification du statut du compte d'abord (Ex: si le compte est 'Inactif' ou 'Bloqué')
        // Ajustez 'status' et la valeur 'Actif' selon les données de votre bdd
        if (isset($employe['status']) && $employe['status'] !== 'Actif') {
            return redirect()->back()->with('error', 'Votre compte est désactivé. Contactez l\'administrateur.');
        }

        // Vérification du mot de passe
        if (!password_verify($password, $employe['password'])) {
            return redirect()->back()->with('error', 'Mot de passe incorrect.');
        }

        // CAS 1 : Première connexion -> Redirection vers le changement de mot de passe
        if (isset($employe['premiere_connexion']) && (int)$employe['premiere_connexion'] == 1) {
            $session->set('temp_employe_id', $employe['id_employe']);
            return redirect()->to('/personnel/changer-mot-de-passe')
                            ->with('info', 'Veuillez définir un nouveau mot de passe.');
        }

        // CAS 2 : Connexion normale (Si ce n'est pas la première connexion)
        $session->set([
            'id_employe'  => $employe['id_employe'],
            'employe_id'  => $employe['id_employe'],
            'matricule'   => $employe['matricule'],
            'nom_complet' => $employe['nom_complet'],
            'role'        => $employe['role'],
            'shop_id'     => $employe['shop_id'],
            'isLoggedIn'  => true,
        ]);

        return redirect()->to('dashboard');
    }

    // Gère la déconnexion de l'utilisateur
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
}

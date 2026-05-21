<?php
namespace App\Controllers;
use App\Services\NotificationService;

class NotificationController extends BaseController
{
    // Liste des notifications
    public function index()
    {
        $db = \Config\Database::connect();

        $notifications = $db->table('notifications n')
            ->select('n.*, c.nomclient, d.code_commande')
            ->join('clients c',       'c.id_client = n.client_id')
            ->join('depots d',        'd.id_depot = n.depot_id', 'left')
            ->orderBy('n.created_at', 'DESC')
            ->limit(100)
            ->get()->getResultArray();

        $nonLues = $db->table('notifications')
            ->where('lu', 0)->where('canal', 'interne')->countAllResults();

        return view('pages/notifications/index', [
            'title'         => 'Notifications',
            'notifications' => $notifications,
            'nonLues'       => $nonLues,
        ]);
    }

    // API pour la cloche (polling)
    public function apiNonLues()
    {
        $db = \Config\Database::connect();
        $notifs = $db->table('notifications n')
            ->select('n.id_notification, n.type, n.sujet, n.created_at, c.nomclient')
            ->join('clients c', 'c.id_client = n.client_id')
            ->where('n.lu', 0)
            ->where('n.canal', 'interne')
            ->orderBy('n.created_at', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        $total = $db->table('notifications')
            ->where('lu', 0)->where('canal', 'interne')->countAllResults();

        return $this->response->setJSON(['total' => $total, 'items' => $notifs]);
    }

    public function marquerLu(int $id)
    {
        \Config\Database::connect()
            ->table('notifications')->where('id_notification', $id)->update(['lu' => 1]);
        return $this->response->setJSON(['success' => true]);
    }

    public function marquerToutLu()
    {
        \Config\Database::connect()
            ->table('notifications')->where('lu', 0)->update(['lu' => 1]);
        return redirect()->back()->with('success', 'Toutes les notifications marquées comme lues.');
    }

    // ── Campagnes ────────────────────────────────
    public function campagnes()
    {
        $db = \Config\Database::connect();
        return view('pages/notifications/campagnes', [
            'title'     => 'Campagnes',
            'campagnes' => $db->table('campagnes')->orderBy('created_at', 'DESC')->get()->getResultArray(),
        ]);
    }

    public function nouvelleCampagne()
    {
        return view('pages/notifications/nouvelle_campagne', ['title' => 'Nouvelle campagne']);
    }

    public function sauvegarderCampagne()
    {
        $db = \Config\Database::connect();
        $db->table('campagnes')->insert([
            'titre'             => $this->request->getPost('titre'),
            'message'           => $this->request->getPost('message'),
            'type_cible'        => $this->request->getPost('type_cible'),
            'canal'             => $this->request->getPost('canal'),
            'jours_inactivite'  => $this->request->getPost('jours_inactivite') ?: null,
            'statut'            => 'brouillon',
            'created_at'        => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('campagnes')->with('success', 'Campagne créée.');
    }

    public function lancerCampagne(int $id)
    {
        $notif  = new NotificationService();
        $result = $notif->lancerCampagne($id);

        $msg = $result['success']
            ? "Campagne envoyée à {$result['nb_envoyes']} client(s)."
            : $result['message'];

        return redirect()->to('campagnes')->with($result['success'] ? 'success' : 'error', $msg);
    }
}
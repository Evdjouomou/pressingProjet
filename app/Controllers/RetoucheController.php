<?php
namespace App\Controllers;

class RetoucheController extends BaseController
{
    private function getClients(): array
    {
        return \Config\Database::connect()
            ->table('clients')->orderBy('nomclient')->get()->getResultArray();
    }

    private function getEmployes(): array
    {
        return \Config\Database::connect()
            ->table('employes')->where('status', 'Actif')->orderBy('nom_complet')->get()->getResultArray();
    }

    // ═══════════════════════════════════════════
    // LISTE
    // ═══════════════════════════════════════════
    public function index()
    {
        $db     = \Config\Database::connect();
        $statut = $this->request->getGet('statut') ?? '';

        $q = $db->table('retouches r')
            ->select('r.*, c.nomclient, c.telephone,
                      e.nom_complet AS retoucheur,
                      d.code_commande')
            ->join('clients c',  'c.id_client = r.client_id')
            ->join('employes e', 'e.id_employe = r.employe_id', 'left')
            ->join('depots d',   'd.id_depot = r.depot_id',     'left')
            ->orderBy('r.created_at', 'DESC');

        if ($statut) $q->where('r.statut', $statut);

        $retouches = $q->get()->getResultArray();

        $stats = [
            'en_attente' => count(array_filter($retouches, fn($r) => $r['statut'] === 'en_attente')),
            'en_cours'   => count(array_filter($retouches, fn($r) => $r['statut'] === 'en_cours')),
            'fait'       => count(array_filter($retouches, fn($r) => $r['statut'] === 'fait')),
        ];

        return view('pages/retouches/index', [
            'title'     => 'Retouches',
            'retouches' => $retouches,
            'stats'     => $stats,
            'statut'    => $statut,
        ]);
    }

    // ═══════════════════════════════════════════
    // NOUVEAU
    // ═══════════════════════════════════════════
    public function nouveau()
    {
        return view('pages/retouches/nouveau', [
            'title'    => 'Nouvelle retouche',
            'clients'  => $this->getClients(),
            'employes' => $this->getEmployes(),
        ]);
    }

    // ═══════════════════════════════════════════
    // CRÉER
    // ═══════════════════════════════════════════
    public function store()
    {
        $db   = \Config\Database::connect();
        $code = 'RET-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        $now  = date('Y-m-d H:i:s');

        $depotId          = $this->request->getPost('depot_id')          ?: null;
        $articleDeposeId  = $this->request->getPost('article_depose_id') ?: null;

        $db->table('retouches')->insert([
            'client_id'         => $this->request->getPost('client_id'),
            'depot_id'          => $depotId,
            'article_depose_id' => $articleDeposeId,
            'employe_id'        => $this->request->getPost('employe_id') ?: null,
            'code_retouche'     => $code,
            'type_retouche'     => $this->request->getPost('type_retouche'),
            'description'       => $this->request->getPost('description'),
            'prix'              => (float) $this->request->getPost('prix') ?: 0,
            'acompte_verse'     => (float) $this->request->getPost('acompte_verse') ?: 0,
            'delai_estime'      => $this->request->getPost('delai_estime') ?: null,
            'statut'            => 'en_attente',
            'observations'      => $this->request->getPost('observations'),
            'enregistre_par'    => employe_connecte_id(),
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        $id = $db->insertID();

        // Notification client
        $notif = new \App\Services\NotificationService();
        $client = $db->table('clients')->where('id_client', $this->request->getPost('client_id'))->get()->getRowArray();
        if ($client) {
            $notif->envoyer(
                $client['id_client'],
                'campagne',
                'Retouche enregistrée — ' . $code,
                "Bonjour {$client['nomclient']},<br><br>
                 Votre retouche <strong>{$code}</strong> a bien été enregistrée.<br>
                 Type : " . $this->request->getPost('type_retouche') . "<br>
                 Délai estimé : " . ($this->request->getPost('delai_estime') ?: 'À définir') . "<br><br>
                 Merci de votre confiance.",
                null,
                ['interne']
            );
        }

        return redirect()->to('retouches/' . $id)
            ->with('success', 'Retouche ' . $code . ' créée.');
    }

    // ═══════════════════════════════════════════
    // DÉTAIL
    // ═══════════════════════════════════════════
    public function detail(int $id)
    {
        $db       = \Config\Database::connect();
        $retouche = $db->table('retouches r')
            ->select('r.*, c.nomclient, c.telephone, c.email,
                      e.nom_complet AS retoucheur,
                      d.code_commande,
                      da.barcode_unique, da.designation_libre,
                      l.nom_libelle')
            ->join('clients c',         'c.id_client = r.client_id')
            ->join('employes e',         'e.id_employe = r.employe_id',           'left')
            ->join('depots d',           'd.id_depot = r.depot_id',               'left')
            ->join('depot_articles da',  'da.id_article_depose = r.article_depose_id', 'left')
            ->join('libelles l',         'l.id_libelle = da.libelle_id',          'left')
            ->where('r.id_retouche', $id)
            ->get()->getRowArray();

        if (!$retouche) return redirect()->to('retouches')->with('error', 'Retouche introuvable.');

        return view('pages/retouches/detail', [
            'title'    => 'Retouche ' . $retouche['code_retouche'],
            'retouche' => $retouche,
            'employes' => $this->getEmployes(),
        ]);
    }

    // ═══════════════════════════════════════════
    // CHANGER STATUT
    // ═══════════════════════════════════════════
    public function changerStatut(int $id)
    {
        $db      = \Config\Database::connect();
        $statut  = $this->request->getPost('statut');
        $retouche = $db->table('retouches r')
            ->select('r.*, c.nomclient, c.id_client')
            ->join('clients c', 'c.id_client = r.client_id')
            ->where('r.id_retouche', $id)->get()->getRowArray();

        if (!$retouche) return redirect()->back()->with('error', 'Retouche introuvable.');

        $db->table('retouches')->where('id_retouche', $id)->update([
            'statut'     => $statut,
            'employe_id' => $this->request->getPost('employe_id') ?: $retouche['employe_id'],
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Notifier client si retouche faite
        if ($statut === 'fait') {
            $notif = new \App\Services\NotificationService();
            $notif->envoyer(
                $retouche['id_client'],
                'commande_prete',
                '✅ Votre retouche ' . $retouche['code_retouche'] . ' est terminée !',
                "Bonjour {$retouche['nomclient']},<br><br>
                 Votre retouche <strong>{$retouche['code_retouche']}</strong> est prête.<br>
                 Vous pouvez passer la récupérer dès maintenant.",
                null,
                ['interne', 'email']
            );
        }

        return redirect()->to('retouches/' . $id)->with('success', 'Statut mis à jour.');
    }

    // ═══════════════════════════════════════════
    // MODIFIER
    // ═══════════════════════════════════════════
    public function update(int $id)
    {
        \Config\Database::connect()->table('retouches')->where('id_retouche', $id)->update([
            'type_retouche' => $this->request->getPost('type_retouche'),
            'description'   => $this->request->getPost('description'),
            'prix'          => (float) $this->request->getPost('prix'),
            'acompte_verse' => (float) $this->request->getPost('acompte_verse'),
            'delai_estime'  => $this->request->getPost('delai_estime') ?: null,
            'employe_id'    => $this->request->getPost('employe_id') ?: null,
            'observations'  => $this->request->getPost('observations'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('retouches/' . $id)->with('success', 'Retouche mise à jour.');
    }

    // ═══════════════════════════════════════════
    // SUPPRIMER
    // ═══════════════════════════════════════════
    public function delete(int $id)
    {
        \Config\Database::connect()->table('retouches')->where('id_retouche', $id)->delete();
        return redirect()->to('retouches')->with('success', 'Retouche supprimée.');
    }

    // ═══════════════════════════════════════════
    // API — charger infos dépôt
    // ═══════════════════════════════════════════
    public function apiDepot(int $id)
    {
        $db    = \Config\Database::connect();
        $depot = $db->table('depots d')
            ->select('d.id_depot, d.code_commande, c.nomclient, c.id_client')
            ->join('clients c', 'c.id_client = d.client_id')
            ->where('d.id_depot', $id)
            ->get()->getRowArray();

        if (!$depot) return $this->response->setJSON(['success' => false]);

        $articles = $db->table('depot_articles da')
            ->select('da.id_article_depose, da.barcode_unique, da.designation_libre, l.nom_libelle')
            ->join('libelles l', 'l.id_libelle = da.libelle_id')
            ->where('da.depot_id', $id)
            ->get()->getResultArray();

        return $this->response->setJSON(['success' => true, 'depot' => $depot, 'articles' => $articles]);
    }
}
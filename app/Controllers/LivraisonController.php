<?php
namespace App\Controllers;

class LivraisonController extends BaseController
{
    private function getLivreurs(): array
    {
        return \Config\Database::connect()
            ->table('employes e')
            ->select('e.*')
            ->where('e.status', 'Actif')
            ->orderBy('e.nom_complet')
            ->get()->getResultArray();
    }

    // ═══════════════════════════════════════════
    // LISTE DES LIVRAISONS
    // ═══════════════════════════════════════════
    public function index()
    {
        $db     = \Config\Database::connect();
        $statut = $this->request->getGet('statut') ?? '';

        $q = $db->table('livraisons l')
            ->select('l.*, c.nomclient, c.telephone,
                    d.code_commande, d.total_ttc,
                    lv.nom_complet AS livreur_nom,
                    lv.telephone   AS livreur_tel,
                    ep.nom_complet AS enregistre_par_nom,
                    COUNT(da.id_article_depose) AS nb_articles')
            ->join('clients c',        'c.id_client = l.client_id')
            ->join('depots d',         'd.id_depot = l.depot_id')
            ->join('livreurs lv',      'lv.id_livreur = l.livreur_id',     'left')
            ->join('employes ep',      'ep.id_employe = l.enregistre_par', 'left')
            ->join('depot_articles da','da.depot_id = l.depot_id',         'left')
            ->groupBy('l.id_livraison')
            ->orderBy('l.date_livraison', 'ASC')
            ->orderBy('l.heure_livraison', 'ASC');

        if ($statut) $q->where('l.statut', $statut);

        $livraisons = $q->get()->getResultArray();

        // Livreurs actifs pour le modal assignation
        $livreurs = $db->table('livreurs')
            ->where('statut', 'actif')
            ->orderBy('nom_complet')
            ->get()->getResultArray();

        $stats = [
            'en_attente' => count(array_filter($livraisons, fn($l) => $l['statut'] === 'en_attente')),
            'assignee'   => count(array_filter($livraisons, fn($l) => $l['statut'] === 'assignee')),
            'en_cours'   => count(array_filter($livraisons, fn($l) => $l['statut'] === 'en_cours')),
            'livree'     => count(array_filter($livraisons, fn($l) => $l['statut'] === 'livree')),
        ];

        return view('pages/livraisons/index', [
            'title'      => 'Livraisons',
            'livraisons' => $livraisons,
            'livreurs'   => $livreurs,
            'stats'      => $stats,
            'statut'     => $statut,
        ]);
    }

    // ═══════════════════════════════════════════
    // DÉTAIL
    // ═══════════════════════════════════════════
    public function detail(int $id)
    {
        $db  = \Config\Database::connect();
        $liv = $db->table('livraisons l')
            ->select('l.*, c.nomclient, c.telephone, c.email, c.adresse,
                    d.code_commande, d.total_ttc, d.acompte_verse,
                    d.date_livraison_prevue,
                    lv.nom_complet  AS livreur_nom,
                    lv.telephone    AS livreur_tel,
                    lv.vehicule     AS livreur_vehicule,
                    lv.zone_livraison AS livreur_zone,
                    ep.nom_complet  AS enregistre_par_nom')
            ->join('clients c',  'c.id_client = l.client_id')
            ->join('depots d',   'd.id_depot = l.depot_id')
            ->join('livreurs lv','lv.id_livreur = l.livreur_id',     'left')
            ->join('employes ep','ep.id_employe = l.enregistre_par', 'left')
            ->where('l.id_livraison', $id)
            ->get()->getRowArray();

        if (!$liv) {
            return redirect()->to('livraison')->with('error', 'Livraison introuvable.');
        }

        $articles = $db->table('depot_articles da')
            ->select('da.*, l2.nom_libelle, dp.prix_applique,
                    dp.options_express, s.type_prestation')
            ->join('libelles l2',         'l2.id_libelle = da.libelle_id')
            ->join('depot_prestations dp','dp.article_depose_id = da.id_article_depose', 'left')
            ->join('services s',          's.id_service = dp.service_id', 'left')
            ->where('da.depot_id', $liv['depot_id'])
            ->get()->getResultArray();

        // Livreurs actifs pour le modal
        $livreurs = $db->table('livreurs')
            ->where('statut', 'actif')
            ->orderBy('nom_complet')
            ->get()->getResultArray();

        return view('pages/livraisons/detail', [
            'title'    => 'Livraison ' . $liv['code_livraison'],
            'liv'      => $liv,
            'articles' => $articles,
            'livreurs' => $livreurs,
        ]);
    }

    // ═══════════════════════════════════════════
    // ASSIGNER UN LIVREUR
    // ═══════════════════════════════════════════
    public function assigner(int $id)
    {
        $db        = \Config\Database::connect();
        $livreurId = (int) $this->request->getPost('livreur_id');
        $now       = date('Y-m-d H:i:s');

        $liv = $db->table('livraisons l')
            ->select('l.*, c.nomclient, c.id_client, c.telephone, d.code_commande')
            ->join('clients c', 'c.id_client = l.client_id')
            ->join('depots d',  'd.id_depot = l.depot_id')
            ->where('l.id_livraison', $id)
            ->get()->getRowArray();

        if (!$liv) {
            return redirect()->back()->with('error', 'Livraison introuvable.');
        }

        // Récupérer le livreur (table livreurs, pas employes)
        $livreur = $db->table('livreurs')
            ->where('id_livreur', $livreurId)
            ->where('statut', 'actif')
            ->get()->getRowArray();

        if (!$livreur) {
            return redirect()->back()
                ->with('error', 'Livreur introuvable ou inactif.');
        }

        $db->table('livraisons')->where('id_livraison', $id)->update([
            'livreur_id' => $livreurId,
            'statut'     => 'assignee',
            'updated_at' => $now,
        ]);

        // Notifier le client
        try {
            $notif = new \App\Services\NotificationService();
            $notif->envoyer(
                $liv['id_client'],
                'campagne',
                '🚴 Votre livreur est assigné — ' . $liv['code_commande'],
                "Bonjour {$liv['nomclient']},<br><br>
                Un livreur a été assigné à votre commande
                <strong>{$liv['code_commande']}</strong>.<br>
                <strong>Livreur :</strong> {$livreur['nom_complet']}<br>
                <strong>Contact :</strong> {$livreur['telephone']}<br><br>
                Il vous contactera avant le passage.<br><br>
                <strong>Pressing Pro</strong>",
                $liv['depot_id'],
                ['interne', 'sms']
            );
        } catch (\Exception $e) {}

        return redirect()->to('livraison/' . $id)
            ->with('success', 'Livreur assigné : ' . $livreur['nom_complet']);
    }

    // ═══════════════════════════════════════════
    // CHANGER STATUT
    // ═══════════════════════════════════════════
    public function changerStatut(int $id)
    {
        $db     = \Config\Database::connect();
        $statut = $this->request->getPost('statut');
        $now    = date('Y-m-d H:i:s');

        $data = [
            'statut'         => $statut,
            'note_livreur'   => $this->request->getPost('note_livreur'),
            'updated_at'     => $now,
        ];

        if ($statut === 'livree') {
            $data['date_livree'] = $now;

            // Mettre le dépôt en "livre"
            $liv = $db->table('livraisons')->where('id_livraison', $id)->get()->getRowArray();
            if ($liv) {
                $db->table('depots')->where('id_depot', $liv['depot_id'])->update([
                    'statut_global' => 'livre',
                    'updated_at'    => $now,
                ]);

                // Notifier le client
                $depot = $db->table('depots d')
                    ->select('d.*, c.nomclient, c.id_client')
                    ->join('clients c', 'c.id_client = d.client_id')
                    ->where('d.id_depot', $liv['depot_id'])
                    ->get()->getRowArray();

                if ($depot) {
                    $notif = new \App\Services\NotificationService();
                    $notif->retraitConfirme($liv['depot_id']);
                }
            }
        }

        $db->table('livraisons')->where('id_livraison', $id)->update($data);

        return redirect()->to('livraison/' . $id)
            ->with('success', 'Statut mis à jour : ' . $statut . '.');
    }

    // ═══════════════════════════════════════════
    // FICHE LIVREUR (imprimable)
    // ═══════════════════════════════════════════
    public function fiche(int $id)
    {
        $db  = \Config\Database::connect();
        $liv = $db->table('livraisons l')
            ->select('l.*, c.nomclient, c.telephone, c.email, c.adresse,
                    d.code_commande, d.total_ttc, d.acompte_verse,
                    lv.nom_complet   AS livreur_nom,
                    lv.telephone     AS livreur_tel,
                    lv.vehicule      AS livreur_vehicule,
                    lv.numero_plaque AS livreur_plaque,
                    s.nom_shop, s.adresse AS adresse_boutique')
            ->join('clients c',  'c.id_client = l.client_id')
            ->join('depots d',   'd.id_depot = l.depot_id')
            ->join('livreurs lv','lv.id_livreur = l.livreur_id', 'left')
            ->join('employes ep','ep.id_employe = l.enregistre_par', 'left')
            ->join('shops s',    's.id_shop = ep.shop_id',         'left')
            ->where('l.id_livraison', $id)
            ->get()->getRowArray();

        $articles = $db->table('depot_articles da')
            ->select('da.barcode_unique, da.designation_libre,
                    l2.nom_libelle, dp.prix_applique,
                    dp.options_express, s2.type_prestation')
            ->join('libelles l2',         'l2.id_libelle = da.libelle_id')
            ->join('depot_prestations dp','dp.article_depose_id = da.id_article_depose', 'left')
            ->join('services s2',         's2.id_service = dp.service_id', 'left')
            ->where('da.depot_id', $liv['depot_id'])
            ->get()->getResultArray();

        return view('pages/livraisons/fiche', [
            'liv'      => $liv,
            'articles' => $articles,
        ]);
    }
}
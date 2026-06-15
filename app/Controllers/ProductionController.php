<?php
namespace App\Controllers;

class ProductionController extends BaseController
{
    // ═══════════════════════════════════════════
    // HELPERS PRIVÉS
    // ═══════════════════════════════════════════
    private function getEtapes(): array
    {
        return \Config\Database::connect()
            ->table('etapes_production')
            ->where('est_actif', 1)
            ->orderBy('ordre', 'ASC')
            ->get()->getResultArray();
    }

    private function getArticleByBarcode(string $barcode): ?array
    {
        $db = \Config\Database::connect();
        return $db->table('depot_articles da')
            ->select('
                da.*,
                l.nom_libelle, l.categorie,
                ep.libelle AS etape_libelle,
                ep.ordre   AS etape_ordre,
                ep.couleur AS etape_couleur,
                dp.prix_applique, dp.options_express,
                s.type_prestation,
                d.code_commande, d.date_livraison_prevue, d.client_id,
                c.nomclient, c.telephone
            ')
            ->join('libelles l',          'l.id_libelle = da.libelle_id')
            ->join('etapes_production ep', 'ep.id_etape = da.etape_courante_id', 'left')
            ->join('depot_prestations dp', 'dp.article_depose_id = da.id_article_depose', 'left')
            ->join('services s',           's.id_service = dp.service_id', 'left')
            ->join('depots d',             'd.id_depot = da.depot_id')
            ->join('clients c',            'c.id_client = d.client_id')
            ->where('da.barcode_unique', $barcode)
            ->get()->getRowArray();
    }

    // ═══════════════════════════════════════════
    // KANBAN GÉRANT
    // ═══════════════════════════════════════════
    public function kanban()
    {
        return view('pages/production/kanban', [
            'title'  => 'Tableau de bord Production',
            'etapes' => $this->getEtapes(),
        ]);
    }

    // API AJAX — données Kanban
    public function apiKanban()
    {
        $db   = \Config\Database::connect();
        $vue  = $this->request->getGet('vue') ?? 'depots'; // 'depots' ou 'articles'

        $etapes = $this->getEtapes();
        $result = [];

        foreach ($etapes as $etape) {
            if ($vue === 'articles') {
                $items = $db->table('depot_articles da')
                    ->select('
                        da.id_article_depose, da.barcode_unique,
                        da.designation_libre, da.observations,
                        l.nom_libelle,
                        dp.options_express, dp.prix_applique,
                        s.type_prestation,
                        d.code_commande, d.date_livraison_prevue,
                        c.nomclient, e_op.nom_complet AS dernier_operateur,
                        TIMESTAMPDIFF(MINUTE, aw.date_entree, NOW()) AS minutes_en_cours
                    ')
                    ->join('libelles l',           'l.id_libelle = da.libelle_id')
                    ->join('depot_prestations dp',  'dp.article_depose_id = da.id_article_depose', 'left')
                    ->join('services s',            's.id_service = dp.service_id', 'left')
                    ->join('depots d',              'd.id_depot = da.depot_id')
                    ->join('clients c',             'c.id_client = d.client_id')
                    ->join('article_workflow aw',   'aw.article_depose_id = da.id_article_depose
                                                     AND aw.date_sortie IS NULL', 'left')
                    ->join('employes e_op',       'e_op.id_employe = aw.employe_id', 'left')
                    ->where('da.etape_courante_id', $etape['id_etape'])
                    ->orderBy('dp.options_express', 'DESC')
                    ->orderBy('d.date_livraison_prevue', 'ASC')
                    ->get()->getResultArray();
            } else {
                // Vue dépôts : un dépôt est dans cette colonne si son article le moins avancé est à cette étape
                $items = $db->query("
                    SELECT
                        d.id_depot, d.code_commande, d.date_livraison_prevue,
                        d.statut_global, d.total_ttc,
                        c.nomclient, c.telephone,
                        COUNT(da.id_article_depose)              AS nb_articles,
                        MIN(ep.ordre)                            AS min_ordre,
                        SUM(dp.options_express)                      AS nb_express,
                        TIMESTAMPDIFF(MINUTE, MIN(aw.date_entree), NOW()) AS minutes_attente
                    FROM depots d
                    JOIN clients c           ON c.id_client = d.client_id
                    JOIN depot_articles da   ON da.depot_id = d.id_depot
                    JOIN etapes_production ep ON ep.id_etape = da.etape_courante_id
                    LEFT JOIN depot_prestations dp ON dp.article_depose_id = da.id_article_depose
                    LEFT JOIN article_workflow aw  ON aw.article_depose_id = da.id_article_depose
                                                   AND aw.date_sortie IS NULL
                    WHERE d.statut_global NOT IN ('livre', 'annule')
                    GROUP BY d.id_depot
                    HAVING MIN(da.etape_courante_id) = ?
                    ORDER BY SUM(dp.options_express) DESC, d.date_livraison_prevue ASC
                ", [$etape['id_etape']])->getResultArray();
            }

            // Calcul alerte dépassement délai
            foreach ($items as &$item) {
                $item['en_retard'] = false;
                if ($etape['duree_prevue_h'] > 0 && isset($item['minutes_en_cours'])) {
                    $item['en_retard'] = $item['minutes_en_cours'] > ($etape['duree_prevue_h'] * 60);
                }
            }

            $result[] = [
                'etape' => $etape,
                'items' => $items,
                'count' => count($items),
            ];
        }

        return $this->response->setJSON($result);
    }

    // API AJAX — statistiques
    public function apiStats()
    {
        $db = \Config\Database::connect();

        $stats = $db->query("
            SELECT
                COUNT(DISTINCT da.id_article_depose)                                  AS total_en_cours,
                COUNT(DISTINCT CASE WHEN dp.options_express = 1
                      THEN da.id_article_depose END)                                   AS total_express,
                COUNT(DISTINCT CASE WHEN da.etape_courante_id = 7
                      THEN da.id_article_depose END)                                   AS prets,
                AVG(TIMESTAMPDIFF(MINUTE, aw.date_entree, aw.date_sortie))            AS cycle_moyen_min,
                COUNT(DISTINCT CASE WHEN d.date_livraison_prevue < NOW()
                      AND da.etape_courante_id < 7
                      THEN da.id_article_depose END)                                   AS en_retard
            FROM depot_articles da
            JOIN depots d                ON d.id_depot = da.depot_id
            LEFT JOIN depot_prestations dp ON dp.article_depose_id = da.id_article_depose
            LEFT JOIN article_workflow aw  ON aw.article_depose_id = da.id_article_depose
            WHERE d.statut_global NOT IN ('livre', 'annule')
        ")->getRowArray();

        return $this->response->setJSON($stats);
    }

    // ═══════════════════════════════════════════
    // INTERFACE SCAN EMPLOYÉ
    // ═══════════════════════════════════════════
    public function scan()
    {
        return view('pages/production/scan', [
            'title'  => 'Scanner un article',
            'etapes' => $this->getEtapes(),
        ]);
    }

    // POST — avancer un article à l'étape suivante
    public function avancer()
{
    $db  = \Config\Database::connect();
    $now = date('Y-m-d H:i:s');

    $barcode = trim(
        $this->request->getPost('barcode') 
        ?? $this->request->getGet('barcode') 
        ?? ''
    );
    $preview = $this->request->getPost('preview') === '1';

    if (!$barcode) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Code-barres manquant.'
        ]);
    }

    // ── Récupérer l'article ───────────────────────────────────
    $article = $db->query("
        SELECT da.*,
               ep.libelle  AS etape_libelle,
               ep.ordre    AS etape_ordre,
               ep.couleur  AS etape_couleur,
               ep.id_etape AS etape_id,
               d.code_commande,
               d.id_depot,
               d.date_livraison_prevue,
               c.nomclient,
               dp.options_express AS est_express,
               s.type_prestation
        FROM depot_articles da
        LEFT JOIN etapes_production ep ON ep.id_etape = da.etape_courante_id
        JOIN depots d   ON d.id_depot   = da.depot_id
        JOIN clients c  ON c.id_client  = d.client_id
        LEFT JOIN depot_prestations dp ON dp.article_depose_id = da.id_article_depose
        LEFT JOIN services s           ON s.id_service = dp.service_id
        WHERE da.barcode_unique = ?
        LIMIT 1
    ", [$barcode])->getRowArray();

    if (!$article) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Article introuvable : ' . $barcode
        ]);
    }

    // ── Étape "Prêt à retirer" (ordre 7) ─────────────────────
    $etapePret = $db->query(
        "SELECT * FROM etapes_production 
         WHERE ordre = 7 AND est_actif = 1 
         LIMIT 1"
    )->getRowArray();

    // ── Étape suivante ────────────────────────────────────────
    $etapeSuivante = $db->query(
        "SELECT * FROM etapes_production 
         WHERE ordre > ? AND est_actif = 1 
         ORDER BY ordre ASC LIMIT 1",
        [(int)$article['etape_ordre']]
    )->getRowArray();

    // ── Bloquer si déjà à "Prêt à retirer" ou au-delà ────────
    $ordrePret = $etapePret ? (int)$etapePret['ordre'] : 7;
    if ((int)$article['etape_ordre'] >= $ordrePret) {
        return $this->response->setJSON([
            'success'  => false,
            'bloque'   => true,
            'article'  => $article,
            'message'  => '⛔ Cet article est déjà <strong>Prêt à retirer</strong>. '
                        . 'Le retrait est géré par la caisse.',
        ]);
    }

    // ── Bloquer si l'étape suivante dépasse "Prêt à retirer" ──
    if ($etapeSuivante && (int)$etapeSuivante['ordre'] > $ordrePret) {
        $etapeSuivante = $etapePret; // on plafonne à "Prêt à retirer"
    }

    // ── MODE PREVIEW : retourner les infos sans avancer ───────
    if ($preview) {
        return $this->response->setJSON([
            'success'       => true,
            'article'       => $article,
            'etape_suivante'=> $etapeSuivante,
        ]);
    }

    // ── Vérifier qu'il y a une étape suivante ─────────────────
    if (!$etapeSuivante) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Aucune étape suivante disponible.'
        ]);
    }

    // ── Avancer l'article ─────────────────────────────────────
    $db->transStart();

    // Clôturer le workflow actuel
    $db->query("
        UPDATE article_workflow
        SET date_sortie      = ?,
            duree_reelle_min = TIMESTAMPDIFF(MINUTE, date_entree, ?)
        WHERE article_depose_id = ?
          AND date_sortie IS NULL
    ", [$now, $now, $article['id_article_depose']]);

    // Créer le workflow pour la nouvelle étape
    $db->table('article_workflow')->insert([
        'article_depose_id' => $article['id_article_depose'],
        'etape_id'          => $etapeSuivante['id_etape'],
        'employe_id'        => employe_connecte_id(),
        'date_entree'       => $now,
    ]);

    // Mettre à jour l'étape courante
    $db->table('depot_articles')
        ->where('id_article_depose', $article['id_article_depose'])
        ->update(['etape_courante_id' => $etapeSuivante['id_etape']]);

    // Recalculer le statut du dépôt
    $this->recalculerStatutDepot($article['id_depot'], $db, $etapePret);

    $db->transComplete();

    if ($db->transStatus() === false) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour.'
        ]);
    }

    // ── Notification si dépôt vient de passer "Prêt" ──────────
    $depotMaj = $db->table('depots')
        ->where('id_depot', $article['id_depot'])
        ->get()->getRowArray();

    if ($depotMaj
        && $depotMaj['statut_global'] === 'pret'
        && empty($depotMaj['notif_pret_envoyee'])) {

        $notif = new \App\Services\NotificationService();
        $notif->commandePrete($article['id_depot']);

        $db->table('depots')
            ->where('id_depot', $article['id_depot'])
            ->update(['notif_pret_envoyee' => 1]);
    }

    return $this->response->setJSON([
        'success'        => true,
        'message'        => '✅ ' . $article['nomclient']
                          . ' — ' . $article['code_commande']
                          . ' : avancé vers <strong>'
                          . $etapeSuivante['libelle'] . '</strong>',
        'etape_suivante' => $etapeSuivante['libelle'],
        'est_pret'       => ((int)$etapeSuivante['ordre'] === $ordrePret),
    ]);
}

// ─────────────────────────────────────────────────────────────
private function recalculerStatutDepot(int $idDepot, $db, ?array $etapePret): void
{
    $result = $db->query("
        SELECT MIN(ep.ordre) AS ordre_min
        FROM depot_articles da
        LEFT JOIN etapes_production ep ON ep.id_etape = da.etape_courante_id
        WHERE da.depot_id = ?
    ", [$idDepot])->getRowArray();

    if (!$result || $result['ordre_min'] === null) return;

    $ordreMin  = (int) $result['ordre_min'];
    $ordrePret = $etapePret ? (int)$etapePret['ordre'] : 7;

    if ($ordreMin <= 1)          $statut = 'depot';
    elseif ($ordreMin < $ordrePret) $statut = 'en_cours';
    else                            $statut = 'pret';

    $db->query("
        UPDATE depots 
        SET statut_global = ?, updated_at = ?
        WHERE id_depot = ? AND statut_global NOT IN ('livre', 'annule')
    ", [$statut, date('Y-m-d H:i:s'), $idDepot]);
}

    // Détail d'un article (pour la modal scan)
    public function articleDetail(int $id)
    {
        $db      = \Config\Database::connect();
        $article = $db->table('depot_articles da')
            ->select('da.*, l.nom_libelle, ep.libelle AS etape_libelle,
                      ep.couleur, ep.ordre AS etape_ordre,
                      dp.options_express, s.type_prestation,
                      d.code_commande, d.date_livraison_prevue,
                      c.nomclient')
            ->join('libelles l',           'l.id_libelle = da.libelle_id')
            ->join('etapes_production ep',  'ep.id_etape = da.etape_courante_id', 'left')
            ->join('depot_prestations dp',  'dp.article_depose_id = da.id_article_depose', 'left')
            ->join('services s',            's.id_service = dp.service_id', 'left')
            ->join('depots d',              'd.id_depot = da.depot_id')
            ->join('clients c',             'c.id_client = d.client_id')
            ->where('da.id_article_depose', $id)
            ->get()->getRowArray();

        $historique = $db->table('article_workflow aw')
            ->select('aw.*, ep.libelle, ep.couleur, ep.icone')
            ->join('etapes_production ep', 'ep.id_etape = aw.etape_id')
            ->where('aw.article_depose_id', $id)
            ->orderBy('aw.date_entree', 'ASC')
            ->get()->getResultArray();

        return $this->response->setJSON([
            'article'    => $article,
            'historique' => $historique,
        ]);
    }

    // ═══════════════════════════════════════════
    // ALERTES
    // ═══════════════════════════════════════════
    public function alertes()
    {
        $db = \Config\Database::connect();

        $alertes = $db->query("
            SELECT
                da.id_article_depose, da.barcode_unique, da.designation_libre,
                l.nom_libelle,
                ep.libelle AS etape_libelle, ep.duree_prevue_h, ep.couleur,
                d.code_commande, d.date_livraison_prevue,
                c.nomclient, c.telephone,
                aw.date_entree,
                TIMESTAMPDIFF(MINUTE, aw.date_entree, NOW()) AS minutes_en_etape,
                dp.options_express
            FROM depot_articles da
            JOIN etapes_production ep   ON ep.id_etape = da.etape_courante_id
            JOIN article_workflow aw    ON aw.article_depose_id = da.id_article_depose
                                       AND aw.date_sortie IS NULL
            JOIN libelles l             ON l.id_libelle = da.libelle_id
            JOIN depots d               ON d.id_depot = da.depot_id
            JOIN clients c              ON c.id_client = d.client_id
            LEFT JOIN depot_prestations dp ON dp.article_depose_id = da.id_article_depose
            WHERE ep.duree_prevue_h > 0
              AND TIMESTAMPDIFF(MINUTE, aw.date_entree, NOW()) > (ep.duree_prevue_h * 60)
              AND d.statut_global NOT IN ('livre', 'annule')
            ORDER BY minutes_en_etape DESC
        ")->getResultArray();

        return view('pages/production/alertes', [
            'title'   => 'Alertes production',
            'alertes' => $alertes,
        ]);
    }
}
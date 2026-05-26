<?php
namespace App\Controllers;

class DashboardController extends BaseController
{
    // ═══════════════════════════════════════════
    // PAGE PRINCIPALE
    // ═══════════════════════════════════════════
    public function index()
    {
        return view('pages/dashboard/index', [
            'title' => 'Tableau de bord',
        ]);
    }

    // ═══════════════════════════════════════════
    // API KPIs — données temps réel
    // ═══════════════════════════════════════════
    public function apiKpis()
    {
        $db = \Config\Database::connect();

        // ── Dates de référence ───────────────────
        $aujourd = date('Y-m-d');
        $debutSemaine  = date('Y-m-d', strtotime('monday this week'));
        $debutMois     = date('Y-m-01');
        $moisPrec      = date('Y-m-01', strtotime('-1 month'));
        $finMoisPrec   = date('Y-m-t', strtotime('-1 month'));
        $semainePrec   = date('Y-m-d', strtotime('monday last week'));
        $finSemainePrec= date('Y-m-d', strtotime('sunday last week'));
        $hierDebut     = date('Y-m-d', strtotime('-1 day'));

        // ── CA Jour ──────────────────────────────
        $caJour = $this->sumCA($db, $aujourd, $aujourd);
        $caHier = $this->sumCA($db, $hierDebut, $hierDebut);

        // ── CA Semaine ───────────────────────────
        $caSemaine     = $this->sumCA($db, $debutSemaine, $aujourd);
        $caSemainePrec = $this->sumCA($db, $semainePrec, $finSemainePrec);

        // ── CA Mois ──────────────────────────────
        $caMois     = $this->sumCA($db, $debutMois, $aujourd);
        $caMoisPrec = $this->sumCA($db, $moisPrec, $finMoisPrec);

        // ── Commandes par statut ─────────────────
        $commandesStatut = $db->query("
            SELECT statut_global, COUNT(*) AS nb
            FROM depots
            WHERE statut_global NOT IN ('annule')
            GROUP BY statut_global
        ")->getResultArray();

        // ── Articles en retard ───────────────────
        $enRetard = $db->query("
            SELECT COUNT(*) AS nb FROM depots
            WHERE date_livraison_prevue < CURDATE()
              AND statut_global NOT IN ('livre','annule')
        ")->getRowArray()['nb'] ?? 0;

        // ── Articles en production ───────────────
        $enProduction = $db->query("
            SELECT COUNT(*) AS nb FROM depot_articles da
            JOIN depots d ON d.id_depot = da.depot_id
            WHERE da.etape_courante_id BETWEEN 2 AND 6
              AND d.statut_global NOT IN ('livre','annule')
        ")->getRowArray()['nb'] ?? 0;

        // ── Prêts à retirer ──────────────────────
        $prets = $db->query("
            SELECT COUNT(*) AS nb FROM depots
            WHERE statut_global = 'pret'
        ")->getRowArray()['nb'] ?? 0;

        // ── Nb clients actifs (30j) ──────────────
        $clientsActifs = $db->query("
            SELECT COUNT(DISTINCT client_id) AS nb FROM depots
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ")->getRowArray()['nb'] ?? 0;

        // ── Taux retour client (30j) ─────────────
        $totalClients = $db->table('clients')->countAllResults();
        $tauxRetour   = $totalClients > 0
            ? round(($clientsActifs / $totalClients) * 100, 1)
            : 0;

        // ── Top 5 prestations (CA) ───────────────
        $topPrestations = $db->query("
            SELECT s.type_prestation,
                   COUNT(dp.article_depose_id) AS nb,
                   SUM(dp.prix_applique) AS ca_total
            FROM depot_prestations dp
            JOIN services s ON s.id_service = dp.service_id
            JOIN depot_articles da ON da.id_article_depose = dp.article_depose_id
            JOIN depots d ON d.id_depot = da.depot_id
            WHERE d.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY s.id_service
            ORDER BY ca_total DESC
            LIMIT 5
        ")->getResultArray();

        // ── Stock en alerte ──────────────────────
        $stockAlertes = $db->query("
            SELECT COUNT(*) AS nb FROM produits_annexes
            WHERE stock <= stock_alerte AND actif = 1
        ")->getRowArray()['nb'] ?? 0;

        // ── Incidents ouverts ────────────────────
        $incidentsOuverts = $db->table('incidents')
            ->whereIn('statut', ['ouvert','en_traitement'])
            ->countAllResults();

        // ── Nb dépôts aujourd'hui ────────────────
        $depotsJour = $db->table('depots')
            ->where('DATE(created_at)', $aujourd)
            ->countAllResults();

        return $this->response->setJSON([
            'ca' => [
                'jour'         => $caJour,
                'hier'         => $caHier,
                'evolution_jour'=> $caHier > 0 ? round((($caJour - $caHier) / $caHier) * 100, 1) : null,
                'semaine'      => $caSemaine,
                'semaine_prec' => $caSemainePrec,
                'evolution_semaine' => $caSemainePrec > 0
                    ? round((($caSemaine - $caSemainePrec) / $caSemainePrec) * 100, 1) : null,
                'mois'         => $caMois,
                'mois_prec'    => $caMoisPrec,
                'evolution_mois' => $caMoisPrec > 0
                    ? round((($caMois - $caMoisPrec) / $caMoisPrec) * 100, 1) : null,
            ],
            'production' => [
                'en_cours'    => $enProduction,
                'prets'       => $prets,
                'en_retard'   => $enRetard,
                'depots_jour' => $depotsJour,
            ],
            'clients' => [
                'actifs_30j'  => $clientsActifs,
                'taux_retour' => $tauxRetour,
            ],
            'alertes' => [
                'stock'     => $stockAlertes,
                'incidents' => $incidentsOuverts,
            ],
            'commandes_statut' => $commandesStatut,
            'top_prestations'  => $topPrestations,
        ]);
    }

    // ═══════════════════════════════════════════
    // API Graphiques
    // ═══════════════════════════════════════════
    public function apiGraphiques()
    {
        $db     = \Config\Database::connect();
        $type   = $this->request->getGet('type') ?? 'ca_30j';

        switch ($type) {
            // CA des 30 derniers jours
            case 'ca_30j':
                $rows = $db->query("
                    SELECT DATE(created_at) AS jour,
                           SUM(total_ttc) AS ca
                    FROM depots
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY jour ASC
                ")->getResultArray();
                return $this->response->setJSON([
                    'labels' => array_map(fn($r) => date('d/m', strtotime($r['jour'])), $rows),
                    'data'   => array_map(fn($r) => (float) $r['ca'], $rows),
                ]);

            // CA par mode de paiement (mois courant)
            case 'modes_paiement':
                $rows = $db->query("
                    SELECT mode_paiement, SUM(montant) AS total
                    FROM transactions
                    WHERE type = 'encaissement'
                      AND statut = 'valide'
                      AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')
                    GROUP BY mode_paiement
                ")->getResultArray();
                return $this->response->setJSON([
                    'labels' => array_map(fn($r) => ucfirst(str_replace('_',' ',$r['mode_paiement'])), $rows),
                    'data'   => array_map(fn($r) => (float) $r['total'], $rows),
                ]);

            // Dépôts par jour (7 derniers jours)
            case 'depots_7j':
                $rows = $db->query("
                    SELECT DATE(created_at) AS jour, COUNT(*) AS nb
                    FROM depots
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY jour ASC
                ")->getResultArray();
                return $this->response->setJSON([
                    'labels' => array_map(fn($r) => date('d/m', strtotime($r['jour'])), $rows),
                    'data'   => array_map(fn($r) => (int) $r['nb'], $rows),
                ]);

            default:
                return $this->response->setJSON([]);
        }
    }

    // ── Helper : somme CA depuis transactions ────
    private function sumCA($db, string $debut, string $fin): float
    {
        $row = $db->query("
            SELECT COALESCE(SUM(montant), 0) AS total
            FROM transactions
            WHERE type = 'encaissement'
              AND statut = 'valide'
              AND DATE(created_at) BETWEEN ? AND ?
        ", [$debut, $fin])->getRowArray();
        return (float) ($row['total'] ?? 0);
    }
}
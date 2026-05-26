<?php
namespace App\Controllers;

class RapportController extends BaseController
{
    // ── Paramètres communs ───────────────────────
    private function getParams(): array
    {
        return [
            'debut'      => $this->request->getGet('debut')      ?? date('Y-m-01'),
            'fin'        => $this->request->getGet('fin')         ?? date('Y-m-d'),
            'employe_id' => $this->request->getGet('employe_id')  ?? '',
            'client_id'  => $this->request->getGet('client_id')   ?? '',
            'prestation' => $this->request->getGet('prestation')  ?? '',
            'mode'       => $this->request->getGet('mode')        ?? '',
        ];
    }

    private function getEmployes(): array
    {
        return \Config\Database::connect()
            ->table('employes')->orderBy('nom_complet')->get()->getResultArray();
    }

    // ═══════════════════════════════════════════
    // PAGE D'ACCUEIL RAPPORTS
    // ═══════════════════════════════════════════
    public function index()
    {
        return view('pages/rapports/index', ['title' => 'Rapports & Analyses']);
    }

    // ═══════════════════════════════════════════
    // RAPPORT CHIFFRE D'AFFAIRES
    // ═══════════════════════════════════════════
    public function chiffreAffaires()
    {
        $db     = \Config\Database::connect();
        $p      = $this->getParams();

        $q = "
            SELECT
                t.id_transaction, t.created_at, t.montant,
                t.mode_paiement, t.type,
                t.montant_especes, t.montant_carte,
                t.montant_mobile, t.rendu_monnaie,
                c.nomclient, c.telephone,
                d.code_commande,
                e.nom_complet AS caissier
            FROM transactions t
            LEFT JOIN clients c  ON c.id_client = t.client_id
            LEFT JOIN depots d   ON d.id_depot = t.depot_id
            LEFT JOIN employes e ON e.id_employe = t.employe_id
            WHERE t.statut = 'valide'
              AND t.type = 'encaissement'
              AND DATE(t.created_at) BETWEEN ? AND ?
        ";
        $binds = [$p['debut'], $p['fin']];

        if ($p['employe_id']) { $q .= " AND t.employe_id = ?"; $binds[] = $p['employe_id']; }
        if ($p['mode'])       { $q .= " AND t.mode_paiement = ?"; $binds[] = $p['mode']; }
        $q .= " ORDER BY t.created_at DESC";

        $transactions = $db->query($q, $binds)->getResultArray();

        // Totaux par mode
        $totaux = [
            'total'   => array_sum(array_column($transactions, 'montant')),
            'especes' => array_sum(array_column($transactions, 'montant_especes')),
            'carte'   => array_sum(array_column($transactions, 'montant_carte')),
            'mobile'  => array_sum(array_column($transactions, 'montant_mobile')),
        ];

        return view('pages/rapports/ca', [
            'title'        => 'Rapport CA',
            'transactions' => $transactions,
            'totaux'       => $totaux,
            'params'       => $p,
            'employes'     => $this->getEmployes(),
            'rapport_type' => 'ca',
        ]);
    }

    // ═══════════════════════════════════════════
    // RAPPORT DÉPÔTS
    // ═══════════════════════════════════════════
    public function depots()
    {
        $db = \Config\Database::connect();
        $p  = $this->getParams();

        $q = "
            SELECT
                d.id_depot, d.code_commande, d.created_at,
                d.total_ttc, d.acompte_verse, d.statut_global,
                d.date_livraison_prevue,
                c.nomclient, c.telephone,
                COUNT(da.id_article_depose) AS nb_articles,
                COALESCE(SUM(t.montant), 0) AS encaisse
            FROM depots d
            JOIN clients c ON c.id_client = d.client_id
            LEFT JOIN depot_articles da ON da.depot_id = d.id_depot
            LEFT JOIN transactions t    ON t.depot_id = d.id_depot
                                       AND t.type = 'encaissement'
                                       AND t.statut = 'valide'
            WHERE DATE(d.created_at) BETWEEN ? AND ?
        ";
        $binds = [$p['debut'], $p['fin']];

        if ($p['client_id']) { $q .= " AND d.client_id = ?"; $binds[] = $p['client_id']; }
        $q .= " GROUP BY d.id_depot ORDER BY d.created_at DESC";

        $depots = $db->query($q, $binds)->getResultArray();

        $totaux = [
            'nb'       => count($depots),
            'ca'       => array_sum(array_column($depots, 'total_ttc')),
            'encaisse' => array_sum(array_column($depots, 'encaisse')),
            'reste'    => array_sum(array_map(fn($d) => max(0, $d['total_ttc'] - $d['encaisse']), $depots)),
        ];

        return view('pages/rapports/depots', [
            'title'        => 'Rapport Dépôts',
            'depots'       => $depots,
            'totaux'       => $totaux,
            'params'       => $p,
            'clients'      => \Config\Database::connect()->table('clients')->orderBy('nomclient')->get()->getResultArray(),
            'rapport_type' => 'depots',
        ]);
    }

    // ═══════════════════════════════════════════
    // RAPPORT CLIENTS
    // ═══════════════════════════════════════════
    public function clients()
    {
        $db = \Config\Database::connect();
        $p  = $this->getParams();

        $clients = $db->query("
            SELECT
                c.id_client, c.nomclient, c.telephone, c.email,
                c.typeclient, c.dateajout, c.solde_fidelite,
                COUNT(DISTINCT d.id_depot)       AS nb_depots,
                COALESCE(SUM(d.total_ttc), 0)    AS ca_total,
                MAX(d.created_at)                AS dernier_depot
            FROM clients c
            LEFT JOIN depots d ON d.client_id = c.id_client
                AND DATE(d.created_at) BETWEEN ? AND ?
            GROUP BY c.id_client
            ORDER BY ca_total DESC
        ", [$p['debut'], $p['fin']])->getResultArray();

        return view('pages/rapports/clients', [
            'title'        => 'Rapport Clients',
            'clients'      => $clients,
            'params'       => $p,
            'rapport_type' => 'clients',
        ]);
    }

    // ═══════════════════════════════════════════
    // RAPPORT PRESTATIONS
    // ═══════════════════════════════════════════
    public function prestations()
    {
        $db = \Config\Database::connect();
        $p  = $this->getParams();

        $prestations = $db->query("
            SELECT
                s.type_prestation,
                COUNT(dp.article_depose_id) AS nb_articles,
                SUM(dp.prix_applique)       AS ca_total,
                AVG(dp.prix_applique)       AS prix_moyen,
                SUM(dp.options_express)     AS nb_express
            FROM depot_prestations dp
            JOIN services s             ON s.id_service = dp.service_id
            JOIN depot_articles da      ON da.id_article_depose = dp.article_depose_id
            JOIN depots d               ON d.id_depot = da.depot_id
            WHERE DATE(d.created_at) BETWEEN ? AND ?
            GROUP BY s.id_service
            ORDER BY ca_total DESC
        ", [$p['debut'], $p['fin']])->getResultArray();

        return view('pages/rapports/prestations', [
            'title'        => 'Rapport Prestations',
            'prestations'  => $prestations,
            'params'       => $p,
            'rapport_type' => 'prestations',
        ]);
    }

    // ═══════════════════════════════════════════
    // RAPPORT EMPLOYÉS
    // ═══════════════════════════════════════════
    public function employes()
    {
        $db = \Config\Database::connect();
        $p  = $this->getParams();

        $employes = $db->query("
            SELECT
                e.id_employe, e.nom_complet, e.matricule,
                p.nom_poste,
                COUNT(DISTINCT pt.id_pointage)          AS jours_travailles,
                COALESCE(SUM(pt.duree_minutes), 0)      AS total_minutes,
                COUNT(DISTINCT tx.id_transaction)       AS nb_transactions,
                COALESCE(SUM(tx.montant), 0)            AS ca_encaisse,
                COUNT(DISTINCT aw.id_workflow)          AS articles_traites
            FROM employes e
            LEFT JOIN postes p        ON p.id_poste = e.poste_id
            LEFT JOIN pointages pt    ON pt.employe_id = e.id_employe
                                     AND DATE(pt.date_pointage) BETWEEN ? AND ?
                                     AND pt.statut = 'present'
            LEFT JOIN transactions tx ON tx.employe_id = e.id_employe
                                     AND DATE(tx.created_at) BETWEEN ? AND ?
                                     AND tx.type = 'encaissement'
                                     AND tx.statut = 'valide'
            LEFT JOIN article_workflow aw ON aw.employe_id = e.id_employe
                                         AND DATE(aw.date_entree) BETWEEN ? AND ?
            WHERE e.status = 'Actif'
            GROUP BY e.id_employe
            ORDER BY ca_encaisse DESC
        ", [$p['debut'], $p['fin'],
            $p['debut'], $p['fin'],
            $p['debut'], $p['fin']])->getResultArray();

        return view('pages/rapports/employes', [
            'title'        => 'Rapport Employés',
            'employes'     => $employes,
            'params'       => $p,
            'rapport_type' => 'employes',
        ]);
    }

    // ═══════════════════════════════════════════
    // EXPORTS
    // ═══════════════════════════════════════════
    public function exportCsv(string $type)
    {
        $data    = $this->getDonneesRapport($type);
        $headers = $this->getHeaders($type);
        $filename = 'rapport_' . $type . '_' . date('Ymd') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
        fputcsv($out, $headers, ';');
        foreach ($data as $row) {
            fputcsv($out, $this->formatLigne($type, $row), ';');
        }
        fclose($out);
        exit;
    }

    public function exportExcel(string $type)
    {
        $data     = $this->getDonneesRapport($type);
        $headers  = $this->getHeaders($type);
        $filename = 'rapport_' . $type . '_' . date('Ymd') . '.xls';

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<table border="1">';
        echo '<tr style="background:#1a1a2e;color:#fff;">';
        foreach ($headers as $h) {
            echo '<th><b>' . htmlspecialchars($h) . '</b></th>';
        }
        echo '</tr>';
        foreach ($data as $i => $row) {
            $bg = $i % 2 === 0 ? '#ffffff' : '#f5f5f5';
            echo '<tr style="background:' . $bg . ';">';
            foreach ($this->formatLigne($type, $row) as $cell) {
                echo '<td>' . htmlspecialchars((string)$cell) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table></body></html>';
        exit;
    }

    public function exportPdf(string $type)
    {
        $data    = $this->getDonneesRapport($type);
        $headers = $this->getHeaders($type);
        $p       = $this->getParams();
        $titre   = $this->getTitreRapport($type);

        $html  = '<!DOCTYPE html><html><head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<style>
            body { font-family: Arial, sans-serif; font-size: 11px; color: #222; }
            h1 { font-size: 16px; color: #1a1a2e; margin-bottom: 4px; }
            .meta { font-size: 11px; color: #666; margin-bottom: 16px; }
            table { width: 100%; border-collapse: collapse; }
            thead tr { background: #1a1a2e; color: #fff; }
            thead th { padding: 8px 10px; text-align: left; font-size: 10px; }
            tbody tr:nth-child(even) { background: #f5f5f5; }
            tbody td { padding: 6px 10px; border-bottom: 1px solid #eee; font-size: 10px; }
            tfoot tr { background: #f0f0f0; font-weight: bold; }
            tfoot td { padding: 8px 10px; }
            .footer { text-align: center; font-size: 9px; color: #999; margin-top: 20px; }
        </style></head><body>';

        $html .= '<h1>' . $titre . '</h1>';
        $html .= '<div class="meta">Période : ' . date('d/m/Y', strtotime($p['debut'])) . ' → ' . date('d/m/Y', strtotime($p['fin'])) . ' | Généré le ' . date('d/m/Y à H:i') . '</div>';
        $html .= '<table><thead><tr>';
        foreach ($headers as $h) {
            $html .= '<th>' . htmlspecialchars($h) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($this->formatLigne($type, $row) as $cell) {
                $html .= '<td>' . htmlspecialchars((string)$cell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        $html .= '<div class="footer">Pressing Pro — ' . count($data) . ' ligne(s)</div>';
        $html .= '</body></html>';

        // Utilise le navigateur pour imprimer en PDF
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        echo '<script>window.onload = function() { window.print(); }</script>';
        exit;
    }

    // ── Helpers exports ──────────────────────────
    private function getDonneesRapport(string $type): array
    {
        return match($type) {
            'ca'         => $this->chiffreAffairesData(),
            'depots'     => $this->depotsData(),
            'clients'    => $this->clientsData(),
            'prestations'=> $this->prestationsData(),
            'employes'   => $this->employesData(),
            default      => [],
        };
    }

    private function getHeaders(string $type): array
    {
        return match($type) {
            'ca'          => ['Date','Heure','Client','Bon','Mode','Espèces','Carte','Mobile','Total (FCFA)'],
            'depots'      => ['Date','N° Bon','Client','Téléphone','Articles','Total TTC','Encaissé','Reste','Statut'],
            'clients'     => ['Client','Téléphone','Email','Type','Dépôts','CA Total (FCFA)','Dernier dépôt','Points fidélité'],
            'prestations' => ['Prestation','Nb articles','CA Total (FCFA)','Prix moyen (FCFA)','Nb express'],
            'employes'    => ['Employé','Matricule','Poste','Jours travaillés','Heures totales','Transactions','CA encaissé (FCFA)','Articles traités'],
            default       => [],
        };
    }

    private function getTitreRapport(string $type): string
    {
        return match($type) {
            'ca'          => 'Rapport Chiffre d\'Affaires',
            'depots'      => 'Rapport Dépôts',
            'clients'     => 'Rapport Clients',
            'prestations' => 'Rapport Prestations',
            'employes'    => 'Rapport Employés',
            default       => 'Rapport',
        };
    }

    private function formatLigne(string $type, array $row): array
    {
        return match($type) {
            'ca' => [
                date('d/m/Y', strtotime($row['created_at'])),
                date('H:i',   strtotime($row['created_at'])),
                $row['nomclient'] ?? '—',
                $row['code_commande'] ?? '—',
                ucfirst(str_replace('_',' ',$row['mode_paiement'])),
                number_format($row['montant_especes'] ?? 0, 0, ',', ' '),
                number_format($row['montant_carte']   ?? 0, 0, ',', ' '),
                number_format($row['montant_mobile']  ?? 0, 0, ',', ' '),
                number_format($row['montant'],           0, ',', ' '),
            ],
            'depots' => [
                date('d/m/Y', strtotime($row['created_at'])),
                $row['code_commande'],
                $row['nomclient'],
                $row['telephone'],
                $row['nb_articles'],
                number_format($row['total_ttc'],  0, ',', ' '),
                number_format($row['encaisse'],   0, ',', ' '),
                number_format(max(0,$row['total_ttc']-$row['encaisse']), 0, ',', ' '),
                ucfirst(str_replace('_',' ',$row['statut_global'])),
            ],
            'clients' => [
                $row['nomclient'],
                $row['telephone'],
                $row['email'] ?? '—',
                ucfirst($row['typeclient']),
                $row['nb_depots'],
                number_format($row['ca_total'], 0, ',', ' '),
                $row['dernier_depot'] ? date('d/m/Y', strtotime($row['dernier_depot'])) : '—',
                $row['solde_fidelite'],
            ],
            'prestations' => [
                $row['type_prestation'],
                $row['nb_articles'],
                number_format($row['ca_total'],   0, ',', ' '),
                number_format($row['prix_moyen'], 0, ',', ' '),
                $row['nb_express'],
            ],
            'employes' => [
                $row['nom_complet'],
                $row['matricule'],
                $row['nom_poste'] ?? '—',
                $row['jours_travailles'],
                intdiv($row['total_minutes'],60).'h'.str_pad($row['total_minutes']%60,2,'0',STR_PAD_LEFT),
                $row['nb_transactions'],
                number_format($row['ca_encaisse'], 0, ',', ' '),
                $row['articles_traites'],
            ],
            default => array_values($row),
        };
    }

    // Alias pour les exports (réutilisent les requêtes)
    private function chiffreAffairesData(): array
    {
        $db = \Config\Database::connect();
        $p  = $this->getParams();
        return $db->query("
            SELECT t.*, c.nomclient, d.code_commande
            FROM transactions t
            LEFT JOIN clients c ON c.id_client = t.client_id
            LEFT JOIN depots d  ON d.id_depot  = t.depot_id
            WHERE t.statut = 'valide' AND t.type = 'encaissement'
              AND DATE(t.created_at) BETWEEN ? AND ?
            ORDER BY t.created_at DESC
        ", [$p['debut'], $p['fin']])->getResultArray();
    }

    private function depotsData(): array
    {
        $db = \Config\Database::connect();
        $p  = $this->getParams();
        return $db->query("
            SELECT d.*, c.nomclient, c.telephone,
                   COUNT(DISTINCT da.id_article_depose) AS nb_articles,
                   COALESCE(SUM(t.montant),0) AS encaisse
            FROM depots d
            JOIN clients c ON c.id_client = d.client_id
            LEFT JOIN depot_articles da ON da.depot_id = d.id_depot
            LEFT JOIN transactions t    ON t.depot_id = d.id_depot
                                       AND t.type='encaissement' AND t.statut='valide'
            WHERE DATE(d.created_at) BETWEEN ? AND ?
            GROUP BY d.id_depot ORDER BY d.created_at DESC
        ", [$p['debut'], $p['fin']])->getResultArray();
    }

    private function clientsData(): array
    {
        $db = \Config\Database::connect();
        $p  = $this->getParams();
        return $db->query("
            SELECT c.*, COUNT(DISTINCT d.id_depot) AS nb_depots,
                   COALESCE(SUM(d.total_ttc),0) AS ca_total,
                   MAX(d.created_at) AS dernier_depot
            FROM clients c
            LEFT JOIN depots d ON d.client_id = c.id_client
                AND DATE(d.created_at) BETWEEN ? AND ?
            GROUP BY c.id_client ORDER BY ca_total DESC
        ", [$p['debut'], $p['fin']])->getResultArray();
    }

    private function prestationsData(): array
    {
        $db = \Config\Database::connect();
        $p  = $this->getParams();
        return $db->query("
            SELECT s.type_prestation,
                   COUNT(dp.article_depose_id) AS nb_articles,
                   SUM(dp.prix_applique) AS ca_total,
                   AVG(dp.prix_applique) AS prix_moyen,
                   SUM(dp.options_express) AS nb_express
            FROM depot_prestations dp
            JOIN services s ON s.id_service = dp.service_id
            JOIN depot_articles da ON da.id_article_depose = dp.article_depose_id
            JOIN depots d ON d.id_depot = da.depot_id
            WHERE DATE(d.created_at) BETWEEN ? AND ?
            GROUP BY s.id_service ORDER BY ca_total DESC
        ", [$p['debut'], $p['fin']])->getResultArray();
    }

    private function employesData(): array
    {
        $db = \Config\Database::connect();
        $p  = $this->getParams();
        return $db->query("
            SELECT e.*, p.nom_poste,
                   COUNT(DISTINCT pt.id_pointage) AS jours_travailles,
                   COALESCE(SUM(pt.duree_minutes),0) AS total_minutes,
                   COUNT(DISTINCT tx.id_transaction) AS nb_transactions,
                   COALESCE(SUM(tx.montant),0) AS ca_encaisse,
                   COUNT(DISTINCT aw.id_workflow) AS articles_traites
            FROM employes e
            LEFT JOIN postes p ON p.id_poste = e.poste_id
            LEFT JOIN pointages pt ON pt.employe_id = e.id_employe
                AND DATE(pt.date_pointage) BETWEEN ? AND ? AND pt.statut='present'
            LEFT JOIN transactions tx ON tx.employe_id = e.id_employe
                AND DATE(tx.created_at) BETWEEN ? AND ?
                AND tx.type='encaissement' AND tx.statut='valide'
            LEFT JOIN article_workflow aw ON aw.employe_id = e.id_employe
                AND DATE(aw.date_entree) BETWEEN ? AND ?
            WHERE e.status = 'Actif'
            GROUP BY e.id_employe ORDER BY ca_encaisse DESC
        ", [$p['debut'],$p['fin'],$p['debut'],$p['fin'],$p['debut'],$p['fin']])->getResultArray();
    }
}
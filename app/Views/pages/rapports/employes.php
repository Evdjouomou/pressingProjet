<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <a href="<?= base_url('rapports') ?>" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i>Rapports
            </a>
            <h4 class="fw-bold mb-0">Rapport Employés</h4>
            <small class="text-muted">
                Du <?= date('d/m/Y', strtotime($params['debut'])) ?>
                au <?= date('d/m/Y', strtotime($params['fin'])) ?>
                — <?= count($employes) ?> employé(s)
            </small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= base_url('rapports/export/csv/employes?'   . http_build_query($params)) ?>"
               class="btn btn-outline-success rounded-2 px-3">
                <i class="fas fa-file-csv me-2"></i>CSV
            </a>
            <a href="<?= base_url('rapports/export/excel/employes?' . http_build_query($params)) ?>"
               class="btn btn-outline-primary rounded-2 px-3">
                <i class="fas fa-file-excel me-2"></i>Excel
            </a>
            <a href="<?= base_url('rapports/export/pdf/employes?'   . http_build_query($params)) ?>"
               target="_blank"
               class="btn btn-outline-danger rounded-2 px-3">
                <i class="fas fa-file-pdf me-2"></i>PDF
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body py-3">
            <form method="GET" class="d-flex gap-3 flex-wrap align-items-end">
                <div>
                    <label class="form-label fw-semibold small mb-1">Du</label>
                    <input type="date" name="debut" value="<?= $params['debut'] ?>"
                           class="form-control form-control-sm">
                </div>
                <div>
                    <label class="form-label fw-semibold small mb-1">Au</label>
                    <input type="date" name="fin" value="<?= $params['fin'] ?>"
                           class="form-control form-control-sm">
                </div>
                <button type="submit" class="btn btn-primary btn-sm rounded-2 px-4">
                    <i class="fas fa-filter me-1"></i>Filtrer
                </button>
            </form>
        </div>
    </div>

    <!-- Totaux -->
    <?php
        $totalCA       = array_sum(array_column($employes, 'ca_encaisse'));
        $totalTx       = array_sum(array_column($employes, 'nb_transactions'));
        $totalArticles = array_sum(array_column($employes, 'articles_traites'));
        $totalMinutes  = array_sum(array_column($employes, 'total_minutes'));
        $totalH        = intdiv($totalMinutes, 60);
        $totalM        = $totalMinutes % 60;
    ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4 text-primary"><?= count($employes) ?></div>
                <div class="text-muted small">Employés actifs</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4 text-success">
                    <?= number_format($totalCA, 0, ',', ' ') ?>
                </div>
                <div class="text-muted small">CA encaissé total (FCFA)</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4 text-dark"><?= $totalH ?>h<?= str_pad($totalM,2,'0',STR_PAD_LEFT) ?></div>
                <div class="text-muted small">Heures travaillées</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4" style="color:#7e22ce;"><?= $totalArticles ?></div>
                <div class="text-muted small">Articles traités</div>
            </div>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">EMPLOYÉ</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">POSTE</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">JOURS</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">HEURES</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">TRANSACTIONS</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">CA ENCAISSÉ</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">ARTICLES</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">SCORE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($employes)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-user-tie fa-2x mb-2 d-block opacity-25"></i>
                                Aucune donnée pour cette période.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php
                        $maxCA       = max(1, max(array_column($employes, 'ca_encaisse')));
                        $maxArticles = max(1, max(array_column($employes, 'articles_traites')));
                        foreach ($employes as $e):
                            $h     = intdiv($e['total_minutes'], 60);
                            $m     = $e['total_minutes'] % 60;
                            $score = min(100, (int) round(
                                (($e['ca_encaisse']    / $maxCA)       * 50) +
                                (($e['articles_traites']/ $maxArticles) * 50)
                            ));
                            $sColor = $score >= 70 ? '#166534' : ($score >= 40 ? '#92400e' : '#991b1b');
                            $sBg    = $score >= 70 ? '#dcfce7' : ($score >= 40 ? '#fef3c7' : '#fee2e2');
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold"><?= esc($e['nom_complet']) ?></div>
                                <div class="text-muted" style="font-size:11px;font-family:monospace;">
                                    <?= esc($e['matricule']) ?>
                                </div>
                            </td>
                            <td style="font-size:12px;"><?= esc($e['nom_poste'] ?? '—') ?></td>
                            <td class="text-center fw-semibold"><?= $e['jours_travailles'] ?></td>
                            <td class="text-center fw-semibold">
                                <?= $h ?>h<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td class="text-center">
                                <span style="background:#eff6ff;color:#1d4ed8;
                                             padding:3px 10px;border-radius:20px;
                                             font-size:12px;font-weight:600;">
                                    <?= $e['nb_transactions'] ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold text-success">
                                <?= number_format($e['ca_encaisse'], 0, ',', ' ') ?> FCFA
                            </td>
                            <td class="text-center">
                                <span style="background:#fdf4ff;color:#7e22ce;
                                             padding:3px 10px;border-radius:20px;
                                             font-size:12px;font-weight:600;">
                                    <?= $e['articles_traites'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:80px;background:#f1f5f9;border-radius:20px;
                                                height:8px;overflow:hidden;">
                                        <div style="width:<?= $score ?>%;background:<?= $sColor ?>;
                                                    height:100%;border-radius:20px;"></div>
                                    </div>
                                    <span style="background:<?= $sBg ?>;color:<?= $sColor ?>;
                                                 padding:2px 8px;border-radius:20px;
                                                 font-size:11px;font-weight:600;">
                                        <?= $score ?>%
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot style="background:#f8fafc;">
                        <tr>
                            <td colspan="4" class="ps-4 fw-bold py-3">TOTAL</td>
                            <td class="text-center fw-bold py-3"><?= $totalTx ?></td>
                            <td class="text-end fw-bold text-success py-3 fs-6">
                                <?= number_format($totalCA, 0, ',', ' ') ?> FCFA
                            </td>
                            <td class="text-center fw-bold py-3" style="color:#7e22ce;">
                                <?= $totalArticles ?>
                            </td>
                            <td class="py-3 text-muted" style="font-size:11px;">
                                <?= $totalH ?>h<?= str_pad($totalM,2,'0',STR_PAD_LEFT) ?> cumulées
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
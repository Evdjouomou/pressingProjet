<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <a href="<?= base_url('rapports') ?>" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i>Rapports
            </a>
            <h4 class="fw-bold mb-0">Rapport Prestations</h4>
            <small class="text-muted">
                Du <?= date('d/m/Y', strtotime($params['debut'])) ?>
                au <?= date('d/m/Y', strtotime($params['fin'])) ?>
                — <?= count($prestations) ?> prestation(s)
            </small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= base_url('rapports/export/csv/prestations?'   . http_build_query($params)) ?>"
               class="btn btn-outline-success rounded-2 px-3">
                <i class="fas fa-file-csv me-2"></i>CSV
            </a>
            <a href="<?= base_url('rapports/export/excel/prestations?' . http_build_query($params)) ?>"
               class="btn btn-outline-primary rounded-2 px-3">
                <i class="fas fa-file-excel me-2"></i>Excel
            </a>
            <a href="<?= base_url('rapports/export/pdf/prestations?'   . http_build_query($params)) ?>"
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
        $totalCA       = array_sum(array_column($prestations, 'ca_total'));
        $totalArticles = array_sum(array_column($prestations, 'nb_articles'));
        $totalExpress  = array_sum(array_column($prestations, 'nb_express'));
        $prixMoyenGlob = $totalArticles > 0
            ? round($totalCA / $totalArticles) : 0;
    ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4 text-purple" style="color:#7e22ce;">
                    <?= count($prestations) ?>
                </div>
                <div class="text-muted small">Types de prestations</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4 text-primary"><?= $totalArticles ?></div>
                <div class="text-muted small">Articles traités</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4 text-success">
                    <?= number_format($totalCA, 0, ',', ' ') ?>
                </div>
                <div class="text-muted small">CA Total (FCFA)</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4 text-danger"><?= $totalExpress ?></div>
                <div class="text-muted small">Commandes express</div>
            </div>
        </div>
    </div>

    <!-- Tableau + barres visuelles -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">#</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">PRESTATION</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">ARTICLES</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">CA TOTAL</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">PRIX MOYEN</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">EXPRESS</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">PART CA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($prestations)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-tshirt fa-2x mb-2 d-block opacity-25"></i>
                                Aucune prestation pour cette période.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php
                        $maxCA = max(1, max(array_column($prestations, 'ca_total')));
                        foreach ($prestations as $i => $p):
                            $pct     = min(100, round(($p['ca_total'] / $totalCA) * 100));
                            $barPct  = min(100, round(($p['ca_total'] / $maxCA)   * 100));
                        ?>
                        <tr>
                            <td class="ps-4 text-muted fw-semibold"><?= $i + 1 ?></td>
                            <td class="fw-semibold"><?= esc($p['type_prestation']) ?></td>
                            <td class="text-center">
                                <span style="background:#eff6ff;color:#1d4ed8;
                                             padding:3px 12px;border-radius:20px;
                                             font-size:13px;font-weight:700;">
                                    <?= $p['nb_articles'] ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold text-success">
                                <?= number_format($p['ca_total'], 0, ',', ' ') ?> FCFA
                            </td>
                            <td class="text-end text-muted" style="font-size:12px;">
                                <?= number_format($p['prix_moyen'], 0, ',', ' ') ?> FCFA
                            </td>
                            <td class="text-center">
                                <?php if ($p['nb_express'] > 0): ?>
                                <span style="background:#fee2e2;color:#991b1b;
                                             padding:3px 10px;border-radius:20px;
                                             font-size:11px;font-weight:600;">
                                    🚀 <?= $p['nb_express'] ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted" style="font-size:12px;">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="min-width:140px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div style="flex:1;background:#f1f5f9;border-radius:20px;
                                                height:8px;overflow:hidden;">
                                        <div style="width:<?= $barPct ?>%;background:#7e22ce;
                                                    height:100%;border-radius:20px;"></div>
                                    </div>
                                    <span style="font-size:11px;color:#6b7280;white-space:nowrap;">
                                        <?= $pct ?>%
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot style="background:#f8fafc;">
                        <tr>
                            <td colspan="2" class="ps-4 fw-bold py-3">TOTAL</td>
                            <td class="text-center fw-bold py-3"><?= $totalArticles ?></td>
                            <td class="text-end fw-bold text-success py-3 fs-6">
                                <?= number_format($totalCA, 0, ',', ' ') ?> FCFA
                            </td>
                            <td class="text-end text-muted py-3" style="font-size:12px;">
                                Moy. <?= number_format($prixMoyenGlob, 0, ',', ' ') ?> FCFA
                            </td>
                            <td class="text-center fw-bold py-3 text-danger">
                                <?= $totalExpress ?>
                            </td>
                            <td class="py-3 text-muted" style="font-size:11px;">100%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
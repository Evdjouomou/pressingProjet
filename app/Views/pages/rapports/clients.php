<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <a href="<?= base_url('rapports') ?>" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i>Rapports
            </a>
            <h4 class="fw-bold mb-0">Rapport Clients</h4>
            <small class="text-muted">
                Du <?= date('d/m/Y', strtotime($params['debut'])) ?>
                au <?= date('d/m/Y', strtotime($params['fin'])) ?>
                — <?= count($clients) ?> client(s)
            </small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= base_url('rapports/export/csv/clients?'   . http_build_query($params)) ?>"
               class="btn btn-outline-success rounded-2 px-3">
                <i class="fas fa-file-csv me-2"></i>CSV
            </a>
            <a href="<?= base_url('rapports/export/excel/clients?' . http_build_query($params)) ?>"
               class="btn btn-outline-primary rounded-2 px-3">
                <i class="fas fa-file-excel me-2"></i>Excel
            </a>
            <a href="<?= base_url('rapports/export/pdf/clients?'   . http_build_query($params)) ?>"
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
                <div>
                    <label class="form-label fw-semibold small mb-1">Type client</label>
                    <select name="type_client" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="particulier"    <?= ($params['type_client'] ?? '') === 'particulier'    ? 'selected' : '' ?>>Particulier</option>
                        <option value="professionnel"  <?= ($params['type_client'] ?? '') === 'professionnel'  ? 'selected' : '' ?>>Professionnel</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm rounded-2 px-4">
                    <i class="fas fa-filter me-1"></i>Filtrer
                </button>
            </form>
        </div>
    </div>

    <!-- Totaux -->
    <?php
        $totalCA      = array_sum(array_column($clients, 'ca_total'));
        $totalDepots  = array_sum(array_column($clients, 'nb_depots'));
        $totalPoints  = array_sum(array_column($clients, 'solde_fidelite'));
        $clientsActifs = count(array_filter($clients, fn($c) => $c['nb_depots'] > 0));
    ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4 text-primary"><?= count($clients) ?></div>
                <div class="text-muted small">Total clients</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4 text-success"><?= $clientsActifs ?></div>
                <div class="text-muted small">Actifs sur la période</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4 text-dark"><?= number_format($totalCA, 0, ',', ' ') ?></div>
                <div class="text-muted small">CA Total (FCFA)</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4 text-warning"><?= number_format($totalPoints, 0, ',', ' ') ?></div>
                <div class="text-muted small">Points fidélité total</div>
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
                            <th class="ps-4 text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">CLIENT</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">TYPE</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">DÉPÔTS</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">CA TOTAL</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">FIDÉLITÉ</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">DERNIER DÉPÔT</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clients)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-users fa-2x mb-2 d-block opacity-25"></i>
                                Aucun client pour cette période.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php
                        $maxCA = max(1, max(array_column($clients, 'ca_total')));
                        foreach ($clients as $c):
                            $pct = min(100, round(($c['ca_total'] / $maxCA) * 100));
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold"><?= esc($c['nomclient']) ?></div>
                                <div class="text-muted" style="font-size:11px;">
                                    <i class="fas fa-phone me-1"></i><?= esc($c['telephone']) ?>
                                    <?php if ($c['email']): ?>
                                    · <?= esc($c['email']) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span style="background:<?= $c['typeclient']==='professionnel' ? '#fef3c7' : '#eff6ff' ?>;
                                             color:<?= $c['typeclient']==='professionnel' ? '#92400e' : '#1d4ed8' ?>;
                                             padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                    <?= ucfirst($c['typeclient']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span style="background:#eff6ff;color:#1d4ed8;padding:3px 12px;
                                             border-radius:20px;font-size:13px;font-weight:700;">
                                    <?= $c['nb_depots'] ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="fw-bold text-success">
                                    <?= number_format($c['ca_total'], 0, ',', ' ') ?> FCFA
                                </div>
                                <div style="background:#f1f5f9;border-radius:20px;height:4px;
                                            margin-top:4px;overflow:hidden;">
                                    <div style="width:<?= $pct ?>%;background:#166534;
                                                height:100%;border-radius:20px;"></div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span style="background:#fef3c7;color:#92400e;
                                             padding:3px 10px;border-radius:20px;
                                             font-size:12px;font-weight:600;">
                                    <i class="fas fa-star me-1" style="font-size:10px;"></i>
                                    <?= number_format($c['solde_fidelite'], 0, ',', ' ') ?> pts
                                </span>
                            </td>
                            <td style="font-size:12px;color:#6b7280;">
                                <?= $c['dernier_depot']
                                    ? date('d/m/Y', strtotime($c['dernier_depot']))
                                    : '—' ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('client/ficheclient/' . $c['id_client']) ?>"
                                   class="btn btn-sm"
                                   style="width:32px;height:32px;border-radius:8px;
                                          background:#f1f5f9;border:1px solid #e2e8f0;">
                                    <i class="fas fa-eye fa-sm text-secondary"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot style="background:#f8fafc;">
                        <tr>
                            <td colspan="2" class="ps-4 fw-bold py-3">TOTAL</td>
                            <td class="text-center fw-bold py-3"><?= $totalDepots ?></td>
                            <td class="text-end fw-bold text-success py-3 fs-6">
                                <?= number_format($totalCA, 0, ',', ' ') ?> FCFA
                            </td>
                            <td class="text-center fw-bold py-3" style="color:#92400e;">
                                <?= number_format($totalPoints, 0, ',', ' ') ?> pts
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-0">Historique des caisses</h4>
            <small class="text-muted"><?= count($caisses) ?> session(s)</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('pos/caisse') ?>"
               class="btn btn-outline-secondary rounded-2 px-3">
                <i class="fas fa-arrow-left me-2"></i>Caisse
            </a>
            <a href="<?= base_url('pos') ?>"
               class="btn btn-outline-primary rounded-2 px-3">
                <i class="fas fa-cash-register me-2"></i>POS
            </a>
        </div>
    </div>

    <!-- Stats globales -->
    <?php
    $totalCA       = array_sum(array_column($caisses, 'total_ca'));
    $totalRmb      = array_sum(array_column($caisses, 'total_rembourse'));
    $totalSessions = count($caisses);
    $sessionsClot  = count(array_filter($caisses, fn($c) => $c['statut'] === 'cloturee'));
    ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-primary"><?= $totalSessions ?></div>
                <div class="text-muted small">Sessions totales</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-success">
                    <?= number_format($totalCA, 0, ',', ' ') ?>
                </div>
                <div class="text-muted small">CA cumulé (FCFA)</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-danger">
                    <?= number_format($totalRmb, 0, ',', ' ') ?>
                </div>
                <div class="text-muted small">Remboursements (FCFA)</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-dark"><?= $sessionsClot ?></div>
                <div class="text-muted small">Clôturées</div>
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
                            <th class="ps-4 text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">OUVERTURE</th>
                            <th class="text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">CLÔTURE</th>
                            <th class="text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">CAISSIER</th>
                            <th class="text-end text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">FOND</th>
                            <th class="text-end text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">ESPÈCES</th>
                            <th class="text-end text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">MOBILE</th>
                            <th class="text-end text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">CARTE</th>
                            <th class="text-end text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">CA TOTAL</th>
                            <th class="text-end text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">REMB.</th>
                            <th class="text-center text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">ÉCART</th>
                            <th class="text-center text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">STATUT</th>
                            <th class="text-center text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($caisses)): ?>
                        <tr>
                            <td colspan="12" class="text-center py-5 text-muted">
                                <i class="fas fa-cash-register fa-2x mb-2 d-block opacity-25"></i>
                                Aucune session de caisse.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($caisses as $c):
                            $ecart      = $c['ecart'] ?? null;
                            $statut     = $c['statut'];
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold" style="font-size:13px;">
                                    <?= date('d/m/Y', strtotime($c['date_ouverture'])) ?>
                                </div>
                                <div class="text-muted" style="font-size:11px;">
                                    <?= date('H:i', strtotime($c['date_ouverture'])) ?>
                                </div>
                            </td>
                            <td style="font-size:12px;">
                                <?php if ($c['date_cloture']): ?>
                                    <div class="fw-semibold">
                                        <?= date('d/m/Y', strtotime($c['date_cloture'])) ?>
                                    </div>
                                    <div class="text-muted" style="font-size:11px;">
                                        <?= date('H:i', strtotime($c['date_cloture'])) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px;">
                                <?= esc($c['caissier'] ?? '—') ?>
                            </td>
                            <td class="text-end" style="font-size:12px;">
                                <?= number_format($c['fond_ouverture'], 0, ',', ' ') ?>
                            </td>
                            <td class="text-end" style="font-size:12px;">
                                <?= number_format($c['total_especes'], 0, ',', ' ') ?>
                            </td>
                            <td class="text-end" style="font-size:12px;">
                                <?= number_format($c['total_mobile'], 0, ',', ' ') ?>
                            </td>
                            <td class="text-end" style="font-size:12px;">
                                <?= number_format($c['total_carte'], 0, ',', ' ') ?>
                            </td>
                            <td class="text-end fw-bold text-success">
                                <?= number_format($c['total_ca'], 0, ',', ' ') ?>
                            </td>
                            <td class="text-end text-danger" style="font-size:12px;">
                                <?= $c['total_rembourse'] > 0
                                    ? number_format($c['total_rembourse'], 0, ',', ' ')
                                    : '—' ?>
                            </td>
                            <td class="text-center">
                                <?php if ($ecart !== null): ?>
                                    <span style="
                                        background:<?= $ecart == 0 ? '#dcfce7' : ($ecart > 0 ? '#eff6ff' : '#fee2e2') ?>;
                                        color:<?= $ecart == 0 ? '#166534' : ($ecart > 0 ? '#1d4ed8' : '#991b1b') ?>;
                                        padding:3px 10px;border-radius:20px;
                                        font-size:11px;font-weight:600;">
                                        <?= $ecart >= 0 ? '+' : '' ?>
                                        <?= number_format($ecart, 0, ',', ' ') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:11px;">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span style="
                                    background:<?= $statut === 'ouverte' ? '#dcfce7' : '#f1f5f9' ?>;
                                    color:<?= $statut === 'ouverte' ? '#166534' : '#374151' ?>;
                                    padding:3px 10px;border-radius:20px;
                                    font-size:11px;font-weight:600;">
                                    <?= $statut === 'ouverte' ? '● Ouverte' : 'Clôturée' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('pos/rapport-caisse/' . $c['id_caisse']) ?>"
                                   target="_blank"
                                   class="btn btn-sm"
                                   style="width:32px;height:32px;border-radius:8px;
                                          background:#eff6ff;border:1px solid #bfdbfe;"
                                   title="Rapport Z">
                                    <i class="fas fa-print fa-sm text-primary"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($caisses)): ?>
                    <tfoot style="background:#f8fafc;">
                        <tr>
                            <td colspan="7" class="ps-4 fw-bold py-3 text-muted"
                                style="font-size:12px;">TOTAUX</td>
                            <td class="text-end fw-bold text-success py-3">
                                <?= number_format($totalCA, 0, ',', ' ') ?> FCFA
                            </td>
                            <td class="text-end fw-bold text-danger py-3">
                                <?= number_format($totalRmb, 0, ',', ' ') ?> FCFA
                            </td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
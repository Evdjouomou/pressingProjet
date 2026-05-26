<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <a href="<?= base_url('rapports') ?>" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i>Rapports
            </a>
            <h4 class="fw-bold mb-0">Rapport Dépôts</h4>
            <small class="text-muted">
                Du <?= date('d/m/Y', strtotime($params['debut'])) ?>
                au <?= date('d/m/Y', strtotime($params['fin'])) ?>
                — <?= count($depots) ?> dépôt(s)
            </small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= base_url('rapports/export/csv/depots?'   . http_build_query($params)) ?>" class="btn btn-outline-success rounded-2 px-3"><i class="fas fa-file-csv me-2"></i>CSV</a>
            <a href="<?= base_url('rapports/export/excel/depots?' . http_build_query($params)) ?>" class="btn btn-outline-primary rounded-2 px-3"><i class="fas fa-file-excel me-2"></i>Excel</a>
            <a href="<?= base_url('rapports/export/pdf/depots?'   . http_build_query($params)) ?>" target="_blank" class="btn btn-outline-danger rounded-2 px-3"><i class="fas fa-file-pdf me-2"></i>PDF</a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body py-3">
            <form method="GET" class="d-flex gap-3 flex-wrap align-items-end">
                <div>
                    <label class="form-label fw-semibold small mb-1">Du</label>
                    <input type="date" name="debut" value="<?= $params['debut'] ?>" class="form-control form-control-sm">
                </div>
                <div>
                    <label class="form-label fw-semibold small mb-1">Au</label>
                    <input type="date" name="fin" value="<?= $params['fin'] ?>" class="form-control form-control-sm">
                </div>
                <button type="submit" class="btn btn-primary btn-sm rounded-2 px-4">
                    <i class="fas fa-filter me-1"></i>Filtrer
                </button>
            </form>
        </div>
    </div>

    <!-- Totaux -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4 text-primary"><?= $totaux['nb'] ?></div>
                <div class="text-muted small">Dépôts</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4 text-success"><?= number_format($totaux['ca'], 0, ',', ' ') ?></div>
                <div class="text-muted small">CA Total (FCFA)</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4 text-primary"><?= number_format($totaux['encaisse'], 0, ',', ' ') ?></div>
                <div class="text-muted small">Encaissé (FCFA)</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4 text-danger"><?= number_format($totaux['reste'], 0, ',', ' ') ?></div>
                <div class="text-muted small">Reste (FCFA)</div>
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
                            <th class="ps-4 text-muted fw-semibold" style="font-size:11px;">DATE</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;">N° BON</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;">CLIENT</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;">ARTICLES</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:11px;">TOTAL TTC</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:11px;">ENCAISSÉ</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:11px;">RESTE</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;">STATUT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $statutsColors = [
                            'depot'    => ['label'=>'Reçu',    'bg'=>'#f1f5f9','color'=>'#374151'],
                            'en_cours' => ['label'=>'En cours','bg'=>'#fef3c7','color'=>'#92400e'],
                            'pret'     => ['label'=>'Prêt',    'bg'=>'#d1fae5','color'=>'#065f46'],
                            'livre'    => ['label'=>'Livré',   'bg'=>'#dcfce7','color'=>'#166534'],
                            'annule'   => ['label'=>'Annulé',  'bg'=>'#fee2e2','color'=>'#991b1b'],
                        ];
                        foreach ($depots as $d):
                            $sc    = $statutsColors[$d['statut_global']] ?? ['label'=>$d['statut_global'],'bg'=>'#f1f5f9','color'=>'#374151'];
                            $reste = max(0, $d['total_ttc'] - $d['encaisse']);
                        ?>
                        <tr>
                            <td class="ps-4" style="font-size:12px;">
                                <?= date('d/m/Y', strtotime($d['created_at'])) ?>
                            </td>
                            <td class="text-primary fw-semibold" style="font-size:12px;">
                                <a href="<?= base_url('depot/detail/' . $d['id_depot']) ?>">
                                    <?= esc($d['code_commande']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold" style="font-size:13px;"><?= esc($d['nomclient']) ?></div>
                                <div class="text-muted" style="font-size:11px;"><?= esc($d['telephone']) ?></div>
                            </td>
                            <td class="text-center">
                                <span style="background:#eff6ff;color:#1d4ed8;padding:2px 8px;border-radius:20px;font-size:12px;font-weight:600;">
                                    <?= $d['nb_articles'] ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold"><?= number_format($d['total_ttc'], 0, ',', ' ') ?> FCFA</td>
                            <td class="text-end text-success"><?= number_format($d['encaisse'], 0, ',', ' ') ?> FCFA</td>
                            <td class="text-end" style="color:<?= $reste > 0 ? '#dc2626' : '#166534' ?>;">
                                <?= $reste > 0 ? number_format($reste,0,',',' ').' FCFA' : '✓ Soldé' ?>
                            </td>
                            <td class="text-center">
                                <span style="background:<?= $sc['bg'] ?>;color:<?= $sc['color'] ?>;
                                             padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                    <?= $sc['label'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <a href="<?= base_url('rapports') ?>" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i>Rapports
            </a>
            <h4 class="fw-bold mb-0">Chiffre d'Affaires</h4>
            <small class="text-muted">
                Du <?= date('d/m/Y', strtotime($params['debut'])) ?>
                au <?= date('d/m/Y', strtotime($params['fin'])) ?>
                — <?= count($transactions) ?> transaction(s)
            </small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= base_url('rapports/export/csv/ca?' . http_build_query($params)) ?>"
               class="btn btn-outline-success rounded-2 px-3">
                <i class="fas fa-file-csv me-2"></i>CSV
            </a>
            <a href="<?= base_url('rapports/export/excel/ca?' . http_build_query($params)) ?>"
               class="btn btn-outline-primary rounded-2 px-3">
                <i class="fas fa-file-excel me-2"></i>Excel
            </a>
            <a href="<?= base_url('rapports/export/pdf/ca?' . http_build_query($params)) ?>"
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
                    <label class="form-label fw-semibold small mb-1">Mode paiement</label>
                    <select name="mode" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="especes"      <?= $params['mode']==='especes'      ?'selected':'' ?>>Espèces</option>
                        <option value="mobile_money" <?= $params['mode']==='mobile_money' ?'selected':'' ?>>Mobile Money</option>
                        <option value="carte"        <?= $params['mode']==='carte'        ?'selected':'' ?>>Carte</option>
                    </select>
                </div>
                <div>
                    <label class="form-label fw-semibold small mb-1">Caissier</label>
                    <select name="employe_id" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <?php foreach ($employes as $e): ?>
                        <option value="<?= $e['id_employe'] ?>"
                            <?= $params['employe_id'] == $e['id_employe'] ? 'selected':'' ?>>
                            <?= esc($e['nom_complet']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm rounded-2 px-4">
                    <i class="fas fa-filter me-1"></i>Filtrer
                </button>
            </form>
        </div>
    </div>

    <!-- Totaux -->
    <div class="row g-3 mb-4">
        <?php
        $cartes = [
            ['label' => 'CA Total',       'val' => $totaux['total'],   'color' => '#166534', 'bg' => '#dcfce7'],
            ['label' => 'Espèces',         'val' => $totaux['especes'], 'color' => '#1d4ed8', 'bg' => '#eff6ff'],
            ['label' => 'Carte bancaire',  'val' => $totaux['carte'],   'color' => '#7e22ce', 'bg' => '#fdf4ff'],
            ['label' => 'Mobile Money',    'val' => $totaux['mobile'],  'color' => '#0e7490', 'bg' => '#ecfeff'],
        ];
        foreach ($cartes as $c): ?>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4" style="color:<?= $c['color'] ?>;">
                    <?= number_format($c['val'], 0, ',', ' ') ?>
                </div>
                <div class="text-muted small"><?= $c['label'] ?> (FCFA)</div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Tableau -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-muted fw-semibold" style="font-size:11px;">DATE</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;">CLIENT</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;">BON</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;">MODE</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;">CAISSIER</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:11px;">MONTANT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                Aucune transaction pour cette période.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold" style="font-size:13px;">
                                    <?= date('d/m/Y', strtotime($tx['created_at'])) ?>
                                </div>
                                <div class="text-muted" style="font-size:11px;">
                                    <?= date('H:i', strtotime($tx['created_at'])) ?>
                                </div>
                            </td>
                            <td><?= esc($tx['nomclient'] ?? '—') ?></td>
                            <td class="text-primary fw-semibold" style="font-size:12px;">
                                <?= esc($tx['code_commande'] ?? '—') ?>
                            </td>
                            <td style="font-size:12px;">
                                <?= ucfirst(str_replace('_',' ',$tx['mode_paiement'])) ?>
                            </td>
                            <td style="font-size:12px;"><?= esc($tx['caissier'] ?? '—') ?></td>
                            <td class="text-end fw-bold text-success">
                                <?= number_format($tx['montant'], 0, ',', ' ') ?> FCFA
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot style="background:#f8fafc;">
                        <tr>
                            <td colspan="5" class="ps-4 fw-bold py-3">TOTAL</td>
                            <td class="text-end fw-bold text-success py-3 fs-5">
                                <?= number_format($totaux['total'], 0, ',', ' ') ?> FCFA
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
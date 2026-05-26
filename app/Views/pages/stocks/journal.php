<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-0">Journal des mouvements</h4>
            <small class="text-muted"><?= count($mouvements) ?> mouvement(s)</small>
        </div>
        <a href="<?= base_url('stocks') ?>" class="btn btn-outline-secondary rounded-2 px-3">
            <i class="fas fa-arrow-left me-2"></i>Stocks
        </a>
    </div>

    <!-- Filtres -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body py-3">
            <form method="GET" class="d-flex gap-3 flex-wrap align-items-end">
                <div>
                    <label class="form-label fw-semibold small mb-1">Du</label>
                    <input type="date" name="debut" value="<?= $debut ?>" class="form-control form-control-sm">
                </div>
                <div>
                    <label class="form-label fw-semibold small mb-1">Au</label>
                    <input type="date" name="fin" value="<?= $fin ?>" class="form-control form-control-sm">
                </div>
                <div>
                    <label class="form-label fw-semibold small mb-1">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="entree"       <?= $filtre==='entree'       ? 'selected':'' ?>>Entrée</option>
                        <option value="sortie"       <?= $filtre==='sortie'       ? 'selected':'' ?>>Sortie</option>
                        <option value="ajustement"   <?= $filtre==='ajustement'   ? 'selected':'' ?>>Ajustement</option>
                        <option value="vente_pos"    <?= $filtre==='vente_pos'    ? 'selected':'' ?>>Vente POS</option>
                        <option value="consommation" <?= $filtre==='consommation' ? 'selected':'' ?>>Consommation</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm rounded-2 px-3">
                    <i class="fas fa-filter me-1"></i>Filtrer
                </button>
            </form>
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
                            <th class="text-muted fw-semibold" style="font-size:11px;">PRODUIT</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;">TYPE</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;">QTÉ</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;">AVANT</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;">APRÈS</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;">MOTIF</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;">OPÉRATEUR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($mouvements)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-history fa-2x mb-2 d-block opacity-25"></i>
                                Aucun mouvement pour cette période.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php
                        $typesMvt = [
                            'entree'       => ['label' => 'Entrée',       'bg' => '#dcfce7', 'color' => '#166534', 'sign' => '+'],
                            'sortie'       => ['label' => 'Sortie',       'bg' => '#fee2e2', 'color' => '#991b1b', 'sign' => '−'],
                            'ajustement'   => ['label' => 'Ajustement',   'bg' => '#fef3c7', 'color' => '#92400e', 'sign' => '↕'],
                            'vente_pos'    => ['label' => 'Vente POS',    'bg' => '#eff6ff', 'color' => '#1d4ed8', 'sign' => '−'],
                            'consommation' => ['label' => 'Consommation', 'bg' => '#fdf4ff', 'color' => '#7e22ce', 'sign' => '−'],
                        ];
                        foreach ($mouvements as $m):
                            $tm = $typesMvt[$m['type_mouvement']] ?? ['label' => $m['type_mouvement'], 'bg' => '#f1f5f9', 'color' => '#374151', 'sign' => '?'];
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div style="font-size:13px;font-weight:600;">
                                    <?= date('d/m/Y', strtotime($m['created_at'])) ?>
                                </div>
                                <div class="text-muted" style="font-size:11px;">
                                    <?= date('H:i', strtotime($m['created_at'])) ?>
                                </div>
                            </td>
                            <td class="fw-semibold" style="font-size:13px;">
                                <?= esc($m['produit_nom']) ?>
                                <div class="text-muted" style="font-size:11px;"><?= esc($m['unite']) ?></div>
                            </td>
                            <td class="text-center">
                                <span style="background:<?= $tm['bg'] ?>;color:<?= $tm['color'] ?>;
                                             padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                    <?= $tm['label'] ?>
                                </span>
                            </td>
                            <td class="text-center fw-bold"
                                style="color:<?= $tm['color'] ?>;font-size:14px;">
                                <?= $tm['sign'] ?><?= $m['quantite'] ?>
                            </td>
                            <td class="text-center text-muted" style="font-size:13px;">
                                <?= $m['stock_avant'] ?>
                            </td>
                            <td class="text-center fw-bold" style="font-size:13px;">
                                <?= $m['stock_apres'] ?>
                            </td>
                            <td style="font-size:12px;max-width:180px;">
                                <span class="d-block text-truncate"><?= esc($m['motif'] ?: '—') ?></span>
                                <?php if ($m['reference_doc']): ?>
                                <span class="text-muted" style="font-size:10px;">
                                    Réf: <?= esc($m['reference_doc']) ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px;"><?= esc($m['nom_complet'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
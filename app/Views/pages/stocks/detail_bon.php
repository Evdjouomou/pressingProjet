<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3">
            <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php
    $statutsBon = [
        'brouillon' => ['label' => 'Brouillon', 'bg' => '#f1f5f9', 'color' => '#374151'],
        'envoye'    => ['label' => 'Envoyé',    'bg' => '#fef3c7', 'color' => '#92400e'],
        'recu'      => ['label' => 'Reçu',      'bg' => '#dcfce7', 'color' => '#166534'],
        'annule'    => ['label' => 'Annulé',    'bg' => '#fee2e2', 'color' => '#991b1b'],
    ];
    $sb = $statutsBon[$bon['statut']] ?? ['label' => $bon['statut'], 'bg' => '#f1f5f9', 'color' => '#374151'];
    ?>

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <a href="<?= base_url('stocks/bons') ?>" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i>Retour aux bons
            </a>
            <h4 class="fw-bold mb-0">
                Bon <span class="text-primary"><?= esc($bon['reference']) ?></span>
            </h4>
            <small class="text-muted">
                Créé le <?= date('d/m/Y à H:i', strtotime($bon['created_at'])) ?>
                par <?= esc($bon['nom_complet'] ?? '—') ?>
            </small>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <span style="background:<?= $sb['bg'] ?>;color:<?= $sb['color'] ?>;
                         padding:6px 16px;border-radius:20px;font-size:13px;font-weight:600;">
                <?= $sb['label'] ?>
            </span>
            <a href="<?= base_url('stocks/bons/imprimer/' . $bon['id_bon']) ?>"
               target="_blank"
               class="btn btn-outline-primary rounded-2 px-3">
                <i class="fas fa-print me-2"></i>Imprimer
            </a>
            <?php if ($bon['statut'] !== 'recu' && $bon['statut'] !== 'annule'): ?>
            <a href="<?= base_url('stocks/bons/recevoir/' . $bon['id_bon']) ?>"
               class="btn btn-success rounded-2 px-3"
               onclick="return confirm('Confirmer la réception ?\n\nLe stock de chaque produit sera mis à jour automatiquement.')">
                <i class="fas fa-check me-2"></i>Marquer comme reçu
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">

        <!-- ════════════════════════════════════ -->
        <!-- COLONNE GAUCHE : Infos bon          -->
        <!-- ════════════════════════════════════ -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body">

                    <!-- Fournisseur -->
                    <p class="text-uppercase text-muted fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-truck me-2"></i>Fournisseur
                    </p>
                    <h5 class="fw-bold mb-1"><?= esc($bon['fournisseur']) ?></h5>

                    <hr>

                    <!-- Informations bon -->
                    <p class="text-uppercase text-muted fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-info-circle me-2"></i>Informations
                    </p>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Référence</span>
                        <strong class="text-primary"><?= esc($bon['reference']) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Statut</span>
                        <span style="background:<?= $sb['bg'] ?>;color:<?= $sb['color'] ?>;
                                     padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                            <?= $sb['label'] ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Créé le</span>
                        <strong><?= date('d/m/Y', strtotime($bon['created_at'])) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Émis par</span>
                        <strong><?= esc($bon['nom_complet'] ?? '—') ?></strong>
                    </div>
                    <?php if ($bon['date_envoi']): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Date envoi</span>
                        <strong><?= date('d/m/Y', strtotime($bon['date_envoi'])) ?></strong>
                    </div>
                    <?php endif; ?>
                    <?php if ($bon['date_reception']): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Date réception</span>
                        <strong class="text-success">
                            <?= date('d/m/Y', strtotime($bon['date_reception'])) ?>
                        </strong>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Nb articles</span>
                        <strong><?= count($lignes) ?></strong>
                    </div>

                    <hr>

                    <!-- Total -->
                    <div class="rounded-3 p-3"
                         style="background:#f0fdf4;border:1px solid #bbf7d0;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small fw-semibold">Total HT</span>
                            <span class="fw-bold text-success fs-5">
                                <?= number_format($bon['total_ht'], 0, ',', ' ') ?> FCFA
                            </span>
                        </div>
                    </div>

                    <?php if ($bon['note']): ?>
                    <hr>
                    <p class="text-uppercase text-muted fw-semibold mb-2"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-sticky-note me-2"></i>Note
                    </p>
                    <p class="text-muted" style="font-size:13px;line-height:1.6;">
                        <?= esc($bon['note']) ?>
                    </p>
                    <?php endif; ?>

                    <?php if ($bon['statut'] === 'recu'): ?>
                    <div class="rounded-3 p-3 mt-3"
                         style="background:#f0fdf4;border:1px solid #bbf7d0;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                            <div>
                                <p class="fw-semibold text-success mb-0" style="font-size:13px;">
                                    Commande reçue
                                </p>
                                <p class="text-muted mb-0" style="font-size:11px;">
                                    Les stocks ont été mis à jour automatiquement.
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════ -->
        <!-- COLONNE DROITE : Lignes commande    -->
        <!-- ════════════════════════════════════ -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">

                    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                        <p class="text-uppercase text-muted fw-semibold mb-0"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-list me-2"></i>Lignes de commande
                        </p>
                        <span class="badge bg-primary rounded-pill">
                            <?= count($lignes) ?> produit(s)
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">#</th>
                                    <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">PRODUIT</th>
                                    <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">STOCK ACTUEL</th>
                                    <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">QTÉ COMMANDÉE</th>
                                    <th class="text-end text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">PRIX UNIT.</th>
                                    <th class="text-end text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lignes as $i => $l): ?>
                                <tr>
                                    <td class="ps-4 text-muted" style="font-size:12px;">
                                        <?= $i + 1 ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= esc($l['nom']) ?></div>
                                        <?php if ($l['ref_produit']): ?>
                                        <div class="text-muted" style="font-size:11px;">
                                            Réf: <?= esc($l['ref_produit']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span style="background:#eff6ff;color:#1d4ed8;padding:3px 12px;
                                                     border-radius:20px;font-size:12px;font-weight:600;">
                                            <?= $l['stock_actuel'] ?> <?= esc($l['unite']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span style="background:#f0fdf4;color:#166534;padding:3px 12px;
                                                     border-radius:20px;font-size:13px;font-weight:700;">
                                            +<?= $l['quantite'] ?> <?= esc($l['unite']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end" style="font-size:13px;">
                                        <?= number_format($l['prix_unitaire'], 0, ',', ' ') ?> FCFA
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        <?= number_format($l['total_ligne'], 0, ',', ' ') ?> FCFA
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot style="background:#f8fafc;">
                                <tr>
                                    <td colspan="5" class="text-end fw-semibold text-muted ps-4 py-3"
                                        style="font-size:13px;">
                                        Total HT
                                    </td>
                                    <td class="text-end fw-bold text-success py-3 fs-5">
                                        <?= number_format($bon['total_ht'], 0, ',', ' ') ?> FCFA
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>

            <!-- Impact sur les stocks (simulation) -->
            <?php if ($bon['statut'] !== 'recu'): ?>
            <div class="card border-0 shadow-sm rounded-3 mt-4">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom">
                        <p class="text-uppercase text-muted fw-semibold mb-0"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-chart-bar me-2"></i>Impact prévu sur les stocks
                        </p>
                    </div>
                    <div class="p-4">
                        <div class="row g-3">
                            <?php foreach ($lignes as $l): ?>
                            <?php
                                $stockApres = $l['stock_actuel'] + $l['quantite'];
                            ?>
                            <div class="col-md-6">
                                <div class="rounded-3 p-3"
                                     style="background:#f8fafc;border:1px solid #e2e8f0;">
                                    <div class="fw-semibold mb-2" style="font-size:13px;">
                                        <?= esc($l['nom']) ?>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span style="background:#fee2e2;color:#991b1b;padding:2px 10px;
                                                     border-radius:20px;font-size:12px;font-weight:600;">
                                            <?= $l['stock_actuel'] ?> <?= esc($l['unite']) ?>
                                        </span>
                                        <i class="fas fa-arrow-right text-muted"></i>
                                        <span style="background:#dcfce7;color:#166534;padding:2px 10px;
                                                     border-radius:20px;font-size:12px;font-weight:600;">
                                            <?= $stockApres ?> <?= esc($l['unite']) ?>
                                        </span>
                                        <span class="text-success fw-semibold" style="font-size:12px;">
                                            (+<?= $l['quantite'] ?>)
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mt-3 rounded-3 p-3"
                             style="background:#fffbeb;border:1px solid #fde68a;">
                            <p class="mb-0" style="font-size:12px;color:#92400e;">
                                <i class="fas fa-info-circle me-1"></i>
                                Ces valeurs sont une simulation. Cliquez sur
                                <strong>"Marquer comme reçu"</strong>
                                pour appliquer les entrées en stock réellement.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card border-0 shadow-sm rounded-3 mt-4">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fas fa-check-circle text-success fa-2x"></i>
                        <div>
                            <p class="fw-semibold text-success mb-0" style="font-size:14px;">
                                Stocks mis à jour
                            </p>
                            <p class="text-muted mb-0" style="font-size:12px;">
                                Toutes les entrées ont été enregistrées dans le journal des mouvements
                                le <?= date('d/m/Y à H:i', strtotime($bon['date_reception'])) ?>.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?= $this->endSection() ?>
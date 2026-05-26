<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php
    $statutsCycle = [
        'en_cours' => ['label' => 'En cours', 'bg' => '#dbeafe', 'color' => '#1d4ed8'],
        'termine'  => ['label' => 'Terminé',  'bg' => '#dcfce7', 'color' => '#166534'],
        'annule'   => ['label' => 'Annulé',   'bg' => '#fee2e2', 'color' => '#991b1b'],
    ];
    $sc = $statutsCycle[$cycle['statut']] ?? ['label' => $cycle['statut'], 'bg' => '#f1f5f9', 'color' => '#374151'];
    $typesMachine = [
        'lavage' => '🫧', 'sechage' => '💨',
        'repassage' => '👔', 'detachage' => '🧴', 'autre' => '⚙️',
    ];
    $emoji = $typesMachine[$cycle['type_machine']] ?? '⚙️';
    ?>

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <a href="<?= base_url('production/cycles') ?>"
               class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
            <h4 class="fw-bold mb-0">
                Cycle <span class="text-primary"><?= esc($cycle['reference']) ?></span>
            </h4>
            <small class="text-muted">
                <?= $emoji ?> <?= esc($cycle['machine_nom']) ?>
                (capacité : <?= $cycle['capacite_max'] ?> articles max)
            </small>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <span style="background:<?= $sc['bg'] ?>;color:<?= $sc['color'] ?>;
                         padding:6px 16px;border-radius:20px;font-size:13px;font-weight:600;">
                <?= $sc['label'] ?>
            </span>
            <?php if ($cycle['statut'] === 'en_cours'): ?>
            <form action="<?= base_url('production/cycles/' . $cycle['id_cycle'] . '/terminer') ?>"
                  method="POST" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success rounded-2 px-3">
                    <i class="fas fa-check me-2"></i>Terminer le cycle
                </button>
            </form>
            <a href="<?= base_url('production/cycles/' . $cycle['id_cycle'] . '/annuler') ?>"
               class="btn btn-outline-danger rounded-2 px-3"
               onclick="return confirm('Annuler ce cycle ?')">
                <i class="fas fa-times me-2"></i>Annuler
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">

        <!-- ════════════════════════════════════ -->
        <!-- COLONNE GAUCHE : Infos + Conso      -->
        <!-- ════════════════════════════════════ -->
        <div class="col-md-4">

            <!-- Infos cycle -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body">
                    <p class="text-uppercase text-muted fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-info-circle me-2"></i>Informations
                    </p>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Machine</span>
                        <strong><?= $emoji ?> <?= esc($cycle['machine_nom']) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Opérateur</span>
                        <strong><?= esc($cycle['operateur'] ?? '—') ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Articles</span>
                        <span style="background:#eff6ff;color:#1d4ed8;padding:2px 10px;
                                     border-radius:20px;font-size:13px;font-weight:700;">
                            <?= $cycle['nb_articles'] ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Début</span>
                        <strong><?= $cycle['date_debut']
                            ? date('d/m/Y H:i', strtotime($cycle['date_debut']))
                            : '—' ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Fin</span>
                        <strong><?= $cycle['date_fin']
                            ? date('d/m/Y H:i', strtotime($cycle['date_fin']))
                            : '—' ?></strong>
                    </div>
                    <?php if ($cycle['observations']): ?>
                    <hr>
                    <p class="text-muted small mb-0"><?= esc($cycle['observations']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Consommables utilisés -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom">
                        <p class="text-uppercase text-muted fw-semibold mb-0"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-flask me-2"></i>Consommables utilisés
                        </p>
                    </div>
                    <?php if (empty($consommables)): ?>
                    <div class="text-center py-4 text-muted small">
                        <i class="fas fa-flask fa-2x mb-2 d-block opacity-25"></i>
                        Aucun consommable enregistré.
                    </div>
                    <?php else: ?>
                    <div class="p-3">
                        <?php foreach ($consommables as $c): ?>
                        <div class="rounded-3 p-3 mb-2"
                             style="background:#f8fafc;border:1px solid #e2e8f0;">
                            <div class="fw-semibold mb-1" style="font-size:13px;">
                                <?= esc($c['nom']) ?>
                            </div>
                            <div class="d-flex justify-content-between" style="font-size:12px;">
                                <span class="text-muted">Quantité totale</span>
                                <span class="fw-semibold text-danger">
                                    <?= $c['quantite_totale'] ?> <?= esc($c['unite']) ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between" style="font-size:12px;">
                                <span class="text-muted">Par article</span>
                                <span class="fw-semibold text-primary">
                                    ≈ <?= $c['quantite_par_article'] ?> <?= esc($c['unite']) ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between mt-1" style="font-size:11px;">
                                <span class="text-muted">Stock restant</span>
                                <span style="color:<?= $c['stock_actuel'] <= 5 ? '#dc2626' : '#166534' ?>;">
                                    <?= $c['stock_actuel'] ?> <?= esc($c['unite']) ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- ════════════════════════════════════ -->
        <!-- COLONNE DROITE : Articles du cycle  -->
        <!-- ════════════════════════════════════ -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                        <p class="text-uppercase text-muted fw-semibold mb-0"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-tshirt me-2"></i>Articles dans ce cycle
                        </p>
                        <span class="badge bg-primary rounded-pill">
                            <?= count($articles) ?> article(s)
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 text-muted fw-semibold" style="font-size:11px;">ARTICLE</th>
                                    <th class="text-muted fw-semibold" style="font-size:11px;">CLIENT / BON</th>
                                    <th class="text-muted fw-semibold" style="font-size:11px;">ÉTAPE</th>
                                    <th class="text-muted fw-semibold" style="font-size:11px;">CONSO / ARTICLE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($articles)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        Aucun article dans ce cycle.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($articles as $art): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold">
                                            <?= esc($art['nom_libelle']) ?>
                                            <?php if ($art['options_express']): ?>
                                                <span class="badge bg-danger ms-1" style="font-size:10px;">🚀</span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-family:monospace;font-size:11px;color:#6b7280;">
                                            <?= esc($art['barcode_unique']) ?>
                                        </div>
                                        <?php if ($art['observations']): ?>
                                        <div style="font-size:11px;color:#f59e0b;">
                                            ⚠ <?= esc($art['observations']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold" style="font-size:13px;">
                                            <?= esc($art['nomclient']) ?>
                                        </div>
                                        <a href="<?= base_url('depot/detail/' . $art['id_depot']) ?>"
                                           class="text-primary" style="font-size:11px;">
                                            <?= esc($art['code_commande']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($art['etape_libelle']): ?>
                                        <span style="background:<?= $art['etape_couleur'] ?>22;
                                                     color:<?= $art['etape_couleur'] ?>;
                                                     padding:3px 10px;border-radius:20px;
                                                     font-size:11px;font-weight:600;">
                                            <?= esc($art['etape_libelle']) ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (empty($consommables)): ?>
                                        <span class="text-muted" style="font-size:11px;">—</span>
                                        <?php else: ?>
                                        <?php foreach ($consommables as $c): ?>
                                        <div style="font-size:11px;color:#374151;">
                                            <span class="fw-semibold"><?= esc($c['nom']) ?></span> :
                                            <span class="text-primary">
                                                <?= $c['quantite_par_article'] ?> <?= esc($c['unite']) ?>
                                            </span>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
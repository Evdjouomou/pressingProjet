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

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-0">Cycles Machine</h4>
            <small class="text-muted"><?= count($cycles) ?> cycle(s) enregistré(s)</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('production/machines') ?>"
               class="btn btn-outline-secondary rounded-2 px-3">
                <i class="fas fa-cog me-2"></i>Machines
            </a>
            <a href="<?= base_url('production/cycles/nouveau') ?>"
               class="btn btn-primary rounded-2 px-4">
                <i class="fas fa-plus me-2"></i>Nouveau cycle
            </a>
        </div>
    </div>

    <!-- Stats rapides -->
    <?php
        $enCours  = count(array_filter($cycles, fn($c) => $c['statut'] === 'en_cours'));
        $termines = count(array_filter($cycles, fn($c) => $c['statut'] === 'termine'));
    ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-primary"><?= $enCours ?></div>
                <div class="text-muted small">En cours</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-success"><?= $termines ?></div>
                <div class="text-muted small">Terminés</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-dark">
                    <?= array_sum(array_column($cycles, 'nb_articles')) ?>
                </div>
                <div class="text-muted small">Articles traités</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-secondary"><?= count($machines) ?></div>
                <div class="text-muted small">Machines actives</div>
            </div>
        </div>
    </div>

    <!-- Tableau cycles -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">RÉFÉRENCE</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">MACHINE</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">OPÉRATEUR</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">ARTICLES</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">DÉBUT</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">FIN</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">STATUT</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cycles)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-industry fa-2x mb-2 d-block opacity-25"></i>
                                Aucun cycle enregistré.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php
                        $statutsCycle = [
                            'en_cours' => ['label' => 'En cours', 'bg' => '#dbeafe', 'color' => '#1d4ed8'],
                            'termine'  => ['label' => 'Terminé',  'bg' => '#dcfce7', 'color' => '#166534'],
                            'annule'   => ['label' => 'Annulé',   'bg' => '#fee2e2', 'color' => '#991b1b'],
                        ];
                        $typesMachine = [
                            'lavage'    => '🫧',
                            'sechage'   => '💨',
                            'repassage' => '👔',
                            'detachage' => '🧴',
                            'autre'     => '⚙️',
                        ];
                        foreach ($cycles as $c):
                            $sc = $statutsCycle[$c['statut']] ?? ['label' => $c['statut'], 'bg' => '#f1f5f9', 'color' => '#374151'];
                            $emoji = $typesMachine[$c['type_machine']] ?? '⚙️';
                        ?>
                        <tr>
                            <td class="ps-4 fw-bold text-primary">
                                <?= esc($c['reference']) ?>
                            </td>
                            <td>
                                <span class="fw-semibold"><?= $emoji ?> <?= esc($c['machine_nom']) ?></span>
                            </td>
                            <td style="font-size:12px;"><?= esc($c['operateur'] ?? '—') ?></td>
                            <td class="text-center">
                                <span style="background:#eff6ff;color:#1d4ed8;padding:3px 12px;
                                             border-radius:20px;font-size:13px;font-weight:700;">
                                    <?= $c['nb_articles'] ?>
                                </span>
                            </td>
                            <td style="font-size:12px;">
                                <?= $c['date_debut']
                                    ? date('d/m/Y H:i', strtotime($c['date_debut']))
                                    : '—' ?>
                            </td>
                            <td style="font-size:12px;">
                                <?= $c['date_fin']
                                    ? date('d/m/Y H:i', strtotime($c['date_fin']))
                                    : '—' ?>
                            </td>
                            <td class="text-center">
                                <span style="background:<?= $sc['bg'] ?>;color:<?= $sc['color'] ?>;
                                             padding:3px 12px;border-radius:20px;
                                             font-size:11px;font-weight:600;">
                                    <?= $sc['label'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('production/cycles/' . $c['id_cycle']) ?>"
                                   class="btn btn-sm"
                                   style="width:32px;height:32px;border-radius:8px;
                                          background:#f1f5f9;border:1px solid #e2e8f0;"
                                   title="Voir le détail">
                                    <i class="fas fa-eye fa-sm text-secondary"></i>
                                </a>
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

<?= $this->endSection() ?>
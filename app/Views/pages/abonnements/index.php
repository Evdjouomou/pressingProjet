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
            <h4 class="fw-bold mb-0">Abonnements</h4>
            <small class="text-muted"><?= count($abonnements) ?> abonnement(s)</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('abonnements/offres') ?>"
               class="btn btn-outline-primary rounded-2 px-3">
                <i class="fas fa-tags me-2"></i>Gérer les offres
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3"
                 style="border-left:4px solid #10b981 !important;">
                <div class="fw-bold fs-3 text-success"><?= $stats['actifs'] ?></div>
                <div class="text-muted small">Actifs</div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3"
                 style="border-left:4px solid #94a3b8 !important;">
                <div class="fw-bold fs-3 text-secondary"><?= $stats['expires'] ?></div>
                <div class="text-muted small">Expirés</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3"
                 style="border-left:4px solid #3b82f6 !important;">
                <div class="fw-bold fs-3 text-primary">
                    <?= number_format($stats['ca_total'], 0, ',', ' ') ?>
                </div>
                <div class="text-muted small">CA abonnements (FCFA)</div>
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
                                style="font-size:11px;letter-spacing:.5px;">RÉFÉRENCE</th>
                            <th class="text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">CLIENT</th>
                            <th class="text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">OFFRE</th>
                            <th class="text-center text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">ARTICLES</th>
                            <th class="text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">VALIDITÉ</th>
                            <th class="text-end text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">MONTANT</th>
                            <th class="text-center text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">STATUT</th>
                            <th class="text-center text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($abonnements)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-id-card fa-2x mb-2 d-block opacity-25"></i>
                                Aucun abonnement enregistré.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php
                        $statuts = [
                            'actif'    => ['label' => 'Actif',    'bg' => '#dcfce7', 'color' => '#166534'],
                            'expire'   => ['label' => 'Expiré',   'bg' => '#f1f5f9', 'color' => '#374151'],
                            'annule'   => ['label' => 'Annulé',   'bg' => '#fee2e2', 'color' => '#991b1b'],
                            'suspendu' => ['label' => 'Suspendu', 'bg' => '#fef3c7', 'color' => '#92400e'],
                        ];
                        foreach ($abonnements as $a):
                            $sa = $statuts[$a['statut']]
                                ?? ['label' => $a['statut'], 'bg' => '#f1f5f9', 'color' => '#374151'];

                            $jRestants = $a['statut'] === 'actif'
                                ? max(0, (new DateTime())->diff(new DateTime($a['date_fin']))->days)
                                : 0;

                            $pctUtilise = $a['nb_articles_total'] > 0
                                ? round(($a['nb_articles_utilises'] / $a['nb_articles_total']) * 100)
                                : 0;
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-primary" style="font-size:13px;">
                                    <?= esc($a['code_abonnement']) ?>
                                </div>
                                <div class="text-muted" style="font-size:11px;">
                                    Par <?= esc($a['enregistre_par'] ?? '—') ?>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= esc($a['nomclient']) ?></div>
                                <div class="text-muted" style="font-size:11px;">
                                    <?= esc($a['telephone']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= esc($a['offre_nom']) ?></div>
                                <div class="text-muted" style="font-size:11px;">
                                    <?= $a['offre_articles'] ?> articles / période
                                </div>
                            </td>
                            <td class="text-center">
                                <div style="font-size:12px;font-weight:600;">
                                    <span class="text-success">
                                        <?= $a['nb_articles_restants'] ?>
                                    </span>
                                    <span class="text-muted">/</span>
                                    <span><?= $a['nb_articles_total'] ?></span>
                                </div>
                                <div class="progress mt-1"
                                     style="height:4px;border-radius:20px;">
                                    <div class="progress-bar
                                         <?= $pctUtilise > 80 ? 'bg-danger' : 'bg-success' ?>"
                                         style="width:<?= $pctUtilise ?>%;
                                                border-radius:20px;">
                                    </div>
                                </div>
                                <div class="text-muted" style="font-size:10px;">
                                    <?= $a['nb_articles_utilises'] ?> utilisés
                                </div>
                            </td>
                            <td>
                                <div style="font-size:12px;">
                                    <?= date('d/m/Y', strtotime($a['date_debut'])) ?>
                                    →
                                    <?= date('d/m/Y', strtotime($a['date_fin'])) ?>
                                </div>
                                <?php if ($a['statut'] === 'actif'): ?>
                                <div style="font-size:11px;
                                    color:<?= $jRestants <= 5 ? '#dc2626' : '#059669' ?>;">
                                    <?= $jRestants <= 5 ? '⚠ ' : '' ?>
                                    <?= $jRestants ?> jour(s) restant(s)
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-bold">
                                <?= number_format($a['montant_paye'], 0, ',', ' ') ?> FCFA
                            </td>
                            <td class="text-center">
                                <span style="background:<?= $sa['bg'] ?>;
                                             color:<?= $sa['color'] ?>;
                                             padding:3px 12px;border-radius:20px;
                                             font-size:11px;font-weight:600;">
                                    <?= $sa['label'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('abonnements/' . $a['id_abonnement']) ?>"
                                   class="btn btn-sm"
                                   style="width:32px;height:32px;border-radius:8px;
                                          background:#f1f5f9;border:1px solid #e2e8f0;"
                                   title="Voir">
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
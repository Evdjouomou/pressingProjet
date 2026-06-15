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
            <h4 class="fw-bold mb-0">
                <i class="fas fa-truck text-primary me-2"></i>Livraisons
            </h4>
            <small class="text-muted"><?= count($livraisons) ?> livraison(s)</small>
        </div>
        <div>
            <a href="<?= base_url('depot/prets') ?>" class="btn btn-outline-secondary rounded-2 px-3">
                <i class="fas fa-arrow-left me-2"></i>Commandes prêtes
            </a>
            <a href="<?= base_url('livreurs') ?>" class="btn btn-primary rounded-2 px-3">
                <i class="fas fa-user-friends me-2"></i>livreurs
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <?php
        $statsCards = [
            ['label' => 'En attente',  'val' => $stats['en_attente'], 'color' => '#92400e', 'bg' => '#fef3c7'],
            ['label' => 'Assignées',   'val' => $stats['assignee'],   'color' => '#1d4ed8', 'bg' => '#eff6ff'],
            ['label' => 'En cours',    'val' => $stats['en_cours'],   'color' => '#0e7490', 'bg' => '#ecfeff'],
            ['label' => 'Livrées',     'val' => $stats['livree'],     'color' => '#166534', 'bg' => '#dcfce7'],
        ];
        foreach ($statsCards as $sc): ?>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3" style="color:<?= $sc['color'] ?>;"><?= $sc['val'] ?></div>
                <div class="text-muted small"><?= $sc['label'] ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Filtres -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body py-3 d-flex gap-2 flex-wrap">
            <?php
            $filtres = [
                ''           => 'Toutes',
                'en_attente' => 'En attente',
                'assignee'   => 'Assignées',
                'en_cours'   => 'En cours',
                'livree'     => 'Livrées',
                'echec'      => 'Échec',
                'annulee'    => 'Annulées',
            ];
            foreach ($filtres as $val => $label): ?>
            <a href="<?= base_url('livraison?statut=' . $val) ?>"
               class="btn btn-sm rounded-2 <?= $statut === $val ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <?= $label ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">LIVRAISON</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">CLIENT</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">ADRESSE</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">DATE / HEURE</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">LIVREUR</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">FRAIS</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">STATUT</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($livraisons)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-truck fa-2x mb-2 d-block opacity-25"></i>
                                Aucune livraison trouvée.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php
                        $statutsLiv = [
                            'en_attente' => ['label' => 'En attente', 'bg' => '#fef3c7', 'color' => '#92400e'],
                            'assignee'   => ['label' => 'Assignée',   'bg' => '#dbeafe', 'color' => '#1d4ed8'],
                            'en_cours'   => ['label' => 'En cours',   'bg' => '#ecfeff', 'color' => '#0e7490'],
                            'livree'     => ['label' => 'Livrée',     'bg' => '#dcfce7', 'color' => '#166534'],
                            'echec'      => ['label' => 'Échec',      'bg' => '#fee2e2', 'color' => '#991b1b'],
                            'annulee'    => ['label' => 'Annulée',    'bg' => '#f1f5f9', 'color' => '#374151'],
                        ];
                        foreach ($livraisons as $l):
                            $sl = $statutsLiv[$l['statut']] ?? ['label' => $l['statut'], 'bg' => '#f1f5f9', 'color' => '#374151'];
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-primary"><?= esc($l['code_livraison']) ?></div>
                                <div class="text-muted" style="font-size:11px;">
                                    <a href="<?= base_url('depot/detail/' . $l['depot_id']) ?>"
                                       class="text-muted">
                                        <?= esc($l['code_commande']) ?>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= esc($l['nomclient']) ?></div>
                                <div class="text-muted" style="font-size:11px;">
                                    <i class="fas fa-phone me-1"></i><?= esc($l['telephone']) ?>
                                </div>
                            </td>
                            <td style="font-size:12px;max-width:180px;">
                                <span class="d-block text-truncate"><?= esc($l['adresse_livraison']) ?></span>
                            </td>
                            <td style="font-size:12px;">
                                <?php if ($l['date_livraison']): ?>
                                    <div class="fw-semibold">
                                        <?= date('d/m/Y', strtotime($l['date_livraison'])) ?>
                                    </div>
                                    <?php if ($l['heure_livraison']): ?>
                                    <div class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        <?= substr($l['heure_livraison'], 0, 5) ?>
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">À planifier</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px;">
                                <?php if ($l['livreur_nom']): ?>
                                    <div class="fw-semibold"><?= esc($l['livreur_nom']) ?></div>
                                <?php else: ?>
                                    <button class="btn btn-xs btn-outline-warning btn-sm rounded-2"
                                            style="font-size:11px;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalAssigner<?= $l['id_livraison'] ?>">
                                        <i class="fas fa-user-plus me-1"></i>Assigner
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-bold">
                                <?= $l['montant_livraison'] > 0
                                    ? number_format($l['montant_livraison'], 0, ',', ' ') . ' FCFA'
                                    : '—' ?>
                            </td>
                            <td class="text-center">
                                <span style="background:<?= $sl['bg'] ?>;color:<?= $sl['color'] ?>;
                                             padding:3px 12px;border-radius:20px;
                                             font-size:11px;font-weight:600;">
                                    <?= $sl['label'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('livraison/' . $l['id_livraison']) ?>"
                                   class="btn btn-sm me-1"
                                   style="width:32px;height:32px;border-radius:8px;
                                          background:#f1f5f9;border:1px solid #e2e8f0;"
                                   title="Voir détail">
                                    <i class="fas fa-eye fa-sm text-secondary"></i>
                                </a>
                                <a href="<?= base_url('livraison/fiche/' . $l['id_livraison']) ?>"
                                   target="_blank"
                                   class="btn btn-sm"
                                   style="width:32px;height:32px;border-radius:8px;
                                          background:#eff6ff;border:1px solid #bfdbfe;"
                                   title="Fiche livreur">
                                    <i class="fas fa-print fa-sm text-primary"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Modal assigner livreur -->
                        <div class="modal fade" id="modalAssigner<?= $l['id_livraison'] ?>"
                             tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
                                <div class="modal-content border-0 shadow-lg rounded-4">
                                    <div class="modal-header border-0 px-4 pt-4 pb-0">
                                        <h5 class="fw-bold mb-0">Assigner un livreur</h5>
                                        <button class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('livraison/assigner/' . $l['id_livraison']) ?>"
                                          method="POST">
                                        <?= csrf_field() ?>
                                        <div class="modal-body px-4 py-3">
                                            <div class="rounded-3 p-3 mb-3"
                                                 style="background:#f8fafc;border:1px solid #e2e8f0;">
                                                <div class="fw-semibold"><?= esc($l['nomclient']) ?></div>
                                                <div class="text-muted small"><?= esc($l['adresse_livraison']) ?></div>
                                                <?php if ($l['date_livraison']): ?>
                                                <div class="text-primary small mt-1">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?= date('d/m/Y', strtotime($l['date_livraison'])) ?>
                                                    <?= $l['heure_livraison'] ? ' à ' . substr($l['heure_livraison'],0,5) : '' ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <label class="form-label fw-semibold small">
                                                Livreur <span class="text-danger">*</span>
                                            </label>
                                            <select name="livreur_id" class="form-select" required>
                                                <option value="" disabled selected>Choisir un livreur...</option>
                                                <?php foreach ($livreurs as $lv): ?>
                                                <option value="<?= $lv['id_livreur'] ?>">
                                                    <?= esc($lv['nom_complet']) ?>
                                                    <?= $lv['vehicule'] ? ' — ' . esc($lv['vehicule']) : '' ?>
                                                    <?= $lv['zone_livraison'] ? ' (' . esc($lv['zone_livraison']) . ')' : '' ?>
                                                    — <?= esc($lv['telephone']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="modal-footer border-0 px-4 pb-4 pt-0">
                                            <button type="button" class="btn btn-light rounded-2"
                                                    data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-primary px-4 rounded-2">
                                                <i class="fas fa-user-check me-2"></i>Assigner
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
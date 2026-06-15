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
    $statuts = [
        'actif'    => ['label' => 'Actif',    'bg' => '#dcfce7', 'color' => '#166534'],
        'expire'   => ['label' => 'Expiré',   'bg' => '#f1f5f9', 'color' => '#374151'],
        'annule'   => ['label' => 'Annulé',   'bg' => '#fee2e2', 'color' => '#991b1b'],
        'suspendu' => ['label' => 'Suspendu', 'bg' => '#fef3c7', 'color' => '#92400e'],
    ];
    $sa = $statuts[$abon['statut']] ?? ['label'=>$abon['statut'],'bg'=>'#f1f5f9','color'=>'#374151'];
    $pctUtilise = $abon['nb_articles_total'] > 0
        ? round(($abon['nb_articles_utilises'] / $abon['nb_articles_total']) * 100)
        : 0;
    $jRestants = $abon['statut'] === 'actif'
        ? max(0, (new DateTime())->diff(new DateTime($abon['date_fin']))->days)
        : 0;
    ?>

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <a href="<?= base_url('abonnements') ?>"
               class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
            <h4 class="fw-bold mb-0">
                Abonnement <span class="text-primary">
                    <?= esc($abon['code_abonnement']) ?>
                </span>
            </h4>
            <small class="text-muted">
                <?= esc($abon['nomclient']) ?> —
                Souscrit le <?= date('d/m/Y', strtotime($abon['created_at'])) ?>
            </small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <span style="background:<?= $sa['bg'] ?>;color:<?= $sa['color'] ?>;
                         padding:6px 16px;border-radius:20px;font-size:13px;font-weight:600;">
                <?= $sa['label'] ?>
            </span>
            <?php if ($abon['statut'] === 'actif'): ?>
            <a href="<?= base_url('abonnements/nouveau/' . $abon['client_id']) ?>"
               class="btn btn-primary rounded-2 px-3">
                <i class="fas fa-redo me-2"></i>Renouveler
            </a>
            <a href="<?= base_url('abonnements/annuler/' . $abon['id_abonnement']) ?>"
               class="btn btn-outline-danger rounded-2 px-3"
               onclick="return confirm('Annuler cet abonnement ?')">
                <i class="fas fa-times me-2"></i>Annuler
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">

        <!-- Infos -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body">
                    <p class="text-uppercase text-muted fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-user me-2"></i>Client
                    </p>
                    <h5 class="fw-bold mb-1"><?= esc($abon['nomclient']) ?></h5>
                    <p class="text-muted mb-3">
                        <i class="fas fa-phone me-1"></i><?= esc($abon['telephone']) ?>
                    </p>

                    <hr>

                    <p class="text-uppercase text-muted fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-tags me-2"></i>Offre
                    </p>
                    <div class="fw-bold mb-1"><?= esc($abon['offre_nom']) ?></div>
                    <?php if ($abon['offre_description']): ?>
                    <p class="text-muted small"><?= esc($abon['offre_description']) ?></p>
                    <?php endif; ?>

                    <hr>

                    <p class="text-uppercase text-muted fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-calendar me-2"></i>Validité
                    </p>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Début</span>
                        <strong><?= date('d/m/Y', strtotime($abon['date_debut'])) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Fin</span>
                        <strong><?= date('d/m/Y', strtotime($abon['date_fin'])) ?></strong>
                    </div>
                    <?php if ($abon['statut'] === 'actif'): ?>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Jours restants</span>
                        <strong style="color:<?= $jRestants <= 5 ? '#dc2626' : '#059669' ?>;">
                            <?= $jRestants <= 5 ? '⚠ ' : '' ?><?= $jRestants ?> jour(s)
                        </strong>
                    </div>
                    <?php endif; ?>

                    <hr>

                    <!-- Utilisation articles -->
                    <p class="text-uppercase text-muted fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-tshirt me-2"></i>Utilisation articles
                    </p>
                    <div class="d-flex justify-content-between mb-1" style="font-size:13px;">
                        <span class="text-muted small">Utilisés</span>
                        <strong><?= $abon['nb_articles_utilises'] ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2" style="font-size:13px;">
                        <span class="text-muted small">Restants</span>
                        <strong class="text-success">
                            <?= $abon['nb_articles_restants'] ?>
                        </strong>
                    </div>
                    <div class="progress mb-1" style="height:8px;border-radius:20px;">
                        <div class="progress-bar
                                    <?= $pctUtilise > 80 ? 'bg-danger' : 'bg-success' ?>"
                             style="width:<?= $pctUtilise ?>%;border-radius:20px;">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between text-muted"
                         style="font-size:10px;">
                        <span>0</span>
                        <span><?= $pctUtilise ?>% utilisé</span>
                        <span><?= $abon['nb_articles_total'] ?></span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Montant payé</span>
                        <strong class="text-primary fs-6">
                            <?= number_format($abon['montant_paye'], 0, ',', ' ') ?> FCFA
                        </strong>
                    </div>

                    <?php if ($abon['note']): ?>
                    <div class="mt-3 rounded-2 p-2"
                         style="background:#fffbeb;border:1px solid #fde68a;
                                font-size:12px;color:#92400e;">
                        <i class="fas fa-info-circle me-1"></i>
                        <?= esc($abon['note']) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Dépôts liés -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                        <p class="text-uppercase text-muted fw-semibold mb-0"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-inbox me-2"></i>Dépôts couverts par cet abonnement
                        </p>
                        <span class="badge bg-primary rounded-pill">
                            <?= count($depots) ?>
                        </span>
                    </div>

                    <?php if (empty($depots)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                        <span class="small">Aucun dépôt enregistré sur cet abonnement.</span>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 text-muted fw-semibold"
                                        style="font-size:11px;">BON</th>
                                    <th class="text-muted fw-semibold"
                                        style="font-size:11px;">DATE</th>
                                    <th class="text-center text-muted fw-semibold"
                                        style="font-size:11px;">ARTICLES</th>
                                    <th class="text-center text-muted fw-semibold"
                                        style="font-size:11px;">STATUT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $statutsDepot = [
                                    'depot'    => ['label'=>'Reçu',    'bg'=>'#f1f5f9','color'=>'#374151'],
                                    'en_cours' => ['label'=>'En cours','bg'=>'#fef3c7','color'=>'#92400e'],
                                    'pret'     => ['label'=>'Prêt',    'bg'=>'#d1fae5','color'=>'#065f46'],
                                    'livre'    => ['label'=>'Livré',   'bg'=>'#dcfce7','color'=>'#166534'],
                                    'annule'   => ['label'=>'Annulé',  'bg'=>'#fee2e2','color'=>'#991b1b'],
                                ];
                                foreach ($depots as $d):
                                    $sd = $statutsDepot[$d['statut_global']]
                                        ?? ['label'=>$d['statut_global'],'bg'=>'#f1f5f9','color'=>'#374151'];
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">
                                        <?= esc($d['code_commande']) ?>
                                    </td>
                                    <td style="font-size:12px;">
                                        <?= date('d/m/Y H:i', strtotime($d['created_at'])) ?>
                                    </td>
                                    <td class="text-center">
                                        <span style="background:#eff6ff;color:#1d4ed8;
                                                     padding:2px 10px;border-radius:20px;
                                                     font-size:12px;font-weight:600;">
                                            <?= $d['nb_articles'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span style="background:<?= $sd['bg'] ?>;
                                                     color:<?= $sd['color'] ?>;
                                                     padding:3px 10px;border-radius:20px;
                                                     font-size:11px;font-weight:600;">
                                            <?= $sd['label'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
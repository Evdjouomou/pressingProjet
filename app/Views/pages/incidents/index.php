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
            <h4 class="fw-bold mb-0">Incidents</h4>
            <small class="text-muted"><?= count($incidents) ?> incident(s)</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('retouches') ?>" class="btn btn-outline-secondary rounded-2 px-3">
                <i class="fas fa-scissors me-2"></i>Retouches
            </a>
            <a href="<?= base_url('incidents/nouveau') ?>" class="btn btn-danger rounded-2 px-4">
                <i class="fas fa-plus me-2"></i>Déclarer un incident
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3"
                 style="border-left:4px solid #dc2626 !important;">
                <div class="fw-bold fs-3 text-danger"><?= $stats['ouvert'] ?></div>
                <div class="text-muted small">Ouverts</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3"
                 style="border-left:4px solid #f59e0b !important;">
                <div class="fw-bold fs-3 text-warning"><?= $stats['en_traitement'] ?></div>
                <div class="text-muted small">En traitement</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3"
                 style="border-left:4px solid #7e22ce !important;">
                <div class="fw-bold fs-3" style="color:#7e22ce;"><?= $stats['critique'] ?></div>
                <div class="text-muted small">Critiques</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-dark"><?= count($incidents) ?></div>
                <div class="text-muted small">Total</div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body py-3 d-flex gap-2 flex-wrap">
            <?php
            $filtres = [
                ''              => 'Tous',
                'ouvert'        => 'Ouverts',
                'en_traitement' => 'En traitement',
                'resolu'        => 'Résolus',
                'cloture'       => 'Clôturés',
            ];
            foreach ($filtres as $val => $label): ?>
            <a href="<?= base_url('incidents?statut=' . $val) ?>"
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
                            <th class="ps-4 text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">RÉFÉRENCE</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">CLIENT</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">TYPE</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">GRAVITÉ</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">RESPONSABLE</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">DÉLAI</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">STATUT</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($incidents)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-check-circle fa-2x mb-2 d-block text-success opacity-50"></i>
                                Aucun incident trouvé.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php
                        $typesInc = [
                            'article_endommage'  => '💔 Article endommagé',
                            'article_perdu'      => '❓ Article perdu',
                            'retard'             => '⏰ Retard',
                            'qualite_insuffisante'=> '👎 Qualité insuffisante',
                            'mauvais_traitement' => '⚠️ Mauvais traitement',
                            'autre'              => '📋 Autre',
                        ];
                        $gravites = [
                            'faible'   => ['label' => 'Faible',   'bg' => '#f0fdf4', 'color' => '#166534'],
                            'moyen'    => ['label' => 'Moyen',    'bg' => '#fef3c7', 'color' => '#92400e'],
                            'eleve'    => ['label' => 'Élevé',    'bg' => '#fff7ed', 'color' => '#c2410c'],
                            'critique' => ['label' => 'Critique', 'bg' => '#fdf4ff', 'color' => '#7e22ce'],
                        ];
                        $statutsInc = [
                            'ouvert'        => ['label' => 'Ouvert',        'bg' => '#fee2e2', 'color' => '#991b1b'],
                            'en_traitement' => ['label' => 'En traitement', 'bg' => '#fef3c7', 'color' => '#92400e'],
                            'resolu'        => ['label' => 'Résolu',        'bg' => '#d1fae5', 'color' => '#065f46'],
                            'cloture'       => ['label' => 'Clôturé',      'bg' => '#f1f5f9', 'color' => '#374151'],
                        ];
                        foreach ($incidents as $inc):
                            $sg  = $gravites[$inc['gravite']]    ?? ['label' => $inc['gravite'],  'bg' => '#f1f5f9', 'color' => '#374151'];
                            $si  = $statutsInc[$inc['statut']]   ?? ['label' => $inc['statut'],   'bg' => '#f1f5f9', 'color' => '#374151'];
                            $retard = $inc['delai_resolution']
                                && new DateTime($inc['delai_resolution']) < new DateTime()
                                && !in_array($inc['statut'], ['resolu','cloture']);
                        ?>
                        <tr style="<?= $inc['gravite'] === 'critique' ? 'background:#fdf4ff;' : ($retard ? 'background:#fffbeb;' : '') ?>">
                            <td class="ps-4">
                                <div class="fw-bold text-danger"><?= esc($inc['code_incident']) ?></div>
                                <?php if ($inc['code_commande']): ?>
                                <div class="text-muted" style="font-size:11px;">
                                    Dépôt : <?= esc($inc['code_commande']) ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= esc($inc['nomclient']) ?></div>
                                <div class="text-muted" style="font-size:11px;"><?= esc($inc['telephone']) ?></div>
                            </td>
                            <td style="font-size:12px;">
                                <?= $typesInc[$inc['type_incident']] ?? $inc['type_incident'] ?>
                                <?php if ($inc['article_nom']): ?>
                                <div class="text-muted" style="font-size:11px;"><?= esc($inc['article_nom']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span style="background:<?= $sg['bg'] ?>;color:<?= $sg['color'] ?>;
                                             padding:3px 10px;border-radius:20px;
                                             font-size:11px;font-weight:600;">
                                    <?= $sg['label'] ?>
                                </span>
                            </td>
                            <td style="font-size:12px;"><?= esc($inc['responsable'] ?? '—') ?></td>
                            <td>
                                <?php if ($inc['delai_resolution']): ?>
                                <span style="color:<?= $retard ? '#dc2626' : '#059669' ?>;font-size:12px;">
                                    <?= $retard ? '⚠ ' : '' ?>
                                    <?= date('d/m/Y', strtotime($inc['delai_resolution'])) ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted" style="font-size:12px;">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span style="background:<?= $si['bg'] ?>;color:<?= $si['color'] ?>;
                                             padding:3px 10px;border-radius:20px;
                                             font-size:11px;font-weight:600;">
                                    <?= $si['label'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('incidents/' . $inc['id_incident']) ?>"
                                   class="btn btn-sm"
                                   style="width:32px;height:32px;border-radius:8px;
                                          background:#f1f5f9;border:1px solid #e2e8f0;">
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
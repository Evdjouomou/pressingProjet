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
            <h4 class="fw-bold mb-0">Retouches</h4>
            <small class="text-muted"><?= count($retouches) ?> retouche(s)</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('incidents') ?>" class="btn btn-outline-danger rounded-2 px-3">
                <i class="fas fa-exclamation-triangle me-2"></i>Incidents
            </a>
            <a href="<?= base_url('retouches/nouvelle') ?>" class="btn btn-primary rounded-2 px-4">
                <i class="fas fa-plus me-2"></i>Nouvelle retouche
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-warning"><?= $stats['en_attente'] ?></div>
                <div class="text-muted small">En attente</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-primary"><?= $stats['en_cours'] ?></div>
                <div class="text-muted small">En cours</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-success"><?= $stats['fait'] ?></div>
                <div class="text-muted small">Terminées</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-dark"><?= count($retouches) ?></div>
                <div class="text-muted small">Total</div>
            </div>
        </div>
    </div>

    <!-- Filtres statut -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body py-3 d-flex gap-2 flex-wrap">
            <?php
            $filtres = [
                ''           => ['label' => 'Toutes',      'class' => 'btn-outline-secondary'],
                'en_attente' => ['label' => 'En attente',  'class' => 'btn-outline-warning'],
                'en_cours'   => ['label' => 'En cours',    'class' => 'btn-outline-primary'],
                'fait'       => ['label' => 'Faites',      'class' => 'btn-outline-success'],
                'livre'      => ['label' => 'Livrées',     'class' => 'btn-outline-dark'],
                'annule'     => ['label' => 'Annulées',    'class' => 'btn-outline-danger'],
            ];
            foreach ($filtres as $val => $f): ?>
            <a href="<?= base_url('retouches?statut=' . $val) ?>"
               class="btn btn-sm rounded-2 <?= $statut === $val ? str_replace('outline-', '', $f['class']) . ' text-white' : $f['class'] ?>">
                <?= $f['label'] ?>
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
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">RETOUCHEUR</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">PRIX</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">DÉLAI</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">STATUT</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($retouches)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-scissors fa-2x mb-2 d-block opacity-25"></i>
                                Aucune retouche trouvée.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php
                        $typesRetouche = [
                            'ourlet'          => '📏 Ourlet',
                            'fermeture_eclair'=> '🔒 Fermeture éclair',
                            'bouton'          => '🔘 Bouton',
                            'couture'         => '🧵 Couture',
                            'teinture'        => '🎨 Teinture',
                            'restauration'    => '✨ Restauration',
                            'broderie'        => '🌸 Broderie',
                            'autre'           => '⚙️ Autre',
                        ];
                        $statutsRet = [
                            'en_attente' => ['label' => 'En attente', 'bg' => '#fef3c7', 'color' => '#92400e'],
                            'en_cours'   => ['label' => 'En cours',   'bg' => '#dbeafe', 'color' => '#1d4ed8'],
                            'fait'       => ['label' => 'Fait',       'bg' => '#d1fae5', 'color' => '#065f46'],
                            'livre'      => ['label' => 'Livré',      'bg' => '#dcfce7', 'color' => '#166534'],
                            'annule'     => ['label' => 'Annulé',     'bg' => '#fee2e2', 'color' => '#991b1b'],
                        ];
                        foreach ($retouches as $r):
                            $sr = $statutsRet[$r['statut']] ?? ['label' => $r['statut'], 'bg' => '#f1f5f9', 'color' => '#374151'];
                            $retard = $r['delai_estime'] && new DateTime($r['delai_estime']) < new DateTime()
                                      && !in_array($r['statut'], ['fait','livre','annule']);
                        ?>
                        <tr style="<?= $retard ? 'background:#fffbeb;' : '' ?>">
                            <td class="ps-4 fw-bold text-primary">
                                <?= esc($r['code_retouche']) ?>
                                <?php if ($r['code_commande']): ?>
                                <div class="text-muted" style="font-size:11px;">
                                    Dépôt : <?= esc($r['code_commande']) ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= esc($r['nomclient']) ?></div>
                                <div class="text-muted" style="font-size:11px;"><?= esc($r['telephone']) ?></div>
                            </td>
                            <td style="font-size:12px;">
                                <?= $typesRetouche[$r['type_retouche']] ?? $r['type_retouche'] ?>
                            </td>
                            <td style="font-size:12px;"><?= esc($r['retoucheur'] ?? '—') ?></td>
                            <td class="text-end fw-bold text-success">
                                <?= number_format($r['prix'], 0, ',', ' ') ?> FCFA
                                <?php if ($r['acompte_verse'] > 0): ?>
                                <div class="text-muted fw-normal" style="font-size:11px;">
                                    Acompte : <?= number_format($r['acompte_verse'], 0, ',', ' ') ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($r['delai_estime']): ?>
                                <span style="color:<?= $retard ? '#dc2626' : '#059669' ?>;font-size:12px;font-weight:500;">
                                    <?= $retard ? '⚠ ' : '' ?>
                                    <?= date('d/m/Y', strtotime($r['delai_estime'])) ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted" style="font-size:12px;">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span style="background:<?= $sr['bg'] ?>;color:<?= $sr['color'] ?>;
                                             padding:3px 12px;border-radius:20px;
                                             font-size:11px;font-weight:600;">
                                    <?= $sr['label'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('retouches/' . $r['id_retouche']) ?>"
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
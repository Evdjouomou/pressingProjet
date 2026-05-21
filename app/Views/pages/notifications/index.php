<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-0">Notifications</h4>
            <small class="text-muted">
                <?php if ($nonLues > 0): ?>
                    <span class="text-danger fw-semibold"><?= $nonLues ?> non lue(s)</span>
                <?php else: ?>
                    Tout est à jour
                <?php endif; ?>
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('campagnes') ?>" class="btn btn-outline-primary rounded-2 px-3">
                <i class="fas fa-bullhorn me-2"></i>Campagnes
            </a>
            <?php if ($nonLues > 0): ?>
            <form action="<?= base_url('notifications/lire-tout') ?>" method="POST">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-secondary rounded-2 px-3">
                    <i class="fas fa-check-double me-2"></i>Tout marquer lu
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stats canaux -->
    <?php
        $parCanal = ['interne' => 0, 'email' => 0, 'sms' => 0];
        $parStatut = ['envoye' => 0, 'echec' => 0, 'en_attente' => 0];
        foreach ($notifications as $n) {
            if (isset($parCanal[$n['canal']])) $parCanal[$n['canal']]++;
            if (isset($parStatut[$n['statut']])) $parStatut[$n['statut']]++;
        }
    ?>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-primary"><?= count($notifications) ?></div>
                <div class="text-muted small">Total</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-success"><?= $parStatut['envoye'] ?></div>
                <div class="text-muted small">Envoyées</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-danger"><?= $parStatut['echec'] ?></div>
                <div class="text-muted small">Échouées</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-warning"><?= $nonLues ?></div>
                <div class="text-muted small">Non lues</div>
            </div>
        </div>
    </div>

    <!-- Tableau notifications -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">

            <?php if (empty($notifications)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-bell-slash fa-3x mb-3 d-block opacity-25"></i>
                    Aucune notification enregistrée.
                </div>
            <?php else: ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">CLIENT</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">TYPE</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">SUJET</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">CANAL</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">STATUT</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">DATE</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $icones = [
                            'depot_confirme'   => ['icon' => 'fa-box',          'bg' => '#eff6ff', 'color' => '#1d4ed8', 'label' => 'Dépôt confirmé'],
                            'commande_prete'   => ['icon' => 'fa-check-circle',  'bg' => '#f0fdf4', 'color' => '#166534', 'label' => 'Commande prête'],
                            'rappel_retrait'   => ['icon' => 'fa-clock',         'bg' => '#fefce8', 'color' => '#854d0e', 'label' => 'Rappel retrait'],
                            'retrait_confirme' => ['icon' => 'fa-handshake',     'bg' => '#f0fdf4', 'color' => '#166534', 'label' => 'Retrait confirmé'],
                            'campagne'         => ['icon' => 'fa-bullhorn',      'bg' => '#fdf4ff', 'color' => '#7e22ce', 'label' => 'Campagne'],
                        ];
                        $canauxBadge = [
                            'interne' => ['label' => 'App',   'bg' => '#f1f5f9', 'color' => '#475569'],
                            'email'   => ['label' => 'Email', 'bg' => '#eff6ff', 'color' => '#1d4ed8'],
                            'sms'     => ['label' => 'SMS',   'bg' => '#f0fdf4', 'color' => '#166534'],
                        ];
                        $statutBadge = [
                            'envoye'     => ['label' => 'Envoyé',     'bg' => '#dcfce7', 'color' => '#166534'],
                            'echec'      => ['label' => 'Échec',      'bg' => '#fee2e2', 'color' => '#991b1b'],
                            'en_attente' => ['label' => 'En attente', 'bg' => '#fef3c7', 'color' => '#92400e'],
                        ];
                        foreach ($notifications as $n):
                            $ic  = $icones[$n['type']]      ?? ['icon' => 'fa-bell', 'bg' => '#f1f5f9', 'color' => '#475569', 'label' => $n['type']];
                            $cb  = $canauxBadge[$n['canal']] ?? ['label' => $n['canal'], 'bg' => '#f1f5f9', 'color' => '#374151'];
                            $sb  = $statutBadge[$n['statut']] ?? ['label' => $n['statut'], 'bg' => '#f1f5f9', 'color' => '#374151'];
                            $nonLue = !$n['lu'] && $n['canal'] === 'interne';
                        ?>
                        <tr style="<?= $nonLue ? 'background:#fffbeb;' : '' ?>">

                            <!-- Client -->
                            <td class="ps-4">
                                <div class="fw-semibold" style="font-size:13px;">
                                    <?= $nonLue ? '<span class="me-1" style="color:#f59e0b;">●</span>' : '' ?>
                                    <?= esc($n['nomclient']) ?>
                                </div>
                                <?php if ($n['code_commande']): ?>
                                    <div class="text-primary" style="font-size:11px;"><?= esc($n['code_commande']) ?></div>
                                <?php endif; ?>
                            </td>

                            <!-- Type -->
                            <td>
                                <span style="background:<?= $ic['bg'] ?>;color:<?= $ic['color'] ?>;
                                             padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                    <i class="fas <?= $ic['icon'] ?> me-1"></i><?= $ic['label'] ?>
                                </span>
                            </td>

                            <!-- Sujet -->
                            <td style="max-width:220px;">
                                <span style="font-size:12px;" class="text-truncate d-block">
                                    <?= esc($n['sujet'] ?? '—') ?>
                                </span>
                            </td>

                            <!-- Canal -->
                            <td class="text-center">
                                <span style="background:<?= $cb['bg'] ?>;color:<?= $cb['color'] ?>;
                                             padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                    <?= $cb['label'] ?>
                                </span>
                            </td>

                            <!-- Statut -->
                            <td class="text-center">
                                <span style="background:<?= $sb['bg'] ?>;color:<?= $sb['color'] ?>;
                                             padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                    <?= $sb['label'] ?>
                                </span>
                                <?php if ($n['statut'] === 'echec' && $n['erreur_detail']): ?>
                                    <button class="btn btn-link p-0 ms-1" style="font-size:10px;color:#dc2626;"
                                            data-bs-toggle="tooltip"
                                            title="<?= esc(substr($n['erreur_detail'], 0, 100)) ?>">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                <?php endif; ?>
                            </td>

                            <!-- Date -->
                            <td style="font-size:12px;color:#6b7280;">
                                <?= $n['date_envoi']
                                    ? date('d/m/Y H:i', strtotime($n['date_envoi']))
                                    : date('d/m/Y H:i', strtotime($n['created_at'])) ?>
                            </td>

                            <!-- Action -->
                            <td class="text-center">
                                <?php if ($nonLue): ?>
                                <button onclick="marquerLu(<?= $n['id_notification'] ?>, this)"
                                        class="btn btn-sm"
                                        style="width:32px;height:32px;border-radius:8px;
                                               background:#fef3c7;border:1px solid #fde68a;"
                                        title="Marquer comme lu">
                                    <i class="fas fa-check fa-sm" style="color:#92400e;"></i>
                                </button>
                                <?php else: ?>
                                <button class="btn btn-sm"
                                        style="width:32px;height:32px;border-radius:8px;
                                               background:#f1f5f9;border:1px solid #e2e8f0;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalMessage<?= $n['id_notification'] ?>"
                                        title="Voir le message">
                                    <i class="fas fa-eye fa-sm text-secondary"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Modal message complet -->
                        <div class="modal fade" id="modalMessage<?= $n['id_notification'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
                                <div class="modal-content border-0 shadow-lg rounded-4">
                                    <div class="modal-header border-0 px-4 pt-4 pb-0">
                                        <div>
                                            <h5 class="fw-bold mb-0"><?= esc($n['sujet'] ?? 'Message') ?></h5>
                                            <small class="text-muted"><?= esc($n['nomclient']) ?> · <?= $ic['label'] ?></small>
                                        </div>
                                        <button class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body px-4 pb-4">
                                        <div style="background:#f8fafc;border-radius:10px;padding:16px;font-size:13px;line-height:1.7;">
                                            <?= $n['message'] ?>
                                        </div>
                                        <div class="d-flex gap-2 mt-3" style="font-size:11px;color:#9ca3af;">
                                            <span>Canal : <?= $cb['label'] ?></span>
                                            <span>·</span>
                                            <span>Statut : <?= $sb['label'] ?></span>
                                            <span>·</span>
                                            <span><?= date('d/m/Y H:i', strtotime($n['created_at'])) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function marquerLu(id, btn) {
    fetch(`<?= base_url('notifications/lire/') ?>${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: '<?= csrf_token() ?>=<?= csrf_hash() ?>'
    }).then(() => {
        const row = btn.closest('tr');
        row.style.background = '';
        btn.outerHTML = '<span style="color:#9ca3af;font-size:11px;">Lu</span>';
    });
}

// Tooltips Bootstrap
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el);
});
</script>

<?= $this->endSection() ?>
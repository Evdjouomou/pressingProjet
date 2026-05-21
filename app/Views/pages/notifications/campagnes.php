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

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-0">Campagnes de communication</h4>
            <small class="text-muted"><?= count($campagnes) ?> campagne(s) créée(s)</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('notifications') ?>" class="btn btn-outline-secondary rounded-2 px-3">
                <i class="fas fa-bell me-2"></i>Notifications
            </a>
            <button class="btn btn-primary rounded-2 px-4"
                    data-bs-toggle="modal" data-bs-target="#modalNouvelleCampagne">
                <i class="fas fa-plus me-2"></i>Nouvelle campagne
            </button>
        </div>
    </div>

    <!-- Liste campagnes -->
    <?php if (empty($campagnes)): ?>
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body text-center py-5 text-muted">
                <i class="fas fa-bullhorn fa-3x mb-3 d-block opacity-25"></i>
                Aucune campagne créée. Commencez par en créer une !
            </div>
        </div>
    <?php else: ?>
    <div class="row g-4">
        <?php
        $cibleLabels = [
            'tous'        => ['label' => 'Tous les clients',    'icon' => 'fa-users',       'color' => '#1d4ed8'],
            'inactifs'    => ['label' => 'Clients inactifs',    'icon' => 'fa-user-clock',  'color' => '#92400e'],
            'anniversaire'=> ['label' => 'Anniversaires',       'icon' => 'fa-birthday-cake','color' => '#7e22ce'],
            'manuel'      => ['label' => 'Sélection manuelle',  'icon' => 'fa-hand-pointer','color' => '#065f46'],
        ];
        $canalLabels = [
            'interne' => ['label' => 'App',        'bg' => '#f1f5f9', 'color' => '#475569'],
            'email'   => ['label' => 'Email',      'bg' => '#eff6ff', 'color' => '#1d4ed8'],
            'sms'     => ['label' => 'SMS',        'bg' => '#f0fdf4', 'color' => '#166534'],
            'tous'    => ['label' => 'Tous canaux','bg' => '#fdf4ff', 'color' => '#7e22ce'],
        ];
        $statutCamp = [
            'brouillon' => ['label' => 'Brouillon', 'bg' => '#f1f5f9', 'color' => '#374151'],
            'planifiee' => ['label' => 'Planifiée', 'bg' => '#fef3c7', 'color' => '#92400e'],
            'envoyee'   => ['label' => 'Envoyée',   'bg' => '#dcfce7', 'color' => '#166534'],
        ];
        foreach ($campagnes as $c):
            $cible  = $cibleLabels[$c['type_cible']] ?? ['label' => $c['type_cible'], 'icon' => 'fa-tag', 'color' => '#374151'];
            $canal  = $canalLabels[$c['canal']]      ?? ['label' => $c['canal'],      'bg' => '#f1f5f9',  'color' => '#374151'];
            $statut = $statutCamp[$c['statut']]      ?? ['label' => $c['statut'],     'bg' => '#f1f5f9',  'color' => '#374151'];
        ?>
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body">

                    <!-- Header carte -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="fw-bold mb-0" style="font-size:14px;"><?= esc($c['titre']) ?></h6>
                        <span style="background:<?= $statut['bg'] ?>;color:<?= $statut['color'] ?>;
                                     padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;
                                     white-space:nowrap;">
                            <?= $statut['label'] ?>
                        </span>
                    </div>

                    <!-- Message aperçu -->
                    <p class="text-muted mb-3"
                       style="font-size:12px;line-height:1.6;
                              display:-webkit-box;-webkit-line-clamp:3;
                              -webkit-box-orient:vertical;overflow:hidden;">
                        <?= esc(strip_tags($c['message'])) ?>
                    </p>

                    <!-- Badges -->
                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <span style="background:#f8fafc;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;color:<?= $cible['color'] ?>;">
                            <i class="fas <?= $cible['icon'] ?> me-1"></i><?= $cible['label'] ?>
                        </span>
                        <span style="background:<?= $canal['bg'] ?>;color:<?= $canal['color'] ?>;
                                     padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                            <?= $canal['label'] ?>
                        </span>
                        <?php if ($c['type_cible'] === 'inactifs' && $c['jours_inactivite']): ?>
                        <span style="background:#fff7ed;color:#c2410c;padding:3px 10px;border-radius:20px;font-size:11px;">
                            +<?= $c['jours_inactivite'] ?> jours
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Infos envoi -->
                    <div style="background:#f8fafc;border-radius:8px;padding:10px 12px;font-size:11px;color:#6b7280;margin-bottom:16px;">
                        <?php if ($c['statut'] === 'envoyee'): ?>
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Envoyée à <strong><?= $c['nb_envoyes'] ?> client(s)</strong>
                            le <?= date('d/m/Y à H:i', strtotime($c['date_envoi_reel'])) ?>
                        <?php elseif ($c['date_envoi_prevue']): ?>
                            <i class="fas fa-clock text-warning me-1"></i>
                            Planifiée le <?= date('d/m/Y à H:i', strtotime($c['date_envoi_prevue'])) ?>
                        <?php else: ?>
                            <i class="fas fa-pencil-alt text-secondary me-1"></i>
                            Créée le <?= date('d/m/Y', strtotime($c['created_at'])) ?>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <?php if ($c['statut'] !== 'envoyee'): ?>
                    <form action="<?= base_url('campagnes/lancer/' . $c['id_campagne']) ?>"
                          method="POST"
                          onsubmit="return confirm('Envoyer cette campagne maintenant à tous les destinataires ?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-success w-100 rounded-2 fw-semibold">
                            <i class="fas fa-paper-plane me-2"></i>Lancer la campagne
                        </button>
                    </form>
                    <?php else: ?>
                    <div class="text-center py-2"
                         style="background:#f0fdf4;border-radius:8px;font-size:12px;color:#166534;font-weight:600;">
                        <i class="fas fa-check-circle me-1"></i>Campagne terminée
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<!-- MODAL NOUVELLE CAMPAGNE -->
<div class="modal fade" id="modalNouvelleCampagne" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <div>
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-bullhorn text-primary me-2"></i>Nouvelle campagne
                    </h5>
                    <small class="text-muted">Créez un message ciblé pour vos clients</small>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('campagnes/sauvegarder') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">
                    <div class="row g-3">

                        <!-- Titre -->
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Titre de la campagne <span class="text-danger">*</span></label>
                            <input type="text" name="titre" class="form-control"
                                   placeholder="Ex: Promotion de fin d'année, Offre anniversaire..."
                                   required>
                        </div>

                        <!-- Cible -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Cible <span class="text-danger">*</span></label>
                            <select name="type_cible" class="form-select" id="selectCible" required>
                                <option value="tous">Tous les clients</option>
                                <option value="inactifs">Clients inactifs depuis X jours</option>
                                <option value="anniversaire">Clients dont c'est l'anniversaire</option>
                            </select>
                        </div>

                        <!-- Jours inactivité (conditionnel) -->
                        <div class="col-md-6" id="zoneJours" style="display:none;">
                            <label class="form-label fw-semibold small">Inactifs depuis (jours)</label>
                            <input type="number" name="jours_inactivite" class="form-control"
                                   value="60" min="1" placeholder="Ex: 60">
                            <div class="form-text" style="font-size:10px;">
                                Clients sans dépôt depuis ce nombre de jours.
                            </div>
                        </div>

                        <!-- Canal -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Canal d'envoi <span class="text-danger">*</span></label>
                            <select name="canal" class="form-select" required>
                                <option value="interne">Application uniquement</option>
                                <option value="email">Email</option>
                                <option value="sms">SMS</option>
                                <option value="tous">Tous les canaux</option>
                            </select>
                        </div>

                        <!-- Message -->
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Message <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="5"
                                      placeholder="Rédigez votre message ici..."
                                      required id="messageArea"></textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <div class="form-text" style="font-size:10px;">
                                    Pour les SMS : gardez sous 160 caractères pour un SMS simple.
                                </div>
                                <span id="compteurChars" style="font-size:10px;color:#6b7280;">0 car.</span>
                            </div>
                        </div>

                        <!-- Aperçu -->
                        <div class="col-12">
                            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px;">
                                <p class="fw-semibold mb-1" style="font-size:12px;color:#166534;">
                                    <i class="fas fa-eye me-1"></i>Aperçu du message
                                </p>
                                <p id="apercuMessage" class="mb-0 text-muted" style="font-size:12px;">
                                    Le message apparaîtra ici...
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2"
                            data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-2">
                        <i class="fas fa-save me-2"></i>Enregistrer en brouillon
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
// Afficher/masquer zone jours inactivité
document.getElementById('selectCible').addEventListener('change', function () {
    document.getElementById('zoneJours').style.display =
        this.value === 'inactifs' ? '' : 'none';
});

// Compteur caractères + aperçu
document.getElementById('messageArea').addEventListener('input', function () {
    document.getElementById('compteurChars').textContent = this.value.length + ' car.';
    document.getElementById('apercuMessage').textContent = this.value || 'Le message apparaîtra ici...';
    const over = this.value.length > 160;
    document.getElementById('compteurChars').style.color = over ? '#dc2626' : '#6b7280';
});
</script>

<?= $this->endSection() ?>
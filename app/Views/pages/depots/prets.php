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
            <h4 class="fw-bold mb-0">
                <i class="fas fa-check-circle text-success me-2"></i>Commandes prêtes
            </h4>
            <small class="text-muted"><?= count($depots) ?> commande(s) en attente de retrait</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('livraison') ?>" class="btn btn-outline-primary rounded-2 px-3">
                <i class="fas fa-truck me-2"></i>Gérer les livraisons
            </a>
            <a href="<?= base_url('depot') ?>" class="btn btn-outline-secondary rounded-2 px-3">
                <i class="fas fa-arrow-left me-2"></i>Tous les dépôts
            </a>
        </div>
    </div>

    <?php if (empty($depots)): ?>
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-check-double fa-3x mb-3 d-block text-success opacity-50"></i>
            <h5 class="fw-semibold">Aucune commande en attente</h5>
            <p class="mb-0">Toutes les commandes ont été retirées ou livrées.</p>
        </div>
    </div>
    <?php else: ?>

    <div class="row g-4">
        <?php
        $statutsMode = [
            'non_defini' => ['label' => 'En attente de choix', 'bg' => '#fef3c7', 'color' => '#92400e'],
            'boutique'   => ['label' => 'Passage boutique',    'bg' => '#dbeafe', 'color' => '#1d4ed8'],
            'livraison'  => ['label' => 'Livraison domicile',  'bg' => '#dcfce7', 'color' => '#166534'],
        ];
        foreach ($depots as $d):
            $sm     = $statutsMode[$d['mode_retrait']] ?? $statutsMode['non_defini'];
            $retard = $d['date_livraison_prevue'] && new DateTime($d['date_livraison_prevue']) < new DateTime();
        ?>
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100"
                 style="<?= $retard ? 'border-left:4px solid #dc2626 !important;' : 'border-left:4px solid #10b981 !important;' ?>">
                <div class="card-body">

                    <!-- En-tête carte -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="fw-bold text-primary mb-0"><?= esc($d['code_commande']) ?></h6>
                            <small class="text-muted">
                                <?= $d['nb_articles'] ?> article(s) ·
                                <?= number_format($d['total_ttc'], 0, ',', ' ') ?> FCFA
                            </small>
                        </div>
                        <span style="background:<?= $sm['bg'] ?>;color:<?= $sm['color'] ?>;
                                     padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                            <?= $sm['label'] ?>
                        </span>
                    </div>

                    <!-- Client -->
                    <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded-2"
                         style="background:#f8fafc;">
                        <i class="fas fa-user-circle fa-lg text-primary"></i>
                        <div>
                            <div class="fw-semibold"><?= esc($d['nomclient']) ?></div>
                            <div class="text-muted" style="font-size:12px;">
                                <i class="fas fa-phone me-1"></i><?= esc($d['telephone']) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div style="background:#f8fafc;border-radius:8px;padding:8px 10px;">
                                <div class="text-muted" style="font-size:10px;text-transform:uppercase;">Prêt depuis</div>
                                <div class="fw-semibold" style="font-size:12px;">
                                    <?= date('d/m/Y', strtotime($d['updated_at'])) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="background:<?= $retard ? '#fff5f5' : '#f8fafc' ?>;
                                         border-radius:8px;padding:8px 10px;">
                                <div class="text-muted" style="font-size:10px;text-transform:uppercase;">Retrait prévu</div>
                                <div class="fw-semibold" style="font-size:12px;
                                     color:<?= $retard ? '#dc2626' : '#374151' ?>;">
                                    <?= $retard ? '⚠ ' : '' ?>
                                    <?= $d['date_livraison_prevue']
                                        ? date('d/m/Y', strtotime($d['date_livraison_prevue']))
                                        : '—' ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notification -->
                    <div class="mb-3 d-flex align-items-center gap-2"
                         style="font-size:12px;color:<?= $d['notif_pret_envoyee'] ? '#166534' : '#92400e' ?>;">
                        <i class="fas fa-<?= $d['notif_pret_envoyee'] ? 'check-circle' : 'exclamation-circle' ?>"></i>
                        <?= $d['notif_pret_envoyee']
                            ? 'Notification envoyée'
                            : 'Client non encore notifié' ?>
                    </div>

                    <!-- Livraison créée -->
                    <?php if ($d['id_livraison']): ?>
                    <div class="mb-3 rounded-2 p-2"
                         style="background:#f0fdf4;border:1px solid #bbf7d0;font-size:12px;">
                        <i class="fas fa-truck text-success me-1"></i>
                        Livraison créée :
                        <a href="<?= base_url('livraison/' . $d['id_livraison']) ?>"
                           class="fw-semibold text-success">
                            <?= esc($d['code_livraison']) ?>
                        </a>
                        <span class="text-muted">(<?= $d['statut_livraison'] ?>)</span>
                    </div>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="d-flex gap-2 flex-wrap">

                        <!-- Notifier -->
                        <form action="<?= base_url('depot/notifier/' . $d['id_depot']) ?>"
                              method="POST" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit"
                                    class="btn btn-sm rounded-2 <?= $d['notif_pret_envoyee'] ? 'btn-outline-secondary' : 'btn-warning' ?>">
                                <i class="fas fa-bell me-1"></i>
                                <?= $d['notif_pret_envoyee'] ? 'Relancer' : 'Notifier' ?>
                            </button>
                        </form>

                        <!-- Définir mode retrait -->
                        <?php if ($d['mode_retrait'] === 'non_defini' && !$d['id_livraison']): ?>
                        <button class="btn btn-sm btn-primary rounded-2"
                                data-bs-toggle="modal"
                                data-bs-target="#modalRetrait<?= $d['id_depot'] ?>">
                            <i class="fas fa-hand-pointer me-1"></i>Choix retrait
                        </button>
                        <?php endif; ?>

                        <!-- Voir détail -->
                        <a href="<?= base_url('depot/detail/' . $d['id_depot']) ?>"
                           class="btn btn-sm btn-outline-secondary rounded-2">
                            <i class="fas fa-eye"></i>
                        </a>

                    </div>
                </div>
            </div>
        </div>

        <!-- Modal choix mode retrait -->
        <div class="modal fade" id="modalRetrait<?= $d['id_depot'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-0 px-4 pt-4 pb-0">
                        <div>
                            <h5 class="fw-bold mb-0">Mode de retrait</h5>
                            <small class="text-muted">
                                <?= esc($d['nomclient']) ?> — <?= esc($d['code_commande']) ?>
                            </small>
                        </div>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="<?= base_url('depot/mode-retrait/' . $d['id_depot']) ?>"
                          method="POST">
                        <?= csrf_field() ?>
                        <div class="modal-body px-4 py-3">

                            <!-- Choix -->
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="mode_retrait"
                                           id="boutique_<?= $d['id_depot'] ?>"
                                           value="boutique" checked>
                                    <label class="btn btn-outline-primary w-100 py-3 rounded-3"
                                           for="boutique_<?= $d['id_depot'] ?>">
                                        <i class="fas fa-store fa-2x d-block mb-2"></i>
                                        <div class="fw-semibold">Passage boutique</div>
                                        <div style="font-size:11px;color:#6b7280;">
                                            Le client vient récupérer
                                        </div>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="mode_retrait"
                                           id="livraison_<?= $d['id_depot'] ?>"
                                           value="livraison"
                                           onchange="toggleLivraison(<?= $d['id_depot'] ?>, this)">
                                    <label class="btn btn-outline-success w-100 py-3 rounded-3"
                                           for="livraison_<?= $d['id_depot'] ?>">
                                        <i class="fas fa-truck fa-2x d-block mb-2"></i>
                                        <div class="fw-semibold">Livraison domicile</div>
                                        <div style="font-size:11px;color:#6b7280;">
                                            Envoi chez le client
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Champs livraison (affichés si livraison choisie) -->
                            <div id="champsLivraison_<?= $d['id_depot'] ?>" style="display:none;">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small">
                                        Adresse de livraison <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="adresse_livraison" class="form-control" rows="2"
                                              placeholder="Quartier, rue, point de repère..."><?= esc($d['adresse'] ?? '') ?></textarea>
                                </div>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small">Date de livraison</label>
                                        <input type="date" name="date_livraison"
                                               class="form-control"
                                               min="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small">Heure souhaitée</label>
                                        <input type="time" name="heure_livraison" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold small">
                                            Frais de livraison (FCFA)
                                        </label>
                                        <div class="input-group">
                                            <input type="number" name="montant_livraison"
                                                   class="form-control" placeholder="0" min="0" step="100">
                                            <span class="input-group-text">FCFA</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold small">Note du client</label>
                                        <textarea name="note_client" class="form-control" rows="2"
                                                  placeholder="Instructions particulières..."></textarea>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer border-0 px-4 pb-4 pt-0">
                            <button type="button" class="btn btn-light rounded-2"
                                    data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success px-4 rounded-2 fw-semibold">
                                <i class="fas fa-check me-2"></i>Confirmer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</div>

<script>
function toggleLivraison(depotId, radio) {
    const zone = document.getElementById('champsLivraison_' + depotId);
    if (zone) zone.style.display = radio.checked ? '' : 'none';
}

// Écouter aussi le choix boutique pour masquer
document.querySelectorAll('[id^="boutique_"]').forEach(radio => {
    radio.addEventListener('change', function () {
        const depotId = this.id.replace('boutique_', '');
        const zone = document.getElementById('champsLivraison_' + depotId);
        if (zone) zone.style.display = 'none';
    });
});
</script>

<?= $this->endSection() ?>
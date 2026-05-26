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
            <h4 class="fw-bold mb-0">Gestion de Caisse</h4>
            <small class="text-muted">
                <?= $caisse ? 'Caisse ouverte depuis ' . date('d/m/Y à H:i', strtotime($caisse['date_ouverture'])) : 'Aucune caisse ouverte' ?>
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('pos') ?>" class="btn btn-outline-secondary rounded-2 px-3">
                <i class="fas fa-arrow-left me-2"></i>Retour POS
            </a>
            <?php if (!$caisse): ?>
            <button class="btn btn-success rounded-2 px-4"
                    data-bs-toggle="modal" data-bs-target="#modalOuvrirCaisse">
                <i class="fas fa-lock-open me-2"></i>Ouvrir la caisse
            </button>
            <?php else: ?>
            <button class="btn btn-danger rounded-2 px-4"
                    data-bs-toggle="modal" data-bs-target="#modalCloturerCaisse">
                <i class="fas fa-lock me-2"></i>Clôturer la caisse
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Caisse courante -->
    <?php if ($caisse): ?>
    <div class="row g-3 mb-4">
        <?php
        $stats = [
            ['label' => 'CA Total',       'val' => $caisse['total_ca'],        'color' => '#166534', 'bg' => '#dcfce7', 'icon' => 'fa-chart-line'],
            ['label' => 'Espèces',        'val' => $caisse['total_especes'],   'color' => '#1d4ed8', 'bg' => '#eff6ff', 'icon' => 'fa-money-bill'],
            ['label' => 'Mobile Money',   'val' => $caisse['total_mobile'],    'color' => '#7e22ce', 'bg' => '#fdf4ff', 'icon' => 'fa-mobile'],
            ['label' => 'Carte',          'val' => $caisse['total_carte'],     'color' => '#0e7490', 'bg' => '#ecfeff', 'icon' => 'fa-credit-card'],
            ['label' => 'Remboursements', 'val' => $caisse['total_rembourse'], 'color' => '#991b1b', 'bg' => '#fee2e2', 'icon' => 'fa-undo'],
            ['label' => 'Fond ouverture', 'val' => $caisse['fond_ouverture'],  'color' => '#92400e', 'bg' => '#fef3c7', 'icon' => 'fa-coins'],
        ];
        ?>
        <?php foreach ($stats as $stat): ?>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3 px-2">
                <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center"
                     style="width:38px;height:38px;background:<?= $stat['bg'] ?>;">
                    <i class="fas <?= $stat['icon'] ?>" style="color:<?= $stat['color'] ?>;font-size:14px;"></i>
                </div>
                <div class="fw-bold" style="color:<?= $stat['color'] ?>;font-size:15px;">
                    <?= number_format($stat['val'], 0, ',', ' ') ?>
                </div>
                <div class="text-muted" style="font-size:11px;"><?= $stat['label'] ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Historique caisses -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="px-4 py-3 border-bottom">
                <p class="text-uppercase text-muted fw-semibold mb-0" style="font-size:11px;letter-spacing:.5px;">
                    <i class="fas fa-history me-2"></i>Historique des caisses
                </p>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-muted fw-semibold" style="font-size:11px;">OUVERTURE</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;">CAISSIER</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:11px;">CA</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:11px;">ÉCART</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;">STATUT</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;">RAPPORT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historique as $h): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold" style="font-size:13px;">
                                    <?= date('d/m/Y', strtotime($h['date_ouverture'])) ?>
                                </div>
                                <div class="text-muted" style="font-size:11px;">
                                    <?= date('H:i', strtotime($h['date_ouverture'])) ?>
                                    <?= $h['date_cloture'] ? '→ ' . date('H:i', strtotime($h['date_cloture'])) : '(en cours)' ?>
                                </div>
                            </td>
                            <td>
                                <span class="fw-semibold" style="font-size:13px;"><?= esc($h['nom_complet'] ?? '—') ?></span>
                                <span class="d-block text-muted" style="font-size:11px;"><?= esc($h['nom_shop'] ?? '') ?></span>
                            </td>
                            <td class="text-end fw-bold text-success">
                                <?= number_format($h['total_ca'], 0, ',', ' ') ?> FCFA
                            </td>
                            <td class="text-end">
                                <?php if ($h['ecart'] !== null): ?>
                                    <span style="color:<?= $h['ecart'] < 0 ? '#dc2626' : '#166534' ?>;font-weight:600;">
                                        <?= ($h['ecart'] >= 0 ? '+' : '') . number_format($h['ecart'], 0, ',', ' ') ?> FCFA
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($h['statut'] === 'ouverte'): ?>
                                    <span style="background:#dcfce7;color:#166534;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                        <i class="fas fa-circle fa-xs me-1"></i>Ouverte
                                    </span>
                                <?php else: ?>
                                    <span style="background:#f1f5f9;color:#475569;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                        Clôturée
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('pos/caisse/rapport/' . $h['id_caisse']) ?>"
                                   target="_blank"
                                   class="btn btn-sm"
                                   style="width:32px;height:32px;border-radius:8px;background:#eff6ff;border:1px solid #bfdbfe;">
                                    <i class="fas fa-file-alt fa-sm text-primary"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL OUVRIR CAISSE -->
<div class="modal fade" id="modalOuvrirCaisse" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="fas fa-lock-open text-success me-2"></i>Ouvrir la caisse</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('pos/caisse/ouvrir') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Boutique</label>
                        <select name="shop_id" class="form-select" required>
                            <option value="" disabled selected>Choisir...</option>
                            <?php foreach ($shops as $s): ?>
                                <option value="<?= $s['id_shop'] ?>"><?= esc($s['nom_shop']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Fond de caisse (FCFA)</label>
                        <div class="input-group">
                            <input type="number" name="fond_ouverture" class="form-control form-control-lg"
                                   placeholder="Ex: 50000" min="0" step="500" required>
                            <span class="input-group-text">FCFA</span>
                        </div>
                        <div class="form-text" style="font-size:10px;">
                            Montant physiquement présent dans le tiroir à l'ouverture.
                        </div>
                    </div>
                    <div style="background:#f0fdf4;border-radius:10px;padding:12px;font-size:12px;color:#166534;">
                        <i class="fas fa-info-circle me-1"></i>
                        Toutes les transactions seront rattachées à cette session de caisse.
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success px-4 rounded-2 fw-semibold">
                        <i class="fas fa-lock-open me-2"></i>Ouvrir la caisse
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL CLÔTURER CAISSE -->
<?php if ($caisse): ?>
<div class="modal fade" id="modalCloturerCaisse" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="fas fa-lock text-danger me-2"></i>Clôturer la caisse</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('pos/caisse/cloturer') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">

                    <!-- Récap théorique -->
                    <div style="background:#f8fafc;border-radius:10px;padding:14px;margin-bottom:16px;">
                        <p class="fw-semibold mb-2" style="font-size:12px;color:#374151;">Récapitulatif théorique</p>
                        <div class="d-flex justify-content-between mb-1" style="font-size:12px;">
                            <span class="text-muted">Fond d'ouverture</span>
                            <span><?= number_format($caisse['fond_ouverture'],0,',',' ') ?> FCFA</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1" style="font-size:12px;">
                            <span class="text-muted">+ Espèces encaissées</span>
                            <span class="text-success"><?= number_format($caisse['total_especes'],0,',',' ') ?> FCFA</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1" style="font-size:12px;">
                            <span class="text-muted">— Remboursements</span>
                            <span class="text-danger"><?= number_format($caisse['total_rembourse'],0,',',' ') ?> FCFA</span>
                        </div>
                        <?php
                            $theoriqueEspeces = $caisse['fond_ouverture'] + $caisse['total_especes'] - $caisse['total_rembourse'];
                        ?>
                        <div class="d-flex justify-content-between border-top pt-2 mt-1 fw-bold" style="font-size:13px;">
                            <span>Espèces attendues</span>
                            <span><?= number_format($theoriqueEspeces,0,',',' ') ?> FCFA</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Espèces comptées réellement (FCFA) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="fond_reel" id="fondReel" class="form-control form-control-lg"
                                   placeholder="0" min="0" step="100" required
                                   oninput="calculerEcart(<?= $theoriqueEspeces ?>)">
                            <span class="input-group-text">FCFA</span>
                        </div>
                        <div id="zoneEcart" class="mt-2 d-none rounded-2 p-2 text-center"
                             style="font-size:13px;"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Note de clôture (optionnel)</label>
                        <textarea name="note_cloture" class="form-control" rows="2"
                                  placeholder="Remarques, explications d'écart..."></textarea>
                    </div>

                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger px-4 rounded-2 fw-semibold">
                        <i class="fas fa-lock me-2"></i>Clôturer définitivement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function calculerEcart(theorique) {
    const reel   = parseFloat(document.getElementById('fondReel').value) || 0;
    const ecart  = reel - theorique;
    const zone   = document.getElementById('zoneEcart');
    zone.classList.remove('d-none');
    const positif = ecart >= 0;
    zone.style.background = positif ? '#f0fdf4' : '#fff5f5';
    zone.style.border      = '1px solid ' + (positif ? '#bbf7d0' : '#fecaca');
    zone.style.color       = positif ? '#166534' : '#dc2626';
    zone.innerHTML = (positif ? '✅ Excédent : +' : '⚠️ Manquant : ') +
                     Math.abs(ecart).toLocaleString('fr-FR') + ' FCFA';
}
</script>
<?php endif; ?>

<?= $this->endSection() ?>
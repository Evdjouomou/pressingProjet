<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4" style="max-width:700px;">

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
    <?php if (session()->getFlashdata('info')): ?>
        <div class="alert alert-info alert-dismissible fade show shadow-sm rounded-3">
            <i class="fas fa-info-circle me-2"></i><?= session()->getFlashdata('info') ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">
            <i class="fas fa-cash-register text-primary me-2"></i>Caisse
        </h4>
        <a href="<?= base_url('pos') ?>" class="btn btn-outline-secondary rounded-2 px-3">
            <i class="fas fa-arrow-left me-2"></i>POS
        </a>
        <a href="<?= base_url('pos/historique-caisses') ?>" class="btn btn-outline-primary rounded-2 px-3">
            <i class="fas fa-history me-2"></i>Historique
        </a>
    </div>

    <?php if (!$caisse): ?>
    <!-- Aucune caisse ouverte -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body text-center py-5">
            <i class="fas fa-lock fa-3x text-muted mb-3 d-block opacity-50"></i>
            <h5 class="fw-bold mb-2">Caisse fermée</h5>
            <p class="text-muted mb-4">Ouvrez la caisse pour commencer la journée.</p>
            <button class="btn btn-success btn-lg rounded-2 px-5"
                    data-bs-toggle="modal" data-bs-target="#modalOuvrir">
                <i class="fas fa-unlock me-2"></i>Ouvrir la caisse
            </button>
        </div>
    </div>

    <?php else: ?>
    <!-- Caisse ouverte -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="rounded-3 d-flex align-items-center justify-content-center"
                     style="width:52px;height:52px;background:#dcfce7;">
                    <i class="fas fa-cash-register text-success fa-lg"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">Caisse ouverte</h5>
                    <small class="text-muted">
                        Depuis le <?= date('d/m/Y à H:i', strtotime($caisse['date_ouverture'])) ?>
                    </small>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="rounded-3 p-3 text-center"
                         style="background:#f8fafc;border:1px solid #e2e8f0;">
                        <div class="text-muted" style="font-size:10px;text-transform:uppercase;">Fond</div>
                        <div class="fw-bold mt-1">
                            <?= number_format($caisse['fond_ouverture'], 0, ',', ' ') ?> FCFA
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="rounded-3 p-3 text-center"
                         style="background:#f0fdf4;border:1px solid #bbf7d0;">
                        <div class="text-muted" style="font-size:10px;text-transform:uppercase;">CA Total</div>
                        <div class="fw-bold text-success mt-1">
                            <?= number_format($caisse['total_ca'], 0, ',', ' ') ?> FCFA
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="rounded-3 p-3 text-center"
                         style="background:#eff6ff;border:1px solid #bfdbfe;">
                        <div class="text-muted" style="font-size:10px;text-transform:uppercase;">Espèces</div>
                        <div class="fw-bold text-primary mt-1">
                            <?= number_format($caisse['total_especes'], 0, ',', ' ') ?> FCFA
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="rounded-3 p-3 text-center"
                         style="background:#fdf4ff;border:1px solid #e9d5ff;">
                        <div class="text-muted" style="font-size:10px;text-transform:uppercase;">Mobile</div>
                        <div class="fw-bold mt-1" style="color:#7e22ce;">
                            <?= number_format($caisse['total_mobile'], 0, ',', ' ') ?> FCFA
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mt-4">
                <button class="btn btn-danger rounded-2 px-4"
                        data-bs-toggle="modal" data-bs-target="#modalCloturer">
                    <i class="fas fa-lock me-2"></i>Clôturer la caisse
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal ouvrir caisse -->
<div class="modal fade" id="modalOuvrir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-0">
                    <i class="fas fa-unlock text-success me-2"></i>Ouvrir la caisse
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('pos/caisse/ouvrir') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">
                    <label class="form-label fw-semibold small">
                        Fond de caisse (FCFA)
                    </label>
                    <div class="input-group">
                        <input type="number" name="fond_ouverture"
                               class="form-control form-control-lg"
                               placeholder="0" min="0" step="500" value="0" required>
                        <span class="input-group-text">FCFA</span>
                    </div>
                    <div class="form-text">Montant en espèces présent à l'ouverture.</div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2"
                            data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success px-4 rounded-2 fw-semibold">
                        <i class="fas fa-unlock me-2"></i>Ouvrir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal clôturer caisse -->
<?php if ($caisse): ?>
<div class="modal fade" id="modalCloturer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-0">
                    <i class="fas fa-lock text-danger me-2"></i>Clôturer la caisse
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('pos/caisse/cloturer') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">
                    <div class="rounded-3 p-3 mb-3"
                         style="background:#f8fafc;border:1px solid #e2e8f0;">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">CA enregistré</span>
                            <strong><?= number_format($caisse['total_ca'], 0, ',', ' ') ?> FCFA</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Théorique espèces</span>
                            <strong>
                                <?= number_format($caisse['fond_ouverture'] + $caisse['total_especes'], 0, ',', ' ') ?> FCFA
                            </strong>
                        </div>
                    </div>
                    <label class="form-label fw-semibold small">
                        Fond réel constaté (FCFA)
                    </label>
                    <div class="input-group">
                        <input type="number" name="fond_reel"
                               class="form-control"
                               placeholder="0" min="0" step="500"
                               value="<?= $caisse['fond_ouverture'] + $caisse['total_especes'] ?>"
                               required>
                        <span class="input-group-text">FCFA</span>
                    </div>
                    <div class="form-text">Comptez les espèces et saisissez le total réel.</div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2"
                            data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger px-4 rounded-2 fw-semibold">
                        <i class="fas fa-lock me-2"></i>Clôturer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
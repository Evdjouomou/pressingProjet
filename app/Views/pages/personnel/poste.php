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
            <h4 class="fw-bold mb-0">Gestion des Postes</h4>
            <small class="text-muted"><?= count($postes) ?> poste(s) enregistré(s)</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('personnel') ?>" class="btn btn-outline-secondary rounded-2 px-3">
                <i class="fas fa-users me-2"></i>Personnel
            </a>
            <a href="<?= base_url('shop') ?>" class="btn btn-outline-secondary rounded-2 px-3">
                <i class="fas fa-store me-2"></i>Boutiques
            </a>
            <button class="btn btn-primary rounded-2 px-4"
                    data-bs-toggle="modal" data-bs-target="#modalCreerPoste">
                <i class="fas fa-plus me-2"></i>Nouveau poste
            </button>
        </div>
    </div>

    <!-- Cartes postes -->
    <?php if (empty($postes)): ?>
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body text-center py-5 text-muted">
                <i class="fas fa-briefcase fa-3x mb-3 d-block opacity-25"></i>
                Aucun poste créé. Commencez par en ajouter un.
            </div>
        </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($postes as $p): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body">

                    <!-- Icône + nom -->
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                             style="width:46px;height:46px;background:#eff6ff;">
                            <i class="fas fa-briefcase text-primary"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0"><?= esc($p['nom_poste']) ?></h6>
                            <small class="text-muted">Créé le <?= date('d/m/Y', strtotime($p['created_at'])) ?></small>
                        </div>
                    </div>

                    <!-- Infos -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;">
                                <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">Salaire</div>
                                <div class="fw-bold text-success mt-1">
                                    <?= number_format($p['salaire'], 0, ',', ' ') ?> FCFA
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;">
                                <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">Employés</div>
                                <div class="fw-bold mt-1">
                                    <span style="background:#eff6ff;color:#1d4ed8;padding:2px 10px;
                                                 border-radius:20px;font-size:13px;">
                                        <?= $p['nb_employes'] ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm flex-fill rounded-2"
                                style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;"
                                data-bs-toggle="modal"
                                data-bs-target="#modalModifierPoste<?= $p['id_poste'] ?>">
                            <i class="fas fa-edit me-1"></i>Modifier
                        </button>
                        <?php if ($p['nb_employes'] == 0): ?>
                        <a href="<?= base_url('poste/delete/' . $p['id_poste']) ?>"
                           class="btn btn-sm rounded-2"
                           style="background:#fff5f5;border:1px solid #fecaca;color:#dc2626;"
                           onclick="return confirm('Supprimer ce poste ?')">
                            <i class="fas fa-trash me-1"></i>
                        </a>
                        <?php else: ?>
                        <button class="btn btn-sm rounded-2 disabled"
                                style="background:#f1f5f9;border:1px solid #e2e8f0;color:#94a3b8;"
                                title="Impossible : des employés sont affectés">
                            <i class="fas fa-lock"></i>
                        </button>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>

        <!-- Modal modifier poste -->
        <div class="modal fade" id="modalModifierPoste<?= $p['id_poste'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-0 px-4 pt-4 pb-0">
                        <div>
                            <h5 class="fw-bold mb-0">Modifier le poste</h5>
                            <small class="text-muted"><?= esc($p['nom_poste']) ?></small>
                        </div>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="<?= base_url('poste/update/' . $p['id_poste']) ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="modal-body px-4 py-3">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Nom du poste <span class="text-danger">*</span></label>
                                <input type="text" name="nom_poste" class="form-control"
                                       value="<?= esc($p['nom_poste']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Salaire de base (FCFA) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="salaire" class="form-control"
                                           value="<?= $p['salaire'] ?>" min="0" step="500" required>
                                    <span class="input-group-text">FCFA</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 px-4 pb-4 pt-0">
                            <button type="button" class="btn btn-light rounded-2" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary px-4 rounded-2">
                                <i class="fas fa-save me-2"></i>Enregistrer
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

<!-- Modal créer poste -->
<div class="modal fade" id="modalCreerPoste" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <div>
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-briefcase text-primary me-2"></i>Nouveau poste
                    </h5>
                    <small class="text-muted">Définissez le poste et son salaire de base</small>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('poste/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nom du poste <span class="text-danger">*</span></label>
                        <input type="text" name="nom_poste" class="form-control"
                               placeholder="Ex: Gérant, Caissier, Agent de production..."
                               required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Salaire de base (FCFA) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="salaire" class="form-control"
                                   placeholder="Ex: 150000" min="0" step="500" required>
                            <span class="input-group-text">FCFA</span>
                        </div>
                    </div>
                    <div style="background:#eff6ff;border-radius:10px;padding:12px;font-size:12px;color:#1d4ed8;">
                        <i class="fas fa-info-circle me-1"></i>
                        Le salaire sera automatiquement associé à chaque employé affecté à ce poste.
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success px-4 rounded-2 fw-semibold">
                        <i class="fas fa-check me-2"></i>Créer le poste
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
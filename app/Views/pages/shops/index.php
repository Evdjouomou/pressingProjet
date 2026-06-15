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

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="fas fa-store text-primary me-2"></i>Établissements
            </h4>
            <small class="text-muted"><?= count($shops) ?> établissement(s)</small>
        </div>
        <button class="btn btn-primary rounded-2 px-4"
                data-bs-toggle="modal" data-bs-target="#modalCreer">
            <i class="fas fa-plus me-2"></i>Nouvel établissement
        </button>
    </div>

    <!-- Grille shops -->
    <?php if (empty($shops)): ?>
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-store fa-3x mb-3 d-block opacity-25"></i>
            <h5>Aucun établissement créé</h5>
            <button class="btn btn-primary rounded-2 px-4 mt-2"
                    data-bs-toggle="modal" data-bs-target="#modalCreer">
                <i class="fas fa-plus me-2"></i>Créer le premier
            </button>
        </div>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($shops as $sh): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100"
                 style="border-top:3px solid #3b82f6 !important;">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-1"><?= esc($sh['nom_shop']) ?></h5>
                            <?php if ($sh['adresse']): ?>
                            <div class="text-muted" style="font-size:12px;">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?= esc($sh['adresse']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <a href="<?= base_url('shop/switcher/' . $sh['id_shop']) ?>"
                           class="btn btn-sm rounded-2"
                           style="background:#eff6ff;border:1px solid #bfdbfe;
                                  color:#1d4ed8;font-size:11px;padding:4px 10px;">
                            <i class="fas fa-eye me-1"></i>Vue
                        </a>
                    </div>

                    <!-- Stats -->
                    <div class="row g-2 mb-3">
                        <div class="col-4 text-center">
                            <div class="rounded-2 py-2"
                                 style="background:#eff6ff;">
                                <div class="fw-bold text-primary">
                                    <?= $sh['nb_employes'] ?>
                                </div>
                                <div class="text-muted" style="font-size:10px;">
                                    Employés
                                </div>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="rounded-2 py-2"
                                 style="background:#f0fdf4;">
                                <div class="fw-bold text-success">
                                    <?= $sh['depots_actifs'] ?>
                                </div>
                                <div class="text-muted" style="font-size:10px;">
                                    En cours
                                </div>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="rounded-2 py-2"
                                 style="background:#fdf4ff;">
                                <div class="fw-bold" style="color:#7e22ce;">
                                    <?= $sh['nb_depots'] ?>
                                </div>
                                <div class="text-muted" style="font-size:10px;">
                                    Total dépôts
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CA -->
                    <div class="d-flex justify-content-between align-items-center
                                rounded-2 px-3 py-2 mb-3"
                         style="background:#f8fafc;border:1px solid #e2e8f0;">
                        <span class="text-muted" style="font-size:12px;">CA total</span>
                        <span class="fw-bold text-success">
                            <?= number_format($sh['ca_total'] ?? 0, 0, ',', ' ') ?> FCFA
                        </span>
                    </div>

                    <?php if ($sh['telephone'] || $sh['email']): ?>
                    <div class="mb-3" style="font-size:12px;color:#6b7280;">
                        <?php if ($sh['telephone']): ?>
                        <div><i class="fas fa-phone me-1"></i><?= esc($sh['telephone']) ?></div>
                        <?php endif; ?>
                        <?php if ($sh['email']): ?>
                        <div><i class="fas fa-envelope me-1"></i><?= esc($sh['email']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm flex-fill rounded-2"
                                style="background:#eff6ff;border:1px solid #bfdbfe;
                                       color:#1d4ed8;"
                                data-bs-toggle="modal"
                                data-bs-target="#modalModifier<?= $sh['id_shop'] ?>">
                            <i class="fas fa-edit me-1"></i>Modifier
                        </button>
                        <?php if ($sh['nb_employes'] == 0 && $sh['nb_depots'] == 0): ?>
                        <a href="<?= base_url('shop/delete/' . $sh['id_shop']) ?>"
                           class="btn btn-sm rounded-2"
                           style="background:#fff5f5;border:1px solid #fecaca;
                                  color:#dc2626;width:36px;"
                           onclick="return confirm('Supprimer cet établissement ?')">
                            <i class="fas fa-trash fa-sm"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal modifier -->
        <div class="modal fade"
             id="modalModifier<?= $sh['id_shop'] ?>"
             tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-0 px-4 pt-4 pb-0">
                        <h5 class="fw-bold mb-0">Modifier l'établissement</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="<?= base_url('shop/update/' . $sh['id_shop']) ?>"
                          method="POST">
                        <?= csrf_field() ?>
                        <div class="modal-body px-4 py-3">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">
                                    Nom <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nom_shop"
                                       class="form-control"
                                       value="<?= esc($sh['nom_shop']) ?>"
                                       required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">
                                    Adresse
                                </label>
                                <input type="text" name="adresse"
                                       class="form-control"
                                       value="<?= esc($sh['adresse'] ?? '') ?>">
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">
                                        Téléphone
                                    </label>
                                    <input type="text" name="telephone"
                                           class="form-control"
                                           value="<?= esc($sh['telephone'] ?? '') ?>">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold small">
                                        Email
                                    </label>
                                    <input type="email" name="email"
                                           class="form-control"
                                           value="<?= esc($sh['email'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 px-4 pb-4 pt-0">
                            <button type="button" class="btn btn-light rounded-2"
                                    data-bs-dismiss="modal">Annuler</button>
                            <button type="submit"
                                    class="btn btn-primary px-4 rounded-2">
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

<!-- Modal créer -->
<div class="modal fade" id="modalCreer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-0">
                    <i class="fas fa-store text-primary me-2"></i>
                    Nouvel établissement
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('shop/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Nom <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nom_shop"
                               class="form-control"
                               placeholder="Ex: Pressing Centre-ville"
                               required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Adresse</label>
                        <input type="text" name="adresse"
                               class="form-control"
                               placeholder="Quartier, ville...">
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Téléphone</label>
                            <input type="text" name="telephone"
                                   class="form-control"
                                   placeholder="+237 6XX...">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Email</label>
                            <input type="email" name="email"
                                   class="form-control"
                                   placeholder="shop@exemple.com">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2"
                            data-bs-dismiss="modal">Annuler</button>
                    <button type="submit"
                            class="btn btn-success px-4 rounded-2 fw-semibold">
                        <i class="fas fa-check me-2"></i>Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
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
            <a href="<?= base_url('abonnements') ?>"
               class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i>Abonnements
            </a>
            <h4 class="fw-bold mb-0">Offres d'abonnement</h4>
            <small class="text-muted">
                Définissez les packs proposés aux clients
            </small>
        </div>
        <button class="btn btn-primary rounded-2 px-4"
                data-bs-toggle="modal" data-bs-target="#modalCreerOffre">
            <i class="fas fa-plus me-2"></i>Nouvelle offre
        </button>
    </div>

    <?php if (empty($offres)): ?>
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-tags fa-3x mb-3 d-block opacity-25"></i>
            <h5 class="fw-semibold">Aucune offre créée</h5>
            <p class="mb-3">Créez des packs d'abonnement pour vos clients.</p>
            <button class="btn btn-primary rounded-2 px-4"
                    data-bs-toggle="modal" data-bs-target="#modalCreerOffre">
                <i class="fas fa-plus me-2"></i>Créer la première offre
            </button>
        </div>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($offres as $o): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100"
                 style="<?= !$o['actif'] ? 'opacity:.6;' : '' ?>
                        border-top:3px solid <?= $o['actif'] ? '#10b981' : '#94a3b8' ?> !important;">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-1"><?= esc($o['nom']) ?></h5>
                            <?php if ($o['description']): ?>
                            <p class="text-muted mb-0" style="font-size:13px;">
                                <?= esc($o['description']) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php if (!$o['actif']): ?>
                        <span style="background:#fee2e2;color:#991b1b;padding:3px 8px;
                                     border-radius:20px;font-size:11px;font-weight:600;">
                            Inactif
                        </span>
                        <?php else: ?>
                        <span style="background:#dcfce7;color:#166534;padding:3px 8px;
                                     border-radius:20px;font-size:11px;font-weight:600;">
                            Actif
                        </span>
                        <?php endif; ?>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div style="background:#f0fdf4;border-radius:10px;
                                        padding:12px;text-align:center;">
                                <div class="fw-bold fs-3 text-success">
                                    <?= $o['nb_articles'] ?>
                                </div>
                                <div class="text-muted" style="font-size:11px;">
                                    articles inclus
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="background:#eff6ff;border-radius:10px;
                                        padding:12px;text-align:center;">
                                <div class="fw-bold fs-3 text-primary">
                                    <?= $o['duree_jours'] ?>j
                                </div>
                                <div class="text-muted" style="font-size:11px;">durée</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3
                                rounded-2 p-2"
                         style="background:#f8fafc;border:1px solid #e2e8f0;">
                        <span class="text-muted small">Prix abonnement</span>
                        <span class="fw-bold fs-5 text-primary">
                            <?= number_format($o['prix'], 0, ',', ' ') ?> FCFA
                        </span>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-sm flex-fill rounded-2"
                                style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;"
                                data-bs-toggle="modal"
                                data-bs-target="#modalModifier<?= $o['id_type_abon'] ?>">
                            <i class="fas fa-edit me-1"></i>Modifier
                        </button>
                        
                        <?php
                        // Vérifier combien d'abonnements utilisent cette offre
                        $db = \Config\Database::connect();
                        $nbActifsOffre = $db->table('abonnements')
                            ->where('type_abon_id', $o['id_type_abon'])
                            ->where('statut', 'actif')
                            ->countAllResults();

                        $nbTotalOffre = $db->table('abonnements')
                            ->where('type_abon_id', $o['id_type_abon'])
                            ->countAllResults();
                        ?>

                        <?php if ($nbActifsOffre > 0): ?>
                            <a href="<?= base_url('abonnements/offres/delete/' . $o['id_type_abon']) ?>"
                               class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                               style="background:#fff3cd;border:1px solid #ffc107;color:#856404;"
                               title="<?= $nbActifsOffre ?> abonnement(s) actif(s) — sera désactivée"
                               onclick="return confirm('⚠️ Cette offre a <?= $nbActifsOffre ?> abonnement(s) actif(s).\n\nElle sera DÉSACTIVÉE (pas supprimée) pour protéger les clients abonnés.\n\nContinuer ?')">
                                <i class="fas fa-eye-slash me-1"></i>
                                <span style="font-size:12px; font-weight: 500;">Désactiver</span>
                            </a>
                        <?php else: ?>
                            <a href="<?= base_url('abonnements/offres/delete/' . $o['id_type_abon']) ?>"
                               class="btn btn-sm rounded-2 d-flex align-items-center justify-content-center"
                               style="background:#fff5f5;border:1px solid #fecaca;color:#dc2626;"
                               title="<?= $nbTotalOffre > 0 ? $nbTotalOffre . ' abonnement(s) historique(s)' : 'Supprimer' ?>"
                               onclick="return confirm('<?= $nbTotalOffre > 0 ? 'Cette offre a ' . $nbTotalOffre . ' abonnement(s) historique(s).\n\nLes données seront préservées grâce au snapshot.\n\nSupprimer quand même ?' : 'Supprimer cette offre définitivement ?' ?>')">
                                <i class="fas fa-trash"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade"
             id="modalModifier<?= $o['id_type_abon'] ?>"
             tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-0 px-4 pt-4 pb-0">
                        <h5 class="fw-bold mb-0">Modifier l'offre</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="<?= base_url('abonnements/offres/update/' . $o['id_type_abon']) ?>"
                          method="POST">
                        <?= csrf_field() ?>
                        <div class="modal-body px-4 py-3">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">
                                    Nom <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nom" class="form-control"
                                       value="<?= esc($o['nom']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">
                                    Description
                                </label>
                                <textarea name="description" class="form-control"
                                          rows="2"><?= esc($o['description'] ?? '') ?></textarea>
                            </div>
                            <div class="row g-3">
                                <div class="col-4">
                                    <label class="form-label fw-semibold small">
                                        Prix (FCFA) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="prix"
                                           class="form-control"
                                           value="<?= $o['prix'] ?>"
                                           min="0" step="500" required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label fw-semibold small">
                                        Nb articles <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="nb_articles"
                                           class="form-control"
                                           value="<?= $o['nb_articles'] ?>"
                                           min="1" required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label fw-semibold small">
                                        Durée (jours) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="duree_jours"
                                           class="form-control"
                                           value="<?= $o['duree_jours'] ?>"
                                           min="1" required>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="form-label fw-semibold small">
                                    Statut
                                </label>
                                <select name="actif" class="form-select">
                                    <option value="1" <?= $o['actif'] ? 'selected' : '' ?>>
                                        ✅ Active
                                    </option>
                                    <option value="0" <?= !$o['actif'] ? 'selected' : '' ?>>
                                        ❌ Inactive
                                    </option>
                                </select>
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

<div class="modal fade" id="modalCreerOffre" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-0">
                    <i class="fas fa-tags text-primary me-2"></i>Nouvelle offre
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('abonnements/offres/store') ?>"
                  method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Nom de l'offre <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nom" class="form-control"
                               placeholder="Ex: Pack Essentiel, Pack Premium..."
                               required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Description
                        </label>
                        <textarea name="description" class="form-control"
                                  rows="2"
                                  placeholder="Ce que comprend cet abonnement..."></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-4">
                            <label class="form-label fw-semibold small">
                                Prix (FCFA) <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="prix"
                                   class="form-control"
                                   placeholder="0" min="0" step="500"
                                   required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold small">
                                Nb articles <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="nb_articles"
                                   class="form-control"
                                   placeholder="10" min="1" required>
                            <div class="form-text" style="font-size:10px;">
                                Articles lavage inclus
                            </div>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-semibold small">
                                Durée (jours) <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="duree_jours"
                                   class="form-control"
                                   value="30" min="1" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2"
                            data-bs-dismiss="modal">Annuler</button>
                    <button type="submit"
                            class="btn btn-success px-4 rounded-2 fw-semibold">
                        <i class="fas fa-check me-2"></i>Créer l'offre
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
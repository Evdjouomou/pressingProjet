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
                <i class="fas fa-motorcycle text-primary me-2"></i>Livreurs
            </h4>
            <small class="text-muted"><?= $stats['total'] ?> livreur(s)</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('livraison') ?>"
               class="btn btn-outline-secondary rounded-2 px-3">
                <i class="fas fa-truck me-2"></i>Livraisons
            </a>
            <button class="btn btn-primary rounded-2 px-4"
                    data-bs-toggle="modal" data-bs-target="#modalCreer">
                <i class="fas fa-plus me-2"></i>Nouveau livreur
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-primary"><?= $stats['total'] ?></div>
                <div class="text-muted small">Total</div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-success"><?= $stats['actifs'] ?></div>
                <div class="text-muted small">Actifs</div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-warning"><?= $stats['encours'] ?></div>
                <div class="text-muted small">En livraison</div>
            </div>
        </div>
    </div>

    <!-- Grille livreurs -->
    <?php if (empty($livreurs)): ?>
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-motorcycle fa-3x mb-3 d-block opacity-25"></i>
            <h5>Aucun livreur enregistré</h5>
            <p class="mb-3">Ajoutez des livreurs pour gérer vos livraisons.</p>
            <button class="btn btn-primary rounded-2 px-4"
                    data-bs-toggle="modal" data-bs-target="#modalCreer">
                <i class="fas fa-plus me-2"></i>Ajouter un livreur
            </button>
        </div>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php
        $statutsLivreur = [
            'actif'    => ['label' => 'Actif',    'bg' => '#dcfce7', 'color' => '#166534'],
            'inactif'  => ['label' => 'Inactif',  'bg' => '#f1f5f9', 'color' => '#374151'],
            'suspendu' => ['label' => 'Suspendu', 'bg' => '#fee2e2', 'color' => '#991b1b'],
        ];
        foreach ($livreurs as $lv):
            $sl = $statutsLivreur[$lv['statut']]
                ?? ['label' => $lv['statut'], 'bg' => '#f1f5f9', 'color' => '#374151'];
        ?>
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body">

                    <!-- En-tête livreur -->
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <?php if ($lv['photo']): ?>
                        <img src="<?= base_url('uploads/livreurs/' . $lv['photo']) ?>"
                             class="rounded-circle"
                             style="width:52px;height:52px;object-fit:cover;">
                        <?php else: ?>
                        <div class="rounded-circle bg-primary bg-opacity-10
                                    d-flex align-items-center justify-content-center"
                             style="width:52px;height:52px;font-size:18px;
                                    font-weight:700;color:#1d4ed8;">
                            <?= strtoupper(substr($lv['nom_complet'], 0, 2)) ?>
                        </div>
                        <?php endif; ?>
                        <div class="flex-fill">
                            <div class="fw-bold"><?= esc($lv['nom_complet']) ?></div>
                            <div class="text-muted" style="font-size:12px;">
                                <i class="fas fa-phone me-1"></i><?= esc($lv['telephone']) ?>
                            </div>
                        </div>
                        <span style="background:<?= $sl['bg'] ?>;color:<?= $sl['color'] ?>;
                                     padding:3px 10px;border-radius:20px;
                                     font-size:11px;font-weight:600;">
                            <?= $sl['label'] ?>
                        </span>
                    </div>

                    <!-- Infos -->
                    <div class="row g-2 mb-3">
                        <?php if ($lv['vehicule']): ?>
                        <div class="col-6">
                            <div style="background:#f8fafc;border-radius:8px;padding:8px 10px;">
                                <div class="text-muted" style="font-size:10px;
                                     text-transform:uppercase;">Véhicule</div>
                                <div class="fw-semibold" style="font-size:12px;">
                                    <?= esc($lv['vehicule']) ?>
                                    <?= $lv['numero_plaque']
                                        ? '<br><span style="font-size:10px;color:#6b7280;">'
                                          . esc($lv['numero_plaque']) . '</span>'
                                        : '' ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($lv['zone_livraison']): ?>
                        <div class="col-6">
                            <div style="background:#f8fafc;border-radius:8px;padding:8px 10px;">
                                <div class="text-muted" style="font-size:10px;
                                     text-transform:uppercase;">Zone</div>
                                <div class="fw-semibold" style="font-size:12px;">
                                    <?= esc($lv['zone_livraison']) ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Stats livraisons -->
                    <div class="d-flex gap-2 mb-3">
                        <div class="flex-fill text-center rounded-2 py-2"
                             style="background:#eff6ff;">
                            <div class="fw-bold text-primary">
                                <?= $lv['total_livraisons'] ?>
                            </div>
                            <div class="text-muted" style="font-size:10px;">Total</div>
                        </div>
                        <div class="flex-fill text-center rounded-2 py-2"
                             style="background:#dcfce7;">
                            <div class="fw-bold text-success">
                                <?= $lv['livraisons_terminees'] ?>
                            </div>
                            <div class="text-muted" style="font-size:10px;">Livrées</div>
                        </div>
                        <div class="flex-fill text-center rounded-2 py-2"
                             style="background:#fef3c7;">
                            <div class="fw-bold" style="color:#92400e;">
                                <?= $lv['livraisons_encours'] ?>
                            </div>
                            <div class="text-muted" style="font-size:10px;">En cours</div>
                        </div>
                    </div>

                    <?php if ($lv['tarif_base'] > 0): ?>
                    <div class="d-flex justify-content-between mb-3"
                         style="font-size:12px;">
                        <span class="text-muted">Tarif base</span>
                        <span class="fw-semibold">
                            <?= number_format($lv['tarif_base'], 0, ',', ' ') ?> FCFA
                        </span>
                    </div>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('livreurs/' . $lv['id_livreur']) ?>"
                           class="btn btn-sm flex-fill rounded-2"
                           style="background:#f1f5f9;border:1px solid #e2e8f0;">
                            <i class="fas fa-eye me-1 text-secondary"></i>
                            <span style="font-size:12px;">Voir</span>
                        </a>
                        <button class="btn btn-sm flex-fill rounded-2"
                                style="background:#eff6ff;border:1px solid #bfdbfe;
                                       color:#1d4ed8;"
                                data-bs-toggle="modal"
                                data-bs-target="#modalModifier<?= $lv['id_livreur'] ?>">
                            <i class="fas fa-edit me-1"></i>
                            <span style="font-size:12px;">Modifier</span>
                        </button>
                        <?php if ($lv['livraisons_encours'] == 0): ?>
                        <a href="<?= base_url('livreurs/delete/' . $lv['id_livreur']) ?>"
                           class="btn btn-sm rounded-2"
                           style="background:#fff5f5;border:1px solid #fecaca;
                                  color:#dc2626;width:36px;"
                           onclick="return confirm('Supprimer ce livreur ?')">
                            <i class="fas fa-trash fa-sm"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal modifier -->
        <div class="modal fade"
             id="modalModifier<?= $lv['id_livreur'] ?>"
             tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-0 px-4 pt-4 pb-0">
                        <h5 class="fw-bold mb-0">Modifier le livreur</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="<?= base_url('livreurs/update/' . $lv['id_livreur']) ?>"
                          method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="modal-body px-4 py-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">
                                        Nom complet <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="nom_complet"
                                           class="form-control"
                                           value="<?= esc($lv['nom_complet']) ?>"
                                           required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">
                                        Téléphone <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="telephone"
                                           class="form-control"
                                           value="<?= esc($lv['telephone']) ?>"
                                           required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">
                                        Téléphone 2
                                    </label>
                                    <input type="text" name="telephone2"
                                           class="form-control"
                                           value="<?= esc($lv['telephone2'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Email</label>
                                    <input type="email" name="email"
                                           class="form-control"
                                           value="<?= esc($lv['email'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Adresse</label>
                                    <input type="text" name="adresse"
                                           class="form-control"
                                           value="<?= esc($lv['adresse'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">
                                        Zones de livraison
                                    </label>
                                    <input type="text" name="zone_livraison"
                                           class="form-control"
                                           value="<?= esc($lv['zone_livraison'] ?? '') ?>"
                                           placeholder="Ex: Akwa, Bonanjo, Deido...">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Véhicule</label>
                                    <select name="vehicule" class="form-select">
                                        <option value="">—</option>
                                        <?php foreach (['Moto','Voiture','Vélo','À pied','Autre'] as $v): ?>
                                        <option value="<?= $v ?>"
                                            <?= $lv['vehicule'] === $v ? 'selected' : '' ?>>
                                            <?= $v ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">
                                        N° Plaque
                                    </label>
                                    <input type="text" name="numero_plaque"
                                           class="form-control"
                                           value="<?= esc($lv['numero_plaque'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">
                                        Tarif de base (FCFA)
                                    </label>
                                    <input type="number" name="tarif_base"
                                           class="form-control"
                                           value="<?= $lv['tarif_base'] ?>"
                                           min="0" step="100">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Statut</label>
                                    <select name="statut" class="form-select">
                                        <option value="actif"
                                            <?= $lv['statut']==='actif' ? 'selected':'' ?>>
                                            Actif
                                        </option>
                                        <option value="inactif"
                                            <?= $lv['statut']==='inactif' ? 'selected':'' ?>>
                                            Inactif
                                        </option>
                                        <option value="suspendu"
                                            <?= $lv['statut']==='suspendu' ? 'selected':'' ?>>
                                            Suspendu
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Photo</label>
                                    <input type="file" name="photo"
                                           class="form-control" accept="image/*">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Note</label>
                                    <textarea name="note" class="form-control"
                                              rows="2"><?= esc($lv['note'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 px-4 pb-4 pt-0">
                            <button type="button" class="btn btn-light rounded-2"
                                    data-bs-dismiss="modal">Annuler</button>
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

<!-- Modal créer livreur -->
<div class="modal fade" id="modalCreer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <div>
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-motorcycle text-primary me-2"></i>
                        Nouveau livreur
                    </h5>
                    <small class="text-muted">
                        Livreur externe — sans accès à l'application
                    </small>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('livreurs/store') ?>"
                  method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">
                                Nom complet <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nom_complet"
                                   class="form-control"
                                   placeholder="Prénom et Nom" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">
                                Téléphone <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="telephone"
                                   class="form-control"
                                   placeholder="+237 6XX..." required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">
                                Téléphone 2
                            </label>
                            <input type="text" name="telephone2"
                                   class="form-control"
                                   placeholder="Optionnel">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email</label>
                            <input type="email" name="email"
                                   class="form-control"
                                   placeholder="email@exemple.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Adresse</label>
                            <input type="text" name="adresse"
                                   class="form-control"
                                   placeholder="Quartier, ville...">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">
                                Zones de livraison couvertes
                            </label>
                            <input type="text" name="zone_livraison"
                                   class="form-control"
                                   placeholder="Ex: Akwa, Bonanjo, Deido, Makepe...">
                            <div class="form-text" style="font-size:10px;">
                                Indiquez les quartiers ou zones que ce livreur peut couvrir.
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Véhicule</label>
                            <select name="vehicule" class="form-select">
                                <option value="">—</option>
                                <option value="Moto">🏍️ Moto</option>
                                <option value="Voiture">🚗 Voiture</option>
                                <option value="Vélo">🚲 Vélo</option>
                                <option value="À pied">🚶 À pied</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">N° Plaque</label>
                            <input type="text" name="numero_plaque"
                                   class="form-control"
                                   placeholder="LT-1234-A">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">
                                Tarif de base (FCFA)
                            </label>
                            <input type="number" name="tarif_base"
                                   class="form-control"
                                   placeholder="0" min="0" step="100" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Photo</label>
                            <input type="file" name="photo"
                                   class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">
                                Note interne
                            </label>
                            <textarea name="note" class="form-control" rows="2"
                                      placeholder="Informations supplémentaires...">
                            </textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2"
                            data-bs-dismiss="modal">Annuler</button>
                    <button type="submit"
                            class="btn btn-success px-4 rounded-2 fw-semibold">
                        <i class="fas fa-check me-2"></i>Créer le livreur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
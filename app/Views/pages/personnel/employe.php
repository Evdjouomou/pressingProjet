<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3">
            <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-0">Gestion du Personnel</h4>
            <small class="text-muted"><?= count($employees) ?> employé(s) enregistré(s)</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= base_url('shop') ?>" class="btn btn-outline-secondary rounded-2 px-3">
                <i class="fas fa-store me-2"></i>Boutiques
            </a>
            <a href="<?= base_url('poste') ?>" class="btn btn-outline-secondary rounded-2 px-3">
                <i class="fas fa-briefcase me-2"></i>Postes
            </a>
            <button type="button" class="btn btn-primary rounded-2 px-4"
                    data-bs-toggle="modal" data-bs-target="#modalCreer">
                <i class="fas fa-user-plus me-2"></i>Nouvel employé
            </button>
        </div>
    </div>

    <!-- Barre de recherche -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body py-3">
            <div class="position-relative" style="max-width:420px;">
                <i class="fas fa-search position-absolute text-muted"
                   style="top:50%;left:14px;transform:translateY(-50%);font-size:13px;"></i>
                <input type="text" id="searchInput"
                       class="form-control bg-light shadow-none ps-5"
                       placeholder="Nom, matricule, poste, boutique...">
            </div>
        </div>
    </div>

    <!-- Tableau employés -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tableEmployes">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">EMPLOYÉ</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">POSTE / BOUTIQUE</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">CONTACT</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">SALAIRE</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">STATUT</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $badgesStatut = [
                            'actif'      => ['label' => 'Actif',      'bg' => '#dcfce7', 'color' => '#166534'],
                            'conge'      => ['label' => 'En congé',   'bg' => '#fef3c7', 'color' => '#92400e'],
                            'inactif'    => ['label' => 'Inactif',    'bg' => '#f3f4f6', 'color' => '#374151'],
                            'désactiver' => ['label' => 'Désactivé',  'bg' => '#fee2e2', 'color' => '#991b1b'],
                        ];
                        $couleursBs = [
                            'actif'      => 'success',
                            'conge'      => 'warning',
                            'inactif'    => 'secondary',
                            'désactiver' => 'danger',
                        ];
                        foreach ($employees as $e):
                            $stat = $e['status'] ?? 'inactif';
                            $bs   = $badgesStatut[$stat] ?? ['label' => $stat, 'bg' => '#f3f4f6', 'color' => '#374151'];
                        ?>
                        <tr>
                            <!-- Employé -->
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= base_url('img/' . ($e['photo'] ?: 'default.png')) ?>"
                                         class="rounded-circle border"
                                         width="42" height="42"
                                         style="object-fit:cover;"
                                         onerror="this.src='<?= base_url('img/avatar-default.png') ?>'">
                                    <div>
                                        <div class="fw-semibold"><?= esc($e['nom_complet']) ?></div>
                                        <div class="text-muted" style="font-size:11px;">
                                            <?= esc($e['matricule']) ?> · <?= date('d/m/Y', strtotime($e['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Poste / Boutique -->
                            <td>
                                <span class="fw-semibold d-block"><?= esc($e['nom_poste']) ?></span>
                                <span class="text-muted small"><?= esc($e['nom_shop']) ?></span>
                            </td>

                            <!-- Contact -->
                            <td>
                                <span class="d-block"><?= esc($e['telephone']) ?></span>
                                <span class="text-muted small"><?= esc($e['email'] ?: '—') ?></span>
                            </td>

                            <!-- Salaire -->
                            <td class="text-end fw-bold">
                                <?= number_format($e['salaire'], 0, ',', ' ') ?> FCFA
                            </td>

                            <!-- Statut -->
                            <td class="text-center">
                                <span style="background:<?= $bs['bg'] ?>;color:<?= $bs['color'] ?>;
                                             padding:4px 14px;border-radius:20px;
                                             font-size:12px;font-weight:600;">
                                    <?= $bs['label'] ?>
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="text-center">
                                <button class="btn btn-sm"
                                        style="width:32px;height:32px;border-radius:8px;
                                               background:#f1f5f9;border:1px solid #e2e8f0;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalVoir<?= $e['id_employe'] ?>"
                                        title="Voir le profil">
                                    <i class="fas fa-eye fa-sm text-secondary"></i>
                                </button>
                                <button class="btn btn-sm ms-1"
                                        style="width:32px;height:32px;border-radius:8px;
                                               background:#eff6ff;border:1px solid #bfdbfe;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalModifier<?= $e['id_employe'] ?>"
                                        title="Modifier">
                                    <i class="fas fa-edit fa-sm text-primary"></i>
                                </button>
                                <a href="<?= base_url('personnel/delete/' . $e['id_employe']) ?>"
                                   class="btn btn-sm ms-1"
                                   style="width:32px;height:32px;border-radius:8px;
                                          background:#fff5f5;border:1px solid #fecaca;"
                                   onclick="return confirm('Supprimer cet employé définitivement ?')"
                                   title="Supprimer">
                                    <i class="fas fa-trash fa-sm text-danger"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- ══════════════════════════════ -->
                        <!-- MODAL VOIR PROFIL             -->
                        <!-- ══════════════════════════════ -->
                        <div class="modal fade" id="modalVoir<?= $e['id_employe'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
                                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

                                    <!-- Header avec photo -->
                                    <div class="text-center px-4 pt-4 pb-3"
                                         style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 100%);">
                                        <button type="button" class="btn-close btn-close-white float-end mt-1"
                                                data-bs-dismiss="modal"></button>
                                        <img src="<?= base_url('img/' . ($e['photo'] ?: 'default.png')) ?>"
                                             class="rounded-circle border border-3 border-white shadow mb-3"
                                             width="80" height="80"
                                             style="object-fit:cover;"
                                             onerror="this.src='<?= base_url('img/avatar-default.png') ?>'">
                                        <h5 class="fw-bold text-white mb-1"><?= esc($e['nom_complet']) ?></h5>
                                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                                            <span class="badge bg-white text-dark"
                                                  style="font-size:11px;"><?= esc($e['matricule']) ?></span>
                                            <span style="background:<?= $bs['bg'] ?>;color:<?= $bs['color'] ?>;
                                                         padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                                <?= $bs['label'] ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Corps -->
                                    <div class="p-4">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;">
                                                    <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">Poste</div>
                                                    <div class="fw-semibold mt-1" style="font-size:13px;"><?= esc($e['nom_poste']) ?></div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;">
                                                    <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">Boutique</div>
                                                    <div class="fw-semibold mt-1" style="font-size:13px;"><?= esc($e['nom_shop']) ?></div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;">
                                                    <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">Téléphone</div>
                                                    <div class="fw-semibold mt-1" style="font-size:13px;"><?= esc($e['telephone']) ?></div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div style="background:#f0fdf4;border-radius:10px;padding:10px 12px;">
                                                    <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">Salaire</div>
                                                    <div class="fw-semibold text-success mt-1" style="font-size:13px;">
                                                        <?= number_format($e['salaire'], 0, ',', ' ') ?> FCFA
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;">
                                                    <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">Email</div>
                                                    <div class="fw-semibold text-primary mt-1" style="font-size:13px;">
                                                        <?= esc($e['email'] ?: 'Non renseigné') ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;">
                                                    <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">N° CNI</div>
                                                    <div class="fw-semibold mt-1" style="font-size:13px;"><?= esc($e['num_cni'] ?? 'N/A') ?></div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div style="background:#fff5f5;border-radius:10px;padding:10px 12px;">
                                                    <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">Urgence</div>
                                                    <div class="fw-semibold text-danger mt-1" style="font-size:13px;">
                                                        <?= esc($e['num_urgence'] ?? 'N/A') ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div style="background:#f8fafc;border-radius:10px;padding:10px 12px;">
                                                    <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">Résidence</div>
                                                    <div class="fw-semibold mt-1" style="font-size:13px;"><?= esc($e['lieu_residence'] ?? 'N/A') ?></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2 mt-3">
                                            <button type="button" class="btn btn-secondary rounded-2 flex-fill"
                                                    data-bs-dismiss="modal">Fermer</button>
                                            <button type="button" class="btn btn-primary rounded-2 flex-fill"
                                                    data-bs-dismiss="modal"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalModifier<?= $e['id_employe'] ?>">
                                                <i class="fas fa-edit me-1"></i>Modifier
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- ══════════════════════════════ -->
                        <!-- MODAL MODIFIER                -->
                        <!-- ══════════════════════════════ -->
                        <div class="modal fade" id="modalModifier<?= $e['id_employe'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
                                <div class="modal-content border-0 shadow-lg rounded-4">

                                    <div class="modal-header border-0 px-4 pt-4 pb-0">
                                        <div>
                                            <h5 class="fw-bold mb-0">Modifier le profil</h5>
                                            <small class="text-muted"><?= esc($e['nom_complet']) ?></small>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="<?= base_url('personnel/update/' . $e['id_employe']) ?>"
                                          method="post" enctype="multipart/form-data">

                                        <div class="modal-body px-4 py-3">
                                            <div class="row g-3">

                                                <!-- Statut -->
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold small">Statut</label>
                                                    <?php
                                                        $statActuel = $e['status'] ?? '';
                                                        $cBs = $couleursBs[$statActuel] ?? 'secondary';
                                                    ?>
                                                    <div class="mb-1">
                                                        <span class="badge bg-<?= $cBs ?>">
                                                            Actuel : <?= ucfirst($statActuel ?: 'Non défini') ?>
                                                        </span>
                                                    </div>
                                                    <select name="status" class="form-select" required>
                                                        <option value="" disabled>-- Choisir --</option>
                                                        <option value="actif"      <?= $statActuel === 'actif'      ? 'selected' : '' ?>>Actif</option>
                                                        <option value="conge"      <?= $statActuel === 'conge'      ? 'selected' : '' ?>>En congé</option>
                                                        <option value="inactif"    <?= $statActuel === 'inactif'    ? 'selected' : '' ?>>Inactif</option>
                                                        <option value="désactiver" <?= $statActuel === 'désactiver' ? 'selected' : '' ?>>Désactivé</option>
                                                    </select>
                                                </div>

                                                <!-- Téléphone -->
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold small">Téléphone</label>
                                                    <input type="text" name="telephone" class="form-control"
                                                           value="<?= esc($e['telephone']) ?>">
                                                </div>

                                                <!-- Email -->
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold small">Email</label>
                                                    <input type="email" name="email" class="form-control"
                                                           value="<?= esc($e['email'] ?? '') ?>">
                                                </div>

                                                <!-- Poste -->
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold small">Poste (salaire lié)</label>
                                                    <select name="id_poste" class="form-select">
                                                        <?php foreach ($postes as $p): ?>
                                                            <option value="<?= $p['id_poste'] ?>"
                                                                <?= $e['poste_id'] == $p['id_poste'] ? 'selected' : '' ?>>
                                                                <?= esc($p['nom_poste']) ?>
                                                                (<?= number_format($p['salaire'], 0, ',', ' ') ?> FCFA)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <!-- Boutique -->
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold small">Boutique d'affectation</label>
                                                    <select name="id_shop" class="form-select">
                                                        <?php foreach ($shops as $s): ?>
                                                            <option value="<?= $s['id_shop'] ?>"
                                                                <?= $e['shop_id'] == $s['id_shop'] ? 'selected' : '' ?>>
                                                                <?= esc($s['nom_shop']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <!-- Photo -->
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold small">
                                                        Changer la photo
                                                        <span class="text-muted fw-normal">(optionnel)</span>
                                                    </label>
                                                    <input type="file" name="photo"
                                                           class="form-control" accept="image/*">
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
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- ══════════════════════════════════════════ -->
<!-- MODAL CRÉER UN EMPLOYÉ                    -->
<!-- (En dehors du foreach)                    -->
<!-- ══════════════════════════════════════════ -->
<div class="modal fade" id="modalCreer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <div>
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-user-plus text-primary me-2"></i>Nouvel employé
                    </h5>
                    <small class="text-muted">Remplissez toutes les informations ci-dessous</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?= base_url('personnel/store') ?>" method="POST" enctype="multipart/form-data">

                <div class="modal-body px-4 py-3">

                    <!-- Section 1 : Identité -->
                    <p class="text-uppercase fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;color:#3b82f6;">
                        <i class="fas fa-id-card me-2"></i>Informations identitaires
                    </p>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" name="nom_complet" class="form-control"
                                   placeholder="Nom et Prénom" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Numéro de CNI <span class="text-danger">*</span></label>
                            <input type="text" name="num_cni" class="form-control"
                                   placeholder="Numéro de la carte d'identité" required>
                        </div>
                    </div>

                    <!-- Section 2 : Contact -->
                    <p class="text-uppercase fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;color:#3b82f6;">
                        <i class="fas fa-phone me-2"></i>Contacts et résidence
                    </p>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Téléphone <span class="text-danger">*</span></label>
                            <input type="tel" name="telephone" class="form-control"
                                   placeholder="6XXXXXXXX" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Email</label>
                            <input type="email" name="email" class="form-control"
                                   placeholder="employe@exemple.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Numéro d'urgence</label>
                            <input type="tel" name="num_urgence" class="form-control"
                                   placeholder="Contact d'un proche">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Lieu de résidence</label>
                            <input type="text" name="lieu_residence" class="form-control"
                                   placeholder="Quartier / Ville">
                        </div>
                    </div>

                    <!-- Section 3 : Affectation -->
                    <p class="text-uppercase fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;color:#3b82f6;">
                        <i class="fas fa-briefcase me-2"></i>Affectation et accès système
                    </p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Poste <span class="text-danger">*</span></label>
                            <select name="poste_id" class="form-select" required>
                                <option value="" disabled selected>Choisir un poste...</option>
                                <?php foreach ($postes as $p): ?>
                                    <option value="<?= $p['id_poste'] ?>"
                                        <?= isset($e) && $e['poste_id'] == $p['id_poste'] ? 'selected' : '' ?>>
                                        <?= esc($p['nom_poste']) ?>
                                        (<?= number_format($p['salaire'], 0, ',', ' ') ?> FCFA)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-danger">
                                Rôle système <span class="text-danger">*</span>
                            </label>
                            <select name="role" class="form-select" required>
                                <option value="employe" selected>Employé (accès limité)</option>
                                <option value="admin">Administrateur (accès complet)</option>
                            </select>
                            <div class="form-text" style="font-size:10px;">
                                Définit les droits de connexion.
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Boutique</label>
                            <select name="shop_id" class="form-select">
                                <option value="" disabled selected>Choisir une boutique...</option>
                                <?php foreach ($shops as $s): ?>
                                    <option value="<?= $s['id_shop'] ?>"
                                        <?= isset($e) && $e['shop_id'] == $s['id_shop'] ? 'selected' : '' ?>>
                                        <?= esc($s['nom_shop']) ?> — <?= esc($s['adresse']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Statut <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="actif" selected>Actif</option>
                                <option value="conge">En congé</option>
                                <option value="inactif">Inactif</option>
                                <option value="désactiver">Désactivé</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Photo de profil</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2"
                            data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success px-5 rounded-2 fw-semibold">
                        <i class="fas fa-check-circle me-2"></i>Enregistrer et créer le matricule
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Recherche temps réel -->
<script>
    document.getElementById('searchInput').addEventListener('keyup', function () {
        const filtre = this.value.toLowerCase();
        document.querySelectorAll('#tableEmployes tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(filtre) ? '' : 'none';
        });
    });
</script>

<?= $this->endSection() ?>
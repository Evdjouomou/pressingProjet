<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php
    $gravites = [
        'faible'   => ['label' => 'Faible',   'bg' => '#f0fdf4', 'color' => '#166534'],
        'moyen'    => ['label' => 'Moyen',    'bg' => '#fef3c7', 'color' => '#92400e'],
        'eleve'    => ['label' => 'Élevé',    'bg' => '#fff7ed', 'color' => '#c2410c'],
        'critique' => ['label' => 'Critique', 'bg' => '#fdf4ff', 'color' => '#7e22ce'],
    ];
    $statutsInc = [
        'ouvert'        => ['label' => 'Ouvert',        'bg' => '#fee2e2', 'color' => '#991b1b'],
        'en_traitement' => ['label' => 'En traitement', 'bg' => '#fef3c7', 'color' => '#92400e'],
        'resolu'        => ['label' => 'Résolu',        'bg' => '#d1fae5', 'color' => '#065f46'],
        'cloture'       => ['label' => 'Clôturé',      'bg' => '#f1f5f9', 'color' => '#374151'],
    ];
    $typesInc = [
        'article_endommage'   => '💔 Article endommagé',
        'article_perdu'       => '❓ Article perdu',
        'retard'              => '⏰ Retard',
        'qualite_insuffisante'=> '👎 Qualité insuffisante',
        'mauvais_traitement'  => '⚠️ Mauvais traitement',
        'autre'               => '📋 Autre',
    ];
    $sg = $gravites[$incident['gravite']]  ?? ['label' => $incident['gravite'],  'bg' => '#f1f5f9', 'color' => '#374151'];
    $si = $statutsInc[$incident['statut']] ?? ['label' => $incident['statut'],   'bg' => '#f1f5f9', 'color' => '#374151'];
    ?>

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <a href="<?= base_url('incidents') ?>" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
            <h4 class="fw-bold mb-0">
                Incident <span class="text-danger"><?= esc($incident['code_incident']) ?></span>
            </h4>
            <small class="text-muted">
                Déclaré le <?= date('d/m/Y à H:i', strtotime($incident['created_at'])) ?>
                par <?= esc($incident['declare_par'] ?? '—') ?>
            </small>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <span style="background:<?= $sg['bg'] ?>;color:<?= $sg['color'] ?>;
                         padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">
                <?= $sg['label'] ?>
            </span>
            <span style="background:<?= $si['bg'] ?>;color:<?= $si['color'] ?>;
                         padding:6px 16px;border-radius:20px;font-size:13px;font-weight:600;">
                <?= $si['label'] ?>
            </span>
            <?php if (!in_array($incident['statut'], ['cloture'])): ?>
            <button class="btn btn-outline-primary rounded-2 px-3"
                    data-bs-toggle="modal" data-bs-target="#modalUpdate">
                <i class="fas fa-sync me-2"></i>Mettre à jour
            </button>
            <?php if ($incident['statut'] !== 'resolu'): ?>
            <button class="btn btn-success rounded-2 px-3"
                    data-bs-toggle="modal" data-bs-target="#modalCloturer">
                <i class="fas fa-check me-2"></i>Clôturer
            </button>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">

        <!-- Infos -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body">
                    <p class="text-uppercase text-muted fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-user me-2"></i>Client
                    </p>
                    <h5 class="fw-bold mb-1"><?= esc($incident['nomclient']) ?></h5>
                    <p class="text-muted mb-0">
                        <i class="fas fa-phone me-1"></i><?= esc($incident['telephone']) ?>
                    </p>
                    <?php if ($incident['email']): ?>
                    <p class="text-muted mb-0" style="font-size:12px;">
                        <i class="fas fa-envelope me-1"></i><?= esc($incident['email']) ?>
                    </p>
                    <?php endif; ?>

                    <hr>

                    <p class="text-uppercase text-muted fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-exclamation-triangle me-2"></i>Incident
                    </p>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Type</span>
                        <strong style="font-size:12px;">
                            <?= $typesInc[$incident['type_incident']] ?? $incident['type_incident'] ?>
                        </strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Responsable</span>
                        <strong><?= esc($incident['responsable'] ?? '—') ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Délai résolution</span>
                        <strong><?= $incident['delai_resolution']
                            ? date('d/m/Y', strtotime($incident['delai_resolution']))
                            : '—' ?></strong>
                    </div>
                    <?php if ($incident['code_commande']): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Dépôt</span>
                        <a href="<?= base_url('depot/detail/' . $incident['depot_id']) ?>"
                           class="text-primary fw-semibold">
                            <?= esc($incident['code_commande']) ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php if ($incident['article_nom']): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Article</span>
                        <strong><?= esc($incident['article_nom']) ?></strong>
                    </div>
                    <?php endif; ?>

                    <?php if ($incident['statut'] === 'cloture'): ?>
                    <hr>
                    <p class="text-uppercase text-muted fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-check-circle me-2"></i>Résolution
                    </p>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Type</span>
                        <strong><?= ucfirst(str_replace('_',' ',$incident['type_resolution'] ?? '—')) ?></strong>
                    </div>
                    <?php if ($incident['montant_resolution'] > 0): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Montant</span>
                        <strong class="text-success">
                            <?= number_format($incident['montant_resolution'], 0, ',', ' ') ?> FCFA
                        </strong>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Clôturé le</span>
                        <strong><?= $incident['date_cloture']
                            ? date('d/m/Y', strtotime($incident['date_cloture']))
                            : '—' ?></strong>
                    </div>
                    <?php if ($incident['note_resolution']): ?>
                    <p class="text-muted small mt-2 fst-italic mb-0">
                        <?= esc($incident['note_resolution']) ?>
                    </p>
                    <?php endif; ?>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Ajouter photo -->
            <?php if ($incident['statut'] !== 'cloture'): ?>
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <p class="text-uppercase text-muted fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-camera me-2"></i>Ajouter des photos
                    </p>
                    <form action="<?= base_url('incidents/' . $incident['id_incident'] . '/photo') ?>"
                          method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="file" name="photos[photos][]" class="form-control form-control-sm mb-2"
                               accept="image/*" multiple required>
                        <button type="submit" class="btn btn-outline-primary btn-sm rounded-2 w-100">
                            <i class="fas fa-upload me-1"></i>Uploader
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Description + Photos -->
        <div class="col-md-8">

            <!-- Description -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom">
                        <p class="text-uppercase text-muted fw-semibold mb-0"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-file-alt me-2"></i>Description
                        </p>
                    </div>
                    <div class="p-4" style="font-size:14px;line-height:1.8;color:#374151;">
                        <?= nl2br(esc($incident['description'])) ?>
                    </div>
                </div>
            </div>

            <!-- Photos -->
            <?php if (!empty($photos)): ?>
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                        <p class="text-uppercase text-muted fw-semibold mb-0"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-images me-2"></i>Photos
                        </p>
                        <span class="badge bg-primary rounded-pill"><?= count($photos) ?></span>
                    </div>
                    <div class="p-4">
                        <div class="row g-3">
                            <?php foreach ($photos as $ph): ?>
                            <div class="col-6 col-md-4">
                                <div class="position-relative">
                                    <img src="<?= base_url('uploads/incidents/' . $ph['nom_fichier']) ?>"
                                         class="img-fluid rounded-3 w-100"
                                         style="height:160px;object-fit:cover;cursor:pointer;"
                                         onclick="this.requestFullscreen()"
                                         title="Cliquer pour agrandir">
                                    <?php if ($incident['statut'] !== 'cloture'): ?>
                                    <a href="<?= base_url('incidents/photo/delete/' . $ph['id_photo']) ?>"
                                       class="position-absolute top-0 end-0 m-1 btn btn-sm btn-danger rounded-2"
                                       style="padding:2px 6px;"
                                       onclick="return confirm('Supprimer cette photo ?')">
                                        <i class="fas fa-times" style="font-size:10px;"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Modal Mettre à jour -->
<div class="modal fade" id="modalUpdate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-0">Mettre à jour l'incident</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('incidents/update/' . $incident['id_incident']) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Statut</label>
                            <select name="statut" class="form-select">
                                <?php foreach ($statutsInc as $val => $s): ?>
                                <option value="<?= $val ?>" <?= $incident['statut']===$val ? 'selected':'' ?>>
                                    <?= $s['label'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Gravité</label>
                            <select name="gravite" class="form-select">
                                <?php foreach ($gravites as $val => $g): ?>
                                <option value="<?= $val ?>" <?= $incident['gravite']===$val ? 'selected':'' ?>>
                                    <?= $g['label'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Responsable</label>
                            <select name="responsable_id" class="form-select">
                                <option value="">-- Aucun --</option>
                                <?php foreach ($employes as $e): ?>
                                <option value="<?= $e['id_employe'] ?>"
                                    <?= $incident['responsable_id'] == $e['id_employe'] ? 'selected':'' ?>>
                                    <?= esc($e['nom_complet']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Délai résolution</label>
                            <input type="date" name="delai_resolution" class="form-control"
                                   value="<?= $incident['delai_resolution'] ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Description (mise à jour)</label>
                            <textarea name="description" class="form-control" rows="3"><?= esc($incident['description']) ?></textarea>
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

<!-- Modal Clôturer -->
<div class="modal fade" id="modalCloturer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-0">
                    <i class="fas fa-check text-success me-2"></i>Clôturer l'incident
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('incidents/cloturer/' . $incident['id_incident']) ?>"
                  method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Type de résolution <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <?php
                            $resolutions = [
                                'avoir'         => ['label' => '🎁 Avoir client',    'color' => 'warning'],
                                'remboursement' => ['label' => '💵 Remboursement',   'color' => 'success'],
                                'compensation'  => ['label' => '🤝 Compensation',    'color' => 'info'],
                                'aucune'        => ['label' => '✅ Sans compensation','color' => 'secondary'],
                            ];
                            foreach ($resolutions as $val => $res): ?>
                            <div class="col-6">
                                <input type="radio" class="btn-check"
                                       name="type_resolution" id="res_<?= $val ?>"
                                       value="<?= $val ?>" required
                                       <?= $val === 'aucune' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-<?= $res['color'] ?> w-100 text-start"
                                       for="res_<?= $val ?>" style="font-size:12px;">
                                    <?= $res['label'] ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-3" id="zoneMontant">
                        <label class="form-label fw-semibold small">Montant (FCFA)</label>
                        <div class="input-group">
                            <input type="number" name="montant_resolution"
                                   class="form-control" placeholder="0" min="0" step="100">
                            <span class="input-group-text">FCFA</span>
                        </div>
                        <div class="form-text" style="font-size:10px;">
                            Pour un avoir : le montant sera crédité sur le compte prépayé du client.
                        </div>
                    </div>

                    <div>
                        <label class="form-label fw-semibold small">Note de clôture</label>
                        <textarea name="note_resolution" class="form-control" rows="3"
                                  placeholder="Expliquez la résolution, les actions prises..."></textarea>
                    </div>

                    <div class="mt-3 rounded-3 p-3"
                         style="background:#f0fdf4;border:1px solid #bbf7d0;">
                        <p class="text-success fw-semibold mb-0" style="font-size:12px;">
                            <i class="fas fa-envelope me-1"></i>
                            Le client sera automatiquement notifié de la résolution de son incident.
                        </p>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success px-4 rounded-2 fw-semibold">
                        <i class="fas fa-check me-2"></i>Clôturer l'incident
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
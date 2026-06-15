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
    $statutsLiv = [
        'en_attente' => ['label' => 'En attente', 'bg' => '#fef3c7', 'color' => '#92400e'],
        'assignee'   => ['label' => 'Assignée',   'bg' => '#dbeafe', 'color' => '#1d4ed8'],
        'en_cours'   => ['label' => 'En cours',   'bg' => '#ecfeff', 'color' => '#0e7490'],
        'livree'     => ['label' => 'Livrée',     'bg' => '#dcfce7', 'color' => '#166534'],
        'echec'      => ['label' => 'Échec',      'bg' => '#fee2e2', 'color' => '#991b1b'],
        'annulee'    => ['label' => 'Annulée',    'bg' => '#f1f5f9', 'color' => '#374151'],
    ];
    $sl = $statutsLiv[$liv['statut']] ?? ['label' => $liv['statut'], 'bg' => '#f1f5f9', 'color' => '#374151'];
    $reste = max(0, $liv['total_ttc'] - $liv['acompte_verse']);
    ?>

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <a href="<?= base_url('livraison') ?>" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
            <h4 class="fw-bold mb-0">
                Livraison <span class="text-primary"><?= esc($liv['code_livraison']) ?></span>
            </h4>
            <small class="text-muted">
                Commande : <a href="<?= base_url('depot/detail/' . $liv['depot_id']) ?>"
                              class="text-primary"><?= esc($liv['code_commande']) ?></a>
            </small>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <span style="background:<?= $sl['bg'] ?>;color:<?= $sl['color'] ?>;
                         padding:6px 16px;border-radius:20px;font-size:13px;font-weight:600;">
                <?= $sl['label'] ?>
            </span>
            <a href="<?= base_url('livraison/fiche/' . $liv['id_livraison']) ?>"
               target="_blank"
               class="btn btn-outline-primary rounded-2 px-3">
                <i class="fas fa-print me-2"></i>Fiche livreur
            </a>
            <?php if ($liv['statut'] !== 'livree' && $liv['statut'] !== 'annulee'): ?>
            <button class="btn btn-success rounded-2 px-3"
                    data-bs-toggle="modal" data-bs-target="#modalStatut">
                <i class="fas fa-sync me-2"></i>Changer statut
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">

        <!-- Infos livraison -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body">

                    <p class="text-uppercase text-muted fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-user me-2"></i>Client
                    </p>
                    <h5 class="fw-bold mb-1"><?= esc($liv['nomclient']) ?></h5>
                    <p class="text-muted mb-0">
                        <i class="fas fa-phone me-1"></i><?= esc($liv['telephone']) ?>
                    </p>
                    <?php if ($liv['email']): ?>
                    <p class="text-muted mb-0" style="font-size:12px;">
                        <i class="fas fa-envelope me-1"></i><?= esc($liv['email']) ?>
                    </p>
                    <?php endif; ?>

                    <hr>

                    <p class="text-uppercase text-muted fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-map-marker-alt me-2"></i>Livraison
                    </p>

                    <div class="rounded-3 p-3 mb-3"
                         style="background:#f8fafc;border:1px solid #e2e8f0;">
                        <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">
                            Adresse
                        </div>
                        <div class="fw-semibold mt-1"><?= esc($liv['adresse_livraison']) ?></div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <div style="background:#f8fafc;border-radius:8px;padding:10px;">
                                <div class="text-muted" style="font-size:10px;text-transform:uppercase;">Date</div>
                                <div class="fw-semibold mt-1" style="font-size:13px;">
                                    <?= $liv['date_livraison']
                                        ? date('d/m/Y', strtotime($liv['date_livraison']))
                                        : '—' ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="background:#f8fafc;border-radius:8px;padding:10px;">
                                <div class="text-muted" style="font-size:10px;text-transform:uppercase;">Heure</div>
                                <div class="fw-semibold mt-1" style="font-size:13px;">
                                    <?= $liv['heure_livraison']
                                        ? substr($liv['heure_livraison'], 0, 5)
                                        : '—' ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($liv['note_client']): ?>
                    <div class="mt-3 rounded-3 p-3"
                         style="background:#fffbeb;border:1px solid #fde68a;">
                        <div class="text-muted" style="font-size:10px;text-transform:uppercase;">
                            Note du client
                        </div>
                        <div style="font-size:12px;margin-top:4px;">
                            <?= esc($liv['note_client']) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <hr>

                    <p class="text-uppercase text-muted fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-motorcycle me-2"></i>Livreur
                    </p>

                    <?php if ($liv['livreur_nom']): ?>
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3"
                         style="background:#f0fdf4;border:1px solid #bbf7d0;">
                        <i class="fas fa-user-circle fa-2x text-success"></i>
                        <div>
                            <div class="fw-bold"><?= esc($liv['livreur_nom']) ?></div>
                            <div class="text-muted small">
                                <i class="fas fa-phone me-1"></i><?= esc($liv['livreur_tel'] ?? '—') ?>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <button class="btn btn-outline-warning w-100 rounded-2"
                            data-bs-toggle="modal" data-bs-target="#modalAssignerDetail">
                        <i class="fas fa-user-plus me-2"></i>Assigner un livreur
                    </button>
                    <?php endif; ?>

                    <hr>

                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Total commande</span>
                        <strong><?= number_format($liv['total_ttc'], 0, ',', ' ') ?> FCFA</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Reste à payer</span>
                        <strong class="<?= $reste > 0 ? 'text-danger' : 'text-success' ?>">
                            <?= $reste > 0
                                ? number_format($reste, 0, ',', ' ') . ' FCFA'
                                : '✓ Soldé' ?>
                        </strong>
                    </div>
                    <?php if ($liv['montant_livraison'] > 0): ?>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Frais de livraison</span>
                        <strong><?= number_format($liv['montant_livraison'], 0, ',', ' ') ?> FCFA</strong>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- Articles -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                        <p class="text-uppercase text-muted fw-semibold mb-0"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-tshirt me-2"></i>Articles à livrer
                        </p>
                        <span class="badge bg-primary rounded-pill"><?= count($articles) ?></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 text-muted fw-semibold" style="font-size:11px;">ARTICLE</th>
                                    <th class="text-muted fw-semibold" style="font-size:11px;">PRESTATION</th>
                                    <th class="text-end text-muted fw-semibold" style="font-size:11px;">PRIX</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($articles as $art): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold"><?= esc($art['nom_libelle']) ?></div>
                                        <div class="text-muted" style="font-size:11px;">
                                            <?= esc($art['designation_libre'] ?: '—') ?>
                                        </div>
                                        <div style="font-family:monospace;font-size:10px;color:#9ca3af;">
                                            <?= esc($art['barcode_unique']) ?>
                                        </div>
                                    </td>
                                    <td style="font-size:12px;">
                                        <?= esc($art['type_prestation'] ?? '—') ?>
                                        <?php if ($art['options_express']): ?>
                                            <span class="badge bg-danger ms-1" style="font-size:9px;">
                                                🚀 Express
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        <?= number_format($art['prix_applique'] ?? 0, 0, ',', ' ') ?> FCFA
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if ($liv['note_livreur']): ?>
            <div class="card border-0 shadow-sm rounded-3 mt-4">
                <div class="card-body">
                    <p class="text-uppercase text-muted fw-semibold mb-2"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-sticky-note me-2"></i>Note du livreur
                    </p>
                    <p class="mb-0" style="font-size:13px;"><?= esc($liv['note_livreur']) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal changer statut -->
<div class="modal fade" id="modalStatut" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-0">Mettre à jour le statut</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('livraison/statut/' . $liv['id_livraison']) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nouveau statut</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php
                            $prochains = [
                                'en_attente' => ['en_cours','annulee'],
                                'assignee'   => ['en_cours','annulee'],
                                'en_cours'   => ['livree','echec'],
                                'echec'      => ['en_cours','annulee'],
                            ][$liv['statut']] ?? [];
                            foreach ($prochains as $val):
                                $s = $statutsLiv[$val] ?? ['label' => $val, 'bg' => '#f1f5f9', 'color' => '#374151'];
                            ?>
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="statut"
                                       id="st_<?= $val ?>" value="<?= $val ?>"
                                       <?= $val === 'livree' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-secondary w-100 text-start py-2"
                                       for="st_<?= $val ?>">
                                    <span style="display:inline-block;width:10px;height:10px;
                                                 border-radius:50%;background:<?= $s['color'] ?>;
                                                 margin-right:6px;"></span>
                                    <?= $s['label'] ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div>
                        <label class="form-label fw-semibold small">Note du livreur</label>
                        <textarea name="note_livreur" class="form-control" rows="3"
                                  placeholder="Commentaire sur la livraison..."><?= esc($liv['note_livreur'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success px-4 rounded-2 fw-semibold">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal assigner livreur (depuis détail) -->
<?php if (!$liv['livreur_nom']): ?>
<div class="modal fade" id="modalAssignerDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-0">Assigner un livreur</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('livraison/assigner/' . $liv['id_livraison']) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">
                    <label class="form-label fw-semibold small">
                        Livreur <span class="text-danger">*</span>
                    </label>
                    <select name="livreur_id" class="form-select" required>
                        <option value="" disabled selected>Choisir un livreur...</option>
                        <?php foreach ($livreurs as $lv): ?>
                        <option value="<?= $lv['id_livreur'] ?>">
                            <?= esc($lv['nom_complet']) ?>
                            <?= $lv['vehicule'] ? ' — ' . esc($lv['vehicule']) : '' ?>
                            <?= $lv['zone_livraison'] ? ' (' . esc($lv['zone_livraison']) . ')' : '' ?>
                            — <?= esc($lv['telephone']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-2">
                        <i class="fas fa-user-check me-2"></i>Assigner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
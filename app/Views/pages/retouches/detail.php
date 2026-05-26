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
    $statutsRet = [
        'en_attente' => ['label' => 'En attente', 'bg' => '#fef3c7', 'color' => '#92400e', 'bs' => 'warning'],
        'en_cours'   => ['label' => 'En cours',   'bg' => '#dbeafe', 'color' => '#1d4ed8', 'bs' => 'primary'],
        'fait'       => ['label' => 'Fait',       'bg' => '#d1fae5', 'color' => '#065f46', 'bs' => 'success'],
        'livre'      => ['label' => 'Livré',      'bg' => '#dcfce7', 'color' => '#166534', 'bs' => 'success'],
        'annule'     => ['label' => 'Annulé',     'bg' => '#fee2e2', 'color' => '#991b1b', 'bs' => 'danger'],
    ];
    $typesRetouche = [
        'ourlet'          => '📏 Ourlet',
        'fermeture_eclair'=> '🔒 Fermeture éclair',
        'bouton'          => '🔘 Bouton',
        'couture'         => '🧵 Couture',
        'teinture'        => '🎨 Teinture',
        'restauration'    => '✨ Restauration',
        'broderie'        => '🌸 Broderie',
        'autre'           => '⚙️ Autre',
    ];
    $sr   = $statutsRet[$retouche['statut']] ?? ['label' => $retouche['statut'], 'bg' => '#f1f5f9', 'color' => '#374151', 'bs' => 'secondary'];
    $reste = max(0, $retouche['prix'] - $retouche['acompte_verse']);
    ?>

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <a href="<?= base_url('retouches') ?>" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
            <h4 class="fw-bold mb-0">
                Retouche <span class="text-primary"><?= esc($retouche['code_retouche']) ?></span>
            </h4>
            <small class="text-muted">
                Créée le <?= date('d/m/Y à H:i', strtotime($retouche['created_at'])) ?>
            </small>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <span style="background:<?= $sr['bg'] ?>;color:<?= $sr['color'] ?>;
                         padding:6px 16px;border-radius:20px;font-size:13px;font-weight:600;">
                <?= $sr['label'] ?>
            </span>
            <?php if (!in_array($retouche['statut'], ['livre','annule'])): ?>
            <button class="btn btn-outline-primary rounded-2 px-3"
                    data-bs-toggle="modal" data-bs-target="#modalStatut">
                <i class="fas fa-sync me-2"></i>Changer statut
            </button>
            <button class="btn btn-outline-secondary rounded-2 px-3"
                    data-bs-toggle="modal" data-bs-target="#modalModifier">
                <i class="fas fa-edit me-2"></i>Modifier
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">

        <!-- Infos -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body">

                    <p class="text-uppercase text-muted fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-user me-2"></i>Client
                    </p>
                    <h5 class="fw-bold mb-1"><?= esc($retouche['nomclient']) ?></h5>
                    <p class="text-muted mb-3">
                        <i class="fas fa-phone me-1"></i><?= esc($retouche['telephone']) ?>
                    </p>

                    <hr>

                    <p class="text-uppercase text-muted fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-scissors me-2"></i>Retouche
                    </p>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Type</span>
                        <strong><?= $typesRetouche[$retouche['type_retouche']] ?? $retouche['type_retouche'] ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Retoucheur</span>
                        <strong><?= esc($retouche['retoucheur'] ?? '—') ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Délai estimé</span>
                        <strong><?= $retouche['delai_estime']
                            ? date('d/m/Y', strtotime($retouche['delai_estime']))
                            : '—' ?></strong>
                    </div>
                    <?php if ($retouche['code_commande']): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Dépôt lié</span>
                        <a href="<?= base_url('depot/detail/' . $retouche['depot_id']) ?>"
                           class="text-primary fw-semibold">
                            <?= esc($retouche['code_commande']) ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php if ($retouche['nom_libelle']): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Article</span>
                        <strong><?= esc($retouche['nom_libelle']) ?></strong>
                    </div>
                    <?php endif; ?>

                    <hr>

                    <p class="text-uppercase text-muted fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-coins me-2"></i>Paiement
                    </p>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Prix total</span>
                        <strong class="text-success">
                            <?= number_format($retouche['prix'], 0, ',', ' ') ?> FCFA
                        </strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Acompte versé</span>
                        <strong><?= number_format($retouche['acompte_verse'], 0, ',', ' ') ?> FCFA</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Reste à payer</span>
                        <?php if ($reste > 0): ?>
                        <strong class="text-danger">
                            <?= number_format($reste, 0, ',', ' ') ?> FCFA
                        </strong>
                        <?php else: ?>
                        <span class="badge bg-success">Soldé</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($retouche['observations']): ?>
                    <hr>
                    <p class="text-muted small mb-0 fst-italic">
                        <?= esc($retouche['observations']) ?>
                    </p>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom">
                        <p class="text-uppercase text-muted fw-semibold mb-0"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-file-alt me-2"></i>Description de la retouche
                        </p>
                    </div>
                    <div class="p-4" style="font-size:14px;line-height:1.8;color:#374151;">
                        <?= nl2br(esc($retouche['description'])) ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal changer statut -->
<div class="modal fade" id="modalStatut" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-0">Changer le statut</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('retouches/statut/' . $retouche['id_retouche']) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nouveau statut</label>
                        <select name="statut" class="form-select" required>
                            <?php foreach ($statutsRet as $val => $s): ?>
                            <option value="<?= $val ?>" <?= $retouche['statut'] === $val ? 'selected':'' ?>>
                                <?= $s['label'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label fw-semibold small">Retoucheur assigné</label>
                        <select name="employe_id" class="form-select">
                            <option value="">-- Aucun --</option>
                            <?php foreach ($employes as $e): ?>
                            <option value="<?= $e['id_employe'] ?>"
                                <?= $retouche['employe_id'] == $e['id_employe'] ? 'selected':'' ?>>
                                <?= esc($e['nom_complet']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
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

<!-- Modal modifier -->
<div class="modal fade" id="modalModifier" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-0">Modifier la retouche</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('retouches/update/' . $retouche['id_retouche']) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Type</label>
                            <select name="type_retouche" class="form-select">
                                <option value="ourlet"          <?= $retouche['type_retouche']==='ourlet'          ? 'selected':'' ?>>📏 Ourlet</option>
                                <option value="fermeture_eclair"<?= $retouche['type_retouche']==='fermeture_eclair'? 'selected':'' ?>>🔒 Fermeture éclair</option>
                                <option value="bouton"          <?= $retouche['type_retouche']==='bouton'          ? 'selected':'' ?>>🔘 Bouton</option>
                                <option value="couture"         <?= $retouche['type_retouche']==='couture'         ? 'selected':'' ?>>🧵 Couture</option>
                                <option value="teinture"        <?= $retouche['type_retouche']==='teinture'        ? 'selected':'' ?>>🎨 Teinture</option>
                                <option value="restauration"    <?= $retouche['type_retouche']==='restauration'    ? 'selected':'' ?>>✨ Restauration</option>
                                <option value="broderie"        <?= $retouche['type_retouche']==='broderie'        ? 'selected':'' ?>>🌸 Broderie</option>
                                <option value="autre"           <?= $retouche['type_retouche']==='autre'           ? 'selected':'' ?>>⚙️ Autre</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Retoucheur</label>
                            <select name="employe_id" class="form-select">
                                <option value="">-- Aucun --</option>
                                <?php foreach ($employes as $e): ?>
                                <option value="<?= $e['id_employe'] ?>"
                                    <?= $retouche['employe_id'] == $e['id_employe'] ? 'selected':'' ?>>
                                    <?= esc($e['nom_complet']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Description</label>
                            <textarea name="description" class="form-control" rows="3" required><?= esc($retouche['description']) ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Prix (FCFA)</label>
                            <input type="number" name="prix" class="form-control" value="<?= $retouche['prix'] ?>" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Acompte versé (FCFA)</label>
                            <input type="number" name="acompte_verse" class="form-control" value="<?= $retouche['acompte_verse'] ?>" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Délai estimé</label>
                            <input type="date" name="delai_estime" class="form-control" value="<?= $retouche['delai_estime'] ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Observations</label>
                            <textarea name="observations" class="form-control" rows="2"><?= esc($retouche['observations'] ?? '') ?></textarea>
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

<?= $this->endSection() ?>
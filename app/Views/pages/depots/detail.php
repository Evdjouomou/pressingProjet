<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= session()->getFlashdata('success') ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <a href="<?= base_url('depot') ?>" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i> Retour
            </a>
            <h4 class="fw-bold mb-0">
                Dépôt <span class="text-primary"><?= esc($depot['code_commande']) ?></span>
            </h4>
            <small class="text-muted">
                Enregistré le <?= date('d/m/Y à H:i', strtotime($depot['created_at'])) ?>
            </small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= base_url('depot/imprimer/'.$depot['id_depot']) ?>" target="_blank"
               class="btn btn-outline-primary">
                <i class="fas fa-print me-1"></i> Bon client
            </a>
            <a href="<?= base_url('depot/fiche-prod/'.$depot['id_depot']) ?>" target="_blank"
               class="btn btn-outline-dark">
                <i class="fas fa-clipboard-list me-1"></i> Fiche production
            </a>
        </div>
    </div>

    <div class="row g-4">

        <!-- Infos client & dépôt -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-uppercase text-muted fw-semibold mb-3" style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-user me-2"></i>Client
                    </p>
                    <h5 class="fw-bold mb-1"><?= esc($depot['nomclient']) ?></h5>
                    <p class="text-muted mb-3"><i class="fas fa-phone me-1"></i><?= esc($depot['telephone']) ?></p>

                    <hr>

                    <p class="text-uppercase text-muted fw-semibold mb-3" style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-info-circle me-2"></i>Informations dépôt
                    </p>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Statut</span>
                        <?php
                        $statuts = [
                            'depot'     => ['label' => 'Reçu',       'class' => 'bg-secondary'],
                            'en_cours'  => ['label' => 'En cours',   'class' => 'bg-warning text-dark'],
                            'pret'      => ['label' => 'Prêt',       'class' => 'bg-info text-dark'],
                            'livre'     => ['label' => 'Livré',      'class' => 'bg-success'],
                            'annule'    => ['label' => 'Annulé',     'class' => 'bg-danger'],
                        ];
                        $s = $statuts[$depot['statut_global']] ?? ['label' => $depot['statut_global'], 'class' => 'bg-secondary'];
                        ?>
                        <span class="badge <?= $s['class'] ?>"><?= $s['label'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Retrait prévu</span>
                        <strong><?= $depot['date_livraison_prevue'] ? date('d/m/Y', strtotime($depot['date_livraison_prevue'])) : '—' ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Articles</span>
                        <strong><?= $depot['nb_articles'] ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Total TTC</span>
                        <strong class="text-success"><?= number_format($depot['total_ttc'], 0, ',', ' ') ?> FCFA</strong>
                    </div>
                    <?php $reste = max(0, $depot['total_ttc'] - $depot['acompte_verse']); ?>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">Acompte versé</span>
                        <strong><?= number_format($depot['acompte_verse'], 0, ',', ' ') ?> FCFA</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center <?= $reste > 0 ? 'mb-3' : 'mb-0' ?>">
                        <span class="text-muted small">Reste à payer</span>
                        <?php if ($reste > 0): ?>
                            <strong class="text-danger"><?= number_format($reste, 0, ',', ' ') ?> FCFA</strong>
                        <?php else: ?>
                            <span class="badge bg-success">
                                <i class="fas fa-check me-1"></i>Soldé
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($reste > 0): ?>
                    <div class="border-top pt-3 mt-1">

                        <!-- Affichage du montant restant -->
                        <div class="rounded-3 p-3 mb-3" style="background:#fff5f5; border:1px solid #fecaca;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-0" style="font-size:11px;">Montant restant dû</p>
                                    <p class="fw-bold text-danger mb-0 fs-5">
                                        <?= number_format($reste, 0, ',', ' ') ?> FCFA
                                    </p>
                                </div>
                                <i class="fas fa-exclamation-circle text-danger fa-2x opacity-50"></i>
                            </div>
                        </div>

                        <!-- Bouton qui ouvre le modal -->
                        <button type="button"
                                class="btn btn-success w-100"
                                data-bs-toggle="modal"
                                data-bs-target="#modalPaiement">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            Enregistrer le paiement
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="rounded-3 p-3 mt-3" style="background:#f0fdf4; border:1px solid #bbf7d0;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                            <div>
                                <p class="fw-semibold text-success mb-0" style="font-size:13px;">Dépôt entièrement soldé</p>
                                <p class="text-muted mb-0" style="font-size:11px;">Aucun montant restant dû</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Articles -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                        <p class="text-uppercase text-muted fw-semibold mb-0" style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-tshirt me-2"></i>Articles déposés
                        </p>
                        <span class="badge bg-primary rounded-pill"><?= $depot['nb_articles'] ?> article(s)</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 text-muted fw-semibold" style="font-size:11px;">Article</th>
                                    <th class="text-muted fw-semibold" style="font-size:11px;">Détails</th>
                                    <th class="text-muted fw-semibold" style="font-size:11px;">Prestation</th>
                                    <th class="text-end text-muted fw-semibold" style="font-size:11px;">Prix</th>
                                    <th class="text-center text-muted fw-semibold" style="font-size:11px;">Ticket</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($depot['articles'] as $art): ?>
                                <tr>
                                    <td class="ps-4">
                                        <strong><?= esc($art['nom_libelle']) ?></strong>
                                        <?php if ($art['options_express']): ?>
                                            <span class="badge bg-danger ms-1">🚀 Express</span>
                                        <?php endif; ?>
                                        <div class="text-muted small"><?= esc($art['barcode_unique']) ?></div>
                                    </td>
                                    <td>
                                        <span class="text-muted small">
                                            <?= esc($art['designation_libre']) ?>
                                            <?php if ($art['matiere']): ?>
                                                · <?= esc($art['matiere']) ?>
                                            <?php endif; ?>
                                        </span>
                                        <?php if ($art['observations']): ?>
                                            <div class="text-warning small"><i class="fas fa-exclamation-circle me-1"></i><?= esc($art['observations']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($art['type_prestation'] ?? '—') ?></td>
                                    <td class="text-end fw-bold text-success">
                                        <?= number_format($art['prix_applique'] ?? 0, 0, ',', ' ') ?> FCFA
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= base_url('depot/ticket/'.$depot['id_depot'].'/'.$art['id_article_depose']) ?>"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Imprimer ticket">
                                            <i class="fas fa-tag"></i>
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

    </div>
</div>

<?php $reste = max(0, $depot['total_ttc'] - $depot['acompte_verse']); ?>
<?php if ($reste > 0): ?>

<div class="modal fade" id="modalPaiement" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-money-bill-wave text-success me-2"></i>
                    Finaliser le paiement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body pt-2">

                <!-- Récap -->
                <div class="rounded-3 p-3 mb-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Bon n°</span>
                        <strong><?= esc($depot['code_commande']) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Total facture</span>
                        <strong><?= number_format($depot['total_ttc'], 0, ',', ' ') ?> FCFA</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Déjà versé</span>
                        <strong><?= number_format($depot['acompte_verse'], 0, ',', ' ') ?> FCFA</strong>
                    </div>
                    <div class="d-flex justify-content-between border-top pt-2 mt-1">
                        <span class="text-muted small fw-semibold">Reste à encaisser</span>
                        <strong class="text-danger fs-6"><?= number_format($reste, 0, ',', ' ') ?> FCFA</strong>
                    </div>
                </div>

                <form action="<?= base_url('depot/payer/' . $depot['id_depot']) ?>" method="POST" id="formPaiement">
                    <?= csrf_field() ?>

                    <!-- Mode de paiement -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Mode de paiement</label>
                        <div class="d-flex gap-2">
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="mode_reglement" id="mode_especes" value="especes" checked>
                                <label class="btn btn-outline-secondary w-100" for="mode_especes">
                                    <i class="fas fa-money-bill me-1"></i> Espèces
                                </label>
                            </div>
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="mode_reglement" id="mode_mobile" value="mobile_money">
                                <label class="btn btn-outline-secondary w-100" for="mode_mobile">
                                    <i class="fas fa-mobile-alt me-1"></i> Mobile Money
                                </label>
                            </div>
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="mode_reglement" id="mode_carte" value="carte">
                                <label class="btn btn-outline-secondary w-100" for="mode_carte">
                                    <i class="fas fa-credit-card me-1"></i> Carte
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Montant encaissé (modifiable si paiement partiel) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Montant encaissé <span class="text-muted fw-normal">(modifiable)</span>
                        </label>
                        <div class="input-group">
                            <input type="number"
                                    name="montant_encaisse"
                                    id="montant_encaisse"
                                    class="form-control"
                                    value="<?= $reste ?>"
                                    min="1"
                                    required>
                            <span class="input-group-text">FCFA</span>
                        </div>
                        <div class="form-text" id="info_montant"></div>
                    </div>

                    <!-- Avertissement si montant < reste -->
                    <div id="alerte_partiel" class="alert alert-warning py-2 d-none" style="font-size:12px;">
                        <i class="fas fa-info-circle me-1"></i>
                        Paiement partiel : un solde restera dû après cet encaissement.
                    </div>

                    <!-- Confirmation visuelle -->
                    <div class="rounded-3 p-3" style="background:#f0fdf4; border:1px solid #bbf7d0;">
                        <p class="text-success fw-semibold mb-0" style="font-size:13px;">
                            <i class="fas fa-shield-alt me-1"></i>
                            Cette action enregistre le paiement de façon définitive.
                        </p>
                        <p class="text-muted mb-0 mt-1" style="font-size:11px;">
                            Le dépôt sera marqué comme soldé et horodaté.
                        </p>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="formPaiement" class="btn btn-success px-4">
                    <i class="fas fa-check me-2"></i>Confirmer l'encaissement
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    const inputMontant  = document.getElementById('montant_encaisse');
    const alertePartiel = document.getElementById('alerte_partiel');
    const formPaiement  = document.getElementById('formPaiement');
    const resteTotal    = <?= $reste ?>;

    inputMontant.addEventListener('input', function () {
        const val = parseFloat(this.value) || 0;
        if (val > 0 && val < resteTotal) {
            alertePartiel.classList.remove('d-none');
        } else {
            alertePartiel.classList.add('d-none');
        }
    });

    formPaiement.addEventListener('submit', function (e) {
        const val = parseFloat(inputMontant.value) || 0;
        if (val <= 0) {
            e.preventDefault();
            inputMontant.classList.add('is-invalid');
            document.getElementById('info_montant').textContent = 'Veuillez saisir un montant supérieur à 0.';
            return;
        }
        inputMontant.classList.remove('is-invalid');
        document.getElementById('info_montant').textContent = '';
    });
</script>
<?php endif; ?>

<?= $this->endSection() ?>
<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4" style="max-width:700px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= base_url('ficheclient/' . $client['id_client']) ?>"
               class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
            <h4 class="fw-bold mb-0">Nouvel abonnement</h4>
            <small class="text-muted">Client : <strong><?= esc($client['nomclient']) ?></strong></small>
        </div>
    </div>

    <!-- Abonnement actif existant -->
    <?php if ($abon_actif): ?>
    <div class="alert rounded-3 d-flex align-items-start gap-3 mb-4"
         style="background:#fffbeb;border:1px solid #fde68a;">
        <i class="fas fa-exclamation-triangle text-warning fa-lg mt-1 flex-shrink-0"></i>
        <div>
            <div class="fw-semibold" style="color:#92400e;">
                Ce client a déjà un abonnement actif
            </div>
            <div style="font-size:13px;color:#78350f;margin-top:4px;">
                <strong><?= $abon_actif['nb_articles_restants'] ?></strong>
                article(s) restant(s) sur
                <strong><?= $abon_actif['nb_articles_total'] ?></strong>
                — valide jusqu'au
                <strong><?= date('d/m/Y', strtotime($abon_actif['date_fin'])) ?></strong>
            </div>
            <div style="font-size:12px;color:#92400e;margin-top:4px;">
                Si vous souscrivez un nouvel abonnement, les
                <strong><?= $abon_actif['nb_articles_restants'] ?></strong>
                articles restants seront <strong>reportés</strong>
                et ajoutés au nouveau pack.
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Sélection offre -->
    <form action="<?= base_url('abonnements/souscrire') ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="client_id" value="<?= $client['id_client'] ?>">

        <div class="row g-4 mb-4">
            <?php foreach ($offres as $o): ?>
            <div class="col-md-6">
                <label class="d-block h-100" style="cursor:pointer;">
                    <input type="radio" name="type_abon_id"
                           value="<?= $o['id_type_abon'] ?>"
                           class="d-none offre-radio"
                           required>
                    <div class="card border-0 shadow-sm rounded-3 h-100 offre-card"
                         style="transition:all .15s;border:2px solid transparent !important;">
                        <div class="card-body">
                            <h5 class="fw-bold mb-2"><?= esc($o['nom']) ?></h5>
                            <?php if ($o['description']): ?>
                            <p class="text-muted small mb-3"><?= esc($o['description']) ?></p>
                            <?php endif; ?>

                            <div class="row g-2 mb-3">
                                <div class="col-6 text-center">
                                    <div style="background:#f0fdf4;border-radius:10px;padding:10px;">
                                        <div class="fw-bold fs-4 text-success">
                                            <?= $o['nb_articles'] ?>
                                        </div>
                                        <div class="text-muted" style="font-size:11px;">
                                            articles inclus
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 text-center">
                                    <div style="background:#eff6ff;border-radius:10px;padding:10px;">
                                        <div class="fw-bold fs-4 text-primary">
                                            <?= $o['duree_jours'] ?>j
                                        </div>
                                        <div class="text-muted" style="font-size:11px;">
                                            durée
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($abon_actif): ?>
                            <div class="rounded-2 p-2 mb-3 text-center"
                                 style="background:#f0fdf4;border:1px dashed #86efac;">
                                <div style="font-size:12px;color:#166534;">
                                    <i class="fas fa-plus-circle me-1"></i>
                                    <strong>
                                        <?= $o['nb_articles'] + $abon_actif['nb_articles_restants'] ?>
                                    </strong>
                                    articles au total
                                    <div style="font-size:10px;color:#4ade80;">
                                        (<?= $o['nb_articles'] ?> nouveaux +
                                        <?= $abon_actif['nb_articles_restants'] ?> reportés)
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="text-center">
                                <span class="fw-bold fs-4 text-primary">
                                    <?= number_format($o['prix'], 0, ',', ' ') ?> FCFA
                                </span>
                                <div class="text-muted" style="font-size:11px;">
                                    / <?= $o['duree_jours'] ?> jours
                                </div>
                            </div>
                        </div>
                    </div>
                </label>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($offres)): ?>
        <div class="alert alert-warning rounded-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Aucune offre disponible.
            <a href="<?= base_url('abonnements/offres') ?>" class="fw-semibold">
                Créer une offre →
            </a>
        </div>
        <?php else: ?>

        <!-- Résumé sélection -->
        <div id="resumeSelection" class="d-none card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body">
                <p class="text-uppercase text-muted fw-semibold mb-3"
                   style="font-size:11px;letter-spacing:.5px;">
                    <i class="fas fa-receipt me-2"></i>Résumé
                </p>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Client</span>
                    <strong><?= esc($client['nomclient']) ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Offre choisie</span>
                    <strong id="resumeOffre">—</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Date début</span>
                    <strong><?= date('d/m/Y') ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Date fin</span>
                    <strong id="resumeDateFin">—</strong>
                </div>
                <div class="d-flex justify-content-between border-top pt-2 mt-2">
                    <span class="text-muted small fw-semibold">Montant à encaisser</span>
                    <strong class="text-primary fs-5" id="resumePrix">—</strong>
                </div>
            </div>
        </div>

        <div class="text-end">
            <a href="<?= base_url('client/ficheclient/' . $client['id_client']) ?>"
               class="btn btn-light rounded-2 px-4 me-2">Annuler</a>
            <button type="submit" class="btn btn-success btn-lg rounded-2 px-5"
                    id="btnSouscrire" disabled>
                <i class="fas fa-check me-2"></i>Activer l'abonnement
            </button>
        </div>
        <?php endif; ?>
    </form>
</div>

<script>
const offres = <?= json_encode(array_map(fn($o) => [
    'id'          => $o['id_type_abon'],
    'nom'         => $o['nom'],
    'prix'        => $o['prix'],
    'nb_articles' => $o['nb_articles'],
    'duree_jours' => $o['duree_jours'],
], $offres)) ?>;

document.querySelectorAll('.offre-radio').forEach(radio => {
    radio.addEventListener('change', function () {
        // Reset toutes les cartes
        document.querySelectorAll('.offre-card').forEach(c => {
            c.style.borderColor = 'transparent';
            c.style.boxShadow   = '';
        });

        // Activer la carte sélectionnée
        const card = this.closest('label').querySelector('.offre-card');
        card.style.borderColor = '#10b981';
        card.style.boxShadow   = '0 0 0 3px #d1fae5';

        // Mettre à jour le résumé
        const offre = offres.find(o => o.id == this.value);
        if (!offre) return;

        const dateFin = new Date();
        dateFin.setDate(dateFin.getDate() + offre.duree_jours);

        document.getElementById('resumeOffre').textContent   = offre.nom;
        document.getElementById('resumeDateFin').textContent =
            dateFin.toLocaleDateString('fr-FR');
        document.getElementById('resumePrix').textContent    =
            offre.prix.toLocaleString('fr-FR') + ' FCFA';

        document.getElementById('resumeSelection').classList.remove('d-none');
        document.getElementById('btnSouscrire').disabled = false;
    });
});
</script>

<?= $this->endSection() ?>
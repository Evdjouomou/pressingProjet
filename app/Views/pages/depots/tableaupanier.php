<?php
// Vérifier si une caisse est ouverte
$db = \Config\Database::connect();
$caissePourVue = $db->table('caisses')
    ->where('statut', 'ouverte')
    ->orderBy('date_ouverture', 'DESC')
    ->limit(1)
    ->get()->getRowArray();
?>

<div class="col-12">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-semibold text-dark mb-0">Articles à déposer</h5>
            <small class="text-muted" id="label_nb_articles">0 article(s) ajouté(s)</small>
        </div>
        <button type="button" class="btn btn-primary rounded-2 px-3"
                data-bs-toggle="modal" data-bs-target="#modalArticle">
            <i class="fas fa-plus me-2"></i>Ajouter un vêtement
        </button>
    </div>

    <!-- Tableau panier -->
    <div class="card border-0 rounded-3 overflow-hidden"
         style="border: 0.5px solid var(--bs-border-color) !important;">
        <table class="table table-hover align-middle mb-0" id="tablePanier">
            <thead class="bg-light">
                <tr>
                    <th class="ps-3 text-uppercase text-muted fw-semibold"
                        style="font-size:11px;letter-spacing:.5px;">Article</th>
                    <th class="text-uppercase text-muted fw-semibold"
                        style="font-size:11px;letter-spacing:.5px;">Couleur / Marque</th>
                    <th class="text-uppercase text-muted fw-semibold"
                        style="font-size:11px;letter-spacing:.5px;">Prestation</th>
                    <th class="text-end text-uppercase text-muted fw-semibold"
                        style="font-size:11px;letter-spacing:.5px;">Prix</th>
                    <th class="text-end text-uppercase text-muted fw-semibold"
                        style="font-size:11px;letter-spacing:.5px;">Points</th>
                    <th class="text-center text-uppercase text-muted fw-semibold"
                        style="font-size:11px;letter-spacing:.5px;">Action</th>
                </tr>
            </thead>
            <tbody id="tbody_panier">
                <tr id="row_vide">
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-tshirt fa-2x mb-2 d-block opacity-25"></i>
                        <span class="small">Aucun vêtement ajouté pour le moment</span>
                    </td>
                </tr>
            </tbody>
            <tfoot class="bg-light">
                <tr>
                    <td colspan="3" class="text-end text-muted small fw-semibold ps-3">Total général</td>
                    <td class="text-end fw-semibold text-success fs-5" id="total_facture">0 FCFA</td>
                    <td class="text-end fw-semibold text-warning" id="total_points">
                        <i class="fas fa-star fa-xs"></i> 0 pts
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Indicateur état caisse -->
    <div class="mt-3">
        <?php if (!$caissePourVue): ?>
        <div class="alert alert-warning rounded-3 shadow-sm d-flex align-items-center gap-3 mb-3 py-2">
            <i class="fas fa-exclamation-triangle fa-lg text-warning flex-shrink-0"></i>
            <div>
                <div class="fw-semibold" style="font-size:13px;">Caisse non ouverte</div>
                <div class="text-muted" style="font-size:12px;">
                    Vous pouvez enregistrer le dépôt mais
                    <strong>aucun acompte ne sera accepté</strong>
                    tant que la caisse n'est pas ouverte.
                    <a href="<?= base_url('pos/caisse') ?>" target="_blank"
                       class="text-primary fw-semibold">Ouvrir la caisse →</a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-success rounded-3 shadow-sm d-flex align-items-center gap-3 mb-3 py-2">
            <i class="fas fa-check-circle text-success flex-shrink-0"></i>
            <div style="font-size:12px;">
                <span class="fw-semibold text-success">Caisse ouverte</span> —
                Fond : <strong><?= number_format($caissePourVue['fond_ouverture'], 0, ',', ' ') ?> FCFA</strong> ·
                CA du jour : <strong><?= number_format($caissePourVue['total_ca'], 0, ',', ' ') ?> FCFA</strong>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bloc Encaissement -->
    <div class="card border-0 rounded-3"
         style="border: 0.5px solid var(--bs-border-color) !important;">
        <div class="card-body">
            <p class="text-uppercase text-muted fw-semibold mb-3"
               style="font-size:11px;letter-spacing:.5px;">
                <i class="fas fa-cash-register me-2"></i>Encaissement
            </p>
            <div class="row g-3 align-items-end">

                <!-- Acompte -->
                <div class="col-md-4">
                    <label class="form-label text-muted small">Acompte versé (FCFA)</label>
                    <input type="number"
                           id="champ_acompte"
                           class="form-control bg-light"
                           placeholder="0"
                           min="0"
                           <?= !$caissePourVue ? 'disabled title="Ouvrez la caisse pour encaisser un acompte"' : '' ?>
                           oninput="calculerReste()">
                    <?php if (!$caissePourVue): ?>
                    <div class="form-text text-warning" style="font-size:10px;">
                        <i class="fas fa-lock me-1"></i>Caisse fermée — acompte désactivé
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Mode de paiement -->
                <div class="col-md-4">
                    <label class="form-label text-muted small">Mode de paiement</label>
                    <select id="champ_mode_paiement"
                            class="form-select bg-light"
                            <?= !$caissePourVue ? 'disabled' : '' ?>>
                        <option value="especes">Espèces</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="carte">Carte bancaire</option>
                    </select>
                </div>

                <!-- Reste à payer -->
                <div class="col-md-4">
                    <label class="form-label text-muted small">Reste à payer</label>
                    <div class="rounded-2 bg-light px-3 py-2"
                         style="border: 0.5px solid var(--bs-border-color);">
                        <p class="text-muted mb-0" style="font-size:11px;">Montant restant</p>
                        <p class="fw-semibold text-danger mb-0 fs-5" id="montant_reste">0 FCFA</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Formulaire final -->
    <div class="text-end mt-3">
        <form action="<?= base_url('depot/valider') ?>" method="POST" id="formFinal">
            <?= csrf_field() ?>
            <input type="hidden" name="id_client"     id="form_id_client">
            <input type="hidden" name="date_retrait"  id="form_date_retrait">
            <input type="hidden" name="numero_bon"    id="form_numero_bon">
            <input type="hidden" name="total_points"  id="form_total_points">
            <input type="hidden" name="acompte"       id="form_acompte">
            <input type="hidden" name="mode_paiement" id="form_mode_paiement">
            <div id="hidden_articles_inputs"></div>

            <button type="submit" class="btn btn-success btn-lg rounded-2 px-5">
                <i class="fas fa-check me-2"></i>Enregistrer le dépôt
            </button>
        </form>
    </div>

</div>

<script>
// État caisse transmis depuis PHP
const caisseOuverte = <?= $caissePourVue ? 'true' : 'false' ?>;

function initFormulaire() {
    document.getElementById('formFinal').addEventListener('submit', function (e) {

        if (!idClientSelectionne || panier.length === 0) {
            e.preventDefault();
            alert('Client ou Panier vide !');
            return;
        }

        const acompte = parseFloat(document.getElementById('champ_acompte').value) || 0;

        // Bloquer si acompte > 0 sans caisse ouverte
        if (acompte > 0 && !caisseOuverte) {
            e.preventDefault();
            alert(
                'Impossible d\'encaisser un acompte : aucune caisse n\'est ouverte.\n\n' +
                'Ouvrez la caisse dans le module POS ou enregistrez le dépôt sans acompte.'
            );
            return;
        }

        document.getElementById('form_id_client').value     = idClientSelectionne;
        document.getElementById('form_acompte').value       = acompte;
        document.getElementById('form_mode_paiement').value = caisseOuverte
            ? document.getElementById('champ_mode_paiement').value
            : 'especes';
        document.getElementById('form_date_retrait').value  = document.getElementById('champ_date_retrait').value;
        document.getElementById('form_numero_bon').value    = document.getElementById('champ_numero_bon').value;
    });
}
</script>
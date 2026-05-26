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
            <h4 class="fw-bold mb-0">Bons de commande fournisseur</h4>
            <small class="text-muted"><?= count($bons) ?> bon(s)</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('stocks') ?>" class="btn btn-outline-secondary rounded-2 px-3">
                <i class="fas fa-arrow-left me-2"></i>Stocks
            </a>
            <button class="btn btn-primary rounded-2 px-4"
                    data-bs-toggle="modal" data-bs-target="#modalCreerBon">
                <i class="fas fa-plus me-2"></i>Nouveau bon
            </button>
        </div>
    </div>

    <!-- Liste bons -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-muted fw-semibold" style="font-size:11px;">RÉFÉRENCE</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;">FOURNISSEUR</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;">LIGNES</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:11px;">TOTAL HT</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;">DATE</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;">STATUT</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bons)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-file-invoice fa-2x mb-2 d-block opacity-25"></i>
                                Aucun bon de commande créé.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php
                        $statutsBon = [
                            'brouillon' => ['label' => 'Brouillon', 'bg' => '#f1f5f9', 'color' => '#374151'],
                            'envoye'    => ['label' => 'Envoyé',    'bg' => '#fef3c7', 'color' => '#92400e'],
                            'recu'      => ['label' => 'Reçu',      'bg' => '#dcfce7', 'color' => '#166534'],
                            'annule'    => ['label' => 'Annulé',    'bg' => '#fee2e2', 'color' => '#991b1b'],
                        ];
                        foreach ($bons as $b):
                            $sb = $statutsBon[$b['statut']] ?? ['label' => $b['statut'], 'bg' => '#f1f5f9', 'color' => '#374151'];
                        ?>
                        <tr>
                            <td class="ps-4 fw-bold text-primary"><?= esc($b['reference']) ?></td>
                            <td><?= esc($b['fournisseur']) ?></td>
                            <td class="text-center">
                                <span style="background:#eff6ff;color:#1d4ed8;padding:2px 10px;
                                             border-radius:20px;font-size:12px;font-weight:600;">
                                    <?= $b['nb_lignes'] ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold">
                                <?= number_format($b['total_ht'], 0, ',', ' ') ?> FCFA
                            </td>
                            <td style="font-size:12px;">
                                <?= date('d/m/Y', strtotime($b['created_at'])) ?>
                                <?php if ($b['date_reception']): ?>
                                <div class="text-success" style="font-size:10px;">
                                    Reçu le <?= date('d/m/Y', strtotime($b['date_reception'])) ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span style="background:<?= $sb['bg'] ?>;color:<?= $sb['color'] ?>;
                                             padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                    <?= $sb['label'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('stocks/bons/' . $b['id_bon']) ?>"
                                   class="btn btn-sm me-1"
                                   style="width:32px;height:32px;border-radius:8px;background:#f1f5f9;border:1px solid #e2e8f0;"
                                   title="Voir">
                                    <i class="fas fa-eye fa-sm text-secondary"></i>
                                </a>
                                <a href="<?= base_url('stocks/bons/imprimer/' . $b['id_bon']) ?>"
                                   target="_blank"
                                   class="btn btn-sm me-1"
                                   style="width:32px;height:32px;border-radius:8px;background:#eff6ff;border:1px solid #bfdbfe;"
                                   title="Imprimer">
                                    <i class="fas fa-print fa-sm text-primary"></i>
                                </a>
                                <?php if ($b['statut'] !== 'recu' && $b['statut'] !== 'annule'): ?>
                                <a href="<?= base_url('stocks/bons/recevoir/' . $b['id_bon']) ?>"
                                   class="btn btn-sm me-1"
                                   style="width:32px;height:32px;border-radius:8px;background:#f0fdf4;border:1px solid #bbf7d0;"
                                   onclick="return confirm('Confirmer la réception ? Le stock sera mis à jour automatiquement.')"
                                   title="Marquer reçu">
                                    <i class="fas fa-check fa-sm text-success"></i>
                                </a>
                                <a href="<?= base_url('stocks/bons/delete/' . $b['id_bon']) ?>"
                                   class="btn btn-sm"
                                   style="width:32px;height:32px;border-radius:8px;background:#fff5f5;border:1px solid #fecaca;"
                                   onclick="return confirm('Supprimer ce bon ?')"
                                   title="Supprimer">
                                    <i class="fas fa-trash fa-sm text-danger"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal créer bon -->
<div class="modal fade" id="modalCreerBon" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <div>
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-file-invoice text-primary me-2"></i>Nouveau bon de commande
                    </h5>
                    <small class="text-muted">Sélectionnez les produits à commander</small>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('stocks/bons/store') ?>" method="POST" id="formBon">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Fournisseur <span class="text-danger">*</span></label>
                            <input type="text" name="fournisseur" class="form-control"
                                   placeholder="Nom du fournisseur" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Note</label>
                            <input type="text" name="note" class="form-control"
                                   placeholder="Note optionnelle">
                        </div>
                    </div>

                    <p class="text-uppercase text-muted fw-semibold mb-2" style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-list me-2"></i>Lignes de commande
                    </p>

                    <div id="lignesBon">
                        <div class="ligne-bon row g-2 mb-2 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold small">Produit</label>
                                <select name="produits[]" class="form-select" required>
                                    <option value="" disabled selected>Choisir un produit...</option>
                                    <?php foreach ($produits as $prod): ?>
                                    <option value="<?= $prod['id_produit'] ?>"
                                            data-prix="<?= $prod['prix_achat'] ?>">
                                        <?= esc($prod['nom']) ?>
                                        (stock: <?= $prod['stock'] ?> <?= esc($prod['unite']) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Quantité</label>
                                <input type="number" name="quantites[]" class="form-control qte-input"
                                       placeholder="0" min="1" required oninput="calculerTotalBon()">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Prix unitaire (FCFA)</label>
                                <input type="number" name="prix[]" class="form-control prix-input"
                                       placeholder="0" min="0" oninput="calculerTotalBon()">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-danger rounded-2 w-100"
                                        onclick="supprimerLigne(this)" style="height:38px;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm rounded-2 mt-2"
                            onclick="ajouterLigne()">
                        <i class="fas fa-plus me-1"></i>Ajouter une ligne
                    </button>

                    <!-- Total -->
                    <div class="mt-3 rounded-3 p-3"
                         style="background:#f8fafc;border:1px solid #e2e8f0;">
                        <div class="d-flex justify-content-between fw-bold fs-6">
                            <span>Total HT</span>
                            <span id="totalBon">0 FCFA</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success px-4 rounded-2 fw-semibold">
                        <i class="fas fa-file-invoice me-2"></i>Créer le bon de commande
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Remplir prix auto au choix du produit
document.addEventListener('change', function (e) {
    if (e.target.matches('select[name="produits[]"]')) {
        const opt = e.target.options[e.target.selectedIndex];
        const prixInput = e.target.closest('.ligne-bon').querySelector('.prix-input');
        if (prixInput) prixInput.value = opt.dataset.prix || 0;
        calculerTotalBon();
    }
});

function ajouterLigne() {
    const container = document.getElementById('lignesBon');
    const premiere  = container.querySelector('.ligne-bon');
    const clone     = premiere.cloneNode(true);
    // Reset valeurs
    clone.querySelectorAll('select, input').forEach(el => {
        if (el.tagName === 'SELECT') el.selectedIndex = 0;
        else el.value = '';
    });
    container.appendChild(clone);
}

function supprimerLigne(btn) {
    const lignes = document.querySelectorAll('.ligne-bon');
    if (lignes.length <= 1) return;
    btn.closest('.ligne-bon').remove();
    calculerTotalBon();
}

function calculerTotalBon() {
    let total = 0;
    document.querySelectorAll('.ligne-bon').forEach(ligne => {
        const qte  = parseFloat(ligne.querySelector('.qte-input').value)  || 0;
        const prix = parseFloat(ligne.querySelector('.prix-input').value) || 0;
        total += qte * prix;
    });
    document.getElementById('totalBon').textContent = total.toLocaleString('fr-FR') + ' FCFA';
}
</script>

<?= $this->endSection() ?>
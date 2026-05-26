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

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-0">Gestion des Stocks</h4>
            <small class="text-muted"><?= $nb_produits ?> produit(s) référencé(s)</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= base_url('stocks/journal') ?>" class="btn btn-outline-secondary rounded-2 px-3">
                <i class="fas fa-history me-2"></i>Journal
            </a>
            <a href="<?= base_url('stocks/bons') ?>" class="btn btn-outline-primary rounded-2 px-3">
                <i class="fas fa-file-invoice me-2"></i>Bons de commande
            </a>
            <button class="btn btn-primary rounded-2 px-4"
                    data-bs-toggle="modal" data-bs-target="#modalCreerProduit">
                <i class="fas fa-plus me-2"></i>Nouveau produit
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-primary"><?= $nb_produits ?></div>
                <div class="text-muted small">Références</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-danger"><?= $nb_alerte ?></div>
                <div class="text-muted small">En alerte</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-warning"><?= $nb_rupture ?></div>
                <div class="text-muted small">En rupture</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-success">
                    <?= number_format($valeur_total, 0, ',', ' ') ?>
                </div>
                <div class="text-muted small">Valeur stock (FCFA)</div>
            </div>
        </div>
    </div>

    <!-- Tableau produits -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tableStock">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">PRODUIT</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">CATÉGORIE</th>
                            <th class="text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">TYPE</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">STOCK</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">SEUIL</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">VALEUR</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $typesLabels = [
                            'boutique'    => ['label' => 'Boutique',    'bg' => '#eff6ff', 'color' => '#1d4ed8'],
                            'production'  => ['label' => 'Production',  'bg' => '#fdf4ff', 'color' => '#7e22ce'],
                            'les_deux'    => ['label' => 'Les deux',    'bg' => '#f0fdf4', 'color' => '#166534'],
                        ];
                        foreach ($produits as $p):
                            $alerte   = $p['stock'] <= $p['stock_alerte'];
                            $rupture  = $p['stock'] == 0;
                            $tp       = $typesLabels[$p['type_produit']] ?? ['label' => $p['type_produit'], 'bg' => '#f1f5f9', 'color' => '#374151'];
                            $valeur   = $p['stock'] * $p['prix_achat'];
                        ?>
                        <tr style="<?= $rupture ? 'background:#fff5f5;' : ($alerte ? 'background:#fffbeb;' : '') ?>">

                            <!-- Produit -->
                            <td class="ps-4">
                                <div class="fw-semibold">
                                    <?php if ($rupture): ?>
                                        <span class="text-danger me-1">●</span>
                                    <?php elseif ($alerte): ?>
                                        <span class="text-warning me-1">●</span>
                                    <?php endif; ?>
                                    <?= esc($p['nom']) ?>
                                </div>
                                <div class="text-muted" style="font-size:11px;">
                                    <?= esc($p['reference'] ?: '—') ?>
                                    <?php if ($p['fournisseur']): ?>
                                        · <?= esc($p['fournisseur']) ?>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Catégorie -->
                            <td style="font-size:12px;"><?= esc($p['categorie'] ?: '—') ?></td>

                            <!-- Type -->
                            <td>
                                <span style="background:<?= $tp['bg'] ?>;color:<?= $tp['color'] ?>;
                                             padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                    <?= $tp['label'] ?>
                                </span>
                            </td>

                            <!-- Stock -->
                            <td class="text-center">
                                <span style="background:<?= $rupture ? '#fee2e2' : ($alerte ? '#fef3c7' : '#dcfce7') ?>;
                                             color:<?= $rupture ? '#991b1b' : ($alerte ? '#92400e' : '#166534') ?>;
                                             padding:4px 14px;border-radius:20px;font-size:13px;font-weight:700;">
                                    <?= $p['stock'] ?> <?= esc($p['unite']) ?>
                                    <?= $rupture ? ' ⛔' : ($alerte ? ' ⚠' : '') ?>
                                </span>
                            </td>

                            <!-- Seuil -->
                            <td class="text-center text-muted" style="font-size:12px;">
                                <?= $p['stock_alerte'] ?> <?= esc($p['unite']) ?>
                            </td>

                            <!-- Valeur -->
                            <td class="text-end fw-semibold" style="font-size:13px;">
                                <?= number_format($valeur, 0, ',', ' ') ?> FCFA
                            </td>

                            <!-- Actions -->
                            <td class="text-center">
                                <a href="<?= base_url('stocks/produit/' . $p['id_produit']) ?>"
                                   class="btn btn-sm me-1"
                                   style="width:32px;height:32px;border-radius:8px;
                                          background:#f1f5f9;border:1px solid #e2e8f0;"
                                   title="Voir historique">
                                    <i class="fas fa-eye fa-sm text-secondary"></i>
                                </a>
                                <button class="btn btn-sm me-1"
                                        style="width:32px;height:32px;border-radius:8px;
                                               background:#f0fdf4;border:1px solid #bbf7d0;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEntree<?= $p['id_produit'] ?>"
                                        title="Entrée stock">
                                    <i class="fas fa-plus fa-sm text-success"></i>
                                </button>
                                <button class="btn btn-sm me-1"
                                        style="width:32px;height:32px;border-radius:8px;
                                               background:#fff5f5;border:1px solid #fecaca;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalSortie<?= $p['id_produit'] ?>"
                                        title="Sortie stock">
                                    <i class="fas fa-minus fa-sm text-danger"></i>
                                </button>
                                <button class="btn btn-sm"
                                        style="width:32px;height:32px;border-radius:8px;
                                               background:#eff6ff;border:1px solid #bfdbfe;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalModifier<?= $p['id_produit'] ?>"
                                        title="Modifier">
                                    <i class="fas fa-edit fa-sm text-primary"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Modal Entrée -->
                        <div class="modal fade" id="modalEntree<?= $p['id_produit'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
                                <div class="modal-content border-0 shadow-lg rounded-4">
                                    <div class="modal-header border-0 px-4 pt-4 pb-0">
                                        <h5 class="fw-bold mb-0">
                                            <i class="fas fa-plus text-success me-2"></i>Entrée de stock
                                        </h5>
                                        <button class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('stocks/entree') ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="produit_id" value="<?= $p['id_produit'] ?>">
                                        <div class="modal-body px-4 py-3">
                                            <div class="rounded-3 p-3 mb-3" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                                <div class="fw-semibold"><?= esc($p['nom']) ?></div>
                                                <div class="text-muted small">
                                                    Stock actuel : <strong><?= $p['stock'] ?> <?= esc($p['unite']) ?></strong>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold small">Quantité à ajouter <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="number" name="quantite" class="form-control"
                                                           placeholder="0" min="1" required>
                                                    <span class="input-group-text"><?= esc($p['unite']) ?></span>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold small">Prix unitaire (FCFA)</label>
                                                <input type="number" name="prix_unitaire" class="form-control"
                                                       value="<?= $p['prix_achat'] ?>" min="0" step="10">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold small">Motif</label>
                                                <input type="text" name="motif" class="form-control"
                                                       placeholder="Réapprovisionnement, livraison...">
                                            </div>
                                            <div>
                                                <label class="form-label fw-semibold small">Référence document</label>
                                                <input type="text" name="reference_doc" class="form-control"
                                                       placeholder="N° facture fournisseur...">
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 px-4 pb-4 pt-0">
                                            <button type="button" class="btn btn-light rounded-2" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-success px-4 rounded-2">
                                                <i class="fas fa-plus me-2"></i>Enregistrer l'entrée
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Sortie -->
                        <div class="modal fade" id="modalSortie<?= $p['id_produit'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
                                <div class="modal-content border-0 shadow-lg rounded-4">
                                    <div class="modal-header border-0 px-4 pt-4 pb-0">
                                        <h5 class="fw-bold mb-0">
                                            <i class="fas fa-minus text-danger me-2"></i>Sortie de stock
                                        </h5>
                                        <button class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('stocks/sortie') ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="produit_id" value="<?= $p['id_produit'] ?>">
                                        <div class="modal-body px-4 py-3">
                                            <div class="rounded-3 p-3 mb-3" style="background:#fff5f5;border:1px solid #fecaca;">
                                                <div class="fw-semibold"><?= esc($p['nom']) ?></div>
                                                <div class="text-muted small">
                                                    Stock actuel : <strong><?= $p['stock'] ?> <?= esc($p['unite']) ?></strong>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold small">Quantité à retirer <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="number" name="quantite" class="form-control"
                                                           placeholder="0" min="1" max="<?= $p['stock'] ?>" required>
                                                    <span class="input-group-text"><?= esc($p['unite']) ?></span>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="form-label fw-semibold small">Motif <span class="text-danger">*</span></label>
                                                <input type="text" name="motif" class="form-control"
                                                       placeholder="Utilisation production, perte, retour..." required>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 px-4 pb-4 pt-0">
                                            <button type="button" class="btn btn-light rounded-2" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-danger px-4 rounded-2">
                                                <i class="fas fa-minus me-2"></i>Enregistrer la sortie
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Modifier -->
                        <div class="modal fade" id="modalModifier<?= $p['id_produit'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content border-0 shadow-lg rounded-4">
                                    <div class="modal-header border-0 px-4 pt-4 pb-0">
                                        <h5 class="fw-bold mb-0">Modifier le produit</h5>
                                        <button class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('stocks/produit/update/' . $p['id_produit']) ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <div class="modal-body px-4 py-3">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold small">Nom <span class="text-danger">*</span></label>
                                                    <input type="text" name="nom" class="form-control" value="<?= esc($p['nom']) ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold small">Référence</label>
                                                    <input type="text" name="reference" class="form-control" value="<?= esc($p['reference'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold small">Catégorie</label>
                                                    <input type="text" name="categorie" class="form-control" value="<?= esc($p['categorie'] ?? '') ?>" placeholder="Ex: Nettoyage, Emballage...">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold small">Unité</label>
                                                    <input type="text" name="unite" class="form-control" value="<?= esc($p['unite']) ?>" placeholder="unité, litre, kg...">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold small">Type</label>
                                                    <select name="type_produit" class="form-select">
                                                        <option value="boutique"   <?= $p['type_produit']==='boutique'   ? 'selected':'' ?>>Boutique</option>
                                                        <option value="production" <?= $p['type_produit']==='production' ? 'selected':'' ?>>Production</option>
                                                        <option value="les_deux"   <?= $p['type_produit']==='les_deux'   ? 'selected':'' ?>>Les deux</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold small">Prix de vente (FCFA)</label>
                                                    <input type="number" name="prix_vente" class="form-control" value="<?= $p['prix'] ?>" min="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold small">Prix d'achat (FCFA)</label>
                                                    <input type="number" name="prix_achat" class="form-control" value="<?= $p['prix_achat'] ?>" min="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold small">Seuil alerte</label>
                                                    <input type="number" name="stock_alerte" class="form-control" value="<?= $p['stock_alerte'] ?>" min="0">
                                                </div>
                                                <div class="col-md-8">
                                                    <label class="form-label fw-semibold small">Fournisseur principal</label>
                                                    <input type="text" name="fournisseur" class="form-control" value="<?= esc($p['fournisseur'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold small">Statut</label>
                                                    <select name="actif" class="form-select">
                                                        <option value="1" <?= $p['actif'] ? 'selected':'' ?>>Actif</option>
                                                        <option value="0" <?= !$p['actif'] ? 'selected':'' ?>>Inactif</option>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold small">Description</label>
                                                    <textarea name="description" class="form-control" rows="2"><?= esc($p['description'] ?? '') ?></textarea>
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

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Créer Produit -->
<div class="modal fade" id="modalCreerProduit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <div>
                    <h5 class="fw-bold mb-0"><i class="fas fa-box text-primary me-2"></i>Nouveau produit</h5>
                    <small class="text-muted">Consommable production ou produit boutique</small>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('stocks/produit/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control"
                                   placeholder="Ex: Housse plastique, Solvant..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Référence</label>
                            <input type="text" name="reference" class="form-control" placeholder="REF-001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Catégorie</label>
                            <input type="text" name="categorie" class="form-control"
                                   placeholder="Nettoyage, Emballage, Retouche...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Unité</label>
                            <select name="unite" class="form-select">
                                <option value="unité">Unité</option>
                                <option value="litre">Litre</option>
                                <option value="kg">Kilogramme</option>
                                <option value="rouleau">Rouleau</option>
                                <option value="boîte">Boîte</option>
                                <option value="sachet">Sachet</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Type <span class="text-danger">*</span></label>
                            <select name="type_produit" class="form-select" required>
                                <option value="boutique">Boutique (vente)</option>
                                <option value="production">Production (consommable)</option>
                                <option value="les_deux">Les deux</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Prix de vente (FCFA)</label>
                            <input type="number" name="prix_vente" class="form-control" placeholder="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Prix d'achat (FCFA)</label>
                            <input type="number" name="prix_achat" class="form-control" placeholder="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Stock initial</label>
                            <input type="number" name="stock_initial" class="form-control" placeholder="0" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Seuil d'alerte <span class="text-danger">*</span></label>
                            <input type="number" name="stock_alerte" class="form-control"
                                   placeholder="5" min="0" value="5" required>
                            <div class="form-text" style="font-size:10px;">Alerte envoyée au gérant sous ce seuil.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Fournisseur principal</label>
                            <input type="text" name="fournisseur" class="form-control"
                                   placeholder="Nom du fournisseur">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Description</label>
                            <textarea name="description" class="form-control" rows="2"
                                      placeholder="Description optionnelle..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success px-4 rounded-2 fw-semibold">
                        <i class="fas fa-check me-2"></i>Créer le produit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Recherche temps réel -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'form-control bg-light shadow-none';
    input.placeholder = 'Rechercher un produit...';
    input.style.maxWidth = '300px';
    input.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#tableStock tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
        });
    });
    document.querySelector('.card.border-0.shadow-sm.rounded-3 .card-body')?.prepend(input);
});
</script>

<?= $this->endSection() ?>
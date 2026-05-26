<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-0">Produits Annexes</h4>
            <small class="text-muted"><?= count($produits) ?> produit(s)</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('pos') ?>" class="btn btn-outline-secondary rounded-2 px-3">
                <i class="fas fa-arrow-left me-2"></i>POS
            </a>
            <button class="btn btn-primary rounded-2 px-4"
                    data-bs-toggle="modal" data-bs-target="#modalCreerProduit">
                <i class="fas fa-plus me-2"></i>Nouveau produit
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">PRODUIT</th>
                            <th class="text-end text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">PRIX</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">STOCK</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">STATUT</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produits as $p): ?>
                        <?php $alerte = $p['stock'] <= $p['stock_alerte']; ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold"><?= esc($p['nom']) ?></div>
                                <div class="text-muted small"><?= esc($p['description'] ?: '—') ?></div>
                            </td>
                            <td class="text-end fw-bold text-success">
                                <?= number_format($p['prix'], 0, ',', ' ') ?> FCFA
                            </td>
                            <td class="text-center">
                                <span style="background:<?= $alerte ? '#fee2e2' : '#dcfce7' ?>;
                                             color:<?= $alerte ? '#991b1b' : '#166534' ?>;
                                             padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;">
                                    <?= $p['stock'] ?>
                                    <?= $alerte ? ' ⚠' : '' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span style="background:<?= $p['actif'] ? '#dcfce7' : '#f1f5f9' ?>;
                                             color:<?= $p['actif'] ? '#166534' : '#6b7280' ?>;
                                             padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                    <?= $p['actif'] ? 'Actif' : 'Inactif' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm"
                                        style="width:32px;height:32px;border-radius:8px;background:#eff6ff;border:1px solid #bfdbfe;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalModifierProduit<?= $p['id_produit'] ?>">
                                    <i class="fas fa-edit fa-sm text-primary"></i>
                                </button>
                                <a href="<?= base_url('pos/produits/delete/' . $p['id_produit']) ?>"
                                   class="btn btn-sm ms-1"
                                   style="width:32px;height:32px;border-radius:8px;background:#fff5f5;border:1px solid #fecaca;"
                                   onclick="return confirm('Supprimer ce produit ?')">
                                    <i class="fas fa-trash fa-sm text-danger"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- Modal modifier -->
                        <div class="modal fade" id="modalModifierProduit<?= $p['id_produit'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
                                <div class="modal-content border-0 shadow-lg rounded-4">
                                    <div class="modal-header border-0 px-4 pt-4 pb-0">
                                        <h5 class="fw-bold mb-0">Modifier le produit</h5>
                                        <button class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="<?= base_url('pos/produits/update/' . $p['id_produit']) ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <div class="modal-body px-4 py-3">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold small">Nom</label>
                                                    <input type="text" name="nom" class="form-control" value="<?= esc($p['nom']) ?>" required>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold small">Description</label>
                                                    <input type="text" name="description" class="form-control" value="<?= esc($p['description'] ?? '') ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold small">Prix (FCFA)</label>
                                                    <input type="number" name="prix" class="form-control" value="<?= $p['prix'] ?>" min="0" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold small">Stock</label>
                                                    <input type="number" name="stock" class="form-control" value="<?= $p['stock'] ?>" min="0" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold small">Seuil alerte stock</label>
                                                    <input type="number" name="stock_alerte" class="form-control" value="<?= $p['stock_alerte'] ?>" min="0">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold small">Statut</label>
                                                    <select name="actif" class="form-select">
                                                        <option value="1" <?= $p['actif'] ? 'selected' : '' ?>>Actif</option>
                                                        <option value="0" <?= !$p['actif'] ? 'selected' : '' ?>>Inactif</option>
                                                    </select>
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

<!-- Modal créer produit -->
<div class="modal fade" id="modalCreerProduit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="fas fa-box text-primary me-2"></i>Nouveau produit</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('pos/produits/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Nom du produit <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control"
                                   placeholder="Ex: Détachant textile, Housse protection..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Description</label>
                            <input type="text" name="description" class="form-control"
                                   placeholder="Description courte optionnelle">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Prix (FCFA) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="prix" class="form-control"
                                       placeholder="0" min="0" step="50" required>
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Stock initial <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control"
                                   placeholder="0" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Seuil d'alerte stock</label>
                            <input type="number" name="stock_alerte" class="form-control"
                                   placeholder="5" min="0" value="5">
                            <div class="form-text" style="font-size:10px;">
                                Une alerte apparaîtra quand le stock atteint ce niveau.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success px-4 rounded-2 fw-semibold">
                        <i class="fas fa-check me-2"></i>Ajouter le produit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
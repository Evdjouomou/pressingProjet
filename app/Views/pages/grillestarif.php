<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= base_url('grillepro') ?>" class="btn btn-sm btn-light mb-2"><i class="bi bi-arrow-left"></i> Retour</a>
            <h2 class="fw-bold">Tarifs : <span class="text-primary"><?= esc($grille['nom_grille']) ?></span></h2>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddPrix">
            <i class="bi bi-plus-lg"></i> Prix spécial
        </button>
    </div>

    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success border-0 shadow-sm"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
        <div class="table-responsive p-3">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Service</th>
                        <th>Prix Public</th>
                        <th>Prix Spécial (<?= esc($grille['nom_grille']) ?>)</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($tarifs)): ?>
                        <?php foreach($tarifs as $t): ?>
                        <tr>
                            <td><strong><?= esc($t['libelle']) ?></strong></td>
                            <td class="text-muted"><?= number_format($t['prix_unitaire_base'], 0, ',', ' ') ?> FCFA</td>
                            <td class="text-primary fw-bold"><?= number_format($t['prix_unitaire'], 0, ',', ' ') ?> FCFA</td>
                            <td class="text-end">
                                <a href="<?= base_url('grilles/delete_tarif/'.$t['id_tarif_spec']) ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Supprimer ce prix spécial ?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                Aucun prix spécial...
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddPrix" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= base_url('grilles/savetarif') ?>" method="post" class="modal-content border-0 shadow">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Nouveau prix spécial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="grille_id" value="<?= $grille['id_grille'] ?>">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Choisir le service</label>
                    <select name="service_id" class="form-select" required>
                        <option value="">Sélectionner...</option>
                        <?php foreach($services as $s): ?>
                            <option value="<?= $s['id_service'] ?>"><?= esc($s['libelle']) ?> (Base: <?= $s['prix_unitaire_base'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Prix Spécial (FCFA)</label>
                    <input type="number" name="prix_unitaire" class="form-control" placeholder="Entrez le nouveau prix" required>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
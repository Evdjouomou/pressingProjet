<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold">Configuration des Libellés</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLibelleModal">
        <i class="bi bi-plus"></i> Nouveau Libellé
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Catégorie</th>
                    <th>Nom du Libellé</th>
                    <th>Code Court</th>
                    <th>Code-Barres</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($libelles as $l): ?>
                <tr>
                    <td><span class="badge bg-info text-dark"><?= esc($l['categorie']) ?></span></td>
                    <td class="fw-bold"><?= esc($l['nom_libelle']) ?></td>
                    <td><code><?= esc($l['code_court']) ?></code></td>
                    <td><?= esc($l['code_barre']) ?: '<em>Aucun</em>' ?></td>
                    <td>
                        
                        <button class="btn btn-sm btn-light border text-danger"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal conforme à ta table Libelle -->
<div class="modal fade" id="addLibelleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Ajouter un Libellé de service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('libelle/save') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Catégorie</label>
                        <input type="text" name="categorie" class="form-control" placeholder="Ex: Vêtement, Accessoire..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nom du Libellé (ex: Pantalon)</label>
                        <input type="text" name="nom_libelle" class="form-control" placeholder="Nom de l'article" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Code Court</label>
                            <input type="text" name="code_court" class="form-control" placeholder="PTL" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Code-Barres</label>
                            <input type="text" name="code_barre" class="form-control" placeholder="Optionnel">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
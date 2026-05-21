<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Paramétrage des Grilles</h2>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddGrille">
            <i class="bi bi-plus-lg"></i> Nouvelle Grille
        </button>
    </div>

    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <div class="row">
        <?php foreach($grilles as $g): ?>
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="icon-box bg-light-primary rounded-3 p-3">
                            <i class="bi bi-tag-fill text-primary fs-4"></i>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                            <ul class="dropdown-menu border-0 shadow-sm">
                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalEditGrille<?= $g['id_grille'] ?>">Modifier</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= base_url('grilles/delete/'.$g['id_grille']) ?>" onclick="return confirm('Supprimer?')">Supprimer</a></li>
                            </ul>
                        </div>
                    </div>
                    <h5 class="fw-bold"><?= $g['nom_grille'] ?></h5>
                    <p class="text-muted small mb-4"><?= esc($g['description']) ?: 'Aucune description fournie.' ?></p>
                    
                    <div class="d-grid">
                        <a href="<?= base_url('grilles/tarifs/'.$g['id_grille']) ?>" class="btn btn-outline-primary rounded-pill btn-sm">
                            Gérer les tarifs spécifiques
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalEditGrille<?= $g['id_grille'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Modifier la grille</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="<?= base_url('grilles/update/'.$g['id_grille']) ?>" method="post">
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nom de la grille</label>
                                <input type="text" name="nom_grille" class="form-control" value="<?= $g['nom_grille'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="description" class="form-control" rows="3"><?= $g['description'] ?></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal fade" id="modalAddGrille" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Nouvelle Grille Tarifaire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('grilles/save') ?>" method="post">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nom de la grille</label>
                        <input type="text" name="nom_grille" class="form-control" placeholder="ex: Tarif Hôtels 4 étoiles" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" placeholder="Détails sur ce profil client..." rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4">Créer la grille</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
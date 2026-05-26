<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4" style="max-width:800px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= base_url('incidents') ?>" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
            <h4 class="fw-bold mb-0">Déclarer un incident</h4>
        </div>
    </div>

    <form action="<?= base_url('incidents/store') ?>" method="POST"
          enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="row g-4">

            <!-- Client + Dépôt -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <p class="text-uppercase text-muted fw-semibold mb-3"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-user me-2"></i>Client et commande concernés
                        </p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Client <span class="text-danger">*</span></label>
                                <select name="client_id" class="form-select" required>
                                    <option value="" disabled selected>Choisir...</option>
                                    <?php foreach ($clients as $c): ?>
                                    <option value="<?= $c['id_client'] ?>">
                                        <?= esc($c['nomclient']) ?> — <?= esc($c['telephone']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">
                                    N° Dépôt lié
                                    <span class="text-muted fw-normal">(optionnel)</span>
                                </label>
                                <input type="number" name="depot_id" class="form-control"
                                       placeholder="ID du dépôt...">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">
                                    ID Article concerné
                                    <span class="text-muted fw-normal">(optionnel)</span>
                                </label>
                                <input type="number" name="article_depose_id" class="form-control"
                                       placeholder="ID de l'article déposé...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Détails incident -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <p class="text-uppercase text-muted fw-semibold mb-3"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-exclamation-triangle me-2"></i>Détails de l'incident
                        </p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Type d'incident <span class="text-danger">*</span></label>
                                <select name="type_incident" class="form-select" required>
                                    <option value="article_endommage">💔 Article endommagé</option>
                                    <option value="article_perdu">❓ Article perdu</option>
                                    <option value="retard">⏰ Retard</option>
                                    <option value="qualite_insuffisante">👎 Qualité insuffisante</option>
                                    <option value="mauvais_traitement">⚠️ Mauvais traitement</option>
                                    <option value="autre">📋 Autre</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Gravité <span class="text-danger">*</span></label>
                                <select name="gravite" class="form-select" required>
                                    <option value="faible">🟢 Faible</option>
                                    <option value="moyen" selected>🟡 Moyen</option>
                                    <option value="eleve">🟠 Élevé</option>
                                    <option value="critique">🔴 Critique</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">
                                    Description détaillée <span class="text-danger">*</span>
                                </label>
                                <textarea name="description" class="form-control" rows="4"
                                          placeholder="Décrivez précisément l'incident, les circonstances, l'état constaté..."
                                          required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Responsable assigné</label>
                                <select name="responsable_id" class="form-select">
                                    <option value="">-- Assigner plus tard --</option>
                                    <?php foreach ($employes as $e): ?>
                                    <option value="<?= $e['id_employe'] ?>">
                                        <?= esc($e['nom_complet']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Délai de résolution prévu</label>
                                <input type="date" name="delai_resolution" class="form-control"
                                       min="<?= date('Y-m-d') ?>">
                            </div>

                            <!-- Photos -->
                            <div class="col-12">
                                <label class="form-label fw-semibold small">
                                    Photos
                                    <span class="text-muted fw-normal">(optionnel, multiple)</span>
                                </label>
                                <input type="file" name="photos[photos][]" class="form-control"
                                       accept="image/*" multiple>
                                <div class="form-text" style="font-size:10px;">
                                    Vous pourrez ajouter des photos supplémentaires après création.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="text-end mt-4">
            <a href="<?= base_url('incidents') ?>" class="btn btn-light rounded-2 px-4 me-2">Annuler</a>
            <button type="submit" class="btn btn-danger btn-lg rounded-2 px-5">
                <i class="fas fa-exclamation-triangle me-2"></i>Déclarer l'incident
            </button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
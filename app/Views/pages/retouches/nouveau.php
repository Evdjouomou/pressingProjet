<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4" style="max-width:800px;">

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3">
            <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= base_url('retouches') ?>" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
            <h4 class="fw-bold mb-0">Nouvelle retouche</h4>
        </div>
    </div>

    <form action="<?= base_url('retouches/store') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="row g-4">

            <!-- Client -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <p class="text-uppercase text-muted fw-semibold mb-3"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-user me-2"></i>Client
                        </p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Client <span class="text-danger">*</span></label>
                                <select name="client_id" class="form-select" required id="selectClient">
                                    <option value="" disabled selected>Choisir un client...</option>
                                    <?php foreach ($clients as $c): ?>
                                    <option value="<?= $c['id_client'] ?>">
                                        <?= esc($c['nomclient']) ?> — <?= esc($c['telephone']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Lié à un dépôt ? -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">
                                    Lié à un dépôt
                                    <span class="text-muted fw-normal">(optionnel)</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" id="inputDepotId"
                                           class="form-control" placeholder="N° ID du dépôt...">
                                    <button type="button" class="btn btn-outline-secondary"
                                            onclick="chargerDepot()">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="depot_id" id="hiddenDepotId">
                                <input type="hidden" name="article_depose_id" id="hiddenArticleId">
                            </div>

                            <!-- Articles du dépôt -->
                            <div class="col-12 d-none" id="zoneArticles">
                                <label class="form-label fw-semibold small">Article concerné</label>
                                <select name="article_depose_id_select" id="selectArticle"
                                        class="form-select" onchange="selectionnerArticle(this)">
                                    <option value="">-- Retouche sur tout le dépôt --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Détails retouche -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <p class="text-uppercase text-muted fw-semibold mb-3"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-scissors me-2"></i>Détails de la retouche
                        </p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Type <span class="text-danger">*</span></label>
                                <select name="type_retouche" class="form-select" required>
                                    <option value="ourlet">📏 Ourlet</option>
                                    <option value="fermeture_eclair">🔒 Fermeture éclair</option>
                                    <option value="bouton">🔘 Bouton</option>
                                    <option value="couture">🧵 Couture</option>
                                    <option value="teinture">🎨 Teinture</option>
                                    <option value="restauration">✨ Restauration</option>
                                    <option value="broderie">🌸 Broderie</option>
                                    <option value="autre">⚙️ Autre</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Retoucheur assigné</label>
                                <select name="employe_id" class="form-select">
                                    <option value="">-- Assigner plus tard --</option>
                                    <?php foreach ($employes as $e): ?>
                                    <option value="<?= $e['id_employe'] ?>">
                                        <?= esc($e['nom_complet']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="3"
                                          placeholder="Décrivez précisément la retouche à effectuer..."
                                          required></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Prix (FCFA) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="prix" class="form-control"
                                           placeholder="0" min="0" step="100" required>
                                    <span class="input-group-text">FCFA</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Acompte versé (FCFA)</label>
                                <div class="input-group">
                                    <input type="number" name="acompte_verse" class="form-control"
                                           placeholder="0" min="0" step="100">
                                    <span class="input-group-text">FCFA</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Délai estimé</label>
                                <input type="date" name="delai_estime" class="form-control"
                                       min="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Observations</label>
                                <textarea name="observations" class="form-control" rows="2"
                                          placeholder="Notes internes..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="text-end mt-4">
            <a href="<?= base_url('retouches') ?>" class="btn btn-light rounded-2 px-4 me-2">Annuler</a>
            <button type="submit" class="btn btn-success btn-lg rounded-2 px-5">
                <i class="fas fa-check me-2"></i>Enregistrer la retouche
            </button>
        </div>
    </form>
</div>

<script>
const BASE = '<?= base_url() ?>';

function chargerDepot() {
    const id = document.getElementById('inputDepotId').value.trim();
    if (!id) return;

    fetch(`${BASE}retouches/api/depot/${id}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { alert('Dépôt introuvable.'); return; }

            document.getElementById('hiddenDepotId').value = data.depot.id_depot;
            document.getElementById('selectClient').value  = data.depot.id_client;

            const sel = document.getElementById('selectArticle');
            sel.innerHTML = '<option value="">-- Retouche sur tout le dépôt --</option>';
            data.articles.forEach(a => {
                const opt = document.createElement('option');
                opt.value = a.id_article_depose;
                opt.textContent = a.nom_libelle + ' — ' + (a.designation_libre || a.barcode_unique);
                sel.appendChild(opt);
            });

            document.getElementById('zoneArticles').classList.remove('d-none');
        });
}

function selectionnerArticle(sel) {
    document.getElementById('hiddenArticleId').value = sel.value;
}
</script>

<?= $this->endSection() ?>
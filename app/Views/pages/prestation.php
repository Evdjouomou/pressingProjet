<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Catalogue des Prestations</h2>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddService">
            <i class="bi bi-plus-lg"></i> Nouveau Service
        </button>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 filter-btn active" data-filter="all">Tous</button>
            <button class="btn btn-outline-primary btn-sm rounded-pill px-3 filter-btn" data-filter="Vêtement">Vêtements</button>
            <button class="btn btn-outline-primary btn-sm rounded-pill px-3 filter-btn" data-filter="Ameublement">Ameublement</button>
            <button class="btn btn-outline-primary btn-sm rounded-pill px-3 filter-btn" data-filter="Cuir/Daim">Cuir/Daim</button>
            <button class="btn btn-outline-primary btn-sm rounded-pill px-3 filter-btn" data-filter="Tapis">Tapis</button>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Désignation</th>
                        <th>Catégorie</th>
                        <th>Prestation</th>
                        <th>Prix Base (HT)</th>
                        <th>Prix TTC</th>
                        <th>Délai</th>
                        <th>Points fidélité</th>
                        <th>Statut</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($services as $s): ?>
                    <tr class="service-row" data-category="<?= $s['categorie'] ?>">
                        <td><span class="badge bg-light text-dark border fw-bold"><?= $s['code_court'] ?></span></td>
                        <td>
                            <div class="fw-bold"><?= $s['nom_libelle'] ?></div>
                            <small class="text-muted"><i class="bi bi-upc-scan"></i> <?= $s['code_barre'] ?: 'Pas de scan' ?></small>
                        </td>
                        <td><?= $s['categorie'] ?></td>
                        <td><?= $s['type_prestation'] ?></td>
                        <td><?= number_format($s['prix_unitaire_base'], 0, ',', ' ') ?> FCFA</td>
                        <td class="text-primary fw-bold">
                            <?php 
                                $ttc = $s['prix_unitaire_base'] * (1 + ($s['taux_tva'] / 100));
                                echo number_format($ttc, 0, ',', ' ') . ' FCFA';
                            ?>
                        </td>
                        <td><?= $s['delai_standard'] ?>h</td>
                        <td><?= $s['points_fidelite'] ?></td>
                        <td>
                            <?php if($s['statut'] == 'actif'): ?>
                                <span class="badge bg-success-soft text-success" style="background-color: #e6fcf5;">Actif</span>
                            <?php else: ?>
                                <span class="badge bg-danger-soft text-danger" style="background-color: #fff5f5;">Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-light text-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEditService<?= $s['id_service'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <a href="<?= base_url('services/delete/'.$s['id_service']) ?>" 
                               class="btn btn-sm btn-light text-danger" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce service ?');">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>

                    <div class="modal fade" id="modalEditService<?= $s['id_service'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Modifier la prestation : <?= $s['nom_libelle'] ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="<?= base_url('services/update/'.$s['id_service']) ?>" method="post">
                                    <?= csrf_field() ?>
                                    <div class="modal-body p-4">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold">Catégorie</label>
                                                <select name="categorie" class="form-select edit-categorie" data-target="edit-libelle-<?= $s['id_service'] ?>">
                                                    <?php 
                                                        foreach($categories as $cat) {
                                                            $selected = ($cat == $s['categorie']) ? 'selected' : '';
                                                            echo '<option value="'.$cat['categorie'].'" '.$selected.'>'.$cat['categorie'].'</option>';
                                                        }
                                                    ?>
                                                </select>
                                            </div>

                                            <div class="col-md-8">
                                                <label class="form-label fw-bold">Libellé du service</label>
                                                <!-- On utilise libelle_id comme name pour correspondre au contrôleur -->
                                                <select name="libelle_id" id="edit-libelle-<?= $s['id_service'] ?>" class="form-select edit-libelle" required>
                                                    <?php foreach($libelles as $l): ?>
                                                        <?php if($l['categorie'] == $s['categorie']): ?>
                                                            <option value="<?= $l['id_libelle'] ?>" <?= ($l['id_libelle'] == $s['libelle_id']) ? 'selected' : '' ?>>
                                                                <?= $l['nom_libelle'] ?>
                                                            </option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Prestation</label>
                                                <input type="text" name="prestation" class="form-control" value="<?= esc($s['type_prestation']) ?>" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Prix de Base (HT)</label>
                                                <input type="number" step="0.01" name="prix_unitaire_base" class="form-control" value="<?= $s['prix_unitaire_base'] ?>" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">TVA (%)</label>
                                                <input type="number" step="0.01" name="taux_tva" class="form-control" value="<?= $s['taux_tva'] ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Délai (Heures)</label>
                                                <input type="number" name="delai_standard" class="form-control" value="<?= $s['delai_standard'] ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Majoration Express (%)</label>
                                                <input type="number" step="0.01" name="majoration_express" class="form-control" value="<?= $s['majoration_express'] ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Points Fidélité</label>
                                                <input type="number" name="points_fidelite" class="form-control" value="<?= $s['points_fidelite'] ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-bold">Statut</label>
                                                <select name="statut" class="form-select">
                                                    <option value="actif" <?= $s['statut'] === 'actif' ? 'selected' : '' ?>>Actif</option>
                                                    <option value="inactif" <?= $s['statut'] === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                                                </select>
                                            </div>
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
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddService" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Ajouter une prestation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('services/save') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Catégorie</label>
                            <select name="categorie" id="select-categorie" class="form-select" required>
                                <option value="">Choisir une catégorie...</option>
                                <?php foreach($categories as $c): ?>
                                    <option value="<?= $c['categorie'] ?>"><?= $c['categorie'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Libellé du service</label>
                            <select name="libelle_id" id="select-libelle" class="form-control" required disabled>
                                <option value="">Sélectionnez d'abord une catégorie</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Prestation</label>
                            <input type="text" name="prestation" class="form-control" placeholder="Lavage simple">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Prix de Base (HT)</label>
                            <input type="number" step="0.01" name="prix_unitaire_base" class="form-control" placeholder="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">TVA (%)</label>
                            <input type="number" step="0.01" name="taux_tva" class="form-control" value="19.25">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Délai (Heures)</label>
                            <input type="number" name="delai_standard" class="form-control" value="72">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Majoration Express (%)</label>
                            <input type="number" step="0.01" name="majoration_express" class="form-control" value="25">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Points Fidélité</label>
                            <input type="number" name="points_fidelite" class="form-control" value="5">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Statut</label>
                            <select name="statut" class="form-select">
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                            </select>
                        </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const tableRows = document.querySelectorAll('.service-row');

    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {

            filterButtons.forEach(b => {
                b.classList.remove('active', 'btn-primary');
                b.classList.add('btn-outline-primary');
            });
            

            if(this.getAttribute('data-filter') === 'all') {
                this.classList.remove('btn-outline-primary');
            }
            this.classList.add('active');

            const selectedCat = this.getAttribute('data-filter');
            
            tableRows.forEach(row => {
                const rowCat = row.getAttribute('data-category');
                if (selectedCat === 'all' || rowCat === selectedCat) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
});
</script>
<script>

    const libellesData = <?= json_encode($libelles) ?>;
    
    const categorySelect = document.getElementById('select-categorie');
    const libelleSelect = document.getElementById('select-libelle');

    categorySelect.addEventListener('change', function() {
        const selectedCat = this.value;

        libelleSelect.innerHTML = '<option value="">Choisir un libellé...</option>';
        
        if (selectedCat) {
            const filtered = libellesData.filter(item => item.categorie === selectedCat);

            filtered.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id_libelle;
                option.textContent = item.nom_libelle; 
                libelleSelect.appendChild(option);
            });

            libelleSelect.disabled = false;
        } else {
            libelleSelect.disabled = true;
        }
    });
</script>

<script>
    const dataLibelles = <?= json_encode($libelles) ?>;

    // On écoute tous les changements sur les selects de catégorie du mode "Edition"
    document.querySelectorAll('.edit-categorie').forEach(selectCat => {
        selectCat.addEventListener('change', function() {
            const selectedCat = this.value;
            const targetId = this.getAttribute('data-target'); // Récupère l'ID du select libellé lié
            const libelleSelect = document.getElementById(targetId);

            // Réinitialiser
            libelleSelect.innerHTML = '<option value="">Choisir...</option>';

            // Filtrer et remplir
            const filtered = dataLibelles.filter(item => item.categorie === selectedCat);
            
            filtered.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id_libelle;
                option.textContent = item.nom_libelle;
                libelleSelect.appendChild(option);
            });
        });
    });
</script>

<?= $this->endSection() ?>


<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="top-bar d-flex justify-content-between align-items-center mb-4">
    <form class="search-form d-flex" action="">
        <input type="text" class="form-control" placeholder="Rechercher un client (Nom, Tel)...">
        <button type="submit" class="btn btn-light border ms-2"><i class="bi bi-search"></i></button>
    </form>

    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddClient">
        <i class="bi bi-plus"></i> Nouveau Client
    </button>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('validation')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('validation') as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nom du Client</th>
                    <th>Contact</th>
                    <th>Type</th>
                    <th>Naissance</th>
                    <th>Solde Fidélité</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($clients as $client) : ?>
                    <tr>
                        <td><span class="text-muted">#<?= $client['id_client'] ?></span></td>
                        <td><span class="fw-bold"><?= $client['nomclient'] ?></span></td>
                        <td>
                            <small><i class="bi bi-telephone text-primary"></i> <?= $client['telephone'] ?></small><br>
                            <small class="text-muted"><i class="bi bi-envelope"></i> <?= $client['email'] ?></small>
                        </td>
                        <td>
                            <span class="badge <?= $client['typeclient'] == 'professionnel' ? 'bg-primary' : 'bg-info' ?> rounded-pill">
                                <?= ucfirst($client['typeclient']) ?>
                            </span>
                        </td>
                        <td><?= $client['journaissance'] ?></td>
                        <td><span class="badge bg-warning text-dark"><?= $client['solde_fidelite'] ?> pts</span></td>
                        <td>
                            <div class="d-flex gap-2">
                                <!-- Voir -->
                                <a href="<?= base_url('ficheclient/' . $client['id_client']) ?>" class="btn btn-sm btn-light border">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                <!-- Modifier -->
                                <button type="button" 
                                    class="btn btn-sm btn-light border text-primary edit-btn"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEditClient"
                                    data-id="<?= $client['id_client'] ?>"
                                    data-nom="<?= $client['nomclient'] ?>"
                                    data-tel="<?= $client['telephone'] ?>"
                                    data-email="<?= $client['email'] ?>"
                                    data-adresse="<?= $client['adresse'] ?>"
                                    data-type="<?= $client['typeclient'] ?>"
                                    data-grille="<?= $client['grille_id'] ?>"
                                    data-pref="<?= $client['preferences'] ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                
                                <!-- Supprimer (avec confirmation JS) -->
                                <a href="<?= base_url('deleteclient/' . $client['id_client']) ?>" 
                                class="btn btn-sm btn-light border text-danger" 
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>

                    <div class="modal fade" id="modalEditClient" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                                <div class="modal-header border-0 p-4">
                                    <h5 class="modal-title fw-bold">Modifier la fiche client</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                
                                <form id="formEditClient" method="post">
                                    <?= csrf_field() ?>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Nom Complet</label>
                                                <input type="text" name="nomclient" id="edit_nom" class="form-control" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Téléphone</label>
                                                <input type="tel" name="telephone" id="edit_tel" class="form-control" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Email</label>
                                                <input type="email" name="email" id="edit_email" class="form-control">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Type de Client</label>
                                                <select name="typeclient" id="edit_type" class="form-select">
                                                    <option value="particulier">Particulier</option>
                                                    <option value="professionnel">Professionnel</option>
                                                </select>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label fw-bold">Adresse</label>
                                                <input type="text" name="adresse" id="edit_adresse" class="form-control">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold">Préférences</label>
                                                <textarea name="preferences" id="edit_pref" class="form-control" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 p-4">
                                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-primary px-4">Mettre à jour</button>
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

<!-- MODAL D'AJOUT CLIENT -->
<div class="modal fade" id="modalAddClient" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 p-4">
                <h5 class="modal-title fw-bold">Ajouter un nouveau client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="<?= base_url('saveclient') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row"> 
                        <!-- Identité -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nom Complet</label>
                            <input type="text" name="nomclient" class="form-control" placeholder="Nom..." required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Téléphone</label>
                            <input type="tel" name="telephone" class="form-control" placeholder="6..." required>
                        </div>

                        <!-- Type & Grille -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Type de Client</label>
                            <select name="typeclient" id="type_select" class="form-select" onchange="toggleGrille()">
                                <option value="particulier">Particulier</option>
                                <option value="professionnel">Professionnel</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="grille_box" style="display:none;">
                            <label class="form-label fw-bold text-primary">Appliquer Grille Tarifaire</label>
                            <select name="grille_id" class="form-select border-primary">
                                <option value="">-- Tarif Standard --</option>
                                <?php foreach($grilles as $g): ?>
                                    <option value="<?= $g['id_grille'] ?>"><?= $g['nom_grille'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Naissance -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Jour de naissance</label>
                            <div class="d-flex gap-2">
                                <select name="jour" class="form-select" required>
                                    <?php for ($i = 1; $i <= 31; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                                <select name="mois" class="form-select" required>
                                    <?php 
                                    $mois = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
                                    foreach ($mois as $m): echo "<option value='$m'>$m</option>"; endforeach; 
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="email@exemple.com">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Adresse</label>
                            <input type="text" name="adresse" class="form-control" placeholder="Quartier, Rue...">
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Préférences / Instructions</label>
                            <textarea name="preferences" class="form-control" rows="2" placeholder="Allergies, ne pas repasser, etc."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4">Enregistrer le client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleGrille() {
    const type = document.getElementById('type_select').value;
    const box = document.getElementById('grille_box');
    box.style.display = (type === 'professionnel') ? 'block' : 'none';
}

document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {

        const id = this.getAttribute('data-id');
        document.getElementById('formEditClient').action = "<?= base_url('updateclient/') ?>/" + id;

        document.getElementById('edit_nom').value = this.getAttribute('data-nom');
        document.getElementById('edit_tel').value = this.getAttribute('data-tel');
        document.getElementById('edit_email').value = this.getAttribute('data-email');
        document.getElementById('edit_adresse').value = this.getAttribute('data-adresse');
        document.getElementById('edit_type').value = this.getAttribute('data-type');
        document.getElementById('edit_pref').value = this.getAttribute('data-pref');
    });
});
</script>

<?= $this->endSection() ?>
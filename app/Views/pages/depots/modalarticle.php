<!-- ══════════════════════════════════════ -->
<!-- MODAL AJOUT ARTICLE                   -->
<!-- ══════════════════════════════════════ -->
<div class="modal fade" id="modalArticle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    <i class="fas fa-tshirt me-2"></i>Détails du vêtement
                </h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">

                    <!-- Catégorie -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Catégorie</label>
                        <select id="sel_categorie" class="form-select border-primary">
                            <option value="">-- Choisir Catégorie --</option>
                            <?php
                            $categories_uniques = array_unique(array_column($libelles, 'categorie'));
                            foreach ($categories_uniques as $cat_nom):
                            ?>
                                <option value="<?= esc($cat_nom) ?>"><?= esc($cat_nom) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Libellé -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Article / Libellé</label>
                        <select id="sel_libelle" class="form-select border-primary" disabled>
                            <option value="">-- Choisir Article --</option>
                        </select>
                    </div>

                    <!-- Couleur -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Couleur</label>
                        <input type="text" id="art_couleur" class="form-control"
                               placeholder="Ex: Bleu marine, Noir...">
                    </div>

                    <!-- Marque -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Marque</label>
                        <input type="text" id="art_marque" class="form-control"
                               placeholder="Ex: Zara, Lacoste...">
                    </div>

                    <!-- Matière (Ajouté pour cohérence Migration) -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Matière</label>
                        <input type="text" id="art_matiere" class="form-control"
                               placeholder="Ex: Coton, Soie...">
                    </div>

                    <!-- Code-barres (Ajouté pour cohérence Migration) -->
                    <div class="col-12">
                        <label class="form-label fw-bold text-muted small">Code-barres / Identifiant unique</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-barcode"></i></span>
                            <input type="text" id="art_barcode" class="form-control bg-light" 
                                   placeholder="Scanner ou génération automatique..." readonly>
                        </div>
                    </div>

                    <!-- Zone Prestation + Prix + Points -->
                    <div id="zone_prestation" class="col-12 d-none">
                        <div class="card border-primary bg-light">
                            <div class="card-body row align-items-center g-0">
                                <div class="col-md-5">
                                    <label class="form-label fw-bold text-primary">Prestation</label>
                                    <select id="sel_prestation" class="form-select"></select>
                                </div>
                                <div class="col-md-3 text-center border-start py-2">
                                    <label class="form-label fw-bold">Prix (FCFA)</label>
                                    <div id="prix_affiche" class="h4 mt-1 text-success fw-bold">0</div>
                                    <input type="hidden" id="input_prix">
                                </div>
                                <div class="col-md-4 text-center border-start py-2">
                                    <label class="form-label fw-bold">Points fidélité</label>
                                    <div class="h4 mt-1 fw-bold" style="color:#f0ad4e;">
                                        <i class="fas fa-star me-1"></i>
                                        <span id="points_valeur">0</span>
                                    </div>
                                    <input type="hidden" id="input_points">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Option Express -->
                    <div class="col-12">
                        <div class="form-check form-switch p-3 border rounded bg-light"
                             style="border-style:dashed !important;border-color:#dc3545 !important;">
                            <input class="form-check-input ms-0 me-2" type="checkbox"
                                   id="options_express">
                            <label class="form-check-label fw-bold text-danger"
                                   for="options_express">
                                <i class="fas fa-bolt me-1"></i> COMMANDE EXPRESS
                            </label>
                            <small class="d-block text-muted ms-4">
                                Cochez pour un retrait le jour même ou sous 24h.
                            </small>
                        </div>
                    </div>

                    <!-- Observations -->
                    <div class="col-12">
                        <label class="form-label fw-bold">Observations</label>
                        <textarea id="art_obs" class="form-control" rows="2"
                                  placeholder="Taches, déchirures, consignes..."></textarea>
                    </div>

                </div>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Annuler</button>
                <button type="button" id="btn_confirmer_article" class="btn btn-primary px-4">
                    <i class="fas fa-plus-circle me-2"></i>Ajouter au dépôt
                </button>
            </div>

        </div>
    </div>
</div>
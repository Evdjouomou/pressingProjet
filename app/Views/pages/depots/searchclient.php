<!-- ══════════════════════════════════════════ -->
<!-- BLOC RECHERCHE CLIENT + DATE + N° BON     -->
<!-- ══════════════════════════════════════════ -->
<div class="col-12 mb-4">
    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-white py-3 border-bottom border-light d-flex align-items-center gap-2">
            <div class="rounded-2 p-2 bg-primary bg-opacity-10">
                <i class="fas fa-file-plus text-primary"></i>
            </div>
            <span class="fw-semibold text-dark">Informations du dépôt</span>
        </div>

        <div class="card-body">
            <div class="row g-3 align-items-end">

                <!-- Recherche client -->
                <div class="col position-relative" id="client_zone">
                    <label class="form-label text-muted small fw-semibold mb-1">Client</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="fas fa-search fa-sm"></i>
                        </span>
                        <input type="text"
                               id="search_input"
                               class="form-control bg-light border-start-0 shadow-none"
                               placeholder="Nom ou téléphone..."
                               autocomplete="off">
                    </div>
                    <div id="suggestion_box"
                         class="list-group shadow-lg d-none position-absolute w-100 mt-1"
                         style="z-index:1050;"></div>
                </div>

                <!-- Date retrait -->
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-semibold mb-1">
                        <i class="fas fa-calendar-alt me-1"></i>Date retrait
                    </label>
                    <input type="date"
                           id="champ_date_retrait"
                           class="form-control bg-light"
                           value="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                </div>

                <!-- Numéro bon -->
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-semibold mb-1">
                        <i class="fas fa-hashtag me-1"></i>N° Bon
                    </label>
                    <input type="text"
                           id="champ_numero_bon"
                           class="form-control bg-light text-muted font-monospace"
                           value="CMD-<?= date('YmdHis') ?>"
                           readonly>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════ -->
<!-- CLIENT SÉLECTIONNÉ                        -->
<!-- ══════════════════════════════════════════ -->
<div id="result_client" class="col-12 mb-4 d-none">
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body d-flex justify-content-between align-items-center py-3">

            <div class="d-flex align-items-center gap-3">
                <!-- Avatar initiales -->
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center
                            justify-content-center fw-semibold text-primary"
                     style="width:46px;height:46px;font-size:15px;" id="client_avatar">
                    --
                </div>
                <div>
                    <p class="text-primary text-uppercase fw-semibold mb-0"
                       style="font-size:11px;letter-spacing:.5px;">
                        Client sélectionné
                    </p>
                    <h5 id="client_nom_trouve" class="mb-0 fw-semibold text-dark"></h5>
                    <p id="client_tel_trouve" class="mb-0 text-muted small">
                        <i class="fas fa-phone fa-xs me-1"></i>
                    </p>
                </div>
            </div>

            <button type="button"
                    class="btn btn-sm btn-outline-danger rounded-2 px-3"
                    onclick="resetClient()">
                <i class="fas fa-user-edit me-1"></i>Changer
            </button>

        </div>
    </div>
</div>
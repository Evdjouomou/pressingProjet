<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<style>
.pos-grid { display:grid; grid-template-columns:1fr 380px; gap:20px; height:calc(100vh - 120px); }
.pos-left  { overflow-y:auto; }
.pos-right { display:flex; flex-direction:column; gap:12px; overflow-y:auto; }
.cmd-card  { cursor:pointer; border:2px solid transparent; transition:.15s; border-radius:12px; }
.cmd-card:hover   { border-color:#3b82f6; box-shadow:0 4px 12px rgba(59,130,246,.15); }
.cmd-card.active  { border-color:#3b82f6; background:#eff6ff; }
.prod-card { cursor:pointer; border:2px solid transparent; border-radius:10px; transition:.15s; }
.prod-card:hover  { border-color:#10b981; }
@media(max-width:900px) { .pos-grid { grid-template-columns:1fr; height:auto; } }
</style>

<div class="container-fluid py-3">

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3 mb-3">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3 mb-3">
            <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Barre supérieure -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <h4 class="fw-bold mb-0"><i class="fas fa-cash-register text-primary me-2"></i>Point de Vente</h4>
            <?php if ($caisse): ?>
                <span style="background:#dcfce7;color:#166534;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600;">
                    <i class="fas fa-circle fa-xs me-1"></i>Caisse ouverte — <?= number_format($caisse['fond_ouverture'],0,',',' ') ?> FCFA fond
                </span>
            <?php else: ?>
                <span style="background:#fee2e2;color:#991b1b;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600;">
                    <i class="fas fa-exclamation-circle me-1"></i>Caisse fermée
                </span>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('pos/caisse') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
                <i class="fas fa-cash-register me-1"></i>Caisse
            </a>
            <a href="<?= base_url('pos/produits') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
                <i class="fas fa-box me-1"></i>Produits
            </a>
            <button class="btn btn-outline-danger btn-sm rounded-2"
                    data-bs-toggle="modal" data-bs-target="#modalRemboursement">
                <i class="fas fa-undo me-1"></i>Remboursement
            </button>
        </div>
    </div>

    <?php if (!$caisse): ?>
    <!-- Alerte caisse fermée -->
    <div class="card border-0 shadow-sm rounded-3 mb-3"
         style="background:linear-gradient(135deg,#fff5f5,#fee2e2);border:1px solid #fecaca !important;">
        <div class="card-body text-center py-4">
            <i class="fas fa-lock fa-3x text-danger mb-3 d-block opacity-50"></i>
            <h5 class="fw-bold text-danger">Caisse non ouverte</h5>
            <p class="text-muted mb-3">Vous devez ouvrir la caisse avant de commencer à encaisser.</p>
            <a href="<?= base_url('pos/caisse') ?>" class="btn btn-danger rounded-2 px-4">
                <i class="fas fa-cash-register me-2"></i>Ouvrir la caisse maintenant
            </a>
        </div>
    </div>
    <?php endif; ?>

    <div class="pos-grid">

        <!-- GAUCHE : commandes + produits -->
        <div class="pos-left">

            <!-- Recherche -->
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body py-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="rechercheInput"
                               class="form-control bg-light border-start-0 shadow-none"
                               placeholder="Nom client, n° bon, téléphone ou scanner QR..."
                               autocomplete="off">
                        <button class="btn btn-primary" id="btnScanQR"
                                data-bs-toggle="modal" data-bs-target="#modalScanQR">
                            <i class="fas fa-qrcode"></i>
                        </button>
                    </div>
                    <div id="dropdownRecherche"
                         class="list-group shadow-lg position-absolute d-none"
                         style="z-index:1050;width:calc(100% - 48px);margin-top:2px;border-radius:10px;">
                    </div>
                </div>
            </div>

            <!-- Commandes prêtes -->
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom d-flex justify-content-between">
                        <p class="text-uppercase text-muted fw-semibold mb-0" style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-check-circle text-success me-2"></i>Commandes prêtes
                        </p>
                        <span class="badge bg-success rounded-pill"><?= count($commandes) ?></span>
                    </div>
                    <div class="p-3">
                        <?php if (empty($commandes)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                                Aucune commande prête à encaisser.
                            </div>
                        <?php else: ?>
                        <div class="row g-2" id="listeCommandes">
                            <?php foreach ($commandes as $cmd): ?>
                            <div class="col-md-6">
                                <div class="cmd-card card border-0 shadow-sm p-3"
                                     onclick="chargerCommande(<?= $cmd['id_depot'] ?>)">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="fw-bold text-primary" style="font-size:13px;">
                                            <?= esc($cmd['code_commande']) ?>
                                        </span>
                                        <?php if ($cmd['reste'] > 0): ?>
                                            <span style="background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;">
                                                <?= number_format($cmd['reste'],0,',',' ') ?> FCFA
                                            </span>
                                        <?php else: ?>
                                            <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:20px;font-size:11px;">Soldé</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="fw-semibold" style="font-size:13px;"><?= esc($cmd['nomclient']) ?></div>
                                    <div class="text-muted" style="font-size:11px;">
                                        <i class="fas fa-phone me-1"></i><?= esc($cmd['telephone']) ?>
                                        · <?= $cmd['nb_articles'] ?> article(s)
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Produits annexes -->
            <?php if (!empty($produits)): ?>
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom">
                        <p class="text-uppercase text-muted fw-semibold mb-0" style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-box me-2"></i>Ventes annexes
                        </p>
                    </div>
                    <div class="p-3">
                        <div class="row g-2">
                            <?php foreach ($produits as $prod): ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="prod-card card border shadow-sm p-2 text-center"
                                     onclick="ajouterProduit(<?= $prod['id_produit'] ?>, '<?= esc($prod['nom']) ?>', <?= $prod['prix'] ?>)">
                                    <i class="fas fa-box-open text-success mb-1"></i>
                                    <div class="fw-semibold" style="font-size:12px;"><?= esc($prod['nom']) ?></div>
                                    <div class="text-success fw-bold" style="font-size:12px;">
                                        <?= number_format($prod['prix'],0,',',' ') ?> FCFA
                                    </div>
                                    <div class="text-muted" style="font-size:10px;">Stock: <?= $prod['stock'] ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- DROITE : ticket d'encaissement -->
        <div class="pos-right">

            <!-- Commande sélectionnée -->
            <div class="card border-0 shadow-sm rounded-3" id="zoneTicket">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center"
                         style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:12px 12px 0 0;">
                        <p class="text-white fw-semibold mb-0" style="font-size:12px;letter-spacing:.5px;">
                            <i class="fas fa-receipt me-2"></i>TICKET
                        </p>
                        <span id="ticketBon" class="text-white opacity-75" style="font-size:12px;">—</span>
                    </div>

                    <!-- Infos client -->
                    <div id="ticketClient" class="px-4 py-3 border-bottom d-none">
                        <div class="fw-bold" id="ticketClientNom"></div>
                        <div class="text-muted small" id="ticketClientTel"></div>
                        <div class="text-warning small" id="ticketFidelite"></div>
                    </div>

                    <!-- Articles -->
                    <div id="ticketArticles" class="px-3 py-2" style="max-height:220px;overflow-y:auto;">
                        <div class="text-center text-muted py-4" id="ticketVide">
                            <i class="fas fa-hand-pointer fa-2x mb-2 d-block opacity-25"></i>
                            Sélectionnez une commande
                        </div>
                        <div id="listeArticlesTicket"></div>
                    </div>

                    <!-- Totaux -->
                    <div id="ticketTotaux" class="px-4 py-3 border-top d-none"
                         style="background:#f8fafc;">
                        <div class="d-flex justify-content-between mb-1" style="font-size:13px;">
                            <span class="text-muted">Total facture</span>
                            <span class="fw-semibold" id="ticketTotal">0 FCFA</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1" style="font-size:13px;">
                            <span class="text-muted">Déjà payé</span>
                            <span class="text-success" id="ticketDejaPaye">0 FCFA</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2 mt-1">
                            <span class="fw-bold">Reste à payer</span>
                            <span class="fw-bold text-danger fs-5" id="ticketReste">0 FCFA</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paiement -->
            <div class="card border-0 shadow-sm rounded-3" id="zonePaiement">
                <div class="card-body">
                    <p class="text-uppercase text-muted fw-semibold mb-3" style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-coins me-2"></i>Mode de paiement
                    </p>

                    <!-- Boutons modes -->
                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <button class="btn btn-sm mode-btn active rounded-2 flex-fill" data-mode="especes">
                            <i class="fas fa-money-bill me-1"></i>Espèces
                        </button>
                        <button class="btn btn-sm mode-btn rounded-2 flex-fill" data-mode="mobile_money">
                            <i class="fas fa-mobile me-1"></i>Mobile
                        </button>
                        <button class="btn btn-sm mode-btn rounded-2 flex-fill" data-mode="carte">
                            <i class="fas fa-credit-card me-1"></i>Carte
                        </button>
                        <button class="btn btn-sm mode-btn rounded-2 flex-fill" data-mode="mixte">
                            <i class="fas fa-layer-group me-1"></i>Mixte
                        </button>
                    </div>

                    <!-- Champs montants -->
                    <div id="champsSimples">
                        <label class="form-label fw-semibold small">Montant encaissé (FCFA)</label>
                        <input type="number" id="montantEncaisse" class="form-control form-control-lg fw-bold"
                               placeholder="0" min="0">
                        <div id="zoneRendu" class="mt-2 d-none rounded-2 p-2 text-center"
                             style="background:#f0fdf4;border:1px solid #bbf7d0;">
                            <span class="text-muted small">Rendu monnaie : </span>
                            <span class="fw-bold text-success fs-5" id="affRendu">0 FCFA</span>
                        </div>
                    </div>

                    <div id="champsMixte" class="d-none">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label fw-semibold" style="font-size:11px;">Espèces</label>
                                <input type="number" id="mixEspeces" class="form-control" placeholder="0" min="0" oninput="calculerMixte()">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold" style="font-size:11px;">Mobile</label>
                                <input type="number" id="mixMobile" class="form-control" placeholder="0" min="0" oninput="calculerMixte()">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold" style="font-size:11px;">Carte</label>
                                <input type="number" id="mixCarte" class="form-control" placeholder="0" min="0" oninput="calculerMixte()">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold" style="font-size:11px;">Fidélité (pts)</label>
                                <input type="number" id="mixFidelite" class="form-control" placeholder="0" min="0" oninput="calculerMixte()">
                            </div>
                        </div>
                        <div class="mt-2 rounded-2 p-2" style="background:#fefce8;border:1px solid #fde68a;">
                            <div class="d-flex justify-content-between" style="font-size:12px;">
                                <span class="text-muted">Total saisi</span>
                                <span class="fw-bold" id="mixTotal">0 FCFA</span>
                            </div>
                            <div class="d-flex justify-content-between" style="font-size:12px;">
                                <span class="text-muted">Reste</span>
                                <span class="fw-bold text-danger" id="mixReste">0 FCFA</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton encaisser -->
                    <button id="btnEncaisser"
                            class="btn btn-success w-100 rounded-2 fw-bold mt-3"
                            style="height:52px;font-size:16px;"
                            onclick="validerEncaissement()"
                            disabled>
                        <i class="fas fa-check me-2"></i>Encaisser
                    </button>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL SCAN QR -->
<div class="modal fade" id="modalScanQR" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="fas fa-qrcode text-primary me-2"></i>Scanner le bon</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <div id="readerPOS" style="width:100%;border-radius:10px;overflow:hidden;"></div>
                <p class="text-muted small text-center mt-2">Pointez vers le QR code du bon de dépôt.</p>
            </div>
        </div>
    </div>
</div>

<!-- MODAL REMBOURSEMENT -->
<div class="modal fade" id="modalRemboursement" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="fas fa-undo text-danger me-2"></i>Remboursement / Avoir</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('pos/rembourser') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">N° de bon concerné</label>
                        <input type="text" name="code_bon" class="form-control"
                               placeholder="BON-XXXXXXXXXX" required>
                    </div>
                    <input type="hidden" name="depot_id" id="remb_depot_id">
                    <input type="hidden" name="client_id" id="remb_client_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Montant à rembourser (FCFA)</label>
                        <input type="number" name="montant_remboursement" class="form-control"
                               placeholder="0" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Type de remboursement</label>
                        <div class="d-flex gap-2">
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="type_remboursement"
                                       id="rembEspeces" value="especes" checked>
                                <label class="btn btn-outline-secondary w-100" for="rembEspeces">
                                    <i class="fas fa-money-bill me-1"></i>Espèces
                                </label>
                            </div>
                            <div class="flex-fill">
                                <input type="radio" class="btn-check" name="type_remboursement"
                                       id="rembAvoir" value="avoir">
                                <label class="btn btn-outline-secondary w-100" for="rembAvoir">
                                    <i class="fas fa-gift me-1"></i>Avoir client
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Motif obligatoire <span class="text-danger">*</span></label>
                        <textarea name="motif" class="form-control" rows="2"
                                  placeholder="Article endommagé, erreur de prestation..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger px-4 rounded-2">
                        <i class="fas fa-undo me-2"></i>Valider le remboursement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
const BASE = '<?= base_url() ?>';
let depotActif   = null;
let modeActif    = 'especes';
let scannerPOS   = null;
let produitsAnnexes = [];

// ── Styles boutons mode ──────────────────────────────
document.querySelectorAll('.mode-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.mode-btn').forEach(b => {
            b.classList.remove('active','btn-primary');
            b.classList.add('btn-outline-secondary');
        });
        this.classList.add('active','btn-primary');
        this.classList.remove('btn-outline-secondary');
        modeActif = this.dataset.mode;

        document.getElementById('champsSimples').classList.toggle('d-none', modeActif === 'mixte');
        document.getElementById('champsMixte').classList.toggle('d-none',   modeActif !== 'mixte');
    });
});
// Init style boutons
document.querySelectorAll('.mode-btn').forEach(b => {
    if (b.dataset.mode !== 'especes') {
        b.classList.add('btn-outline-secondary');
        b.classList.remove('btn-primary');
    } else {
        b.classList.add('btn-primary');
    }
});

// ── Recherche live ───────────────────────────────────
let rechTimer = null;
document.getElementById('rechercheInput').addEventListener('input', function () {
    clearTimeout(rechTimer);
    const q = this.value.trim();
    if (q.length < 2) { document.getElementById('dropdownRecherche').classList.add('d-none'); return; }
    rechTimer = setTimeout(() => {
        fetch(`${BASE}pos/api/recherche?q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(data => {
                const dd = document.getElementById('dropdownRecherche');
                dd.innerHTML = '';
                if (!data.length) { dd.classList.add('d-none'); return; }
                data.forEach(d => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action d-flex justify-content-between';
                    btn.innerHTML = `<div><strong>${d.code_commande}</strong> — ${d.nomclient}</div>
                                     <span class="text-danger fw-bold">${Number(d.total_ttc - d.acompte_verse).toLocaleString('fr-FR')} FCFA</span>`;
                    btn.onclick = () => { chargerCommande(d.id_depot); dd.classList.add('d-none'); };
                    dd.appendChild(btn);
                });
                dd.classList.remove('d-none');
            });
    }, 300);
});

// ── Charger commande ─────────────────────────────────
function chargerCommande(id) {
    document.querySelectorAll('.cmd-card').forEach(c => c.classList.remove('active'));
    const card = document.querySelector(`[onclick="chargerCommande(${id})"]`);
    if (card) card.classList.add('active');

    fetch(`${BASE}pos/commande/${id}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { alert(data.message); return; }
            depotActif = data.depot;
            afficherTicket(depotActif);
        });
}

function afficherTicket(depot) {
    document.getElementById('ticketVide').classList.add('d-none');
    document.getElementById('ticketBon').textContent  = depot.code_commande;
    document.getElementById('ticketClient').classList.remove('d-none');
    document.getElementById('ticketClientNom').textContent = depot.nomclient;
    document.getElementById('ticketClientTel').textContent = '📞 ' + depot.telephone;
    document.getElementById('ticketFidelite').textContent  = '⭐ ' + depot.solde_fidelite + ' pts fidélité';
    document.getElementById('ticketTotaux').classList.remove('d-none');
    document.getElementById('ticketTotal').textContent    = Number(depot.total_ttc).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('ticketDejaPaye').textContent = Number(depot.deja_paye).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('ticketReste').textContent    = Number(depot.reste_a_payer).toLocaleString('fr-FR') + ' FCFA';

    const liste = document.getElementById('listeArticlesTicket');
    liste.innerHTML = depot.articles.map(a => `
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="font-size:12px;">
            <div>
                <div class="fw-semibold">${a.nom_libelle}</div>
                <div class="text-muted">${a.type_prestation || '—'} ${a.options_express == 1 ? '🚀' : ''}</div>
            </div>
            <span class="fw-bold text-success">${Number(a.prix_applique).toLocaleString('fr-FR')} FCFA</span>
        </div>
    `).join('');

    // Pré-remplir montant
    document.getElementById('montantEncaisse').value = depot.reste_a_payer;
    document.getElementById('btnEncaisser').disabled = false;
    calculerRendu();
}

// ── Calculs ──────────────────────────────────────────
document.getElementById('montantEncaisse').addEventListener('input', calculerRendu);

function calculerRendu() {
    if (!depotActif) return;
    const encaisse = parseFloat(document.getElementById('montantEncaisse').value) || 0;
    const reste    = depotActif.reste_a_payer;
    const rendu    = encaisse - reste;
    const zoneRendu = document.getElementById('zoneRendu');
    if (modeActif === 'especes' && encaisse > 0) {
        zoneRendu.classList.remove('d-none');
        document.getElementById('affRendu').textContent = Math.max(0, rendu).toLocaleString('fr-FR') + ' FCFA';
    } else {
        zoneRendu.classList.add('d-none');
    }
}

function calculerMixte() {
    if (!depotActif) return;
    const esp = parseFloat(document.getElementById('mixEspeces').value)  || 0;
    const mob = parseFloat(document.getElementById('mixMobile').value)   || 0;
    const car = parseFloat(document.getElementById('mixCarte').value)    || 0;
    const fid = parseFloat(document.getElementById('mixFidelite').value) || 0;
    const total = esp + mob + car + fid;
    const reste = Math.max(0, depotActif.reste_a_payer - total);
    document.getElementById('mixTotal').textContent = total.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('mixReste').textContent = reste.toLocaleString('fr-FR') + ' FCFA';
}

// ── Produits annexes ─────────────────────────────────
function ajouterProduit(id, nom, prix) {
    const existant = produitsAnnexes.findIndex(p => p.id === id);
    if (existant >= 0) {
        produitsAnnexes[existant].qte++;
    } else {
        produitsAnnexes.push({ id, nom, prix, qte: 1 });
    }
    // TODO: afficher dans le ticket et encaisser séparément si pas de dépôt actif
    alert(`${nom} ajouté (${prix.toLocaleString('fr-FR')} FCFA)`);
}

// ── Valider encaissement ─────────────────────────────
function validerEncaissement() {
    if (!depotActif) { alert('Sélectionnez une commande.'); return; }

    let payload = {
        depot_id:    depotActif.id_depot,
        client_id:   depotActif.client_id,
        '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
    };

    if (modeActif === 'mixte') {
        const esp = parseFloat(document.getElementById('mixEspeces').value)  || 0;
        const mob = parseFloat(document.getElementById('mixMobile').value)   || 0;
        const car = parseFloat(document.getElementById('mixCarte').value)    || 0;
        const fid = parseFloat(document.getElementById('mixFidelite').value) || 0;
        payload.montant_total   = esp + mob + car + fid;
        payload.mode_paiement   = 'mixte';
        payload.montant_especes = esp;
        payload.montant_mobile  = mob;
        payload.montant_carte   = car;
        payload.montant_fidelite = fid;
    } else {
        const montant = parseFloat(document.getElementById('montantEncaisse').value) || 0;
        payload.montant_total   = montant;
        payload.mode_paiement   = modeActif;
        payload.montant_especes = modeActif === 'especes'      ? montant : 0;
        payload.montant_mobile  = modeActif === 'mobile_money' ? montant : 0;
        payload.montant_carte   = modeActif === 'carte'        ? montant : 0;
        payload.rendu_monnaie   = Math.max(0, montant - depotActif.reste_a_payer);
    }

    const btn = document.getElementById('btnEncaisser');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Traitement...';

    fetch(`${BASE}pos/encaisser`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(payload),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Proposer reçu ou facture
            const choix = confirm('Encaissement réussi !\n\nOK = Imprimer le reçu\nAnnuler = Continuer sans imprimer');
            if (choix) window.open(`${BASE}pos/recu/${data.id_transaction}`, '_blank');
            location.reload();
        } else {
            alert('Erreur : ' + data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check me-2"></i>Encaisser';
        }
    });
}

// ── Scan QR ──────────────────────────────────────────
document.getElementById('modalScanQR').addEventListener('shown.bs.modal', () => {
    scannerPOS = new Html5Qrcode('readerPOS');
    scannerPOS.start(
        { facingMode: 'environment' },
        { fps: 10, qrbox: { width: 250, height: 120 } },
        (code) => {
            scannerPOS.stop();
            bootstrap.Modal.getInstance(document.getElementById('modalScanQR')).hide();
            // Le QR code contient l'id_depot ou le code_commande
            const id = parseInt(code);
            if (!isNaN(id)) chargerCommande(id);
            else document.getElementById('rechercheInput').value = code;
        },
        () => {}
    ).catch(() => {
        document.getElementById('readerPOS').innerHTML =
            '<div class="alert alert-warning">Caméra indisponible.</div>';
    });
});
document.getElementById('modalScanQR').addEventListener('hidden.bs.modal', () => {
    if (scannerPOS) { scannerPOS.stop().catch(() => {}); scannerPOS = null; }
});
</script>

<?= $this->endSection() ?>
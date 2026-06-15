<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<style>
    .pos-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 16px;
        height: calc(100vh - 80px);
    }
    @media (max-width: 992px) {
        .pos-layout { grid-template-columns: 1fr; height: auto; }
    }
    .pos-left  { overflow-y: auto; }
    .pos-right {
        background: #1a1a2e;
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .commande-card {
        background: #fff;
        border-radius: 10px;
        padding: 14px;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: all .15s;
    }
    .commande-card:hover,
    .commande-card.active {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px #bfdbfe;
    }
    .produit-card {
        background: #fff;
        border-radius: 10px;
        padding: 10px;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        text-align: center;
        transition: all .15s;
        font-size: 12px;
    }
    .produit-card:hover { border-color: #10b981; box-shadow: 0 0 0 2px #d1fae5; }
    .mode-btn { cursor: pointer; transition: all .15s; }
    .mode-btn.active { background: #3b82f6 !important; color: #fff !important; }
</style>

<div class="container-fluid py-3">

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <h4 class="fw-bold mb-0">
                <i class="fas fa-cash-register text-primary me-2"></i>Point de Vente
            </h4>
            <span class="badge rounded-pill px-3 py-2"
                  style="background:#dcfce7;color:#166534;font-size:12px;">
                ● Caisse ouverte — <?= number_format($caisse['fond_ouverture'], 0, ',', ' ') ?> FCFA fond
            </span>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('pos/caisse') ?>" class="btn btn-outline-secondary btn-sm rounded-2">
                <i class="fas fa-cash-register me-1"></i>Caisse
            </a>
            <a href="<?= base_url('stocks') ?>" class="btn btn-outline-primary btn-sm rounded-2">
                <i class="fas fa-box me-1"></i>Produits
            </a>
        </div>
    </div>

    <div class="pos-layout">

        <!-- ════════════════ GAUCHE ════════════════ -->
        <div class="pos-left">

            <!-- Recherche -->
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body py-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text"
                               id="searchInput"
                               class="form-control bg-light shadow-none"
                               placeholder="Nom client, n° bon, téléphone ou scanner QR..."
                               autocomplete="off">
                        <button class="btn btn-primary" onclick="lancerRecherche()">
                            <i class="fas fa-qrcode"></i>
                        </button>
                    </div>
                    <div id="resultatsRecherche" class="mt-2"></div>
                </div>
            </div>

            <!-- Commandes prêtes -->
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                        <p class="text-uppercase fw-semibold mb-0"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Commandes prêtes
                        </p>
                        <span class="badge bg-success rounded-pill">
                            <?= count($commandesPrêtes) ?>
                        </span>
                    </div>
                    <div class="p-3">
                        <?php if (empty($commandesPrêtes)): ?>
                        <div class="text-center py-4 text-muted small">
                            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                            Aucune commande prête à encaisser.
                        </div>
                        <?php else: ?>
                        <div class="row g-2">
                            <?php foreach ($commandesPrêtes as $cmd):
                                $reste = max(0, $cmd['total_ttc'] - $cmd['total_encaisse']);
                            ?>
                            <div class="col-md-6">
                                <div class="commande-card"
                                     onclick="chargerCommande(<?= $cmd['id_depot'] ?>)">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-bold text-primary" style="font-size:13px;">
                                            <?= esc($cmd['code_commande']) ?>
                                        </span>
                                        <?php if ($reste > 0): ?>
                                        <span style="background:#fee2e2;color:#991b1b;
                                                     padding:2px 8px;border-radius:20px;
                                                     font-size:11px;font-weight:600;">
                                            <?= number_format($reste, 0, ',', ' ') ?> FCFA
                                        </span>
                                        <?php else: ?>
                                        <span style="background:#dcfce7;color:#166534;
                                                     padding:2px 8px;border-radius:20px;
                                                     font-size:11px;font-weight:600;">
                                            Soldé ✓
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="fw-semibold" style="font-size:12px;">
                                        <?= esc($cmd['nomclient']) ?>
                                    </div>
                                    <div class="text-muted" style="font-size:11px;">
                                        <?= esc($cmd['telephone']) ?> ·
                                        <?= $cmd['nb_articles'] ?> article(s)
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Produits boutique -->
            <?php if (!empty($produits)): ?>
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom">
                        <p class="text-uppercase fw-semibold mb-0"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-shopping-bag text-primary me-2"></i>
                            Produits boutique
                        </p>
                    </div>
                    <div class="p-3">
                        <div class="row g-2">
                            <?php foreach ($produits as $prod): ?>
                            <div class="col-6 col-md-3">
                                <div class="produit-card"
                                     onclick="ajouterProduit(<?= $prod['id_produit'] ?>,
                                              '<?= esc($prod['nom']) ?>',
                                              <?= $prod['prix'] ?>,
                                              <?= $prod['stock'] ?>)">
                                    <i class="fas fa-box text-primary mb-1 d-block"></i>
                                    <div class="fw-semibold" style="font-size:11px;">
                                        <?= esc($prod['nom']) ?>
                                    </div>
                                    <div class="text-success fw-bold" style="font-size:12px;">
                                        <?= number_format($prod['prix'], 0, ',', ' ') ?> FCFA
                                    </div>
                                    <div class="text-muted" style="font-size:10px;">
                                        Stock : <?= $prod['stock'] ?> <?= esc($prod['unite']) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- ════════════════ DROITE : TICKET ════════════════ -->
        <div class="pos-right">

            <!-- Header ticket -->
            <div class="px-4 py-3 border-bottom border-secondary">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-white fw-bold">
                        <i class="fas fa-receipt me-2"></i>TICKET
                    </span>
                    <button onclick="viderTicket()"
                            class="btn btn-sm"
                            style="background:rgba(255,255,255,.1);color:#fff;border:none;">
                        ✕
                    </button>
                </div>
            </div>

            <!-- Contenu ticket -->
            <div id="ticketContenu" class="flex-fill overflow-auto px-4 py-3">
                <div class="text-center text-secondary py-5" id="ticketVide">
                    <i class="fas fa-hand-point-left fa-2x mb-2 d-block opacity-50"></i>
                    <span style="font-size:13px;">Sélectionnez une commande</span>
                </div>
                <div id="ticketData" class="d-none">
                    <!-- Infos commande -->
                    <div class="mb-3">
                        <div class="text-muted" style="font-size:10px;text-transform:uppercase;">Client</div>
                        <div class="text-white fw-bold" id="t_client">—</div>
                        <div class="text-secondary" style="font-size:11px;" id="t_tel">—</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted" style="font-size:10px;text-transform:uppercase;">Commande</div>
                        <div class="text-primary fw-bold" id="t_bon">—</div>
                    </div>

                    <!-- Articles -->
                    <div id="t_articles" class="mb-3"></div>

                    <!-- Totaux -->
                    <div class="border-top border-secondary pt-2 mb-3">
                        <div class="d-flex justify-content-between text-white mb-1">
                            <span style="font-size:12px;">Total facture</span>
                            <span class="fw-bold" id="t_total">—</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary" style="font-size:12px;">Déjà encaissé</span>
                            <span class="text-success" id="t_encaisse">—</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-white fw-bold">Reste à payer</span>
                            <span class="text-danger fw-bold fs-5" id="t_reste">—</span>
                        </div>
                    </div>
                </div>

                <!-- Produits boutique ajoutés -->
                <div id="produitsTicket" class="d-none mb-3">
                    <div class="text-muted" style="font-size:10px;text-transform:uppercase;
                                                   margin-bottom:6px;">Produits boutique</div>
                    <div id="listeProduits"></div>
                </div>
            </div>

            <!-- Mode paiement -->
            <div class="px-4 py-3 border-top border-secondary">
                <div class="text-muted mb-2" style="font-size:10px;text-transform:uppercase;
                                                     letter-spacing:.5px;">Mode de paiement</div>
                <div class="d-flex gap-2 mb-3">
                    <?php
                    $modes = [
                        'especes'      => ['label' => 'Espèces',   'icon' => 'fa-money-bill-wave'],
                        'mobile_money' => ['label' => 'Mobile',    'icon' => 'fa-mobile-alt'],
                        'carte'        => ['label' => 'Carte',     'icon' => 'fa-credit-card'],
                        'mixte'        => ['label' => 'Mixte',     'icon' => 'fa-layer-group'],
                    ];
                    foreach ($modes as $val => $m):
                    ?>
                    <div class="flex-fill mode-btn rounded-2 text-center py-2 px-1
                                <?= $val === 'especes' ? 'active' : '' ?>"
                         style="background:rgba(255,255,255,.08);color:#94a3b8;font-size:11px;"
                         data-mode="<?= $val ?>"
                         onclick="selectionnerMode('<?= $val ?>')">
                        <i class="fas <?= $m['icon'] ?> d-block mb-1" style="font-size:14px;"></i>
                        <?= $m['label'] ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Montant mixte -->
                <div id="zoneMixte" class="d-none mb-3">
                    <div class="row g-2">
                        <div class="col-4">
                            <label class="text-muted" style="font-size:10px;">Espèces</label>
                            <input type="number" id="mix_especes" class="form-control form-control-sm"
                                   placeholder="0" min="0" oninput="calculerMixte()">
                        </div>
                        <div class="col-4">
                            <label class="text-muted" style="font-size:10px;">Mobile</label>
                            <input type="number" id="mix_mobile" class="form-control form-control-sm"
                                   placeholder="0" min="0" oninput="calculerMixte()">
                        </div>
                        <div class="col-4">
                            <label class="text-muted" style="font-size:10px;">Carte</label>
                            <input type="number" id="mix_carte" class="form-control form-control-sm"
                                   placeholder="0" min="0" oninput="calculerMixte()">
                        </div>
                    </div>
                    <div id="mixteErreur" class="text-warning mt-1 d-none"
                         style="font-size:11px;"></div>
                </div>

                <!-- Rendu monnaie -->
                <div id="zoneRendu" class="d-none mb-2 rounded-2 p-2 text-center"
                     style="background:rgba(16,185,129,.15);">
                    <div class="text-muted" style="font-size:10px;">Rendu monnaie</div>
                    <div class="text-success fw-bold fs-5" id="montantRendu">0 FCFA</div>
                </div>

                <!-- Bouton encaisser -->
                <button id="btnEncaisser"
                        class="btn btn-success w-100 btn-lg rounded-2 fw-bold"
                        onclick="procederEncaissement()"
                        disabled>
                    <i class="fas fa-check me-2"></i>Encaisser
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Toast feedback -->
<div id="toastPos"
     class="position-fixed bottom-0 end-0 m-3 toast align-items-center border-0"
     style="z-index:9999;min-width:280px;"
     role="alert">
    <div class="d-flex">
        <div class="toast-body fw-semibold" id="toastMsg"></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto"
                data-bs-dismiss="toast"></button>
    </div>
</div>

<script>
const BASE        = '<?= base_url() ?>';
const CSRF_TOKEN  = '<?= csrf_token() ?>';
const CSRF_HASH   = '<?= csrf_hash() ?>';

let depotActif    = null; // données de la commande sélectionnée
let modeActif     = 'especes';
let produitsPanier = []; // produits boutique ajoutés

// ─────────────────────────────────────────────────────────────
// RECHERCHE
// ─────────────────────────────────────────────────────────────
let rechercheTimer = null;
document.getElementById('searchInput').addEventListener('input', function () {
    clearTimeout(rechercheTimer);
    const q = this.value.trim();
    if (q.length < 2) {
        document.getElementById('resultatsRecherche').innerHTML = '';
        return;
    }
    rechercheTimer = setTimeout(() => rechercherCommande(q), 300);
});

document.getElementById('searchInput').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') rechercherCommande(this.value.trim());
});

function lancerRecherche() {
    rechercherCommande(document.getElementById('searchInput').value.trim());
}

function rechercherCommande(q) {
    if (!q) return;
    fetch(`${BASE}/pos/api/recherche?q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(data => {
            const zone = document.getElementById('resultatsRecherche');
            if (!data.length) {
                zone.innerHTML = '<div class="text-muted small py-2">Aucun résultat.</div>';
                return;
            }
            const statutColors = {
                'pret'     : '#dcfce7',
                'depot'    : '#f1f5f9',
                'en_cours' : '#fef3c7',
                'livre'    : '#f1f5f9',
            };
            const statutLabels = {
                'pret'     : '✅ Prêt',
                'depot'    : '📦 Reçu',
                'en_cours' : '⚙️ En cours',
                'livre'    : '✓ Livré',
            };
            zone.innerHTML = data.map(d => {
                const reste = Math.max(0, d.total_ttc - d.total_encaisse);
                const bg    = statutColors[d.statut_global] || '#f1f5f9';
                return `
                <div class="commande-card mb-1 d-flex justify-content-between align-items-center"
                     style="background:${bg};"
                     onclick="chargerCommande(${d.id_depot})">
                    <div>
                        <span class="fw-bold text-primary" style="font-size:12px;">
                            ${d.code_commande}
                        </span>
                        <span class="text-muted ms-2" style="font-size:11px;">
                            ${statutLabels[d.statut_global] || d.statut_global}
                        </span>
                        <div style="font-size:12px;">${d.nomclient} · ${d.telephone}</div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold" style="font-size:12px;">
                            ${reste > 0
                                ? `<span class="text-danger">${reste.toLocaleString('fr-FR')} FCFA</span>`
                                : '<span class="text-success">Soldé ✓</span>'}
                        </div>
                        <div class="text-muted" style="font-size:10px;">
                            ${d.nb_articles} art.
                        </div>
                    </div>
                </div>`;
            }).join('');
        });
}

// ─────────────────────────────────────────────────────────────
// CHARGER UNE COMMANDE DANS LE TICKET
// ─────────────────────────────────────────────────────────────
function chargerCommande(idDepot) {
    fetch(`${BASE}/pos/api/commande/${idDepot}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { afficherToast('danger', 'Commande introuvable.'); return; }

            depotActif = data.depot;
            produitsPanier = [];

            document.getElementById('ticketVide').classList.add('d-none');
            document.getElementById('ticketData').classList.remove('d-none');
            document.getElementById('produitsTicket').classList.add('d-none');

            document.getElementById('t_client').textContent   = data.depot.nomclient;
            document.getElementById('t_tel').textContent      = data.depot.telephone;
            document.getElementById('t_bon').textContent      = data.depot.code_commande;
            document.getElementById('t_total').textContent    =
                data.depot.total_ttc.toLocaleString('fr-FR') + ' FCFA';
            document.getElementById('t_encaisse').textContent =
                data.depot.total_encaisse.toLocaleString('fr-FR') + ' FCFA';
            document.getElementById('t_reste').textContent    =
                data.depot.reste.toLocaleString('fr-FR') + ' FCFA';

            // Articles
            document.getElementById('t_articles').innerHTML = data.depot.articles.map(a => `
                <div class="d-flex justify-content-between mb-1"
                     style="font-size:11px;color:#94a3b8;">
                    <span>${a.nom_libelle} ${a.options_express
                        ? '<span style="color:#f87171;">⚡</span>' : ''}</span>
                    <span>${(a.prix_applique||0).toLocaleString('fr-FR')} F</span>
                </div>`).join('');

            mettreAJourBoutonEncaisser();

            // Marquer active
            document.querySelectorAll('.commande-card').forEach(c =>
                c.classList.remove('active'));
        });
}

// ─────────────────────────────────────────────────────────────
// PRODUITS BOUTIQUE
// ─────────────────────────────────────────────────────────────
function ajouterProduit(id, nom, prix, stock) {
    const existe = produitsPanier.find(p => p.id === id);
    if (existe) {
        if (existe.qte >= stock) {
            afficherToast('warning', 'Stock insuffisant.');
            return;
        }
        existe.qte++;
    } else {
        produitsPanier.push({ id, nom, prix, stock, qte: 1 });
    }
    afficherProduitsTicket();
    mettreAJourBoutonEncaisser();
}

function afficherProduitsTicket() {
    if (produitsPanier.length === 0) {
        document.getElementById('produitsTicket').classList.add('d-none');
        return;
    }
    document.getElementById('produitsTicket').classList.remove('d-none');
    document.getElementById('listeProduits').innerHTML = produitsPanier.map((p, i) => `
        <div class="d-flex justify-content-between align-items-center mb-1"
             style="font-size:11px;color:#94a3b8;">
            <span>${p.nom} × ${p.qte}</span>
            <div class="d-flex align-items-center gap-2">
                <span>${(p.prix * p.qte).toLocaleString('fr-FR')} F</span>
                <button onclick="retirerProduit(${i})"
                        style="background:rgba(255,255,255,.1);border:none;
                               color:#f87171;border-radius:4px;padding:0 5px;cursor:pointer;">
                    ×
                </button>
            </div>
        </div>`).join('');
}

function retirerProduit(index) {
    produitsPanier.splice(index, 1);
    afficherProduitsTicket();
    mettreAJourBoutonEncaisser();
}

// ─────────────────────────────────────────────────────────────
// MODE PAIEMENT
// ─────────────────────────────────────────────────────────────
function selectionnerMode(mode) {
    modeActif = mode;
    document.querySelectorAll('.mode-btn').forEach(b => {
        b.classList.toggle('active', b.dataset.mode === mode);
    });
    document.getElementById('zoneMixte').classList.toggle('d-none', mode !== 'mixte');
    document.getElementById('zoneRendu').classList.add('d-none');
    calculerRendu();
}

function calculerRendu() {
    if (!depotActif || modeActif !== 'especes') {
        document.getElementById('zoneRendu').classList.add('d-none');
        return;
    }
    // Pour l'instant on affiche le rendu si espèces
    // Le rendu sera calculé côté serveur
}

function calculerMixte() {
    const esp = parseFloat(document.getElementById('mix_especes').value) || 0;
    const mob = parseFloat(document.getElementById('mix_mobile').value)  || 0;
    const car = parseFloat(document.getElementById('mix_carte').value)   || 0;
    const total = esp + mob + car;
    const reste = depotActif ? depotActif.reste : 0;

    const errEl = document.getElementById('mixteErreur');
    if (total !== reste && reste > 0) {
        errEl.textContent = `Total mixte : ${total.toLocaleString('fr-FR')} FCFA (reste : ${reste.toLocaleString('fr-FR')} FCFA)`;
        errEl.classList.remove('d-none');
    } else {
        errEl.classList.add('d-none');
    }
}

// ─────────────────────────────────────────────────────────────
// ENCAISSEMENT
// ─────────────────────────────────────────────────────────────
function mettreAJourBoutonEncaisser() {
    const btn = document.getElementById('btnEncaisser');
    const aCommande  = depotActif && depotActif.reste > 0;
    const aProduits  = produitsPanier.length > 0;
    btn.disabled = !(aCommande || aProduits);
}

async function procederEncaissement() {
    const btn = document.getElementById('btnEncaisser');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>En cours...';

    try {
        // 1. Encaisser la commande si présente
        if (depotActif && depotActif.reste > 0) {
            let body = new URLSearchParams({
                depot_id       : depotActif.id_depot,
                montant        : depotActif.reste,
                mode_paiement  : modeActif,
                [CSRF_TOKEN]   : CSRF_HASH,
            });

            if (modeActif === 'mixte') {
                body.append('montant_especes', document.getElementById('mix_especes').value || 0);
                body.append('montant_mobile',  document.getElementById('mix_mobile').value  || 0);
                body.append('montant_carte',   document.getElementById('mix_carte').value   || 0);
            }

            const res  = await fetch(`${BASE}/pos/encaisser`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body
            });
            const data = await res.json();

            if (!data.success) {
                afficherToast('danger', data.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check me-2"></i>Encaisser';
                return;
            }

            if (data.success && data.id_transaction) {
                imprimerRecu(data.id_transaction);
            }

            if (data.rendu > 0) {
                document.getElementById('zoneRendu').classList.remove('d-none');
                document.getElementById('montantRendu').textContent =
                    data.rendu.toLocaleString('fr-FR') + ' FCFA';
            }

            afficherToast('success', data.message);

            if (data.solde) {
                setTimeout(() => {
                    viderTicket();
                    location.reload();
                }, 2000);
                return;
            }
        }

        // 2. Vendre produits boutique
        for (const prod of produitsPanier) {
            await fetch(`${BASE}/pos/vendre-produit`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new URLSearchParams({
                    produit_id    : prod.id,
                    quantite      : prod.qte,
                    mode_paiement : modeActif,
                    [CSRF_TOKEN]  : CSRF_HASH,
                })
            });
        }

        if (produitsPanier.length > 0) {
            afficherToast('success', '✅ Produits vendus !');
            produitsPanier = [];
            afficherProduitsTicket();
        }

    } catch (err) {
        afficherToast('danger', 'Erreur réseau.');
        console.error(err);
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-check me-2"></i>Encaisser';
    mettreAJourBoutonEncaisser();
}

// ─────────────────────────────────────────────────────────────
// VIDER TICKET
// ─────────────────────────────────────────────────────────────
function viderTicket() {
    depotActif     = null;
    produitsPanier = [];
    document.getElementById('ticketVide').classList.remove('d-none');
    document.getElementById('ticketData').classList.add('d-none');
    document.getElementById('produitsTicket').classList.add('d-none');
    document.getElementById('zoneRendu').classList.add('d-none');
    document.getElementById('btnEncaisser').disabled = true;
    document.getElementById('searchInput').value = '';
    document.getElementById('resultatsRecherche').innerHTML = '';
}

// ─────────────────────────────────────────────────────────────
// TOAST
// ─────────────────────────────────────────────────────────────
function afficherToast(type, msg) {
    const el  = document.getElementById('toastPos');
    const txt = document.getElementById('toastMsg');
    el.className = `position-fixed bottom-0 end-0 m-3 toast align-items-center border-0 bg-${type}`;
    txt.textContent = msg;
    txt.className   = `toast-body fw-semibold ${type === 'success' ? 'text-white' : 'text-white'}`;
    new bootstrap.Toast(el, { delay: 3000 }).show();
}

// ─────────────────────────────────────────────────────────────
// REMBOURSEMENT
// ─────────────────────────────────────────────────────────────
function ouvrirRemboursement() {
    if (!depotActif) {
        afficherToast('warning', 'Sélectionnez d\'abord une commande.');
        return;
    }
    const montant = prompt(
        `Remboursement pour ${depotActif.nomclient}\n` +
        `Montant à rembourser (FCFA) :`,
        ''
    );
    if (!montant || isNaN(montant) || parseFloat(montant) <= 0) return;

    const motif = prompt('Motif du remboursement :', 'Remboursement client');
    if (motif === null) return;

    fetch(`${BASE}/pos/rembourser`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({
            depot_id      : depotActif.id_depot,
            montant       : montant,
            motif         : motif,
            mode_paiement : modeActif,
            [CSRF_TOKEN]  : CSRF_HASH,
        })
    })
    .then(r => r.json())
    .then(data => {
        afficherToast(data.success ? 'success' : 'danger', data.message);
        if (data.success) chargerCommande(depotActif.id_depot);
    });
}

// ─────────────────────────────────────────────────────────────
// IMPRIMER REÇU après encaissement
// ─────────────────────────────────────────────────────────────
function imprimerRecu(idTransaction) {
    window.open(`${BASE}/pos/recu/${idTransaction}?auto=1`, '_blank');
}
</script>

<?= $this->endSection() ?>
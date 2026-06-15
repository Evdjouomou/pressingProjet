<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4" style="max-width:900px;">

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3">
            <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= base_url('production/cycles') ?>"
               class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
            <h4 class="fw-bold mb-0">Nouveau cycle machine</h4>
            <small class="text-muted">
                Scannez ou saisissez les codes-barres des articles à traiter
            </small>
        </div>
    </div>

    <form action="<?= base_url('production/cycles/store') ?>"
          method="POST" id="formCycle">
        <?= csrf_field() ?>

        <div class="row g-4">

            <!-- ════════════════════════════════ -->
            <!-- COLONNE GAUCHE                  -->
            <!-- ════════════════════════════════ -->
            <div class="col-md-5">

                <!-- Machine -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-body">
                        <p class="text-uppercase text-muted fw-semibold mb-3"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-industry me-2"></i>Machine
                        </p>
                        <select name="machine_id" class="form-select" required
                                id="selectMachine">
                            <option value="" disabled selected>
                                Choisir une machine...
                            </option>
                            <?php foreach ($machines as $m): ?>
                            <option value="<?= $m['id_machine'] ?>"
                                    data-capacite="<?= $m['capacite_max'] ?>">
                                <?= esc($m['nom']) ?>
                                (max <?= $m['capacite_max'] ?> articles)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="capaciteInfo"
                             class="mt-2 d-none rounded-2 p-2 text-center"
                             style="background:#eff6ff;border:1px solid #bfdbfe;
                                    font-size:12px;color:#1d4ed8;">
                        </div>
                    </div>
                </div>

                <!-- Consommables -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-body">
                        <p class="text-uppercase text-muted fw-semibold mb-3"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-flask me-2"></i>Consommables utilisés
                        </p>

                        <div id="lignesConsommables">
                            <div class="ligne-conso row g-2 mb-2 align-items-end">
                                <div class="col-7">
                                    <label class="form-label fw-semibold small">Produit</label>
                                    <select name="produits[]" class="form-select form-select-sm" required>
                                        <option value="" disabled selected>-- Choisir un produit --</option>
                                        <?php foreach ($consommables as $c): ?>
                                        <option value="<?= $c['id_produit'] ?>">
                                            <?= esc($c['nom']) ?> (<?= $c['stock'] ?> <?= esc($c['unite']) ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <label class="form-label fw-semibold small">Qté utilisée</label>
                                    <input type="number" name="quantites[]" class="form-control form-control-sm"
                                        placeholder="0" min="0.1" step="0.1" required>
                                </div>
                                <div class="col-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-2"
                                            onclick="supprimerConsommable(this)" style="height:31px;width:31px;padding:0;">
                                        <i class="fas fa-times" style="font-size:11px;"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="button"
                                class="btn btn-outline-primary btn-sm rounded-2 mt-1"
                                onclick="ajouterConsommable()">
                            <i class="fas fa-plus me-1"></i>Ajouter un consommable
                        </button>
                    </div>
                </div>

                <!-- Observations -->
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <p class="text-uppercase text-muted fw-semibold mb-3"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-sticky-note me-2"></i>Observations
                        </p>
                        <textarea name="observations" class="form-control" rows="3"
                                  placeholder="Remarques sur ce cycle..."></textarea>
                    </div>
                </div>

            </div>

            <!-- ════════════════════════════════ -->
            <!-- COLONNE DROITE : Articles       -->
            <!-- ════════════════════════════════ -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <p class="text-uppercase text-muted fw-semibold mb-3"
                        style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-tshirt me-2"></i>Articles à traiter
                            <span id="compteurArticles"
                                class="ms-2 badge bg-primary rounded-pill">0</span>
                        </p>

                        <div class="mb-2">
                            <label class="form-label fw-semibold small">N° Ticket article</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-barcode text-muted"></i>
                                </span>
                                <input type="text"
                                    id="inputScan"
                                    class="form-control shadow-none"
                                    placeholder="BC-XXXXXXXXXX-0"
                                    autocomplete="off"
                                    style="font-family:monospace;letter-spacing:1px;">
                                <button type="button"
                                        class="btn btn-primary"
                                        onclick="scannerArticle()">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <div id="feedbackScan" class="d-none mb-3 rounded-2 p-2"
                            style="font-size:12px;"></div>

                        <div id="listeArticles" class="mt-3">
                            <div id="videMsg" class="text-center py-4 text-muted border border-dashed rounded-3">
                                <i class="fas fa-qrcode fa-2x mb-2 d-block text-secondary"></i>
                                Aucun article scanné pour ce cycle.
                            </div>
                        </div>

                        <textarea name="barcodes" id="barcodesCaches" class="d-none"></textarea>

                    </div>
                </div>
            </div>

        </div>

        <!-- Bouton submit -->
        <div class="text-end mt-4">
            <a href="<?= base_url('production/cycles') ?>"
               class="btn btn-light rounded-2 px-4 me-2">Annuler</a>
            <button type="submit"
                    class="btn btn-success btn-lg rounded-2 px-5"
                    id="btnSubmit" disabled>
                <i class="fas fa-play me-2"></i>Lancer le cycle
            </button>
        </div>

    </form>
</div>

<script>
const BASE          = '<?= base_url() ?>';
let articlesScannés = {};
let capaciteMax     = 0;

// ── Machine : afficher capacité ──────────────────────
document.getElementById('selectMachine')
    .addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        capaciteMax = parseInt(opt.dataset.capacite) || 0;
        const info = document.getElementById('capaciteInfo');
        info.classList.remove('d-none');
        info.innerHTML = `<i class="fas fa-info-circle me-1"></i>
            Capacité max : <strong>${capaciteMax} articles</strong>`;
        verifierBouton();
    });

// ── Scan au clavier ──────────────────────────────────
document.getElementById('inputScan')
    .addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            scannerArticle();
        }
    });

// ── Focus automatique sur le champ scan ──────────────
document.getElementById('inputScan').focus();

// ── Scanner un article ───────────────────────────────
function scannerArticle() {
    const raw  = document.getElementById('inputScan').value.trim();
    const code = raw.toUpperCase(); // normaliser

    if (!code) return;

    // Vérifier format BC-
    if (!code.startsWith('BC-')) {
        afficherFeedback('warning',
            '⚠️ Format invalide. Le code doit commencer par <strong>BC-</strong> '
            + '(ex: BC-1780356482-0).'
        );
        document.getElementById('inputScan').select();
        return;
    }

    if (articlesScannés[code]) {
        afficherFeedback('warning',
            '⚠️ Cet article est déjà dans la liste : <strong>' + code + '</strong>'
        );
        document.getElementById('inputScan').value = '';
        return;
    }

    if (capaciteMax > 0 && Object.keys(articlesScannés).length >= capaciteMax) {
        afficherFeedback('danger',
            '🚫 Capacité max atteinte (<strong>'
            + capaciteMax + ' articles</strong>). '
            + 'Choisissez une autre machine ou lancez ce cycle.'
        );
        return;
    }

    // Appel API
    fetch(`${BASE}/production/api/article/${encodeURIComponent(code)}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                afficherFeedback('danger',
                    '❌ <strong>' + code + '</strong> introuvable. '
                    + 'Vérifiez le numéro de ticket sur l\'étiquette de l\'article.'
                );
                return;
            }
            const art = data.article;
            articlesScannés[code] = art;
            afficherFeedback('success',
                '✅ <strong>' + art.nom_libelle + '</strong> — '
                + art.nomclient + ' ('
                + art.code_commande + ')'
            );
            ajouterCarteArticle(art, code);
            mettreAJourBarcodes();
            document.getElementById('inputScan').value = '';
            document.getElementById('inputScan').focus();
        })
        .catch(() => {
            afficherFeedback('danger', '❌ Erreur réseau. Réessayez.');
        });
}

// ── Carte article ─────────────────────────────────────
function ajouterCarteArticle(art, code) {
    document.getElementById('videMsg').classList.add('d-none');

    const div     = document.createElement('div');
    div.id        = 'art-' + code.replace(/[^a-zA-Z0-9]/g, '_');
    div.className = 'border rounded-2 p-3 mb-2 d-flex justify-content-between align-items-start';
    div.style.fontSize = '12px';
    div.innerHTML = `
        <div>
            <div class="fw-semibold" style="font-size:13px;">
                ${art.nom_libelle}
                ${art.options_express == 1
                    ? '<span class="badge bg-danger ms-1" style="font-size:10px;">🚀 Express</span>'
                    : ''}
            </div>
            <div class="text-muted">
                ${art.nomclient}
                · <span class="text-primary">${art.code_commande}</span>
            </div>
            <div style="font-family:monospace;font-size:11px;color:#6b7280;
                        letter-spacing:1px;margin-top:2px;">
                🏷️ ${code}
            </div>
            ${art.etape_libelle
                ? `<div class="text-muted" style="font-size:10px;">
                       Étape actuelle : ${art.etape_libelle}
                   </div>`
                : ''}
            ${art.observations
                ? `<div style="color:#f59e0b;font-size:11px;">
                       ⚠ ${art.observations}
                   </div>`
                : ''}
        </div>
        <button type="button"
                class="btn btn-sm btn-outline-danger rounded-2 ms-2"
                style="height:28px;width:28px;padding:0;flex-shrink:0;"
                onclick="retirerArticle('${code}')">
            <i class="fas fa-times" style="font-size:11px;"></i>
        </button>`;

    document.getElementById('listeArticles').appendChild(div);
    document.getElementById('compteurArticles')
        .textContent = Object.keys(articlesScannés).length;
    verifierBouton();
}

// ── Retirer un article ────────────────────────────────
function retirerArticle(code) {
    delete articlesScannés[code];
    const id = 'art-' + code.replace(/[^a-zA-Z0-9]/g, '_');
    const el = document.getElementById(id);
    if (el) el.remove();

    if (Object.keys(articlesScannés).length === 0) {
        document.getElementById('videMsg').classList.remove('d-none');
    }
    document.getElementById('compteurArticles')
        .textContent = Object.keys(articlesScannés).length;
    mettreAJourBarcodes();
    verifierBouton();
}

// ── Mettre à jour textarea cachée ────────────────────
function mettreAJourBarcodes() {
    document.getElementById('barcodesCaches').value =
        Object.keys(articlesScannés).join("\n");
}

// ── Activer bouton submit ─────────────────────────────
function verifierBouton() {
    const nb  = Object.keys(articlesScannés).length;
    const btn = document.getElementById('btnSubmit');
    btn.disabled = nb === 0;
    if (nb > 0) {
        btn.innerHTML = `<i class="fas fa-play me-2"></i>Lancer le cycle (${nb} article(s))`;
    } else {
        btn.innerHTML = `<i class="fas fa-play me-2"></i>Lancer le cycle`;
    }
}

// ── Feedback ──────────────────────────────────────────
function afficherFeedback(type, msg) {
    const fb = document.getElementById('feedbackScan');
    fb.className = `mb-3 rounded-2 p-2 alert alert-${type}`;
    fb.style.fontSize = '12px';
    fb.innerHTML = msg;
    fb.classList.remove('d-none');
    if (type !== 'danger') {
        setTimeout(() => fb.classList.add('d-none'), 4000);
    }
}

// ── Consommables ──────────────────────────────────────
const ligneConsoHtml = document.querySelector('.ligne-conso').outerHTML;

function ajouterConsommable() {
    const container = document.getElementById('lignesConsommables');
    const div       = document.createElement('div');
    div.innerHTML   = ligneConsoHtml;
    
    // Reset les valeurs du clone mais préserve les attributs 'required'
    div.querySelectorAll('select, input').forEach(el => {
        if (el.tagName === 'SELECT') {
            el.selectedIndex = 0;
            el.required = true; // S'assure que c'est requis
        } else {
            el.value = '';
            el.required = true; // S'assure que c'est requis
        }
    });
    container.appendChild(div.firstElementChild);
}

function supprimerConsommable(btn) {
    const lignes = document.querySelectorAll('.ligne-conso');
    if (lignes.length <= 1) {
        // Reset plutôt que supprimer si c'est la dernière ligne restante
        btn.closest('.ligne-conso')
            .querySelectorAll('select, input').forEach(el => {
                if (el.tagName === 'SELECT') el.selectedIndex = 0;
                else el.value = '';
            });
        return;
    }
    btn.closest('.ligne-conso').remove();
}
</script>

<?= $this->endSection() ?>
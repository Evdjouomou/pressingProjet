<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container py-4" style="max-width:600px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="fas fa-qrcode me-2 text-primary"></i>Scanner un article</h4>
        <a href="<?= base_url('production') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Kanban
        </a>
    </div>

    <!-- Zone de scan -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">

            <!-- Tabs : caméra / manuel -->
            <ul class="nav nav-pills mb-3" id="scanTabs">
                <li class="nav-item">
                    <button class="nav-link active" id="tab_camera" onclick="switchTab('camera')">
                        <i class="fas fa-camera me-1"></i>Caméra
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab_manuel" onclick="switchTab('manuel')">
                        <i class="fas fa-keyboard me-1"></i>Manuel / Scanner USB
                    </button>
                </li>
            </ul>

            <!-- Caméra QR -->
            <div id="zone_camera">
                <div id="reader" style="width:100%;border-radius:10px;overflow:hidden;"></div>
                <p class="text-muted small text-center mt-2">Pointez la caméra vers le QR code ou code-barres</p>
            </div>

            <!-- Saisie manuelle -->
            <div id="zone_manuel" class="d-none">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-barcode"></i></span>
                    <input type="text"
                           id="input_barcode"
                           class="form-control form-control-lg"
                           placeholder="Scanner ou saisir le code..."
                           autocomplete="off"
                           autofocus>
                    <button class="btn btn-primary" onclick="traiterBarcode(document.getElementById('input_barcode').value)">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
                <p class="text-muted small mt-2">Appuyez sur Entrée ou cliquez sur la flèche.</p>
            </div>

        </div>
    </div>

    <!-- Résultat scan -->
    <div id="zone_resultat" class="d-none">

        <!-- Infos article -->
        <div class="card border-0 shadow-sm rounded-3 mb-3" id="card_article">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold mb-1" id="res_nom_article">—</h5>
                        <span class="text-muted small" id="res_barcode"></span>
                    </div>
                    <span id="res_express_badge"></span>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:8px;padding:10px;">
                            <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">Client</div>
                            <div class="fw-semibold" id="res_client">—</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:8px;padding:10px;">
                            <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">Bon</div>
                            <div class="fw-semibold text-primary" id="res_bon">—</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:8px;padding:10px;">
                            <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">Prestation</div>
                            <div class="fw-semibold" id="res_prestation">—</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:8px;padding:10px;">
                            <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">Retrait prévu</div>
                            <div class="fw-semibold" id="res_retrait">—</div>
                        </div>
                    </div>
                </div>

                <!-- Étape courante -->
                <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded-2" id="etape_courante_zone"
                     style="background:#f0fdf4;">
                    <i class="fas fa-map-marker-alt text-success"></i>
                    <div>
                        <div style="font-size:10px;color:#6b7280;text-transform:uppercase;">Étape actuelle</div>
                        <div class="fw-semibold" id="res_etape_courante">—</div>
                    </div>
                </div>

                <!-- Bouton avancer -->
                <button id="btn_avancer"
                        class="btn btn-success w-100 btn-lg rounded-2"
                        onclick="avancerArticle()">
                    <i class="fas fa-arrow-right me-2"></i>
                    Passer à : <strong id="res_etape_suivante">—</strong>
                </button>
            </div>
        </div>

        <!-- Message feedback -->
        <div id="zone_feedback" class="d-none rounded-3 p-3 mb-3"></div>

    </div>

</div>

<!-- html5-qrcode -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>

<script>
    let scanner   = null;
    let barcodeActif = null;

    const BASE = '<?= base_url() ?>';

    // ── Switch tabs ──────────────────────────────────────
    function switchTab(tab) {
        document.getElementById('tab_camera').classList.toggle('active', tab === 'camera');
        document.getElementById('tab_manuel').classList.toggle('active', tab === 'manuel');
        document.getElementById('zone_camera').classList.toggle('d-none', tab !== 'camera');
        document.getElementById('zone_manuel').classList.toggle('d-none', tab !== 'manuel');

        if (tab === 'camera') {
            demarrerCamera();
        } else {
            arreterCamera();
            document.getElementById('input_barcode').focus();
        }
    }

    // ── Caméra QR ────────────────────────────────────────
    function demarrerCamera() {
        if (scanner) return;
        scanner = new Html5Qrcode("reader");
        scanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 150 } },
            (decodedText) => {
                arreterCamera();
                traiterBarcode(decodedText);
            },
            () => {}
        ).catch(() => {
            document.getElementById('zone_camera').innerHTML =
                '<div class="alert alert-warning">Caméra non disponible. Utilisez la saisie manuelle.</div>';
        });
    }

    function arreterCamera() {
        if (scanner) {
            scanner.stop().catch(() => {});
            scanner = null;
        }
    }

    // Démarrer caméra au chargement
    demarrerCamera();

    // ── Saisie manuelle (Entrée) ─────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('input_barcode');
        if (input) {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') traiterBarcode(this.value);
            });
        }
    });

    // ── Traitement du barcode scané ──────────────────────
    function traiterBarcode(code) {
        code = (code || '').trim();
        if (!code) return;

        barcodeActif = code;
        document.getElementById('zone_resultat').classList.remove('d-none');
        document.getElementById('zone_feedback').classList.add('d-none');

        fetch(`${BASE}production/api/kanban`) // on réutilise articleDetail
            .then(() => {}) // juste pour ne pas bloquer

        // On charge le détail via barcode → avancer endpoint fait la vérif
        afficherArticleParCode(code);
    }

    function afficherArticleParCode(barcode) {
        // Appel à avancer en mode "preview" (GET simulé via POST avec flag)
        fetch(`${BASE}production/avancer`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest' },
            body: new URLSearchParams({
                barcode:    barcode,
                preview:    '1',
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            })
        })
        .then(r => r.json())
        .then(data => {
            if (!data.article) {
                afficherFeedback('danger', data.message || 'Article introuvable.');
                return;
            }
            const art = data.article;
            document.getElementById('res_nom_article').textContent   = art.nom_libelle || '—';
            document.getElementById('res_barcode').textContent       = art.barcode_unique || '';
            document.getElementById('res_client').textContent        = art.nomclient || '—';
            document.getElementById('res_bon').textContent           = art.code_commande || '—';
            document.getElementById('res_prestation').textContent    = art.type_prestation || '—';
            document.getElementById('res_retrait').textContent       = art.date_livraison_prevue
                ? new Date(art.date_livraison_prevue).toLocaleDateString('fr-FR') : '—';
            document.getElementById('res_etape_courante').textContent = art.etape_libelle || '—';
            document.getElementById('res_express_badge').innerHTML    = parseInt(art.est_express)
                ? '<span class="badge bg-danger">⚡ Express</span>' : '';

            if (data.etape_suivante) {
                document.getElementById('res_etape_suivante').textContent = data.etape_suivante.libelle;
                document.getElementById('btn_avancer').disabled = false;
            } else {
                document.getElementById('res_etape_suivante').textContent = 'Dernière étape';
                document.getElementById('btn_avancer').disabled = true;
            }

            document.getElementById('etape_courante_zone').style.background = art.etape_couleur + '22';
        });
    }

    // ── Avancer l'article ────────────────────────────────
    function avancerArticle() {
        if (!barcodeActif) return;

        const btn = document.getElementById('btn_avancer');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>En cours...';

        fetch(`${BASE}production/avancer`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest' },
            body: new URLSearchParams({
                barcode: barcodeActif,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                afficherFeedback('success', data.message);
                // Recharger l'affichage avec le nouvel état
                setTimeout(() => afficherArticleParCode(barcodeActif), 600);
            } else {
                afficherFeedback('danger', data.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-arrow-right me-2"></i>Réessayer';
            }
        });
    }

    function afficherFeedback(type, msg) {
        const zone = document.getElementById('zone_feedback');
        zone.className = `rounded-3 p-3 mb-3 alert alert-${type}`;
        zone.innerHTML = msg;
        zone.classList.remove('d-none');
    }

    function traiterReponse(data) {
        const feedback = document.getElementById('feedback_scan');
        const input    = document.getElementById('input_barcode');

        if (!feedback) return;

        if (data.success) {
            // Succès — afficher en vert
            feedback.className = 'alert alert-success rounded-3 shadow-sm mt-3';
            feedback.innerHTML = `
                <div class="d-flex align-items-center gap-3">
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                    <div>
                        <div class="fw-bold">${data.message}</div>
                        <div class="text-muted small">→ ${data.etape_suivante}</div>
                        ${data.est_pret
                            ? `<div class="mt-1 text-success fw-semibold">
                                <i class="fas fa-bell me-1"></i>
                                Le client a été notifié — dépôt transmis à la caisse.
                            </div>`
                            : ''}
                    </div>
                </div>`;

        } else if (data.bloque) {
            // Bloqué — article déjà prêt, afficher en orange
            feedback.className = 'alert alert-warning rounded-3 shadow-sm mt-3';
            feedback.innerHTML = `
                <div class="d-flex align-items-center gap-3">
                    <i class="fas fa-hand-paper fa-2x text-warning"></i>
                    <div>
                        <div class="fw-bold">${data.message}</div>
                        <div class="mt-2">
                            <a href="${BASE_URL}depot/prets"
                            class="btn btn-sm btn-warning rounded-2 px-3">
                                <i class="fas fa-cash-register me-1"></i>
                                Aller à la caisse →
                            </a>
                        </div>
                    </div>
                </div>`;

        } else {
            // Erreur — afficher en rouge
            feedback.className = 'alert alert-danger rounded-3 shadow-sm mt-3';
            feedback.innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-exclamation-circle text-danger"></i>
                    <span>${data.message}</span>
                </div>`;
        }

        feedback.classList.remove('d-none');

        // Vider et refocaliser le champ
        if (input) {
            input.value = '';
            input.focus();
        }

        // Masquer le feedback après 5 secondes (sauf si bloqué)
        if (data.success && !data.est_pret) {
            setTimeout(() => feedback.classList.add('d-none'), 5000);
        }
    }

</script>

<?= $this->endSection() ?>
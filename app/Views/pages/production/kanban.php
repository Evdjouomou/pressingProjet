<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<style>
    .kanban-col {
        padding: 5px;
        background: #f8fafc;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .kanban-col-header {
        background: #fff;
        padding: 12px 14px 10px;
        border-bottom: 1px solid #f1f5f9;
    }
    .kanban-col-body {
        padding: 10px;
        max-height: 70vh;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .kanban-empty {
        text-align: center;
        padding: 24px 0;
    }
    .carte-kanban {
        background: #fff;
        border-radius: 10px;
        padding: 12px;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: box-shadow .15s, transform .1s;
    }
    .carte-kanban:hover  { box-shadow: 0 4px 12px rgba(0,0,0,.08); transform: translateY(-1px); }
    .carte-retard        { border-left: 3px solid #f59e0b !important; }
    .carte-express       { border-left: 3px solid #dc2626 !important; }
    .carte-retard.carte-express { border-left: 3px solid #dc2626 !important; }
</style>

<div class="container-fluid py-1" style="overflow-x: auto;">

    <!-- En-tête + stats -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0">Production</h4>
            <small class="text-muted">Mise à jour en temps réel</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <!-- Switch vue -->
            <div class="btn-group" id="switchVue">
                <button class="btn btn-primary btn-sm active" data-vue="depots">
                    <i class="fas fa-clipboard-list me-1"></i>Dépôts
                </button>
                <button class="btn btn-outline-primary btn-sm" data-vue="articles">
                    <i class="fas fa-tshirt me-1"></i>Articles
                </button>
            </div>
            <a href="<?= base_url('production/scan') ?>"
               class="btn btn-success btn-sm">
                <i class="fas fa-qrcode me-1"></i>Scanner
            </a>
            <a href="<?= base_url('production/alertes') ?>"
               class="btn btn-outline-danger btn-sm" id="btn_alertes">
                <i class="fas fa-bell me-1"></i>Alertes
                <span class="badge bg-danger ms-1 d-none" id="badge_alertes">0</span>
            </a>
        </div>
    </div>

    <!-- Barre de stats -->
    <div class="row g-3 mb-4" id="zone_stats">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4 text-primary" id="stat_en_cours">—</div>
                <div class="text-muted small">En production</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4 text-success" id="stat_prets">—</div>
                <div class="text-muted small">Prêts à retirer</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4 text-warning" id="stat_express">—</div>
                <div class="text-muted small">Express</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-4 text-danger" id="stat_retard">—</div>
                <div class="text-muted small">En retard</div>
            </div>
        </div>
    </div>

    <!-- Kanban -->
    <div id="kanban_board" style="display:flex; gap:14px; overflow-x:auto; padding-bottom:16px; align-items:flex-start;">
        <!-- Colonnes générées en JS -->
        <div class="text-center text-muted py-5 w-100">
            <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block"></i>Chargement...
        </div>
    </div>

</div>

<script>
    let vueActive = 'depots';

    // ── Rendu d'une carte dépôt ──────────────────────────
    function carteDepot(item, etape) {
        const retard  = item.en_retard;
        const express = parseInt(item.nb_express) > 0;
        const retrait = item.date_livraison_prevue
            ? new Date(item.date_livraison_prevue).toLocaleDateString('fr-FR')
            : '—';
        const retraitPasse = item.date_livraison_prevue && new Date(item.date_livraison_prevue) < new Date();

        return `
        <div class="carte-kanban ${retard ? 'carte-retard' : ''} ${express ? 'carte-express' : ''}"
            onclick="voirDepot(${item.id_depot})">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="fw-semibold text-primary" style="font-size:13px;">${item.code_commande}</span>
                <div class="d-flex gap-1">
                    ${express ? '<span class="badge bg-danger" style="font-size:10px;">⚡ Express</span>' : ''}
                    ${retard  ? '<span class="badge bg-warning text-dark" style="font-size:10px;">⚠ Retard</span>' : ''}
                </div>
            </div>
            <div class="fw-semibold text-dark mb-1" style="font-size:13px;">${item.nomclient}</div>
            <div class="text-muted" style="font-size:11px; margin-bottom:6px;">${item.telephone}</div>
            <div class="d-flex justify-content-between align-items-center">
                <span style="background:#eff6ff;color:#1d4ed8;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;">
                    ${item.nb_articles} article(s)
                </span>
                <span style="font-size:11px; color:${retraitPasse ? '#dc2626' : '#6b7280'};">
                    ${retraitPasse ? '⚠' : '📅'} ${retrait}
                </span>
            </div>
        </div>`;
    }

    // Rendu d'une carte article 
    function carteArticle(item, etape) {
        const retard  = item.en_retard;
        const express = parseInt(item.est_express) === 1;

        return `
        <div class="carte-kanban ${retard ? 'carte-retard' : ''} ${express ? 'carte-express' : ''}"
            onclick="voirArticle(${item.id_article_depose})">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <span class="fw-semibold text-dark" style="font-size:13px;">${item.nom_libelle}</span>
                ${express ? '<span class="badge bg-danger" style="font-size:10px;">⚡</span>' : ''}
            </div>
            <div class="text-muted" style="font-size:11px; margin-bottom:4px;">${item.designation_libre || '—'}</div>
            <div style="font-size:11px; color:#3b82f6; font-weight:600;">${item.code_commande}</div>
            <div style="font-size:11px; color:#6b7280; margin-top:2px;">${item.nomclient}</div>
            <div style="font-size:11px; color:#6b7280; margin-top:4px; font-family:monospace;">${item.barcode_unique}</div>
            ${item.observations ? `<div style="font-size:11px;color:#f59e0b;margin-top:4px;">⚠ ${item.observations}</div>` : ''}
            ${retard ? '<div style="font-size:11px;color:#dc2626;margin-top:4px;font-weight:600;">⏱ Dépassement délai</div>' : ''}
        </div>`;
    }

    // ── Chargement du Kanban ─────────────────────────────
    function chargerKanban() {
        fetch(`<?= base_url('production/api/kanban') ?>?vue=${vueActive}`)
            .then(r => r.json())
            .then(colonnes => {
                const board = document.getElementById('kanban_board');
                board.innerHTML = '';

                if (colonnes.length === 0) {
                    board.innerHTML = '<div class="text-center text-muted py-5 w-100">Aucune donnée.</div>';
                    return;
                }

                colonnes.forEach(col => {
                    const etape = col.etape;
                    const div   = document.createElement('div');
                    div.className = 'kanban-col';
                    div.innerHTML = `
                        <div class="kanban-col-header" style="border-top:3px solid ${etape.couleur};">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold" style="font-size:13px;">
                                    <i class="fas ${etape.icone} me-1" style="color:${etape.couleur};"></i>
                                    ${etape.libelle}
                                </span>
                                <span class="badge rounded-pill"
                                    style="background:${etape.couleur};color:#fff;font-size:11px;">
                                    ${col.count}
                                </span>
                            </div>
                            ${etape.duree_prevue_h > 0
                                ? `<div style="font-size:10px;color:#9ca3af;margin-top:2px;">Délai : ${etape.duree_prevue_h}h</div>`
                                : ''}
                        </div>
                        <div class="kanban-col-body">
                            ${col.items.length === 0
                                ? `<div class="kanban-empty">
                                    <i class="fas fa-check-circle" style="color:${etape.couleur};opacity:.3;font-size:22px;"></i>
                                    <div style="font-size:11px;color:#d1d5db;margin-top:4px;">Vide</div>
                                </div>`
                                : col.items.map(item =>
                                    vueActive === 'articles'
                                        ? carteArticle(item, etape)
                                        : carteDepot(item, etape)
                                ).join('')
                            }
                        </div>`;
                    board.appendChild(div);
                });
            });
    }

    // ── Stats ────────────────────────────────────────────
    function chargerStats() {
        fetch('<?= base_url('production/api/stats') ?>')
            .then(r => r.json())
            .then(s => {
                document.getElementById('stat_en_cours').textContent = s.total_en_cours ?? '0';
                document.getElementById('stat_prets').textContent    = s.prets ?? '0';
                document.getElementById('stat_express').textContent  = s.total_express ?? '0';
                document.getElementById('stat_retard').textContent   = s.en_retard ?? '0';

                const badge = document.getElementById('badge_alertes');
                if (parseInt(s.en_retard) > 0) {
                    badge.textContent = s.en_retard;
                    badge.classList.remove('d-none');
                } else {
                    badge.classList.add('d-none');
                }
            });
    }

    // ── Switch vue ───────────────────────────────────────
    document.querySelectorAll('#switchVue button').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#switchVue button').forEach(b => {
                b.classList.remove('active', 'btn-primary');
                b.classList.add('btn-outline-primary');
            });
            this.classList.add('active', 'btn-primary');
            this.classList.remove('btn-outline-primary');
            vueActive = this.dataset.vue;
            chargerKanban();
        });
    });

    // ── Navigation ───────────────────────────────────────
    function voirDepot(id)   { window.location.href = '<?= base_url('depot/detail/') ?>' + id; }
    function voirArticle(id) { window.location.href = '<?= base_url('depot/detail/') ?>' + id; }

    // ── Init + polling toutes les 30s ────────────────────
    chargerKanban();
    chargerStats();
    setInterval(() => { chargerKanban(); chargerStats(); }, 30000);
</script>

<?= $this->endSection() ?>
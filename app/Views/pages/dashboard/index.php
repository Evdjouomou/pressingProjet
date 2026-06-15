<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0">Tableau de bord</h4>
            <small class="text-muted" id="lastUpdate">Chargement...</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('rapports') ?>" class="btn btn-outline-primary rounded-2 px-3">
                <i class="fas fa-chart-bar me-2"></i>Rapports
            </a>
            <button onclick="chargerKpis()" class="btn btn-outline-secondary rounded-2 px-3">
                <i class="fas fa-sync me-2"></i>Actualiser
            </button>
        </div>
    </div>

    <!-- ── KPIs CA ── -->
    <div class="row g-3 mb-4" id="kpiCA">
        <?php
        $kpis = [
            ['id' => 'caJour',    'label' => 'CA Aujourd\'hui', 'icon' => 'fa-sun',      'color' => '#1d4ed8', 'bg' => '#eff6ff'],
            ['id' => 'caSemaine', 'label' => 'CA Semaine',      'icon' => 'fa-calendar-week','color' => '#7e22ce','bg' => '#fdf4ff'],
            ['id' => 'caMois',    'label' => 'CA Mois',         'icon' => 'fa-calendar',  'color' => '#166534', 'bg' => '#f0fdf4'],
        ];
        foreach ($kpis as $k): ?>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                             style="width:42px;height:42px;background:<?= $k['bg'] ?>;">
                            <i class="fas <?= $k['icon'] ?>" style="color:<?= $k['color'] ?>;"></i>
                        </div>
                        <span id="evo_<?= $k['id'] ?>" class="badge rounded-pill"
                              style="font-size:11px;display:none;"></span>
                    </div>
                    <div class="fw-bold" style="font-size:22px;color:<?= $k['color'] ?>;"
                         id="val_<?= $k['id'] ?>">—</div>
                    <div class="text-muted" style="font-size:12px;"><?= $k['label'] ?></div>
                    <div class="text-muted" style="font-size:11px;" id="cmp_<?= $k['id'] ?>"></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── KPIs Opérationnels ── -->
    <div class="row g-3 mb-4">
        <?php
        $ops = [
            ['id' => 'depotsJour', 'label' => 'Dépôts aujourd\'hui', 'icon' => 'fa-inbox',        'color' => '#0e7490', 'bg' => '#ecfeff'],
            ['id' => 'enCours',    'label' => 'En production',        'icon' => 'fa-industry',      'color' => '#92400e', 'bg' => '#fef3c7'],
            ['id' => 'prets',      'label' => 'Prêts à retirer',      'icon' => 'fa-check-circle',  'color' => '#166534', 'bg' => '#dcfce7'],
            ['id' => 'enRetard',   'label' => 'En retard',            'icon' => 'fa-clock',         'color' => '#991b1b', 'bg' => '#fee2e2'],
            ['id' => 'stockAlerte','label' => 'Alertes stock',        'icon' => 'fa-exclamation-triangle','color' => '#c2410c','bg' => '#fff7ed'],
            ['id' => 'incidents',  'label' => 'Incidents ouverts',    'icon' => 'fa-bug',           'color' => '#7e22ce', 'bg' => '#fdf4ff'],
        ];
        foreach ($ops as $op): ?>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3 px-2">
                <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center"
                     style="width:36px;height:36px;background:<?= $op['bg'] ?>;">
                    <i class="fas <?= $op['icon'] ?>" style="color:<?= $op['color'] ?>;font-size:14px;"></i>
                </div>
                <div class="fw-bold fs-4" style="color:<?= $op['color'] ?>;"
                     id="val_<?= $op['id'] ?>">—</div>
                <div class="text-muted" style="font-size:10px;"><?= $op['label'] ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Stats par établissement (admin central uniquement) -->
    <div id="zone_stats_shops" class="d-none mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 text-muted" style="font-size:11px;
                text-transform:uppercase;letter-spacing:.5px;">
                <i class="fas fa-store me-2"></i>Vue par établissement
            </h6>
        </div>
        <div class="row g-3" id="cartes_shops"></div>
    </div>

    <div class="row g-4">

        <!-- Graphique CA 30 jours -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p class="text-uppercase text-muted fw-semibold mb-0"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-chart-line me-2"></i>CA des 30 derniers jours
                        </p>
                        <div class="d-flex gap-2">
                            <button class="btn btn-xs btn-outline-secondary btn-sm"
                                    onclick="changerGraphique('ca_30j')" id="btn_ca30">CA 30j</button>
                            <button class="btn btn-xs btn-outline-secondary btn-sm"
                                    onclick="changerGraphique('depots_7j')" id="btn_dep7">Dépôts 7j</button>
                            <button class="btn btn-xs btn-outline-secondary btn-sm"
                                    onclick="changerGraphique('modes_paiement')" id="btn_modes">Paiements</button>
                        </div>
                    </div>
                    <canvas id="graphPrincipal" height="90"></canvas>
                </div>
            </div>
        </div>

        <!-- Top prestations + clients -->
        <div class="col-md-4">

            <!-- Top prestations -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom">
                        <p class="text-uppercase text-muted fw-semibold mb-0"
                           style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-trophy me-2"></i>Top prestations (30j)
                        </p>
                    </div>
                    <div id="topPrestations" class="p-3">
                        <div class="text-center text-muted py-3 small">Chargement...</div>
                    </div>
                </div>
            </div>

            <!-- Clients -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <p class="text-uppercase text-muted fw-semibold mb-3"
                       style="font-size:11px;letter-spacing:.5px;">
                        <i class="fas fa-users me-2"></i>Clients (30j)
                    </p>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Clients actifs</span>
                        <span class="fw-bold" id="val_clientsActifs">—</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Taux de retour</span>
                        <span class="fw-bold text-success" id="val_tauxRetour">—</span>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1" style="font-size:11px;">
                            <span class="text-muted">Fidélité</span>
                            <span id="val_tauxRetourPct" class="text-muted">—</span>
                        </div>
                        <div class="progress" style="height:6px;border-radius:20px;">
                            <div class="progress-bar bg-success" id="barRetour"
                                 style="width:0%;border-radius:20px;"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const BASE = '<?= base_url() ?>';
let chartPrincipal = null;
let typeGraphActif = 'ca_30j';

// ── Formater FCFA ────────────────────────────────────
function fcfa(n) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA';
}

// ── Évolution badge ──────────────────────────────────
function badgeEvo(val) {
    if (val === null || val === undefined) return '';
    const positif = val >= 0;
    return `<span style="background:${positif?'#dcfce7':'#fee2e2'};color:${positif?'#166534':'#991b1b'};
                          padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;">
                ${positif?'↑':'↓'} ${Math.abs(val)}%
            </span>`;
}

// ── Charger KPIs ─────────────────────────────────────
function chargerKpis() {
    fetch(`${BASE}dashboard/api/kpis`)
        .then(r => r.json())
        .then(d => {
            // CA
            document.getElementById('val_caJour').textContent    = fcfa(d.ca.jour);
            document.getElementById('val_caSemaine').textContent = fcfa(d.ca.semaine);
            document.getElementById('val_caMois').textContent    = fcfa(d.ca.mois);

            document.getElementById('evo_caJour').outerHTML    = badgeEvo(d.ca.evolution_jour)    || '';
            document.getElementById('evo_caSemaine').outerHTML = badgeEvo(d.ca.evolution_semaine) || '';
            document.getElementById('evo_caMois').outerHTML    = badgeEvo(d.ca.evolution_mois)    || '';

            document.getElementById('cmp_caJour').textContent    = 'Hier : ' + fcfa(d.ca.hier);
            document.getElementById('cmp_caSemaine').textContent = 'Sem. préc. : ' + fcfa(d.ca.semaine_prec);
            document.getElementById('cmp_caMois').textContent    = 'Mois préc. : ' + fcfa(d.ca.mois_prec);

            // Opérationnel
            document.getElementById('val_depotsJour').textContent = d.production.depots_jour;
            document.getElementById('val_enCours').textContent    = d.production.en_cours;
            document.getElementById('val_prets').textContent      = d.production.prets;
            document.getElementById('val_enRetard').textContent   = d.production.en_retard;
            document.getElementById('val_stockAlerte').textContent= d.alertes.stock;
            document.getElementById('val_incidents').textContent  = d.alertes.incidents;

            // Clients
            document.getElementById('val_clientsActifs').textContent = d.clients.actifs_30j;
            document.getElementById('val_tauxRetour').textContent    = d.clients.taux_retour + '%';
            document.getElementById('val_tauxRetourPct').textContent = d.clients.taux_retour + '%';
            document.getElementById('barRetour').style.width        = d.clients.taux_retour + '%';

            // Top prestations
            const top = document.getElementById('topPrestations');
            if (d.top_prestations.length === 0) {
                top.innerHTML = '<div class="text-center text-muted small py-2">Aucune donnée</div>';
            } else {
                const maxCA = Math.max(...d.top_prestations.map(p => p.ca_total));
                top.innerHTML = d.top_prestations.map((p, i) => `
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1" style="font-size:12px;">
                            <span class="fw-semibold">${p.type_prestation}</span>
                            <span class="text-success fw-bold">${new Intl.NumberFormat('fr-FR').format(Math.round(p.ca_total))} FCFA</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="flex-fill" style="background:#f1f5f9;border-radius:20px;height:6px;overflow:hidden;">
                                <div style="width:${(p.ca_total/maxCA)*100}%;background:#1d4ed8;height:100%;border-radius:20px;"></div>
                            </div>
                            <span style="font-size:10px;color:#6b7280;">${p.nb} art.</span>
                        </div>
                    </div>`).join('');
            }

            document.getElementById('lastUpdate').textContent =
                'Dernière mise à jour : ' + new Date().toLocaleTimeString('fr-FR');

            if (d.est_admin && d.stats_par_shop && d.stats_par_shop.length > 0) {
                const zone   = document.getElementById('zone_stats_shops');
                const cartes = document.getElementById('cartes_shops');
                if (zone && cartes) {
                    zone.classList.remove('d-none');
                    cartes.innerHTML = d.stats_par_shop.map(s => `
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between
                                                align-items-start mb-2">
                                        <div class="fw-bold">${s.nom_shop}</div>
                                        <a href="${BASE}shop/switcher/${s.id_shop}"
                                           class="btn btn-xs btn-outline-primary"
                                           style="font-size:10px;padding:2px 8px;
                                                  border-radius:20px;">
                                            Voir →
                                        </a>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-4 text-center">
                                            <div class="fw-bold text-primary"
                                                 style="font-size:18px;">
                                                ${s.depots_actifs}
                                            </div>
                                            <div class="text-muted"
                                                 style="font-size:10px;">En cours</div>
                                        </div>
                                        <div class="col-4 text-center">
                                            <div class="fw-bold text-dark"
                                                 style="font-size:18px;">
                                                ${s.nb_depots}
                                            </div>
                                            <div class="text-muted"
                                                 style="font-size:10px;">Total</div>
                                        </div>
                                        <div class="col-4 text-center">
                                            <div class="fw-bold text-success"
                                                 style="font-size:15px;">
                                                ${new Intl.NumberFormat('fr-FR')
                                                    .format(s.ca_jour)}
                                            </div>
                                            <div class="text-muted"
                                                 style="font-size:10px;">CA jour</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`).join('');
                }
            }
        });
}

// ── Graphiques ───────────────────────────────────────
function changerGraphique(type) {
    typeGraphActif = type;
    ['btn_ca30','btn_dep7','btn_modes'].forEach(id => {
        document.getElementById(id).classList.remove('btn-primary');
        document.getElementById(id).classList.add('btn-outline-secondary');
    });
    const map = {'ca_30j':'btn_ca30','depots_7j':'btn_dep7','modes_paiement':'btn_modes'};
    if (map[type]) {
        document.getElementById(map[type]).classList.add('btn-primary');
        document.getElementById(map[type]).classList.remove('btn-outline-secondary');
    }
    chargerGraphique(type);
}

function chargerGraphique(type) {
    fetch(`${BASE}dashboard/api/graphiques?type=${type}`)
        .then(r => r.json())
        .then(d => {
            if (chartPrincipal) chartPrincipal.destroy();

            const ctx    = document.getElementById('graphPrincipal').getContext('2d');
            const estBar = type === 'modes_paiement' || type === 'depots_7j';

            chartPrincipal = new Chart(ctx, {
                type: estBar ? 'bar' : 'line',
                data: {
                    labels: d.labels,
                    datasets: [{
                        label: type === 'ca_30j' ? 'CA (FCFA)' : type === 'depots_7j' ? 'Dépôts' : 'Montant (FCFA)',
                        data: d.data,
                        backgroundColor: estBar
                            ? 'rgba(29,78,216,0.7)'
                            : 'rgba(29,78,216,0.1)',
                        borderColor: '#1d4ed8',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: !estBar,
                        pointRadius: 3,
                    }],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => type === 'depots_7j'
                                    ? ctx.raw + ' dépôts'
                                    : new Intl.NumberFormat('fr-FR').format(ctx.raw) + ' FCFA',
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: v => type === 'depots_7j'
                                    ? v
                                    : new Intl.NumberFormat('fr-FR',{notation:'compact'}).format(v),
                            },
                        },
                    },
                },
            });
        });
}

// ── Init ─────────────────────────────────────────────
chargerKpis();
changerGraphique('ca_30j');
setInterval(chargerKpis, 60000); // Rafraîchissement toutes les minutes
</script>

<?= $this->endSection() ?>
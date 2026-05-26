<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Rapports & Analyses</h4>
        <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary rounded-2 px-3">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
    </div>

    <div class="row g-4">
        <?php
        $rapports = [
            [
                'url'   => 'rapports/ca',
                'titre' => 'Chiffre d\'Affaires',
                'desc'  => 'Transactions, modes de paiement, évolution par période.',
                'icon'  => 'fa-chart-line',
                'color' => '#1d4ed8',
                'bg'    => '#eff6ff',
            ],
            [
                'url'   => 'rapports/depots',
                'titre' => 'Dépôts',
                'desc'  => 'Liste des commandes, statuts, montants encaissés et restants.',
                'icon'  => 'fa-inbox',
                'color' => '#0e7490',
                'bg'    => '#ecfeff',
            ],
            [
                'url'   => 'rapports/clients',
                'titre' => 'Clients',
                'desc'  => 'CA par client, fréquence, fidélité, segmentation.',
                'icon'  => 'fa-users',
                'color' => '#166534',
                'bg'    => '#f0fdf4',
            ],
            [
                'url'   => 'rapports/prestations',
                'titre' => 'Prestations',
                'desc'  => 'Top des services, CA généré, prix moyen, taux express.',
                'icon'  => 'fa-tshirt',
                'color' => '#7e22ce',
                'bg'    => '#fdf4ff',
            ],
            [
                'url'   => 'rapports/employes',
                'titre' => 'Employés',
                'desc'  => 'Productivité, heures, CA encaissé, articles traités.',
                'icon'  => 'fa-user-tie',
                'color' => '#92400e',
                'bg'    => '#fef3c7',
            ],
        ];
        foreach ($rapports as $r): ?>
        <div class="col-md-6 col-xl-4">
            <a href="<?= base_url($r['url']) ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-3 h-100 rapport-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center"
                                 style="width:50px;height:50px;background:<?= $r['bg'] ?>;">
                                <i class="fas <?= $r['icon'] ?> fa-lg"
                                   style="color:<?= $r['color'] ?>;"></i>
                            </div>
                            <h5 class="fw-bold mb-0" style="color:<?= $r['color'] ?>;">
                                <?= $r['titre'] ?>
                            </h5>
                        </div>
                        <p class="text-muted mb-3" style="font-size:13px;line-height:1.6;">
                            <?= $r['desc'] ?>
                        </p>
                        <div class="d-flex gap-2 flex-wrap">
                            <span style="background:<?= $r['bg'] ?>;color:<?= $r['color'] ?>;
                                         padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                <i class="fas fa-file-csv me-1"></i>CSV
                            </span>
                            <span style="background:<?= $r['bg'] ?>;color:<?= $r['color'] ?>;
                                         padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                <i class="fas fa-file-excel me-1"></i>Excel
                            </span>
                            <span style="background:<?= $r['bg'] ?>;color:<?= $r['color'] ?>;
                                         padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                <i class="fas fa-file-pdf me-1"></i>PDF
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.rapport-card { transition: transform .15s, box-shadow .15s; cursor: pointer; }
.rapport-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.1) !important; }
</style>

<?= $this->endSection() ?>
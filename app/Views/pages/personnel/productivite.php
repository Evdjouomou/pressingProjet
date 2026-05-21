<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-0">Productivité individuelle</h4>
            <small class="text-muted"><?= date('F Y', strtotime($mois . '-01')) ?></small>
        </div>
        <form method="GET" class="d-flex gap-2">
            <input type="month" name="mois" value="<?= $mois ?>"
                   class="form-control form-control-sm"
                   onchange="this.form.submit()">
        </form>
    </div>

    <!-- Tableau productivité -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">EMPLOYÉ</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">JOURS</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">HEURES TOTALES</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">MOY / JOUR</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">ARTICLES TRAITÉS</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">MOY / ARTICLE</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">SCORE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        //$maxArticles = max(1, max(array_column($stats, 'articles_traites')));
                        foreach ($stats as $s):
                            $h     = intdiv($s['total_minutes'], 60);
                            $m     = $s['total_minutes'] % 60;
                            $hMoy  = intdiv($s['moy_minutes_jour'], 60);
                            $mMoy  = $s['moy_minutes_jour'] % 60;
                            $score = min(100, (int) round(($s['articles_traites'] / $maxArticles) * 100));
                            $scoreColor = $score >= 75 ? '#166534' : ($score >= 40 ? '#92400e' : '#991b1b');
                            $scoreBg    = $score >= 75 ? '#dcfce7' : ($score >= 40 ? '#fef3c7' : '#fee2e2');
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= base_url('uploads/photos/' . ($s['photo'] ?: 'default.png')) ?>"
                                         class="rounded-circle border" width="38" height="38"
                                         style="object-fit:cover;">
                                    <div>
                                        <div class="fw-semibold"><?= esc($s['nom_complet']) ?></div>
                                        <div class="text-muted" style="font-size:11px;">
                                            <?= esc($s['titre_poste']) ?> · <?= esc($s['nom_boutique']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center fw-semibold"><?= $s['jours_travailles'] ?></td>
                            <td class="text-center">
                                <span class="fw-semibold"><?= $h ?>h<?= str_pad($m,2,'0',STR_PAD_LEFT) ?></span>
                            </td>
                            <td class="text-center text-muted">
                                <?= $hMoy ?>h<?= str_pad((int)$mMoy,2,'0',STR_PAD_LEFT) ?>
                            </td>
                            <td class="text-center">
                                <span style="background:#eff6ff;color:#1d4ed8;padding:3px 12px;
                                             border-radius:20px;font-size:13px;font-weight:600;">
                                    <?= $s['articles_traites'] ?>
                                </span>
                            </td>
                            <td class="text-center text-muted">
                                <?= $s['articles_traites'] > 0
                                    ? round($s['moy_min_article']) . ' min'
                                    : '—' ?>
                            </td>
                            <td class="text-center">
                                <div style="display:inline-flex;align-items:center;gap:8px;">
                                    <div style="width:80px;background:#f1f5f9;border-radius:20px;height:8px;overflow:hidden;">
                                        <div style="width:<?= $score ?>%;background:<?= $scoreColor ?>;height:100%;border-radius:20px;"></div>
                                    </div>
                                    <span style="background:<?= $scoreBg ?>;color:<?= $scoreColor ?>;
                                                 padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;">
                                        <?= $score ?>%
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>
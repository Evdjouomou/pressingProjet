<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-0">Pointages</h4>
            <small class="text-muted"><?= date('d/m/Y', strtotime($filtre)) ?></small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <!-- Filtre date -->
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="date" value="<?= $filtre ?>"
                       class="form-control form-control-sm"
                       onchange="this.form.submit()">
            </form>
            <!-- Bouton pointage QR -->
            <button class="btn btn-primary btn-sm rounded-2"
                    data-bs-toggle="modal" data-bs-target="#modalQr">
                <i class="fas fa-qrcode me-1"></i>Scanner QR
            </button>
            <!-- Pointage bouton -->
            <button class="btn btn-outline-success btn-sm rounded-2"
                    data-bs-toggle="modal" data-bs-target="#modalBouton">
                <i class="fas fa-hand-pointer me-1"></i>Pointer
            </button>
        </div>
    </div>

    <!-- Stats du jour -->
    <div class="row g-3 mb-4">
        <?php
            $enCours  = count(array_filter($pointages, fn($p) => $p['statut'] === 'en_cours'));
            $presents = count(array_filter($pointages, fn($p) => $p['statut'] === 'present'));
            $nbAbsents = count($absents);
        ?>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-success"><?= $presents ?></div>
                <div class="text-muted small">Présents</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-primary"><?= $enCours ?></div>
                <div class="text-muted small">En cours</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <div class="fw-bold fs-3 text-danger"><?= $nbAbsents ?></div>
                <div class="text-muted small">Absents</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 text-center py-3">
                <?php
                    $totalMin = array_sum(array_column($pointages, 'duree_minutes'));
                    $h = intdiv($totalMin, 60);
                    $m = $totalMin % 60;
                ?>
                <div class="fw-bold fs-3 text-dark"><?= $h ?>h<?= str_pad($m,2,'0',STR_PAD_LEFT) ?></div>
                <div class="text-muted small">Total heures</div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <!-- Tableau pointages -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
                        <p class="text-uppercase text-muted fw-semibold mb-0" style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-clock me-2"></i>Relevé du jour
                        </p>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 text-muted fw-semibold" style="font-size:11px;">EMPLOYÉ</th>
                                    <th class="text-muted fw-semibold" style="font-size:11px;">ARRIVÉE</th>
                                    <th class="text-muted fw-semibold" style="font-size:11px;">DÉPART</th>
                                    <th class="text-center text-muted fw-semibold" style="font-size:11px;">DURÉE</th>
                                    <th class="text-center text-muted fw-semibold" style="font-size:11px;">STATUT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pointages)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-clock fa-2x mb-2 d-block opacity-25"></i>
                                        Aucun pointage pour cette date.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($pointages as $p): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?= base_url('uploads/photos/' . ($p['photo'] ?: 'default.png')) ?>"
                                                 class="rounded-circle border" width="34" height="34"
                                                 style="object-fit:cover;">
                                            <div>
                                                <div class="fw-semibold" style="font-size:13px;"><?= esc($p['nom_complet']) ?></div>
                                                <div class="text-muted" style="font-size:11px;"><?= esc($p['nom_poste']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-semibold text-success">
                                        <?= $p['heure_arrivee'] ? date('H:i', strtotime($p['heure_arrivee'])) : '—' ?>
                                        <?php if ($p['type_pointage'] === 'qrcode'): ?>
                                            <span class="badge bg-primary ms-1" style="font-size:9px;">QR</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted">
                                        <?= $p['heure_depart'] ? date('H:i', strtotime($p['heure_depart'])) : '—' ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($p['duree_minutes']): ?>
                                            <?= intdiv($p['duree_minutes'], 60) ?>h<?= str_pad($p['duree_minutes'] % 60, 2, '0', STR_PAD_LEFT) ?>
                                        <?php elseif ($p['statut'] === 'en_cours'): ?>
                                            <span class="text-primary" style="font-size:12px;">En cours...</span>
                                        <?php else: ?>—
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($p['statut'] === 'en_cours'): ?>
                                            <span style="background:#dbeafe;color:#1d4ed8;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                                En cours
                                            </span>
                                        <?php else: ?>
                                            <span style="background:#dcfce7;color:#166534;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                                                Présent
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Absents du jour -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="px-4 py-3 border-bottom">
                        <p class="text-uppercase text-muted fw-semibold mb-0" style="font-size:11px;letter-spacing:.5px;">
                            <i class="fas fa-user-slash me-2 text-danger"></i>Absents
                        </p>
                    </div>
                    <?php if (empty($absents)): ?>
                        <div class="text-center py-4 text-muted small">
                            <i class="fas fa-check-circle text-success fa-2x mb-2 d-block"></i>
                            Tous les actifs ont pointé !
                        </div>
                    <?php else: ?>
                    <?php foreach ($absents as $a): ?>
                    <div class="d-flex align-items-center gap-3 px-4 py-2 border-bottom">
                        <img src="<?= base_url('uploads/photos/' . ($a['photo'] ?: 'default.png')) ?>"
                             class="rounded-circle border" width="36" height="36"
                             style="object-fit:cover;">
                        <div>
                            <div class="fw-semibold" style="font-size:13px;"><?= esc($a['nom_complet']) ?></div>
                            <div class="text-muted" style="font-size:11px;"><?= esc($a['nom_poste']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- MODAL POINTAGE QR CODE -->
<div class="modal fade" id="modalQr" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="fas fa-qrcode text-primary me-2"></i>Scan QR Code</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <div id="reader_pointage" style="width:100%;border-radius:10px;overflow:hidden;"></div>
                <div id="feedback_qr" class="mt-3 d-none rounded-3 p-3 text-center fw-semibold"></div>
                <p class="text-muted small text-center mt-2">
                    Scannez le QR code de la carte employé.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- MODAL POINTAGE BOUTON -->
<div class="modal fade" id="modalBouton" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="fas fa-hand-pointer text-success me-2"></i>Pointer manuellement</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('personnel/pointer') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 pb-0">
                    <label class="form-label fw-semibold small">Sélectionner l'employé</label>
                    <select name="employe_id" class="form-select" required>
                        <option value="" disabled selected>Choisir un employé...</option>
                        <?php foreach ($employees as $e): ?>
                            <?php if ($e['status'] === 'Actif'): ?>
                            <option value="<?= $e['id_employe'] ?>">
                                <?= esc($e['nom_complet']) ?> — <?= esc($e['nom_poste']) ?>
                            </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <div class="rounded-3 p-3 mt-3" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                        <p class="text-success fw-semibold mb-0" style="font-size:13px;">
                            <i class="fas fa-info-circle me-1"></i>
                            Si l'employé est déjà arrivé, ce bouton enregistrera son départ.
                        </p>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-2" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success px-4 rounded-2">
                        <i class="fas fa-check me-2"></i>Enregistrer le pointage
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
let scannerPt = null;

document.getElementById('modalQr').addEventListener('shown.bs.modal', () => {
    scannerPt = new Html5Qrcode("reader_pointage");
    scannerPt.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 220, height: 120 } },
        (matricule) => {
            scannerPt.stop();
            fetch('<?= base_url('personnel/pointer-qr') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'matricule=' + encodeURIComponent(matricule)
                    + '&<?= csrf_token() ?>=<?= csrf_hash() ?>'
            })
            .then(r => r.json())
            .then(data => {
                const fb = document.getElementById('feedback_qr');
                fb.classList.remove('d-none');
                fb.className = 'mt-3 rounded-3 p-3 text-center fw-semibold alert '
                    + (data.success ? 'alert-success' : 'alert-danger');
                fb.textContent = data.message;
                if (data.success) setTimeout(() => bootstrap.Modal.getInstance(document.getElementById('modalQr')).hide(), 2000);
            });
        },
        () => {}
    ).catch(() => {
        document.getElementById('reader_pointage').innerHTML =
            '<div class="alert alert-warning">Caméra indisponible. Utilisez le pointage manuel.</div>';
    });
});

document.getElementById('modalQr').addEventListener('hidden.bs.modal', () => {
    if (scannerPt) { scannerPt.stop().catch(() => {}); scannerPt = null; }
});
</script>

<?= $this->endSection() ?>
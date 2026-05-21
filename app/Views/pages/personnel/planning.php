<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3">
            <?= session()->getFlashdata('success') ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-0">Planning hebdomadaire</h4>
            <small class="text-muted">Semaine du <?= date('d/m/Y', strtotime($semaine)) ?></small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <!-- Navigation semaines -->
            <?php
                $semainePrec = date('Y-m-d', strtotime($semaine . ' -7 days'));
                $semaineSuiv = date('Y-m-d', strtotime($semaine . ' +7 days'));
            ?>
            <a href="?semaine=<?= $semainePrec ?>" class="btn btn-outline-secondary btn-sm rounded-2">
                <i class="fas fa-chevron-left"></i> Préc.
            </a>
            <a href="?semaine=<?= date('Y-m-d', strtotime('monday this week')) ?>"
               class="btn btn-outline-primary btn-sm rounded-2">Semaine actuelle</a>
            <a href="?semaine=<?= $semaineSuiv ?>" class="btn btn-outline-secondary btn-sm rounded-2">
                Suiv. <i class="fas fa-chevron-right"></i>
            </a>
            <button class="btn btn-primary btn-sm rounded-2"
                    data-bs-toggle="modal" data-bs-target="#modalAjoutCreneau">
                <i class="fas fa-plus me-1"></i>Ajouter créneau
            </button>
        </div>
    </div>

    <!-- Grille planning -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" style="min-width:800px;">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3 text-muted fw-semibold" style="font-size:11px;width:130px;">EMPLOYÉ</th>
                            <?php
                            $joursLabels = [
                                'lundi'     => 'Lun', 'mardi'    => 'Mar',
                                'mercredi'  => 'Mer', 'jeudi'    => 'Jeu',
                                'vendredi'  => 'Ven', 'samedi'   => 'Sam',
                                'dimanche'  => 'Dim',
                            ];
                            $jourOffset  = 0;
                            foreach ($jours as $j):
                                $dateJour = date('d/m', strtotime($semaine . ' +' . $jourOffset . ' days'));
                                $jourOffset++;
                            ?>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;">
                                <?= $joursLabels[$j] ?><br>
                                <span style="font-size:12px;color:#1d4ed8;"><?= $dateJour ?></span>
                            </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Grouper par employé
                        $parEmploye = [];
                        foreach ($plannings as $pl) {
                            $parEmploye[$pl['employe_id']][$pl['jour']][] = $pl;
                        }
                        foreach ($employees as $e):
                            if ($e['status'] !== 'Actif') continue;
                        ?>
                        <tr>
                            <td class="ps-3 py-2">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= base_url('uploads/photos/' . ($e['photo'] ?: 'default.png')) ?>"
                                         class="rounded-circle border" width="28" height="28"
                                         style="object-fit:cover;">
                                    <span style="font-size:12px;font-weight:600;"><?= esc($e['nom_complet']) ?></span>
                                </div>
                            </td>
                            <?php foreach ($jours as $j): ?>
                            <td class="text-center py-2" style="vertical-align:middle;">
                                <?php if (isset($parEmploye[$e['id_employe']][$j])): ?>
                                    <?php foreach ($parEmploye[$e['id_employe']][$j] as $cr): ?>
                                    <div style="background:#eff6ff;border-radius:6px;padding:3px 6px;
                                                font-size:11px;margin-bottom:2px;position:relative;">
                                        <span class="fw-semibold text-primary">
                                            <?= substr($cr['heure_debut'],0,5) ?> – <?= substr($cr['heure_fin'],0,5) ?>
                                        </span>
                                        <?php if ($cr['note']): ?>
                                            <div class="text-muted" style="font-size:10px;"><?= esc($cr['note']) ?></div>
                                        <?php endif; ?>
                                        <a href="<?= base_url('personnel/planning/delete/' . $cr['id_planning']) ?>"
                                           onclick="return confirm('Supprimer ce créneau ?')"
                                           style="position:absolute;top:2px;right:4px;color:#dc2626;font-size:10px;text-decoration:none;">
                                            ×
                                        </a>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:12px;">—</span>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL AJOUTER CRÉNEAU -->
<div class="modal fade" id="modalAjoutCreneau" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="fas fa-calendar-plus text-primary me-2"></i>Ajouter un créneau</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('personnel/planning/sauvegarder') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="semaine" value="<?= $semaine ?>">
                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Employé</label>
                            <select name="employe_id" class="form-select" required>
                                <option value="" disabled selected>Choisir...</option>
                                <?php foreach ($employees as $e): ?>
                                    <?php if ($e['status'] === 'Actif'): ?>
                                    <option value="<?= $e['id_employe'] ?>"><?= esc($e['nom_complet']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Jour</label>
                            <select name="jour" class="form-select" required>
                                <option value="" disabled selected>Choisir...</option>
                                <?php foreach ($jours as $j): ?>
                                    <option value="<?= $j ?>"><?= ucfirst($j) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Heure début</label>
                            <input type="time" name="heure_debut" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Heure fin</label>
                            <input type="time" name="heure_fin" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Note (optionnel)</label>
                            <input type="text" name="note" class="form-control" placeholder="Ex: Ouverture, Formation...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-2">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
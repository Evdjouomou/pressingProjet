<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-0">Gestion des machines</h4>
            <small class="text-muted"><?= count($machines) ?> machine(s)</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('production/cycles') ?>"
               class="btn btn-outline-secondary rounded-2 px-3">
                <i class="fas fa-arrow-left me-2"></i>Cycles
            </a>
            <button class="btn btn-primary rounded-2 px-4"
                    data-bs-toggle="modal" data-bs-target="#modalCreerMachine">
                <i class="fas fa-plus me-2"></i>Nouvelle machine
            </button>
        </div>
    </div>

    <div class="row g-4">
        <?php
        $typesMachine = [
            'lavage'    => ['label' => 'Lavage',    'emoji' => '🫧', 'bg' => '#eff6ff', 'color' => '#1d4ed8'],
            'sechage'   => ['label' => 'Séchage',   'emoji' => '💨', 'bg' => '#ecfeff', 'color' => '#0e7490'],
            'repassage' => ['label' => 'Repassage', 'emoji' => '👔', 'bg' => '#fdf4ff', 'color' => '#7e22ce'],
            'detachage' => ['label' => 'Détachage', 'emoji' => '🧴', 'bg' => '#fff7ed', 'color' => '#c2410c'],
            'autre'     => ['label' => 'Autre',     'emoji' => '⚙️', 'bg' => '#f1f5f9', 'color' => '#475569'],
        ];
        foreach ($machines as $m):
            $tm = $typesMachine[$m['type_machine']] ?? $typesMachine['autre'];
        ?>
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                             style="width:48px;height:48px;background:<?= $tm['bg'] ?>;font-size:22px;">
                            <?= $tm['emoji'] ?>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0"><?= esc($m['nom']) ?></h6>
                            <span style="background:<?= $tm['bg'] ?>;color:<?= $tm['color'] ?>;
                                         padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;">
                                <?= $tm['label'] ?>
                            </span>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div style="background:#f8fafc;border-radius:10px;padding:10px;">
                                <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">Capacité max</div>
                                <div class="fw-bold mt-1"><?= $m['capacite_max'] ?> articles</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="background:#f8fafc;border-radius:10px;padding:10px;">
                                <div class="text-muted" style="font-size:10px;text-transform:uppercase;letter-spacing:.5px;">Cycles réalisés</div>
                                <div class="fw-bold mt-1"><?= $m['nb_cycles'] ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm flex-fill rounded-2"
                                style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;"
                                data-bs-toggle="modal"
                                data-bs-target="#modalModifier<?= $m['id_machine'] ?>">
                            <i class="fas fa-edit me-1"></i>Modifier
                        </button>
                        <?php if ($m['nb_cycles'] == 0): ?>
                        <a href="<?= base_url('production/machines/delete/' . $m['id_machine']) ?>"
                           class="btn btn-sm rounded-2"
                           style="background:#fff5f5;border:1px solid #fecaca;color:#dc2626;"
                           onclick="return confirm('Supprimer cette machine ?')">
                            <i class="fas fa-trash"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal modifier machine -->
        <div class="modal fade" id="modalModifier<?= $m['id_machine'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-0 px-4 pt-4 pb-0">
                        <h5 class="fw-bold mb-0">Modifier la machine</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="<?= base_url('production/machines/update/' . $m['id_machine']) ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="modal-body px-4 py-3">
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Nom</label>
                                <input type="text" name="nom" class="form-control" value="<?= esc($m['nom']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Type</label>
                                <select name="type_machine" class="form-select">
                                    <?php foreach ($typesMachine as $key => $val): ?>
                                    <option value="<?= $key ?>" <?= $m['type_machine'] === $key ? 'selected':'' ?>>
                                        <?= $val['emoji'] ?> <?= $val['label'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Capacité max (articles)</label>
                                <input type="number" name="capacite_max" class="form-control"
                                       value="<?= $m['capacite_max'] ?>" min="1" required>
                            </div>
                            <div>
                                <label class="form-label fw-semibold small">Statut</label>
                                <select name="actif" class="form-select">
                                    <option value="1" <?= $m['actif'] ? 'selected':'' ?>>Active</option>
                                    <option value="0" <?= !$m['actif'] ? 'selected':'' ?>>Inactive</option>
                                </select>
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

        <?php endforeach; ?>
    </div>
</div>

<!-- Modal créer machine -->
<div class="modal fade" id="modalCreerMachine" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="fas fa-industry text-primary me-2"></i>Nouvelle machine</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('production/machines/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control"
                               placeholder="Ex: Machine lavage 3..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Type</label>
                        <select name="type_machine" class="form-select">
                            <option value="lavage">🫧 Lavage</option>
                            <option value="sechage">💨 Séchage</option>
                            <option value="repassage">👔 Repassage</option>
                            <option value="detachage">🧴 Détachage</option>
                            <option value="autre">⚙️ Autre</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label fw-semibold small">Capacité max (articles) <span class="text-danger">*</span></label>
                        <input type="number" name="capacite_max" class="form-control"
                               placeholder="10" min="1" value="10" required>
                        <div class="form-text" style="font-size:10px;">
                            Nombre maximum d'articles pouvant être traités en un cycle.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success px-4 rounded-2 fw-semibold">
                        <i class="fas fa-check me-2"></i>Créer la machine
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
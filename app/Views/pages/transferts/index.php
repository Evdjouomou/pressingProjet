<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3">
            <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="fas fa-exchange-alt text-primary me-2"></i>
                Transferts inter-établissements
            </h4>
            <small class="text-muted"><?= count($transferts) ?> transfert(s)</small>
        </div>
        <button class="btn btn-primary rounded-2 px-4"
                data-bs-toggle="modal" data-bs-target="#modalTransfert">
            <i class="fas fa-plus me-2"></i>Nouveau transfert
        </button>
    </div>

    <!-- Tableau -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">ARTICLE</th>
                            <th class="text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">CLIENT</th>
                            <th class="text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">TRAJET</th>
                            <th class="text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">DEMANDÉ PAR</th>
                            <th class="text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">DATE</th>
                            <th class="text-center text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">STATUT</th>
                            <th class="text-center text-muted fw-semibold"
                                style="font-size:11px;letter-spacing:.5px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transferts)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-exchange-alt fa-2x mb-2 d-block opacity-25"></i>
                                Aucun transfert enregistré.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php
                        $statuts = [
                            'en_attente' => ['label'=>'En attente','bg'=>'#fef3c7','color'=>'#92400e'],
                            'confirme'   => ['label'=>'Confirmé',  'bg'=>'#dcfce7','color'=>'#166534'],
                            'annule'     => ['label'=>'Annulé',    'bg'=>'#fee2e2','color'=>'#991b1b'],
                        ];
                        foreach ($transferts as $tr):
                            $st = $statuts[$tr['statut']] ?? ['label'=>$tr['statut'],'bg'=>'#f1f5f9','color'=>'#374151'];
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold"><?= esc($tr['nom_libelle']) ?></div>
                                <div style="font-family:monospace;font-size:11px;color:#6b7280;">
                                    <?= esc($tr['barcode_unique']) ?>
                                </div>
                                <div class="text-primary" style="font-size:11px;">
                                    <?= esc($tr['code_commande']) ?>
                                </div>
                            </td>
                            <td><?= esc($tr['nomclient']) ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2"
                                     style="font-size:12px;">
                                    <span style="background:#eff6ff;color:#1d4ed8;
                                                 padding:3px 8px;border-radius:20px;
                                                 font-size:11px;font-weight:600;">
                                        <?= esc($tr['shop_source']) ?>
                                    </span>
                                    <i class="fas fa-arrow-right text-muted"></i>
                                    <span style="background:#dcfce7;color:#166634;
                                                 padding:3px 8px;border-radius:20px;
                                                 font-size:11px;font-weight:600;">
                                        <?= esc($tr['shop_dest']) ?>
                                    </span>
                                </div>
                                <?php if ($tr['motif']): ?>
                                <div class="text-muted" style="font-size:11px;margin-top:3px;">
                                    <?= esc($tr['motif']) ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px;">
                                <?= esc($tr['demandeur'] ?? '—') ?>
                            </td>
                            <td style="font-size:12px;">
                                <?= date('d/m/Y H:i', strtotime($tr['created_at'])) ?>
                            </td>
                            <td class="text-center">
                                <span style="background:<?= $st['bg'] ?>;
                                             color:<?= $st['color'] ?>;
                                             padding:3px 12px;border-radius:20px;
                                             font-size:11px;font-weight:600;">
                                    <?= $st['label'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($tr['statut'] === 'en_attente'): ?>
                                <form action="<?= base_url('transferts/confirmer/'
                                              . $tr['id_transfert']) ?>"
                                      method="POST" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit"
                                            class="btn btn-sm me-1"
                                            style="width:32px;height:32px;border-radius:8px;
                                                   background:#dcfce7;border:1px solid #bbf7d0;"
                                            title="Confirmer réception"
                                            onclick="return confirm('Confirmer la réception ?')">
                                        <i class="fas fa-check fa-sm text-success"></i>
                                    </button>
                                </form>
                                <a href="<?= base_url('transferts/annuler/'
                                          . $tr['id_transfert']) ?>"
                                   class="btn btn-sm"
                                   style="width:32px;height:32px;border-radius:8px;
                                          background:#fff5f5;border:1px solid #fecaca;"
                                   title="Annuler"
                                   onclick="return confirm('Annuler ce transfert ?')">
                                    <i class="fas fa-times fa-sm text-danger"></i>
                                </a>
                                <?php else: ?>
                                <span class="text-muted" style="font-size:11px;">—</span>
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

<!-- Modal nouveau transfert -->
<div class="modal fade" id="modalTransfert" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <div>
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-exchange-alt text-primary me-2"></i>
                        Nouveau transfert
                    </h5>
                    <small class="text-muted">Déplacer un article vers un autre établissement</small>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('transferts/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body px-4 py-3">

                    <div class="alert rounded-3 py-2 mb-3"
                         style="background:#fffbeb;border:1px solid #fde68a;font-size:12px;">
                        <i class="fas fa-info-circle text-warning me-1"></i>
                        Le transfert sera en attente jusqu'à confirmation
                        par le shop destinataire.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Code-barres de l'article
                            <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-barcode text-muted"></i>
                            </span>
                            <input type="text" name="barcode"
                                   class="form-control"
                                   placeholder="BC-XXXXXXXXXX-0"
                                   style="font-family:monospace;"
                                   autocomplete="off" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            Établissement destinataire
                            <span class="text-danger">*</span>
                        </label>
                        <select name="shop_dest_id" class="form-select" required>
                            <option value="" disabled selected>
                                Choisir un établissement...
                            </option>
                            <?php foreach ($shops as $sh): ?>
                            <?php if ($sh['id_shop'] != shop_actif_id()): ?>
                            <option value="<?= $sh['id_shop'] ?>">
                                <?= esc($sh['nom_shop']) ?>
                                <?php if ($sh['adresse']): ?>
                                    — <?= esc($sh['adresse']) ?>
                                <?php endif; ?>
                            </option>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="form-label fw-semibold small">
                            Motif du transfert
                        </label>
                        <textarea name="motif" class="form-control" rows="2"
                                  placeholder="Ex: Atelier centralisé, urgence client...">
                        </textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-2"
                            data-bs-dismiss="modal">Annuler</button>
                    <button type="submit"
                            class="btn btn-primary px-4 rounded-2 fw-semibold">
                        <i class="fas fa-paper-plane me-2"></i>Demander le transfert
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
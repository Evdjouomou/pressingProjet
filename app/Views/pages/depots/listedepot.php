<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-0">Dépôts</h4>
            <small class="text-muted">
                <?= $total ?> dépôt<?= $total > 1 ? 's' : '' ?> au total
            </small>
        </div>
        <a href="<?= base_url('depot/nouveau') ?>" class="btn btn-primary rounded-2 px-4">
            <i class="fas fa-plus me-2"></i>Nouveau dépôt
        </a>
    </div>

    <!-- Barre de recherche -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="<?= base_url('depot') ?>">
                <div class="input-group" style="max-width:420px;">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="fas fa-search fa-sm"></i>
                    </span>
                    <input type="text"
                           name="q"
                           class="form-control bg-light border-start-0 shadow-none"
                           placeholder="N° bon, nom client, téléphone..."
                           value="<?= esc($recherche) ?>"
                           autocomplete="off">
                    <?php if ($recherche): ?>
                        <a href="<?= base_url('depot') ?>"
                           class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary">Rechercher</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">

            <?php if (empty($depots)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i>
                    <p class="mb-0">
                        <?= $recherche ? 'Aucun dépôt ne correspond à "' . esc($recherche) . '"' : 'Aucun dépôt enregistré.' ?>
                    </p>
                </div>
            <?php else: ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">N° BON</th>
                            <th class="text-muted fw-semibold"       style="font-size:11px;letter-spacing:.5px;">CLIENT</th>
                            <th class="text-muted fw-semibold"       style="font-size:11px;letter-spacing:.5px;">DATE DÉPÔT</th>
                            <th class="text-muted fw-semibold"       style="font-size:11px;letter-spacing:.5px;">DATE RETRAIT</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">ARTICLES</th>
                            <th class="text-end text-muted fw-semibold"   style="font-size:11px;letter-spacing:.5px;">MONTANT</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">STATUT</th>
                            <th class="text-center text-muted fw-semibold" style="font-size:11px;letter-spacing:.5px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $statuts = [
                            'depot'    => ['label' => 'Reçu',     'bg' => '#e0e7ff', 'color' => '#3730a3'],
                            'en_cours' => ['label' => 'En cours', 'bg' => '#fef3c7', 'color' => '#92400e'],
                            'pret'     => ['label' => 'Prêt',     'bg' => '#d1fae5', 'color' => '#065f46'],
                            'livre'    => ['label' => 'Livré',    'bg' => '#dcfce7', 'color' => '#166534'],
                            'annule'   => ['label' => 'Annulé',   'bg' => '#fee2e2', 'color' => '#991b1b'],
                        ];
                        foreach ($depots as $d):
                            $s     = $statuts[$d['statut_global']] ?? ['label' => $d['statut_global'], 'bg' => '#f3f4f6', 'color' => '#374151'];
                            $reste = max(0, $d['total_ttc'] - $d['acompte_verse']);
                        ?>
                        <tr>
                            <!-- N° bon -->
                            <td class="ps-4">
                                <a href="<?= base_url('depot/detail/' . $d['id_depot']) ?>"
                                   class="fw-semibold text-primary text-decoration-none">
                                    <?= esc($d['code_commande']) ?>
                                </a>
                            </td>

                            <!-- Client -->
                            <td>
                                <span class="fw-semibold d-block"><?= esc($d['nomclient']) ?></span>
                                <span class="text-muted small">
                                    <i class="fas fa-phone fa-xs me-1"></i><?= esc($d['telephone']) ?>
                                </span>
                            </td>

                            <!-- Date dépôt -->
                            <td>
                                <span class="fw-medium"><?= date('d/m/Y', strtotime($d['created_at'])) ?></span>
                                <span class="d-block text-muted" style="font-size:11px;"><?= date('H:i', strtotime($d['created_at'])) ?></span>
                            </td>

                            <!-- Date retrait -->
                            <td>
                                <?php if ($d['date_livraison_prevue']): ?>
                                    <?php
                                        $retrait   = new DateTime($d['date_livraison_prevue']);
                                        $auj       = new DateTime();
                                        $estPasse  = $retrait < $auj;
                                        $estProche = !$estPasse && $retrait->diff($auj)->days <= 2;
                                        $couleur   = $estPasse ? '#dc2626' : ($estProche ? '#d97706' : '#059669');
                                        $icone     = $estPasse ? '⚠ ' : ($estProche ? '⏰ ' : '');
                                    ?>
                                    <span style="color:<?= $couleur ?>;font-weight:500;">
                                        <?= $icone . date('d/m/Y', strtotime($d['date_livraison_prevue'])) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>

                            <!-- Articles -->
                            <td class="text-center">
                                <span style="background:#eff6ff;color:#1d4ed8;font-weight:600;
                                             padding:3px 12px;border-radius:20px;font-size:13px;">
                                    <?= $d['nb_articles'] ?>
                                </span>
                            </td>

                            <!-- Montant -->
                            <td class="text-end">
                                <span class="fw-bold d-block"><?= number_format($d['total_ttc'], 0, ',', ' ') ?> FCFA</span>
                                <?php if ($reste <= 0): ?>
                                    <span style="background:#dcfce7;color:#166534;font-size:11px;
                                                 font-weight:600;padding:2px 8px;border-radius:20px;">
                                        <i class="fas fa-check me-1"></i>Soldé
                                    </span>
                                <?php else: ?>
                                    <span style="background:#fee2e2;color:#991b1b;font-size:11px;
                                                 font-weight:600;padding:2px 8px;border-radius:20px;">
                                        Reste : <?= number_format($reste, 0, ',', ' ') ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Statut -->
                            <td class="text-center">
                                <span style="background:<?= $s['bg'] ?>;color:<?= $s['color'] ?>;
                                             padding:4px 14px;border-radius:20px;
                                             font-size:12px;font-weight:600;">
                                    <?= $s['label'] ?>
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="text-center">
                                <a href="<?= base_url('depot/detail/' . $d['id_depot']) ?>"
                                   title="Voir"
                                   style="display:inline-flex;align-items:center;justify-content:center;
                                          width:32px;height:32px;border-radius:8px;
                                          background:#f1f5f9;color:#475569;text-decoration:none;
                                          border:1px solid #e2e8f0;">
                                    <i class="fas fa-eye fa-sm"></i>
                                </a>
                                <a href="<?= base_url('depot/imprimer/' . $d['id_depot']) ?>"
                                   target="_blank"
                                   title="Imprimer bon"
                                   style="display:inline-flex;align-items:center;justify-content:center;
                                          width:32px;height:32px;border-radius:8px;
                                          background:#eff6ff;color:#1d4ed8;text-decoration:none;
                                          border:1px solid #bfdbfe;margin-left:4px;">
                                    <i class="fas fa-print fa-sm"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">

                <small class="text-muted">
                    Affichage de
                    <strong><?= (($page - 1) * $parPage) + 1 ?></strong>
                    à
                    <strong><?= min($page * $parPage, $total) ?></strong>
                    sur <strong><?= $total ?></strong> dépôts
                </small>

                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">

                        <!-- Précédent -->
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link rounded-2"
                               href="<?= base_url('depot?page=' . ($page - 1) . ($recherche ? '&q=' . urlencode($recherche) : '')) ?>">
                                <i class="fas fa-chevron-left fa-xs"></i>
                            </a>
                        </li>

                        <!-- Pages numérotées -->
                        <?php
                        $debut = max(1, $page - 2);
                        $fin   = min($totalPages, $page + 2);
                        ?>

                        <?php if ($debut > 1): ?>
                            <li class="page-item">
                                <a class="page-link rounded-2"
                                   href="<?= base_url('depot?page=1' . ($recherche ? '&q=' . urlencode($recherche) : '')) ?>">1</a>
                            </li>
                            <?php if ($debut > 2): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">…</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $debut; $i <= $fin; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link rounded-2"
                                   href="<?= base_url('depot?page=' . $i . ($recherche ? '&q=' . urlencode($recherche) : '')) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($fin < $totalPages): ?>
                            <?php if ($fin < $totalPages - 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">…</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link rounded-2"
                                   href="<?= base_url('depot?page=' . $totalPages . ($recherche ? '&q=' . urlencode($recherche) : '')) ?>">
                                    <?= $totalPages ?>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Suivant -->
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link rounded-2"
                               href="<?= base_url('depot?page=' . ($page + 1) . ($recherche ? '&q=' . urlencode($recherche) : '')) ?>">
                                <i class="fas fa-chevron-right fa-xs"></i>
                            </a>
                        </li>

                    </ul>
                </nav>
            </div>
            <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>

</div>

<?= $this->endSection() ?>
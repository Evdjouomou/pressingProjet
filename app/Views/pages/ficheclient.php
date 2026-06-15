<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<style>
    .modal-backdrop.show { opacity: 0.7; backdrop-filter: blur(4px); }
    .cursor-pointer { cursor: pointer; }
    .forfait-card { transition: all 0.2s; border-color: #f1f5f9; }
    .btn-check:checked + .forfait-card { background-color: #eff6ff !important; border-color: #3b82f6 !important; }
</style>

<div>
    <a href="<?= base_url('client') ?>" style="text-decoration: none; color: #3b82f6; padding: 8px 12px;"> <i class="bi bi-arrow-left"></i> Retour</a>
    <div class="client-card-container" style="margin-top: 5px;">
        <!-- Infos client — remplacer le bloc existant -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body">

                <h4 class="fw-bold mb-1"><?= esc($client['nomclient']) ?></h4>

                <?php if ($client['telephone']): ?>
                <div class="text-muted mb-1">
                    <i class="fas fa-phone me-2 text-primary"></i>
                    <?= esc($client['telephone']) ?>
                </div>
                <?php endif; ?>

                <?php if ($client['email']): ?>
                <div class="text-muted mb-1">
                    <i class="fas fa-envelope me-2 text-primary"></i>
                    <?= esc($client['email']) ?>
                </div>
                <?php endif; ?>

                <div class="text-muted mb-1">
                    <i class="fas fa-calendar me-2 text-primary"></i>
                    Membre depuis :
                    <?= date('d/m/Y à H:i', strtotime($client['dateajout'])) ?>
                </div>

                <!-- Établissement principal -->
                <?php if ($client['shop_nom']): ?>
                    <div class="text-muted mb-1">
                        <i class="fas fa-store me-2 text-primary"></i>
                        Établissement :
                        <?= esc($client['shop_nom']) ?>
                    </div>
                <?php endif; ?>

                <!-- Enregistré par -->
                <div class="text-muted mb-1">
                    <i class="fas fa-user-check me-2 text-primary"></i>
                    Enregistré par :
                    <?php if ($client['enregistre_par_nom']): ?>
                        <?= esc($client['enregistre_par_nom']) ?>
                    <?php else: ?>
                        <span class="text-muted fst-italic">Non renseigné</span>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <div class="loyalty-info">
            <div class="loyalty-section">
                <?php
                    $totalPointsGagnes = array_sum(array_column($depots, 'total_points'));
                ?>

                <div class="loyalty-row">
                    <div class="loyalty-label">
                        <p style="margin:0; font-size:12px; color:#6b7280;">Solde fidélité</p>
                        <h2 style="margin:4px 0; font-size:24px; font-weight:700; color:#f59e0b;">
                            <?= number_format($client['solde_fidelite'], 0, ',', ' ') ?>
                            <span style="font-size:14px; font-weight:500;">pts</span>
                        </h2>
                        <p style="margin:0; font-size:11px; color:#9ca3af;">
                            Cumulés sur <?= count($depots) ?> dépôt<?= count($depots) > 1 ? 's' : '' ?>
                        </p>
                    </div>
                    <div class="loyalty-actions">
                        <button class="btn-use" 
                                <?= $client['solde_fidelite'] <= 0 ? 'disabled' : '' ?>
                                style="<?= $client['solde_fidelite'] <= 0 ? 'opacity:.4; cursor:not-allowed;' : '' ?>">
                            Utiliser
                        </button>
                    </div>
                </div>
                <div class="loyalty-row">
                    <a href="<?= base_url('abonnements/nouveau/' . $client['id_client']) ?>" class="btn btn-outline-primary">
                        <i class="bi bi-card-checklist"></i> Prendre un abonnement
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div style="margin-top: 24px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px;">
        <h3 style="margin:0;">
            Historique des dépôts
            <span style="font-size:14px; font-weight:500; color:#6b7280; margin-left:8px;">
                (<?= count($depots) ?> dépôt<?= count($depots) > 1 ? 's' : '' ?>)
            </span>
        </h3>
    </div>

    <?php if (empty($depots)): ?>
        <div style="text-align:center; padding:40px; color:#9ca3af; background:#f9fafb; border-radius:10px; border:1px dashed #e5e7eb;">
            <i class="bi bi-inbox" style="font-size:32px; display:block; margin-bottom:8px;"></i>
            Aucun dépôt enregistré pour ce client.
        </div>
    <?php else: ?>
    <table class="table table-hover" style="border-radius:10px; overflow:hidden;">
        <thead class="table-light">
            <tr>
                <th style="font-size:12px; text-transform:uppercase; letter-spacing:.5px; color:#6b7280;">N° Bon</th>
                <th style="font-size:12px; text-transform:uppercase; letter-spacing:.5px; color:#6b7280;">Date dépôt</th>
                <th style="font-size:12px; text-transform:uppercase; letter-spacing:.5px; color:#6b7280;">Date retrait</th>
                <th style="font-size:12px; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; text-align:center;">Articles</th>
                <th style="font-size:12px; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; text-align:center;">Points</th>
                <th style="font-size:12px; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; text-align:right;">Montant</th>
                <th style="font-size:12px; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; text-align:center;">Statut</th>
                <th style="font-size:12px; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; text-align:center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($depots as $depot): ?>
            <?php
                $statuts = [
                    'depot'    => ['label' => 'Reçu',     'bg' => '#e0e7ff', 'color' => '#3730a3'],
                    'en_cours' => ['label' => 'En cours', 'bg' => '#fef3c7', 'color' => '#92400e'],
                    'pret'     => ['label' => 'Prêt',     'bg' => '#d1fae5', 'color' => '#065f46'],
                    'livre'    => ['label' => 'Livré',    'bg' => '#dcfce7', 'color' => '#166534'],
                    'annule'   => ['label' => 'Annulé',   'bg' => '#fee2e2', 'color' => '#991b1b'],
                ];
                $s = $statuts[$depot['statut_global']] ?? ['label' => $depot['statut_global'], 'bg' => '#f3f4f6', 'color' => '#374151'];
                $reste = max(0, $depot['total_ttc'] - $depot['acompte_verse']);
            ?>
            <tr>
                <td style="font-weight:600; color:#1d4ed8;">
                    <?= esc($depot['code_commande']) ?>
                </td>
                <td style="color:#374151;">
                    <span style="font-weight:500;"><?= date('d/m/Y', strtotime($depot['created_at'])) ?></span>
                    <span style="display:block; font-size:11px; color:#9ca3af;"><?= date('H:i', strtotime($depot['created_at'])) ?></span>
                </td>
                <td>
                    <?php if ($depot['date_livraison_prevue']): ?>
                        <?php
                            $retrait    = new DateTime($depot['date_livraison_prevue']);
                            $aujourd    = new DateTime();
                            $estPasse   = $retrait < $aujourd;
                            $estProche  = !$estPasse && $retrait->diff($aujourd)->days <= 2;
                        ?>
                        <span style="font-weight:500; color:<?= $estPasse ? '#dc2626' : ($estProche ? '#d97706' : '#059669') ?>;">
                            <?= $estPasse ? '⚠ ' : ($estProche ? '⏰ ' : '') ?>
                            <?= date('d/m/Y', strtotime($depot['date_livraison_prevue'])) ?>
                        </span>
                    <?php else: ?>
                        <span style="color:#d1d5db;">—</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;">
                    <span style="background:#eff6ff; color:#1d4ed8; font-weight:600; padding:3px 10px; border-radius:20px; font-size:13px;">
                        <?= $depot['nb_articles'] ?>
                    </span>
                </td>
                <td style="text-align:center;">
                    <?php if ($depot['total_points'] > 0): ?>
                        <span style="background:#fef3c7; color:#92400e; font-weight:600; padding:3px 10px; border-radius:20px; font-size:12px;">
                            +<?= $depot['total_points'] ?> pts
                        </span>
                    <?php else: ?>
                        <span style="color:#d1d5db; font-size:12px;">—</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:right;">
                    <span style="font-weight:700; color:#111827; display:block;">
                        <?= number_format($depot['total_ttc'], 0, ',', ' ') ?> FCFA
                    </span>
                    <?php if ($reste <= 0): ?>
                        <span style="display:inline-block; margin-top:4px; background:#dcfce7; color:#166534; font-size:11px; font-weight:600; padding:2px 10px; border-radius:20px;">
                            <i class="bi bi-check-circle-fill me-1"></i>Soldé
                        </span>
                    <?php else: ?>
                        <span style="font-size:11px; color:#6b7280; display:block; margin-top:2px;">
                            Versé : <?= number_format($depot['acompte_verse'], 0, ',', ' ') ?>
                        </span>
                        <span style="display:inline-block; margin-top:3px; background:#fee2e2; color:#991b1b; font-size:11px; font-weight:600; padding:2px 10px; border-radius:20px;">
                            <i class="bi bi-exclamation-circle-fill me-1"></i>Reste : <?= number_format($reste, 0, ',', ' ') ?> FCFA
                        </span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;">
                    <span style="background:<?= $s['bg'] ?>; color:<?= $s['color'] ?>; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600;">
                        <?= $s['label'] ?>
                    </span>
                </td>
                <td style="text-align:center;">
                    <a href="<?= base_url('depot/detail/' . $depot['id_depot']) ?>" title="Voir le dépôt" style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; background:#f1f5f9; color:#475569; text-decoration:none; border:1px solid #e2e8f0;">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="<?= base_url('depot/imprimer/' . $depot['id_depot']) ?>" target="_blank" title="Imprimer bon" style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; background:#eff6ff; color:#1d4ed8; text-decoration:none; border:1px solid #bfdbfe; margin-left:4px;">
                        <i class="bi bi-printer"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot style="background:#f8fafc; border-top:2px solid #e2e8f0;">
            <tr>
                <td colspan="4" style="text-align:right; font-size:12px; color:#6b7280; padding:10px 12px;">Total cumulé client</td>
                <td style="text-align:right; font-weight:700; color:#111827; font-size:15px; padding:10px 12px;">
                    <?= number_format(array_sum(array_column($depots, 'total_ttc')), 0, ',', ' ') ?> FCFA
                </td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
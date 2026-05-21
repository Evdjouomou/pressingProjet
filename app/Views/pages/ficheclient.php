<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<style>
    .modal-backdrop.show { opacity: 0.7; backdrop-filter: blur(4px); }
    .cursor-pointer { cursor: pointer; }
    .forfait-card { transition: all 0.2s; border-color: #f1f5f9; }
    .btn-check:checked + .forfait-card { background-color: #eff6ff !important; border-color: #3b82f6 !important; }
</style>

<div >
    <a href="<?= base_url('client') ?>" style="text-decoration: none; color: #3b82f6; padding: 8px 12px;"> <i class="bi bi-arrow-left"></i> Retour</a>
    <div class="client-card-container" style="margin-top: 5px;">
        <div class="profile-info">
            <div class="profile-details">
                <h2><?= esc($client['nomclient']) ?></h2>
                <p><i class="bi bi-telephone"></i> <?= esc($client['telephone']) ?></p>
                <p><i class="bi bi-envelope"></i> <?= esc($client['email']) ?></p>
                <p><i class="bi bi-calendar"></i> Membre depuis: <?= esc($client['dateajout']) ?></p>
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
                    <button type="button"  
                            data-bs-toggle="modal" 
                            data-bs-target="#modalAbonnement"
                            style="text-decoration: none; background-color: #3b82f6; color: white; padding: 8px 12px; border-radius: 4px; border: none;">
                        <i class="bi bi-card-checklist"></i> Prendre un abonnement
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAbonnement" tabindex="-1" aria-hidden="true" style="width: 45%; margin-left: 30%;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header border-0">
                <h3 class="modal-title fs-5">Nouvel Abonnement</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="<?= base_url('receptionniste/saveabonnement/'.$client['id_client']) ?>" method="post">
                    <div class="">
                        <p>Vous ete sur le point de prendre un abonnement pour M/Mme : <h4><?= esc($client['nomclient']) ?></h4></p>
                        <select name="id_type_abonnement" id="" required style="width: 50%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #ced4da;">
                            <option value="">Sélectionnez un forfait</option>
                            <?php foreach($forfaits as $f): ?>
                                <option value="<?= $f['id_type_abonnement'] ?>"><?= $f['libelle'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <div>
                            <label for="date_debut">Date de debut </label>
                            <input type="datetime-local" name="date_debut" id="date_debut" required 
                                style="width: 30%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #ced4da;">
                            
                            <label for="type_abonnement" style="margin-left: 10px;">Abonnement</label>
                            <select name="type_abonnement" id="type_abonnement" style="padding: 8px; border-radius: 4px; border: 1px solid #ced4da;">
                                <option value="7">Hebdomadaire</option>
                                <option value="30" selected>Mensuel</option> 
                                <option value="90">Trimestriel</option>
                                <option value="365">Annuel</option>
                            </select>
                        </div>
                        
                        <label style="margin-top: 10px; display: block;">Date de fin : 
                            <span id="display_date_fin" style="font-weight: bold; color: #3b82f6;">
                                <?php echo date('Y-m-d - H:i', strtotime('+30 days')); ?>
                            </span>
                        </label>
                    </div>
                    <div class="mt-4">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal" style="height: 45px; margin-top: 10px;">Annuler</button>
                        <button type="submit" class="btn btn-primary px-4">Confirmer</button>
                    </div>
                </form>
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
                // Badge statut
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
                <!-- N° bon -->
                <td style="font-weight:600; color:#1d4ed8;">
                    <?= esc($depot['code_commande']) ?>
                </td>

                <!-- Date de dépôt -->
                <td style="color:#374151;">
                    <span style="font-weight:500;"><?= date('d/m/Y', strtotime($depot['created_at'])) ?></span>
                    <span style="display:block; font-size:11px; color:#9ca3af;"><?= date('H:i', strtotime($depot['created_at'])) ?></span>
                </td>

                <!-- Date retrait prévue -->
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

                <!-- Nb articles -->
                <td style="text-align:center;">
                    <span style="background:#eff6ff; color:#1d4ed8; font-weight:600; padding:3px 10px; border-radius:20px; font-size:13px;">
                        <?= $depot['nb_articles'] ?>
                    </span>
                </td>

                <td style="text-align:center;">
                    <?php if ($depot['total_points'] > 0): ?>
                        <span style="background:#fef3c7; color:#92400e; font-weight:600;
                                    padding:3px 10px; border-radius:20px; font-size:12px;">
                            +<?= $depot['total_points'] ?> pts
                        </span>
                    <?php else: ?>
                        <span style="color:#d1d5db; font-size:12px;">—</span>
                    <?php endif; ?>
                </td>

                <!-- Montant figé -->
                <td style="text-align:right;">
                    <span style="font-weight:700; color:#111827; display:block;">
                        <?= number_format($depot['total_ttc'], 0, ',', ' ') ?> FCFA
                    </span>

                    <?php if ($reste <= 0): ?>
                        <span style="display:inline-block; margin-top:4px; background:#dcfce7; color:#166534;
                                    font-size:11px; font-weight:600; padding:2px 10px; border-radius:20px;">
                            <i class="bi bi-check-circle-fill me-1"></i>Soldé
                        </span>
                    <?php else: ?>
                        <span style="font-size:11px; color:#6b7280; display:block; margin-top:2px;">
                            Versé : <?= number_format($depot['acompte_verse'], 0, ',', ' ') ?>
                        </span>
                        <span style="display:inline-block; margin-top:3px; background:#fee2e2; color:#991b1b;
                                    font-size:11px; font-weight:600; padding:2px 10px; border-radius:20px;">
                            <i class="bi bi-exclamation-circle-fill me-1"></i>Reste : <?= number_format($reste, 0, ',', ' ') ?> FCFA
                        </span>
                    <?php endif; ?>
                </td>

                <!-- Statut -->
                <td style="text-align:center;">
                    <span style="
                        background:<?= $s['bg'] ?>;
                        color:<?= $s['color'] ?>;
                        padding:4px 12px;
                        border-radius:20px;
                        font-size:12px;
                        font-weight:600;
                    ">
                        <?= $s['label'] ?>
                    </span>
                </td>

                <!-- Actions -->
                <td style="text-align:center;">
                    <a href="<?= base_url('depot/detail/' . $depot['id_depot']) ?>"
                       title="Voir le dépôt"
                       style="display:inline-flex; align-items:center; justify-content:center;
                              width:32px; height:32px; border-radius:8px;
                              background:#f1f5f9; color:#475569; text-decoration:none;
                              border:1px solid #e2e8f0;">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="<?= base_url('depot/imprimer/' . $depot['id_depot']) ?>"
                       target="_blank"
                       title="Imprimer bon"
                       style="display:inline-flex; align-items:center; justify-content:center;
                              width:32px; height:32px; border-radius:8px;
                              background:#eff6ff; color:#1d4ed8; text-decoration:none;
                              border:1px solid #bfdbfe; margin-left:4px;">
                        <i class="bi bi-printer"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>

        <!-- Pied de tableau récapitulatif -->
        <tfoot style="background:#f8fafc; border-top:2px solid #e2e8f0;">
            <tr>
                <td colspan="4" style="text-align:right; font-size:12px; color:#6b7280; padding:10px 12px;">
                    Total cumulé client
                </td>
                <td style="text-align:right; font-weight:700; color:#111827; font-size:15px; padding:10px 12px;">
                    <?= number_format(array_sum(array_column($depots, 'total_ttc')), 0, ',', ' ') ?> FCFA
                </td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>
</div>

<script>
    const inputDebut = document.getElementById('date_debut');
    const selectAbo = document.getElementById('type_abonnement');
    const displayFin = document.getElementById('display_date_fin');

    function calculerDateFin() {
        const dateDebutVal = inputDebut.value;
        const joursAAjouter = parseInt(selectAbo.value);

        if (dateDebutVal && joursAAjouter) {
            let dateFin = new Date(dateDebutVal);

            dateFin.setDate(dateFin.getDate() + joursAAjouter);

            const year = dateFin.getFullYear();
            const month = String(dateFin.getMonth() + 1).padStart(2, '0');
            const day = String(dateFin.getDate()).padStart(2, '0');
            const hours = String(dateFin.getHours()).padStart(2, '0');
            const minutes = String(dateFin.getMinutes()).padStart(2, '0');
            
            displayFin.textContent = `${year}-${month}-${day} - ${hours}:${minutes}`;
        }
    }

    inputDebut.addEventListener('change', calculerDateFin);
    selectAbo.addEventListener('change', calculerDateFin);

    if(!inputDebut.value) {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        inputDebut.value = now.toISOString().slice(0, 16);
    }
</script>

<?= $this->endSection() ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Z — <?= date('d/m/Y', strtotime($caisse['date_ouverture'])) ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            background: #fff;
            color: #222;
        }
        .page  { max-width: 380px; margin: 0 auto; padding: 20px; }
        .titre { text-align:center; font-weight:700; font-size:18px; margin-bottom:4px; }
        .sous  { text-align:center; font-size:11px; color:#555; margin-bottom:16px; }
        .sep   { border-top:1px dashed #999; margin:12px 0; }
        .sep2  { border-top:2px solid #222; margin:12px 0; }
        .ligne {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 12px;
        }
        .ligne.total { font-weight:700; font-size:14px; }
        .ligne.sous-total { font-weight:600; }
        .badge-statut {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }
        .ecart-ok   { background:#dcfce7; color:#166534; }
        .ecart-pos  { background:#eff6ff; color:#1d4ed8; }
        .ecart-neg  { background:#fee2e2; color:#991b1b; }
        .section-titre {
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin: 10px 0 6px;
            color: #555;
        }
        table { width:100%; border-collapse:collapse; font-size:11px; margin-bottom:10px; }
        th    { border-bottom:1px solid #ccc; padding:4px 2px; text-align:left; font-size:10px; }
        td    { padding:3px 2px; border-bottom:1px solid #f0f0f0; }
        .text-right { text-align:right; }
        .no-print { margin-bottom:16px; display:flex; gap:8px; }
        @media print {
            .no-print { display:none; }
            @page { size:80mm auto; margin:5mm; }
        }
    </style>
</head>
<body>
<div class="page">

    <div class="no-print">
        <button onclick="window.print()"
                style="padding:6px 16px;background:#1a1a2e;color:#fff;
                       border:none;border-radius:6px;cursor:pointer;font-size:12px;">
            🖨️ Imprimer
        </button>
        <button onclick="window.close()"
                style="padding:6px 16px;background:#eee;border:none;
                       border-radius:6px;cursor:pointer;font-size:12px;">
            ✕ Fermer
        </button>
    </div>

    <!-- Entête -->
    <div class="titre">PRESSING PRO</div>
    <div class="sous">
        Rapport de caisse (Rapport Z)<br>
        <?= date('d/m/Y', strtotime($caisse['date_ouverture'])) ?>
    </div>

    <div class="sep2"></div>

    <!-- Infos session -->
    <div class="section-titre">Informations session</div>
    <div class="ligne">
        <span>Caissier</span>
        <span><?= esc($caisse['caissier'] ?? '—') ?></span>
    </div>
    <div class="ligne">
        <span>Ouverture</span>
        <span><?= date('d/m/Y H:i', strtotime($caisse['date_ouverture'])) ?></span>
    </div>
    <div class="ligne">
        <span>Clôture</span>
        <span>
            <?= $caisse['date_cloture']
                ? date('d/m/Y H:i', strtotime($caisse['date_cloture']))
                : 'En cours' ?>
        </span>
    </div>
    <div class="ligne">
        <span>Transactions</span>
        <span><?= count($transactions) ?></span>
    </div>

    <div class="sep"></div>

    <!-- Totaux par mode -->
    <div class="section-titre">Encaissements par mode</div>
    <?php
    $modesLabels = [
        'especes'      => 'Espèces',
        'mobile_money' => 'Mobile Money',
        'carte'        => 'Carte bancaire',
        'mixte'        => 'Paiement mixte',
        'avoir'        => 'Avoir client',
    ];
    foreach ($par_mode as $mode => $montant): ?>
    <div class="ligne">
        <span><?= $modesLabels[$mode] ?? ucfirst($mode) ?></span>
        <span><?= number_format($montant, 0, ',', ' ') ?> FCFA</span>
    </div>
    <?php endforeach; ?>

    <div class="sep"></div>

    <!-- Récapitulatif financier -->
    <div class="section-titre">Récapitulatif</div>
    <div class="ligne">
        <span>Fond d'ouverture</span>
        <span><?= number_format($caisse['fond_ouverture'], 0, ',', ' ') ?> FCFA</span>
    </div>
    <div class="ligne">
        <span>Total encaissé</span>
        <span><?= number_format($total_enc, 0, ',', ' ') ?> FCFA</span>
    </div>
    <?php if ($total_rmb > 0): ?>
    <div class="ligne">
        <span>Remboursements</span>
        <span>- <?= number_format($total_rmb, 0, ',', ' ') ?> FCFA</span>
    </div>
    <?php endif; ?>
    <div class="ligne sous-total">
        <span>Net encaissé</span>
        <span><?= number_format($total_enc - $total_rmb, 0, ',', ' ') ?> FCFA</span>
    </div>

    <div class="sep"></div>

    <!-- Espèces théoriques vs réelles -->
    <?php if ($caisse['date_cloture']): ?>
    <div class="section-titre">Contrôle espèces</div>
    <div class="ligne">
        <span>Espèces théoriques</span>
        <span>
            <?= number_format($caisse['fond_ouverture'] + $caisse['total_especes'], 0, ',', ' ') ?> FCFA
        </span>
    </div>
    <div class="ligne">
        <span>Espèces comptées</span>
        <span><?= number_format($caisse['fond_reel'] ?? 0, 0, ',', ' ') ?> FCFA</span>
    </div>
    <div class="ligne total">
        <span>Écart</span>
        <span>
            <?php $ecart = $caisse['ecart'] ?? 0; ?>
            <?= $ecart >= 0 ? '+' : '' ?>
            <?= number_format($ecart, 0, ',', ' ') ?> FCFA
        </span>
    </div>
    <div class="sep"></div>
    <?php endif; ?>

    <!-- Détail transactions -->
    <?php if (!empty($transactions)): ?>
    <div class="section-titre">Détail des transactions</div>
    <table>
        <thead>
            <tr>
                <th>Heure</th>
                <th>Bon</th>
                <th>Type</th>
                <th class="text-right">Montant</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $tx):
                $signe = $tx['type'] === 'encaissement' ? '+' : '−';
                $color = $tx['type'] === 'encaissement' ? '' : 'color:#dc2626;';
            ?>
            <tr>
                <td><?= date('H:i', strtotime($tx['created_at'])) ?></td>
                <td><?= esc($tx['code_commande'] ?: ($tx['motif'] ?: '—')) ?></td>
                <td><?= $tx['type'] === 'encaissement' ? 'Enc.' : 'Rmb.' ?></td>
                <td class="text-right" style="<?= $color ?>font-weight:600;">
                    <?= $signe ?><?= number_format($tx['montant'], 0, ',', ' ') ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <div class="sep2"></div>

    <!-- Total final -->
    <div class="ligne total">
        <span>TOTAL CA</span>
        <span><?= number_format($caisse['total_ca'], 0, ',', ' ') ?> FCFA</span>
    </div>

    <div class="sep"></div>

    <div style="text-align:center;font-size:10px;color:#888;margin-top:10px;">
        Document généré le <?= date('d/m/Y à H:i') ?><br>
        Pressing Pro — Rapport Z
    </div>

</div>
</body>
</html>
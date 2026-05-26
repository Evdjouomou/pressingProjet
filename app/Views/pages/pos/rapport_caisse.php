<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport de caisse — <?= date('d/m/Y', strtotime($caisse['date_ouverture'])) ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',Arial,sans-serif; font-size:13px; color:#222; background:#fff; }
        .page { max-width:800px; margin:0 auto; padding:32px; }
        .header { display:flex; justify-content:space-between; align-items:flex-start;
                  margin-bottom:28px; padding-bottom:18px; border-bottom:2px solid #1a1a2e; }
        .logo h1 { font-size:20px; font-weight:700; color:#1a1a2e; }
        .logo p  { font-size:11px; color:#666; margin-top:3px; }
        .rapport-title { text-align:right; }
        .rapport-title h2 { font-size:16px; font-weight:700; color:#1a1a2e; }
        .rapport-title p  { font-size:11px; color:#666; margin-top:3px; }
        .stats-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:24px; }
        .stat-box { border:1px solid #e0e0e0; border-radius:8px; padding:14px; text-align:center; }
        .stat-box .val { font-size:18px; font-weight:700; margin-bottom:4px; }
        .stat-box .lbl { font-size:10px; color:#888; text-transform:uppercase; letter-spacing:.5px; }
        table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        thead tr { background:#1a1a2e; color:#fff; }
        thead th { padding:9px 12px; text-align:left; font-size:11px; font-weight:600; }
        tbody tr { border-bottom:1px solid #f0f0f0; }
        tbody tr:nth-child(even) { background:#fafafa; }
        tbody td { padding:9px 12px; font-size:12px; }
        tfoot tr { background:#f8fafc; font-weight:700; }
        tfoot td { padding:10px 12px; border-top:2px solid #1a1a2e; }
        .text-right { text-align:right; }
        .text-center { text-align:center; }
        .badge { padding:2px 8px; border-radius:20px; font-size:10px; font-weight:600; }
        .section-title { font-size:13px; font-weight:700; color:#1a1a2e;
                         text-transform:uppercase; letter-spacing:.5px;
                         margin:20px 0 10px; padding-bottom:6px; border-bottom:1px solid #e0e0e0; }
        .ecart-box { border-radius:8px; padding:14px 18px; margin-bottom:24px; }
        .no-print { margin-bottom:20px; display:flex; gap:10px; }
        @media print {
            .no-print { display:none; }
            @page { size:A4; margin:20mm; }
        }
    </style>
</head>
<body>
<div class="page">

    <div class="no-print">
        <button onclick="window.print()"
                style="padding:8px 20px;background:#1a1a2e;color:#fff;border:none;border-radius:6px;cursor:pointer;">
            🖨️ Imprimer le rapport Z
        </button>
        <button onclick="window.close()"
                style="padding:8px 20px;background:#eee;border:none;border-radius:6px;cursor:pointer;">
            ✕ Fermer
        </button>
    </div>

    <!-- En-tête -->
    <div class="header">
        <div class="logo">
            <h1>PRESSING PRO</h1>
            <p>Rapport de caisse (Rapport Z)</p>
            <p><?= esc($caisse['nom_shop'] ?? 'Boutique principale') ?></p>
        </div>
        <div class="rapport-title">
            <h2>SESSION DE CAISSE</h2>
            <p>Ouverture : <?= date('d/m/Y à H:i', strtotime($caisse['date_ouverture'])) ?></p>
            <?php if ($caisse['date_cloture']): ?>
            <p>Clôture : <?= date('d/m/Y à H:i', strtotime($caisse['date_cloture'])) ?></p>
            <?php endif; ?>
            <p>Caissier : <strong><?= esc($caisse['nom_complet'] ?? '—') ?></strong></p>
        </div>
    </div>

    <!-- Stats globales -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="val" style="color:#166534;"><?= number_format($caisse['total_ca'],0,',',' ') ?> FCFA</div>
            <div class="lbl">Chiffre d'affaires</div>
        </div>
        <div class="stat-box">
            <div class="val" style="color:#1d4ed8;"><?= count($transactions) ?></div>
            <div class="lbl">Transactions</div>
        </div>
        <div class="stat-box">
            <div class="val" style="color:#991b1b;"><?= number_format($caisse['total_rembourse'],0,',',' ') ?> FCFA</div>
            <div class="lbl">Remboursements</div>
        </div>
    </div>

    <!-- Ventilation par mode de paiement -->
    <div class="section-title">Ventilation par mode de paiement</div>
    <table>
        <thead>
            <tr>
                <th>Mode de paiement</th>
                <th class="text-right">Montant encaissé</th>
                <th class="text-right">% du CA</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $ventilation = [
                ['label' => '💵 Espèces',      'val' => $caisse['total_especes']],
                ['label' => '📱 Mobile Money',  'val' => $caisse['total_mobile']],
                ['label' => '💳 Carte bancaire','val' => $caisse['total_carte']],
                ['label' => '🎁 Avoir client',  'val' => $caisse['total_avoir']],
            ];
            $caTotal = $caisse['total_ca'] ?: 1;
            foreach ($ventilation as $v):
                if ($v['val'] <= 0) continue;
            ?>
            <tr>
                <td><?= $v['label'] ?></td>
                <td class="text-right"><strong><?= number_format($v['val'],0,',',' ') ?> FCFA</strong></td>
                <td class="text-right"><?= round(($v['val'] / $caTotal) * 100, 1) ?>%</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td>TOTAL CA</td>
                <td class="text-right"><?= number_format($caisse['total_ca'],0,',',' ') ?> FCFA</td>
                <td class="text-right">100%</td>
            </tr>
        </tfoot>
    </table>

    <!-- Contrôle de caisse physique -->
    <?php if ($caisse['fond_reel'] !== null): ?>
    <?php
        $theorique = $caisse['fond_ouverture'] + $caisse['total_especes'] - $caisse['total_rembourse'];
        $ecart     = $caisse['ecart'];
        $positif   = $ecart >= 0;
    ?>
    <div class="section-title">Contrôle de caisse physique (Espèces)</div>
    <div class="ecart-box" style="background:<?= $positif ? '#f0fdf4' : '#fff5f5' ?>;
                                  border:1px solid <?= $positif ? '#bbf7d0' : '#fecaca' ?>;">
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:12px;">
            <span style="color:#6b7280;">Fond d'ouverture</span>
            <span><?= number_format($caisse['fond_ouverture'],0,',',' ') ?> FCFA</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:12px;">
            <span style="color:#6b7280;">+ Espèces encaissées</span>
            <span style="color:#166534;">+<?= number_format($caisse['total_especes'],0,',',' ') ?> FCFA</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:10px;font-size:12px;">
            <span style="color:#6b7280;">— Remboursements espèces</span>
            <span style="color:#dc2626;">-<?= number_format($caisse['total_rembourse'],0,',',' ') ?> FCFA</span>
        </div>
        <div style="display:flex;justify-content:space-between;border-top:1px solid #e0e0e0;
                    padding-top:8px;font-weight:700;font-size:13px;">
            <span>Espèces théoriques</span>
            <span><?= number_format($theorique,0,',',' ') ?> FCFA</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:6px;font-weight:700;font-size:13px;">
            <span>Espèces comptées</span>
            <span><?= number_format($caisse['fond_reel'],0,',',' ') ?> FCFA</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:10px;padding-top:8px;
                    border-top:2px solid <?= $positif ? '#16a34a' : '#dc2626' ?>;
                    font-weight:700;font-size:15px;color:<?= $positif ? '#166534' : '#dc2626' ?>;">
            <span><?= $positif ? '✅ Excédent' : '⚠️ Manquant' ?></span>
            <span><?= ($positif ? '+' : '') . number_format($ecart,0,',',' ') ?> FCFA</span>
        </div>
        <?php if ($caisse['note_cloture']): ?>
        <div style="margin-top:10px;font-size:11px;color:#6b7280;font-style:italic;">
            Note : <?= esc($caisse['note_cloture']) ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Détail des transactions -->
    <div class="section-title">Détail des transactions</div>
    <table>
        <thead>
            <tr>
                <th>Heure</th>
                <th>Client</th>
                <th>Bon</th>
                <th>Type</th>
                <th>Mode</th>
                <th class="text-right">Montant</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $modesCourts = [
                'especes'      => 'Espèces',
                'mobile_money' => 'Mobile',
                'carte'        => 'Carte',
                'avoir'        => 'Avoir',
                'fidelite'     => 'Fidélité',
                'mixte'        => 'Mixte',
            ];
            $totalEnc  = 0;
            $totalRemb = 0;
            foreach ($transactions as $tx):
                if ($tx['type'] === 'encaissement') $totalEnc  += $tx['montant'];
                else                                $totalRemb += $tx['montant'];
            ?>
            <tr>
                <td><?= date('H:i', strtotime($tx['created_at'])) ?></td>
                <td><?= esc($tx['nomclient'] ?? '—') ?></td>
                <td><?= esc($tx['code_commande'] ?? '—') ?></td>
                <td>
                    <span class="badge" style="background:<?= $tx['type']==='encaissement' ? '#dcfce7' : '#fee2e2' ?>;
                                               color:<?= $tx['type']==='encaissement' ? '#166534' : '#991b1b' ?>;">
                        <?= $tx['type'] === 'encaissement' ? 'Encaissement' : 'Remboursement' ?>
                    </span>
                </td>
                <td><?= $modesCourts[$tx['mode_paiement']] ?? $tx['mode_paiement'] ?></td>
                <td class="text-right" style="font-weight:600;color:<?= $tx['type']==='encaissement' ? '#166534' : '#dc2626' ?>;">
                    <?= $tx['type']==='encaissement' ? '+' : '-' ?><?= number_format($tx['montant'],0,',',' ') ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">Net encaissé (CA — Remboursements)</td>
                <td class="text-right" style="color:#166534;">
                    <?= number_format($totalEnc - $totalRemb, 0, ',', ' ') ?> FCFA
                </td>
            </tr>
        </tfoot>
    </table>

    <div style="text-align:center;font-size:10px;color:#999;margin-top:24px;border-top:1px solid #eee;padding-top:12px;">
        Rapport généré le <?= date('d/m/Y à H:i') ?> — Pressing Pro
    </div>

</div>
</body>
</html>
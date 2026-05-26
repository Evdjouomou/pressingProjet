<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon de commande <?= esc($bon['reference']) ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',Arial,sans-serif; font-size:13px; color:#222; background:#fff; }
        .page { max-width:800px; margin:0 auto; padding:32px; }
        .header { display:flex; justify-content:space-between; margin-bottom:28px;
                  padding-bottom:18px; border-bottom:2px solid #1a1a2e; }
        .logo h1 { font-size:20px; font-weight:700; color:#1a1a2e; }
        .logo p  { font-size:11px; color:#666; margin-top:3px; }
        .bon-info { text-align:right; }
        .bon-info h2 { font-size:16px; font-weight:700; color:#1a1a2e; }
        .bon-info p  { font-size:11px; color:#666; margin-top:3px; }
        table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        thead tr { background:#1a1a2e; color:#fff; }
        thead th { padding:10px 12px; text-align:left; font-size:11px; font-weight:600; }
        tbody tr { border-bottom:1px solid #f0f0f0; }
        tbody tr:nth-child(even) { background:#fafafa; }
        tbody td { padding:10px 12px; font-size:12px; }
        tfoot tr { background:#f8fafc; font-weight:700; }
        tfoot td { padding:10px 12px; border-top:2px solid #1a1a2e; }
        .text-right { text-align:right; }
        .footer-sign { display:flex; justify-content:space-between; margin-top:40px; }
        .sign-box { text-align:center; width:200px; }
        .sign-line { border-top:1px solid #999; padding-top:6px; font-size:11px; color:#666; }
        .no-print { margin-bottom:20px; display:flex; gap:10px; }
        @media print { .no-print { display:none; } @page { size:A4; margin:20mm; } }
    </style>
</head>
<body>
<div class="page">
    <div class="no-print">
        <button onclick="window.print()"
                style="padding:8px 20px;background:#1a1a2e;color:#fff;border:none;border-radius:6px;cursor:pointer;">
            🖨️ Imprimer
        </button>
        <button onclick="window.close()"
                style="padding:8px 20px;background:#eee;border:none;border-radius:6px;cursor:pointer;">
            ✕ Fermer
        </button>
    </div>

    <div class="header">
        <div class="logo">
            <h1>PRESSING PRO</h1>
            <p>Tél : +237 6XX XXX XXX</p>
            <p>Yaoundé, Cameroun</p>
        </div>
        <div class="bon-info">
            <h2>BON DE COMMANDE</h2>
            <p><strong><?= esc($bon['reference']) ?></strong></p>
            <p>Date : <?= date('d/m/Y', strtotime($bon['created_at'])) ?></p>
            <p>Émis par : <?= esc($bon['nom_complet'] ?? '—') ?></p>
        </div>
    </div>

    <div style="margin-bottom:20px;padding:14px 18px;background:#f8fafc;border-radius:8px;border:1px solid #e0e0e0;">
        <strong>Fournisseur :</strong> <?= esc($bon['fournisseur']) ?>
        <?php if ($bon['note']): ?>
        <br><span style="color:#666;font-size:12px;">Note : <?= esc($bon['note']) ?></span>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Référence</th>
                <th>Désignation</th>
                <th>Unité</th>
                <th class="text-right">Qté</th>
                <th class="text-right">Prix unit. HT</th>
                <th class="text-right">Total HT</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lignes as $i => $l): ?>
            <tr>
                <td style="color:#999;"><?= $i + 1 ?></td>
                <td><?= esc($l['ref_produit'] ?: '—') ?></td>
                <td><strong><?= esc($l['nom']) ?></strong></td>
                <td><?= esc($l['unite']) ?></td>
                <td class="text-right"><strong><?= $l['quantite'] ?></strong></td>
                <td class="text-right"><?= number_format($l['prix_unitaire'], 0, ',', ' ') ?> FCFA</td>
                <td class="text-right"><strong><?= number_format($l['total_ligne'], 0, ',', ' ') ?> FCFA</strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right">TOTAL HT</td>
                <td class="text-right"><?= number_format($bon['total_ht'], 0, ',', ' ') ?> FCFA</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-sign">
        <div class="sign-box">
            <div style="height:50px;"></div>
            <div class="sign-line">Émis par</div>
        </div>
        <div class="sign-box">
            <div style="height:50px;"></div>
            <div class="sign-line">Approuvé par le gérant</div>
        </div>
        <div class="sign-box">
            <div style="height:50px;"></div>
            <div class="sign-line">Cachet fournisseur</div>
        </div>
    </div>

    <div style="text-align:center;font-size:10px;color:#999;margin-top:28px;
                border-top:1px solid #eee;padding-top:12px;">
        Document généré le <?= date('d/m/Y à H:i') ?> — Pressing Pro
    </div>
</div>
</body>
</html>
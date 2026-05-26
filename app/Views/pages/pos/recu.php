<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu <?= esc($tx['reference'] ?? $tx['id_transaction']) ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Courier New',monospace; font-size:12px; background:#fff; display:flex; justify-content:center; padding:16px; }
        .ticket { width:80mm; }
        .center { text-align:center; }
        .bold   { font-weight:bold; }
        .line   { border-top:1px dashed #999; margin:8px 0; }
        .row    { display:flex; justify-content:space-between; margin:3px 0; }
        .big    { font-size:16px; font-weight:bold; }
        .no-print { margin-bottom:12px; display:flex; gap:8px; }
        @media print {
            body { padding:0; justify-content:flex-start; }
            .no-print { display:none; }
            @page { size:80mm auto; margin:0; }
        }
    </style>
</head>
<body>
<div>
    <div class="no-print">
        <button onclick="window.print()" style="padding:8px 20px;background:#1a1a2e;color:#fff;border:none;border-radius:6px;cursor:pointer;">
            🖨️ Imprimer
        </button>
        <button onclick="window.close()" style="padding:8px 20px;background:#eee;border:none;border-radius:6px;cursor:pointer;">✕</button>
    </div>

    <div class="ticket">
        <div class="center bold" style="font-size:14px;">PRESSING PRO</div>
        <div class="center" style="font-size:10px;">Votre pressing de confiance</div>
        <div class="center" style="font-size:10px;">Tél : +237 6XX XXX XXX</div>
        <div class="line"></div>

        <div class="center bold">REÇU DE PAIEMENT</div>
        <div class="center" style="font-size:10px;"><?= date('d/m/Y à H:i') ?></div>
        <div class="line"></div>

        <?php if ($tx['nomclient']): ?>
        <div class="row"><span>Client :</span><span class="bold"><?= esc($tx['nomclient']) ?></span></div>
        <div class="row"><span>Tél :</span><span><?= esc($tx['telephone'] ?? '') ?></span></div>
        <?php endif; ?>
        <?php if ($tx['code_commande']): ?>
        <div class="row"><span>Bon :</span><span class="bold"><?= esc($tx['code_commande']) ?></span></div>
        <?php endif; ?>
        <div class="line"></div>

        <?php if ($depot): ?>
        <?php foreach ($depot['articles'] as $a): ?>
        <div class="row">
            <span><?= esc($a['nom_libelle']) ?></span>
            <span><?= number_format($a['prix_applique'],0,',',' ') ?></span>
        </div>
        <div style="font-size:10px;color:#666;margin-left:4px;"><?= esc($a['type_prestation'] ?? '') ?></div>
        <?php endforeach; ?>
        <div class="line"></div>
        <?php endif; ?>

        <div class="row"><span>Montant payé :</span><span class="bold big"><?= number_format($tx['montant'],0,',',' ') ?> FCFA</span></div>
        <div class="row"><span>Mode :</span><span><?= ucfirst(str_replace('_',' ',$tx['mode_paiement'])) ?></span></div>
        <?php if ($tx['rendu_monnaie'] > 0): ?>
        <div class="row"><span>Rendu :</span><span class="bold"><?= number_format($tx['rendu_monnaie'],0,',',' ') ?> FCFA</span></div>
        <?php endif; ?>
        <div class="line"></div>

        <div class="center" style="font-size:10px;">Merci de votre confiance !</div>
        <div class="center" style="font-size:10px;">Conservez ce reçu.</div>
        <div class="line"></div>
        <div class="center" style="font-size:9px;">Réf: <?= $tx['id_transaction'] ?></div>
    </div>
</div>
</body>
</html>
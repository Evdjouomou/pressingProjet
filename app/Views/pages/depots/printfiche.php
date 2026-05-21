<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche production <?= esc($depot['code_commande']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; background: #fff; color: #111; }
        .page { max-width: 800px; margin: 0 auto; padding: 24px; }
        .header { background: #1a1a2e; color: #fff; padding: 16px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 16px; letter-spacing: 1px; }
        .header .meta { text-align: right; font-size: 12px; opacity: .85; }
        .header .meta strong { font-size: 15px; display: block; }

        .fiche-article { border: 1.5px solid #1a1a2e; border-radius: 8px; margin-bottom: 16px; overflow: hidden; page-break-inside: avoid; }
        .fiche-head { background: #f0f4ff; padding: 10px 16px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #d0d8f0; }
        .fiche-head .art-num { font-size: 11px; font-weight: 700; color: #1a1a2e; text-transform: uppercase; letter-spacing: .5px; }
        .fiche-head .art-nom { font-size: 14px; font-weight: 700; color: #1a1a2e; }
        .badge-express { background: #dc3545; color: #fff; font-size: 10px; padding: 3px 10px; border-radius: 20px; font-weight: 700; }
        .fiche-body { padding: 12px 16px; }
        .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 10px; }
        .info-item .label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #888; margin-bottom: 2px; }
        .info-item .value { font-size: 13px; font-weight: 600; color: #222; }
        .obs-box { background: #fff8e1; border-left: 3px solid #f0ad4e; padding: 8px 12px; border-radius: 4px; font-size: 12px; color: #7a5c00; margin-top: 8px; }
        .fiche-footer { display: flex; justify-content: space-between; align-items: center; padding: 8px 16px; background: #fafafa; border-top: 1px solid #eee; font-size: 11px; color: #555; }
        .barcode { font-family: monospace; font-size: 13px; font-weight: 700; background: #1a1a2e; color: #fff; padding: 3px 10px; border-radius: 4px; }

        .check-list { display: flex; gap: 20px; flex-wrap: wrap; margin-top: 4px; }
        .check-item { display: flex; align-items: center; gap: 6px; font-size: 11px; }
        .check-box { width: 14px; height: 14px; border: 1.5px solid #333; border-radius: 3px; display: inline-block; }

        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
<div class="page">

    <div class="no-print" style="margin-bottom:16px;display:flex;gap:10px;">
        <button onclick="window.print()" style="padding:8px 20px;background:#1a1a2e;color:#fff;border:none;border-radius:6px;cursor:pointer;">
            🖨️ Imprimer fiche production
        </button>
        <button onclick="window.close()" style="padding:8px 20px;background:#eee;border:none;border-radius:6px;cursor:pointer;">✕ Fermer</button>
    </div>

    <div class="header">
        <div>
            <h1>FICHE DE PRODUCTION</h1>
            <div style="font-size:12px;opacity:.8;margin-top:4px;">
                Client : <strong><?= esc($depot['nomclient']) ?></strong> · <?= esc($depot['telephone']) ?>
            </div>
        </div>
        <div class="meta">
            <strong><?= esc($depot['code_commande']) ?></strong>
            <span>Date : <?= date('d/m/Y') ?></span><br>
            <?php if ($depot['date_livraison_prevue']): ?>
            <span>⏰ Retrait : <?= date('d/m/Y', strtotime($depot['date_livraison_prevue'])) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <?php foreach ($depot['articles'] as $i => $art): ?>
    <div class="fiche-article">
        <div class="fiche-head">
            <div>
                <div class="art-num">Article <?= $i + 1 ?> / <?= $depot['nb_articles'] ?></div>
                <div class="art-nom">
                    <?= esc($art['nom_libelle']) ?>
                    <?php if ($art['options_express']): ?>
                        <span class="badge-express">🚀 EXPRESS</span>
                    <?php endif; ?>
                </div>
            </div>
            <span class="barcode"><?= esc($art['barcode_unique']) ?></span>
        </div>
        <div class="fiche-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="label">Prestation</div>
                    <div class="value"><?= esc($art['type_prestation'] ?? '—') ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Désignation</div>
                    <div class="value"><?= esc($art['designation_libre'] ?: '—') ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Matière</div>
                    <div class="value"><?= esc($art['matiere'] ?: '—') ?></div>
                </div>
            </div>

            <!-- Cases à cocher production -->
            <div class="check-list">
                <div class="check-item"><span class="check-box"></span> Réception vérifiée</div>
                <div class="check-item"><span class="check-box"></span> Triage effectué</div>
                <div class="check-item"><span class="check-box"></span> Traitement appliqué</div>
                <div class="check-item"><span class="check-box"></span> Contrôle qualité</div>
                <div class="check-item"><span class="check-box"></span> Emballage</div>
                <div class="check-item"><span class="check-box"></span> Prêt pour retrait</div>
            </div>

            <?php if ($art['observations']): ?>
            <div class="obs-box">
                ⚠️ <strong>Observations :</strong> <?= esc($art['observations']) ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="fiche-footer">
            <span>Opérateur : ____________________</span>
            <span>Heure traitement : ________</span>
            <span>Signature : ________________</span>
        </div>
    </div>
    <?php endforeach; ?>

</div>
</body>
</html>
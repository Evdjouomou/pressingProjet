<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ticket <?= esc($article['barcode_unique']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #fff; display: flex; justify-content: center; padding: 20px; }

        .ticket {
            width: 80mm;
            border: 2px solid #1a1a2e;
            border-radius: 10px;
            overflow: hidden;
            font-size: 11px;
        }
        .ticket-head {
            background: #1a1a2e;
            color: #fff;
            text-align: center;
            padding: 10px;
        }
        .ticket-head h2 { font-size: 13px; letter-spacing: 1px; }
        .ticket-head p { font-size: 10px; opacity: .8; margin-top: 2px; }
        .ticket-body { padding: 12px; }

        .ticket-row { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px dashed #e0e0e0; }
        .ticket-row:last-child { border-bottom: none; }
        .ticket-row .lbl { color: #888; font-size: 10px; }
        .ticket-row .val { font-weight: 700; font-size: 11px; color: #111; text-align: right; max-width: 55%; }

        .ticket-barcode {
            text-align: center;
            background: #f5f5f5;
            padding: 10px;
            font-family: monospace;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 2px;
            color: #1a1a2e;
            border-top: 1px dashed #ccc;
        }

        .express-banner {
            background: #dc3545;
            color: #fff;
            text-align: center;
            font-weight: 700;
            font-size: 12px;
            padding: 6px;
            letter-spacing: 2px;
        }

        .no-print { margin-bottom: 16px; }

        @media print {
            body { padding: 0; background: #fff; }
            .no-print { display: none; }
            @page { size: 80mm auto; margin: 0; }
        }
    </style>
</head>
<body>
<div>
    <div class="no-print" style="display:flex;gap:8px;">
        <button onclick="window.print()" style="padding:7px 18px;background:#1a1a2e;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:12px;">
            🖨️ Imprimer ticket
        </button>
        <button onclick="window.close()" style="padding:7px 18px;background:#eee;border:none;border-radius:6px;cursor:pointer;font-size:12px;">✕ Fermer</button>
    </div>

    <div class="ticket">

        <?php if ($article['options_express']): ?>
        <div class="express-banner">⚡ EXPRESS ⚡</div>
        <?php endif; ?>

        <div class="ticket-head">
            <h2>PRESSING PRO</h2>
            <p>Ticket de dépôt</p>
        </div>

        <div class="ticket-body">
            <div class="ticket-row">
                <span class="lbl">Bon n°</span>
                <span class="val"><?= esc($article['code_commande']) ?></span>
            </div>
            <div class="ticket-row">
                <span class="lbl">Client</span>
                <span class="val"><?= esc($article['nomclient']) ?></span>
            </div>
            <div class="ticket-row">
                <span class="lbl">Tél.</span>
                <span class="val"><?= esc($article['telephone']) ?></span>
            </div>
            <div class="ticket-row">
                <span class="lbl">Article</span>
                <span class="val"><?= esc($article['nom_libelle']) ?></span>
            </div>
            <div class="ticket-row">
                <span class="lbl">Désignation</span>
                <span class="val"><?= esc($article['designation_libre'] ?: '—') ?></span>
            </div>
            <div class="ticket-row">
                <span class="lbl">Matière</span>
                <span class="val"><?= esc($article['matiere'] ?: '—') ?></span>
            </div>
            <div class="ticket-row">
                <span class="lbl">Prestation</span>
                <span class="val"><?= esc($article['type_prestation'] ?? '—') ?></span>
            </div>
            <?php if ($article['date_livraison_prevue']): ?>
            <div class="ticket-row">
                <span class="lbl">Retrait prévu</span>
                <span class="val"><?= date('d/m/Y', strtotime($article['date_livraison_prevue'])) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($article['observations']): ?>
            <div class="ticket-row" style="background:#fff8e1;">
                <span class="lbl" style="color:#e67e22;">⚠ Obs.</span>
                <span class="val" style="color:#7a5c00;"><?= esc($article['observations']) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="ticket-barcode"><?= esc($article['barcode_unique']) ?></div>
    </div>
</div>
</body>
</html>
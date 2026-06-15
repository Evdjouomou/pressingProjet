<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu — <?= esc($tx['code_commande'] ?? 'Vente') ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            background: #fff;
            color: #222;
        }
        .page  { max-width: 320px; margin: 0 auto; padding: 16px; }
        .titre { text-align:center; font-weight:700; font-size:16px; }
        .sous  { text-align:center; font-size:11px; color:#555; margin-bottom:12px; }
        .sep   { border-top:1px dashed #999; margin:10px 0; }
        .sep2  { border-top:2px solid #222; margin:10px 0; }
        .ligne {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 12px;
        }
        .ligne.big { font-size:15px; font-weight:700; }
        .ligne.muted { color:#777; }
        .text-center { text-align:center; }
        .no-print { margin-bottom:12px; display:flex; gap:8px; }
        @media print {
            .no-print { display:none; }
            @page { size:80mm auto; margin:3mm; }
        }
    </style>
</head>
<body>
<div class="page">

    <div class="no-print">
        <button onclick="window.print()"
                style="padding:5px 14px;background:#1a1a2e;color:#fff;
                       border:none;border-radius:6px;cursor:pointer;font-size:11px;">
            🖨️ Imprimer
        </button>
        <button onclick="window.close()"
                style="padding:5px 14px;background:#eee;border:none;
                       border-radius:6px;cursor:pointer;font-size:11px;">
            ✕ Fermer
        </button>
    </div>

    <div class="titre">PRESSING PRO</div>
    <div class="sous">
        Tél : +237 6XX XXX XXX<br>
        <?= date('d/m/Y H:i', strtotime($tx['created_at'])) ?>
    </div>

    <div class="sep2"></div>

    <?php if ($tx['nomclient']): ?>
    <div class="ligne">
        <span>Client</span>
        <span><?= esc($tx['nomclient']) ?></span>
    </div>
    <?php if ($tx['telephone']): ?>
    <div class="ligne muted">
        <span>Tél.</span>
        <span><?= esc($tx['telephone']) ?></span>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <?php if ($tx['code_commande']): ?>
    <div class="ligne">
        <span>Bon n°</span>
        <span><strong><?= esc($tx['code_commande']) ?></strong></span>
    </div>
    <?php endif; ?>

    <div class="ligne">
        <span>Caissier</span>
        <span><?= esc($tx['caissier'] ?? '—') ?></span>
    </div>

    <div class="sep"></div>

    <!-- Détail paiement -->
    <div class="ligne">
        <span>Type</span>
        <span><?= $tx['type'] === 'encaissement' ? 'Encaissement' : 'Remboursement' ?></span>
    </div>
    <div class="ligne">
        <span>Mode</span>
        <span>
            <?php
            $modesLabels = [
                'especes'      => 'Espèces',
                'mobile_money' => 'Mobile Money',
                'carte'        => 'Carte bancaire',
                'mixte'        => 'Mixte',
            ];
            echo $modesLabels[$tx['mode_paiement']] ?? ucfirst($tx['mode_paiement']);
            ?>
        </span>
    </div>

    <?php if ($tx['mode_paiement'] === 'mixte'): ?>
    <?php if ($tx['montant_especes'] > 0): ?>
    <div class="ligne muted">
        <span>— Espèces</span>
        <span><?= number_format($tx['montant_especes'], 0, ',', ' ') ?> FCFA</span>
    </div>
    <?php endif; ?>
    <?php if ($tx['montant_mobile'] > 0): ?>
    <div class="ligne muted">
        <span>— Mobile</span>
        <span><?= number_format($tx['montant_mobile'], 0, ',', ' ') ?> FCFA</span>
    </div>
    <?php endif; ?>
    <?php if ($tx['montant_carte'] > 0): ?>
    <div class="ligne muted">
        <span>— Carte</span>
        <span><?= number_format($tx['montant_carte'], 0, ',', ' ') ?> FCFA</span>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <div class="sep"></div>

    <?php if (isset($tx['total_ttc']) && $tx['code_commande']): ?>
    <div class="ligne muted">
        <span>Total commande</span>
        <span><?= number_format($tx['total_ttc'], 0, ',', ' ') ?> FCFA</span>
    </div>
    <div class="ligne muted">
        <span>Total encaissé</span>
        <span><?= number_format($tx['total_encaisse'] ?? $tx['montant'], 0, ',', ' ') ?> FCFA</span>
    </div>
    <?php if (isset($tx['reste']) && $tx['reste'] > 0): ?>
    <div class="ligne">
        <span>Reste dû</span>
        <span style="color:#dc2626;font-weight:700;">
            <?= number_format($tx['reste'], 0, ',', ' ') ?> FCFA
        </span>
    </div>
    <?php else: ?>
    <div class="ligne">
        <span>Statut</span>
        <span style="color:#166534;font-weight:700;">✓ Soldé</span>
    </div>
    <?php endif; ?>
    <div class="sep"></div>
    <?php endif; ?>

    <!-- Montant encaissé -->
    <div class="ligne big">
        <span>MONTANT</span>
        <span><?= number_format($tx['montant'], 0, ',', ' ') ?> FCFA</span>
    </div>

    <?php if ($tx['rendu_monnaie'] > 0): ?>
    <div class="sep"></div>
    <div class="ligne">
        <span>Rendu monnaie</span>
        <span><strong><?= number_format($tx['rendu_monnaie'], 0, ',', ' ') ?> FCFA</strong></span>
    </div>
    <?php endif; ?>

    <div class="sep2"></div>

    <div class="text-center" style="font-size:10px;color:#888;margin-top:8px;">
        Merci de votre visite !<br>
        Pressing Pro — <?= date('d/m/Y à H:i') ?>
    </div>

</div>

<script>
    // Impression automatique si paramètre ?auto=1
    const params = new URLSearchParams(window.location.search);
    if (params.get('auto') === '1') window.print();
</script>
</body>
</html>
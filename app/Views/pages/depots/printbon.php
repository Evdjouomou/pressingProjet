<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon de dépôt <?= esc($depot['code_commande']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #222; background: #fff; }
        .page { max-width: 800px; margin: 0 auto; padding: 32px; }

        /* En-tête */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; padding-bottom: 20px; border-bottom: 2px solid #1a1a2e; }
        .logo-zone h1 { font-size: 22px; font-weight: 700; color: #1a1a2e; letter-spacing: 1px; }
        .logo-zone p { font-size: 11px; color: #666; margin-top: 4px; }
        .bon-info { text-align: right; }
        .bon-info .bon-num { font-size: 18px; font-weight: 700; color: #1a1a2e; }
        .bon-info p { font-size: 11px; color: #666; margin-top: 4px; }

        /* Bloc client */
        .bloc-client { display: flex; gap: 24px; margin-bottom: 24px; }
        .bloc { flex: 1; border: 1px solid #e0e0e0; border-radius: 8px; padding: 14px 18px; }
        .bloc-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: #888; margin-bottom: 8px; }
        .bloc strong { font-size: 15px; display: block; margin-bottom: 4px; }
        .bloc p { font-size: 12px; color: #555; margin: 2px 0; }

        /* Tableau articles */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead tr { background: #1a1a2e; color: #fff; }
        thead th { padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 600; letter-spacing: .5px; }
        tbody tr { border-bottom: 1px solid #f0f0f0; }
        tbody tr:nth-child(even) { background: #fafafa; }
        tbody td { padding: 10px 12px; font-size: 12px; vertical-align: top; }
        .text-right { text-align: right; }
        .badge-express { background: #dc3545; color: #fff; font-size: 10px; padding: 2px 7px; border-radius: 20px; font-weight: 600; }

        /* Totaux */
        .totaux { margin-left: auto; width: 280px; margin-bottom: 28px; }
        .totaux-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; border-bottom: 1px solid #f0f0f0; }
        .totaux-row.total { font-weight: 700; font-size: 15px; border-top: 2px solid #1a1a2e; border-bottom: none; padding-top: 10px; color: #1a1a2e; }
        .totaux-row.reste { color: #dc3545; font-weight: 600; }

        /* Pied de page */
        .footer { border-top: 1px solid #e0e0e0; padding-top: 20px; margin-top: 12px; display: flex; justify-content: space-between; align-items: flex-end; }
        .conditions { font-size: 10px; color: #888; max-width: 340px; line-height: 1.6; }
        .signature { text-align: center; }
        .signature-line { border-top: 1px solid #999; width: 180px; margin: 40px auto 4px; }
        .signature p { font-size: 11px; color: #666; }

        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
            .page { padding: 0; }
        }
    </style>
</head>
<body>
<div class="page">

    <!-- Boutons (masqués à l'impression) -->
    <div class="no-print" style="margin-bottom:20px;display:flex;gap:10px;">
        <button onclick="window.print()" style="padding:8px 20px;background:#1a1a2e;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;">
            🖨️ Imprimer
        </button>
        <button onclick="window.close()" style="padding:8px 20px;background:#eee;border:none;border-radius:6px;cursor:pointer;font-size:13px;">
            ✕ Fermer
        </button>
    </div>

    <!-- En-tête -->
    <div class="header">
        <div class="logo-zone">
            <h1>PRESSING PRO</h1>
            <p>Votre pressing de confiance</p>
            <p>Tél : +237 6XX XXX XXX</p>
        </div>
        <div class="bon-info">
            <div class="bon-num">BON DE DÉPÔT</div>
            <p style="font-size:15px;font-weight:600;color:#444;margin-top:4px;"><?= esc($depot['code_commande']) ?></p>
            <p>Émis le : <?= date('d/m/Y') ?></p>
            <?php if ($depot['date_livraison_prevue']): ?>
            <p>Retrait prévu : <strong><?= date('d/m/Y', strtotime($depot['date_livraison_prevue'])) ?></strong></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Client & Infos -->
    <div class="bloc-client">
        <div class="bloc">
            <div class="bloc-title">Client</div>
            <strong><?= esc($depot['nomclient']) ?></strong>
            <p>📞 <?= esc($depot['telephone']) ?></p>
            <?php if ($depot['email'] ?? ''): ?>
            <p>✉ <?= esc($depot['email']) ?></p>
            <?php endif; ?>
        </div>
        <div class="bloc">
            <div class="bloc-title">Récapitulatif</div>
            <strong><?= $depot['nb_articles'] ?> article(s)</strong>
            <p>Mode paiement : <?= esc($depot['mode_paiement'] ?? 'Non précisé') ?></p>
            <p style="margin-top:6px;">Statut :
                <strong><?= ucfirst(str_replace('_', ' ', $depot['statut_global'])) ?></strong>
            </p>
        </div>
    </div>

    <!-- Tableau articles -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Article</th>
                <th>Désignation</th>
                <th>Prestation</th>
                <th>Obs.</th>
                <th class="text-right">Prix</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($depot['articles'] as $i => $art): ?>
            <tr>
                <td style="color:#999;"><?= $i + 1 ?></td>
                <td>
                    <strong><?= esc($art['nom_libelle']) ?></strong>
                    <?php if ($art['options_express']): ?>
                        <span class="badge-express">EXPRESS</span>
                    <?php endif; ?>
                </td>
                <td style="color:#555;"><?= esc($art['designation_libre']) ?><?= $art['matiere'] ? ' · ' . esc($art['matiere']) : '' ?></td>
                <td><?= esc($art['type_prestation'] ?? '—') ?></td>
                <td style="color:#e67e22;font-size:11px;"><?= esc($art['observations'] ?? '') ?></td>
                <td class="text-right"><strong><?= number_format($art['prix_applique'] ?? 0, 0, ',', ' ') ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totaux -->
    <div class="totaux">
        <div class="totaux-row">
            <span>Sous-total</span>
            <span><?= number_format($depot['total_ttc'], 0, ',', ' ') ?> FCFA</span>
        </div>
        <div class="totaux-row">
            <span>Acompte versé</span>
            <span>- <?= number_format($depot['acompte_verse'], 0, ',', ' ') ?> FCFA</span>
        </div>
        <div class="totaux-row total">
            <span>TOTAL TTC</span>
            <span><?= number_format($depot['total_ttc'], 0, ',', ' ') ?> FCFA</span>
        </div>
        <?php $reste = max(0, $depot['total_ttc'] - $depot['acompte_verse']); ?>
        <?php if ($reste > 0): ?>
        <div class="totaux-row reste">
            <span>Reste à payer</span>
            <span><?= number_format($reste, 0, ',', ' ') ?> FCFA</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <div class="conditions">
            <strong>Conditions :</strong><br>
            Les articles non retirés dans les 30 jours suivant la date de retrait prévue
            pourront être cédés. Nous déclinons toute responsabilité en cas de perte
            de ce bon. Merci de votre confiance.
        </div>
        <div class="signature">
            <div class="signature-line"></div>
            <p>Signature du client</p>
        </div>
    </div>

</div>
</body>
</html>
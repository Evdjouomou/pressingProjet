<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche livraison <?= esc($liv['code_livraison']) ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',Arial,sans-serif; font-size:13px; color:#222; background:#fff; }
        .page { max-width:800px; margin:0 auto; padding:32px; }
        .header { display:flex; justify-content:space-between; align-items:flex-start;
                  margin-bottom:24px; padding-bottom:18px; border-bottom:2px solid #1a1a2e; }
        .logo h1 { font-size:20px; font-weight:700; color:#1a1a2e; }
        .logo p  { font-size:11px; color:#666; }
        .fiche-title { text-align:right; }
        .fiche-title h2 { font-size:18px; font-weight:700; color:#1a1a2e; }
        .fiche-title .ref { font-size:22px; font-weight:900; color:#3b82f6; }

        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px; }
        .info-box { border:1px solid #e0e0e0; border-radius:10px; padding:16px; }
        .info-box .titre { font-size:10px; font-weight:700; text-transform:uppercase;
                           letter-spacing:.8px; color:#888; margin-bottom:8px; }
        .info-box .valeur { font-size:15px; font-weight:700; color:#1a1a2e; }
        .info-box .sous { font-size:12px; color:#555; margin-top:4px; }

        .adresse-box { background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px;
                       padding:16px; margin-bottom:24px; }
        .adresse-box .titre { font-size:10px; font-weight:700; text-transform:uppercase;
                              letter-spacing:.8px; color:#1d4ed8; margin-bottom:6px; }
        .adresse-box .adresse { font-size:16px; font-weight:700; color:#1a1a2e; }

        table { width:100%; border-collapse:collapse; margin-bottom:24px; }
        thead tr { background:#1a1a2e; color:#fff; }
        thead th { padding:10px 12px; text-align:left; font-size:11px; }
        tbody tr { border-bottom:1px solid #f0f0f0; }
        tbody td { padding:10px 12px; font-size:12px; }

        .checklist { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:24px; }
        .check-item { display:flex; align-items:center; gap:10px; padding:10px 14px;
                      border:1px solid #e0e0e0; border-radius:8px; }
        .check-box  { width:18px; height:18px; border:2px solid #333; border-radius:4px; flex-shrink:0; }

        .signatures { display:flex; justify-content:space-between; margin-top:32px; }
        .sign-box { text-align:center; width:220px; }
        .sign-line { border-top:1px solid #999; padding-top:6px; margin-top:50px; font-size:11px; color:#666; }

        .no-print { margin-bottom:20px; display:flex; gap:10px; }
        @media print {
            .no-print { display:none; }
            @page { size:A4; margin:15mm; }
        }
    </style>
</head>
<body>
<div class="page">

    <div class="no-print">
        <button onclick="window.print()"
                style="padding:8px 20px;background:#1a1a2e;color:#fff;border:none;border-radius:6px;cursor:pointer;">
            🖨️ Imprimer la fiche
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
            <p>Tél : +237 6XX XXX XXX</p>
            <p><?= esc($liv['adresse_boutique'] ?? 'Yaoundé, Cameroun') ?></p>
        </div>
        <div class="fiche-title">
            <h2>FICHE DE LIVRAISON</h2>
            <div class="ref"><?= esc($liv['code_livraison']) ?></div>
            <p style="font-size:11px;color:#666;">
                Émise le <?= date('d/m/Y à H:i') ?>
            </p>
        </div>
    </div>

    <!-- Infos principales -->
    <div class="info-grid">
        <div class="info-box">
            <div class="titre">Client</div>
            <div class="valeur"><?= esc($liv['nomclient']) ?></div>
            <div class="sous">📞 <?= esc($liv['telephone']) ?></div>
            <?php if ($liv['email']): ?>
            <div class="sous">✉ <?= esc($liv['email']) ?></div>
            <?php endif; ?>
        </div>
        <div class="info-box">
            <div class="titre">Bon de commande</div>
            <div class="valeur"><?= esc($liv['code_commande']) ?></div>
            <div class="sous">Total : <?= number_format($liv['total_ttc'], 0, ',', ' ') ?> FCFA</div>
            <?php $reste = max(0, $liv['total_ttc'] - $liv['acompte_verse']); ?>
            <?php if ($reste > 0): ?>
            <div class="sous" style="color:#dc2626;font-weight:600;">
                À encaisser : <?= number_format($reste, 0, ',', ' ') ?> FCFA
            </div>
            <?php else: ?>
            <div class="sous" style="color:#166534;">✓ Entièrement payé</div>
            <?php endif; ?>
        </div>
        <div class="info-box">
            <div class="titre">Livreur assigné</div>
            <div class="valeur"><?= esc($liv['livreur_nom'] ?? 'Non assigné') ?></div>
            <?php if ($liv['livreur_tel']): ?>
            <div class="sous">📞 <?= esc($liv['livreur_tel']) ?></div>
            <?php endif; ?>
        </div>
        <div class="info-box">
            <div class="titre">Date & Heure de livraison</div>
            <div class="valeur">
                <?= $liv['date_livraison']
                    ? date('d/m/Y', strtotime($liv['date_livraison']))
                    : 'À confirmer' ?>
            </div>
            <?php if ($liv['heure_livraison']): ?>
            <div class="sous">⏰ <?= substr($liv['heure_livraison'], 0, 5) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Adresse de livraison -->
    <div class="adresse-box">
        <div class="titre">📍 Adresse de livraison</div>
        <div class="adresse"><?= esc($liv['adresse_livraison']) ?></div>
        <?php if ($liv['note_client']): ?>
        <div style="margin-top:8px;font-size:12px;color:#1d4ed8;">
            <strong>Note :</strong> <?= esc($liv['note_client']) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Articles -->
    <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;
              color:#888;margin-bottom:10px;">Articles à livrer</p>
    <table>
        <thead>
            <tr>
                <th>Article</th>
                <th>Désignation</th>
                <th>Prestation</th>
                <th style="text-align:right;">Prix</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $art): ?>
            <tr>
                <td>
                    <strong><?= esc($art['nom_libelle']) ?></strong>
                    <?php if ($art['options_express']): ?>
                        <span style="background:#dc2626;color:#fff;font-size:9px;
                                     padding:1px 5px;border-radius:10px;">EXPRESS</span>
                    <?php endif; ?>
                </td>
                <td style="color:#555;"><?= esc($art['designation_libre'] ?: '—') ?></td>
                <td><?= esc($art['type_prestation'] ?? '—') ?></td>
                <td style="text-align:right;font-weight:600;">
                    <?= number_format($art['prix_applique'] ?? 0, 0, ',', ' ') ?> FCFA
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Checklist livreur -->
    <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;
              color:#888;margin-bottom:10px;">Checklist avant départ</p>
    <div class="checklist">
        <div class="check-item"><span class="check-box"></span> Articles comptés et vérifiés</div>
        <div class="check-item"><span class="check-box"></span> Bon de livraison signé</div>
        <div class="check-item"><span class="check-box"></span> Adresse confirmée avec le client</div>
        <div class="check-item"><span class="check-box"></span> Montant à encaisser noté</div>
        <?php if ($liv['montant_livraison'] > 0): ?>
        <div class="check-item">
            <span class="check-box"></span>
            Frais livraison encaissés : <?= number_format($liv['montant_livraison'],0,',',' ') ?> FCFA
        </div>
        <?php endif; ?>
        <?php if ($reste > 0): ?>
        <div class="check-item" style="border-color:#dc2626;">
            <span class="check-box" style="border-color:#dc2626;"></span>
            <span style="color:#dc2626;">Solde à encaisser : <?= number_format($reste,0,',',' ') ?> FCFA</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <div class="sign-box">
            <div class="sign-line">Signature du livreur</div>
        </div>
        <div class="sign-box">
            <div class="sign-line">Signature du client (réception)</div>
        </div>
        <div class="sign-box">
            <div class="sign-line">Cachet / Responsable</div>
        </div>
    </div>

    <div style="text-align:center;font-size:9px;color:#999;margin-top:24px;
                border-top:1px solid #eee;padding-top:12px;">
        Pressing Pro — Fiche générée le <?= date('d/m/Y à H:i') ?> — <?= esc($liv['code_livraison']) ?>
    </div>

</div>
</body>
</html>
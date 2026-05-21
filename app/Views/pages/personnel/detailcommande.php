<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="order-detail-container">
    <div class="order-header">
        <div class="order-title">
            <button class="btn-back"><i class="bi bi-arrow-left"></i></button>
            <h1>Détail Commande - <span>CMD-2024-0152</span></h1>
        </div>
        <div class="header-actions">
            <button class="btn-outline" style="color: black;"><i class="bi bi-printer"></i> Imprimer</button>
            <button class="btn-primary"><i class="bi bi-pencil"></i> Modifier</button>
        </div>
    </div>

    <div class="order-tabs">
        <button class="tab-item active">Informations</button>
        <button class="tab-item">Articles (4)</button>
        <button class="tab-item">Paiements (1)</button>
        <button class="tab-item">Historique</button>
    </div>

    <div class="info-grid">
        <div class="info-card">
            <h3>Informations Client</h3>
            <div class="client-profile-mini">
                <img src="<?= base_url('img/avatar.jpg') ?>" alt="Avatar" class="mini-avatar">
                <div class="client-text">
                    <strong>Marie Dubois</strong>
                    <p><i class="bi bi-telephone"></i> 06 12 34 56 78</p>
                    <p><i class="bi bi-envelope"></i> marie.dubois@email.com</p>
                    <span class="badge-status-green">Cliente régulière</span>
                </div>
            </div>
        </div>

        <div class="info-card">
            <h3>Informations Commande</h3>
            <div class="detail-row"><span>Date de dépôt</span> <strong>15/05/2024 10:30</strong></div>
            <div class="detail-row"><span>Date de livraison promise</span> <strong>18/05/2024</strong></div>
            <div class="detail-row"><span>Statut actuel</span> <span class="badge-ready">Prête à retirer</span></div>
            <div class="detail-row"><span>Employé</span> <strong>Thomas Leroy</strong></div>
        </div>

        <div class="info-card summary-card">
            <h3>Résumé</h3>
            <div class="detail-row"><span>Sous-total</span> <strong>40,00 €</strong></div>
            <div class="detail-row"><span>TVA (20%)</span> <strong>5,00 €</strong></div>
            <div class="detail-row"><span>Acompte payé</span> <strong class="text-danger">-10,00 €</strong></div>
            <hr>
            <div class="detail-row total"><span>Total</span> <strong>45,00 €</strong></div>
            <div class="detail-row balance"><span>Solde restant</span> <strong class="text-success">0,00 €</strong></div>
        </div>
    </div>

    <div class="articles-section">
        <h3>Articles de la Commande</h3>
        <table class="detail-table">
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Traitement</th>
                    <th>Prix Unitaire</th>
                    <th>Qté</th>
                    <th>Total</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Chemise blanche</td>
                    <td>Lavage + Repassage</td>
                    <td>3,50 €</td>
                    <td>2</td>
                    <td>7,00 €</td>
                    <td><span class="badge-ready">Prête</span></td>
                </tr>
                <tr>
                    <td>Robe de soirée</td>
                    <td>Nettoyage à sec</td>
                    <td>15,00 €</td>
                    <td>1</td>
                    <td>15,00 €</td>
                    <td><span class="badge-ready">Prête</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
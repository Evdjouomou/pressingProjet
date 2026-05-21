<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="top-bar">
    <form class="search-form" action="">
        <input type="text" placeholder="Rechercher...">
        <button type="submit"><i class="bi bi-search"></i></button>
    </form>
</div>
    
<div class="card shadow-sm">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>Id</th>
                <th>Nom Client</th>
                <th>Type Abonnement</th>
                <th>Telephone</th>
                <th>Adresse</th>
                <th>Pieces Restantes</th>
                <th>Date debut</th>
                <th>Date fin</th>
                <th>status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($clients as $client) : ?>
                <tr>
                    <td><?= esc($client['id_souscription']) ?></td>
                    <td><?= esc($client['nomclient']) ?></td>
                    <td><?= esc($client['libelle']) ?></td>
                    <td><?= esc($client['telephone']) ?></td>
                    <td><?= esc($client['adresse']) ?></td>
                    <td><?= esc($client['pieces_restantes']) ?></td>
                    <td><?= esc($client['date_achat']) ?></td>
                    <td><?= esc($client['date_expiration']) ?></td>
                    <td><?= esc($client['statut']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>
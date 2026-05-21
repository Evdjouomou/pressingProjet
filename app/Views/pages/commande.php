<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="top-bar">
    <form class="search-form" action="">
        <input type="text" placeholder="Rechercher...">
        <button type="submit"><i class="bi bi-search"></i></button>
    </form>

    <p class="new-order">
        <a href=""><i class="bi bi-plus"></i> Nouvelle Commande</a>
    </p>
</div>

<div class="filter-bar">
    <form action="" class="select-form">
        <div class="filter-group">
            <label>Statut</label>
            <select>
                <option value="">Tous</option>
                <option>En cours</option>
                <option>Prêt</option>
                <option>Livré</option>
            </select>
        </div>

        <div class="filter-group">
            <label>Période</label>
            <select>
                <option value="">Aujourd'hui</option>
                <option>Cette semaine</option>
                <option>Ce mois</option>
            </select>
        </div>
    </form>
</div>

<div class="dashboard-overview">
    <div class="first">
        <p>Total Commandes</p>
        <p>154</p>
        <p>+12% vs mois dernier</p>
    </div>
    <div class="first">
        <p>En Cours</p>
        <p>37</p>
        <p>24% du Total</p>
    </div>
    <div class="first">
        <p>Pretes a Retirer</p>
        <p>18</p>
        <p>depot livré</p>
    </div>
    <div class="first">
        <p>En Retard</p>
        <p>7</p>
        <p>5% du Total</p>
    </div>
</div>
    
<div class="card shadow-sm">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>N Commande </th>
                <th>Client</th>
                <th>Articles</th>
                <th>Date de Depot</th>
                <th>Date de Livraison</th>
                <th>Statut</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>REF001</td>
                <td>John Doe</td>
                <td>5</td>
                <td>2023-10-01</td>
                <td>2023-10-15</td>
                <td>En attente</td>
                <td>150.00$</td>
            </tr>
            <tr>
                <td>REF001</td>
                <td>John Doe</td>
                <td>5</td>
                <td>2023-10-01</td>
                <td>2023-10-15</td>
                <td>En attente</td>
                <td>150.00$</td>
            </tr>
            <tr>
                <td>REF001</td>
                <td>John Doe</td>
                <td>5</td>
                <td>2023-10-01</td>
                <td>2023-10-15</td>
                <td>En attente</td>
                <td>150.00$</td>
            </tr>
            <tr>
                <td>REF001</td>
                <td>John Doe</td>
                <td>5</td>
                <td>2023-10-01</td>
                <td>2023-10-15</td>
                <td>En attente</td>
                <td>150.00$</td>
            </tr>
            <tr>
                <td>REF001</td>
                <td>John Doe</td>
                <td>5</td>
                <td>2023-10-01</td>
                <td>2023-10-15</td>
                <td>En attente</td>
                <td>150.00$</td>
            </tr>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>
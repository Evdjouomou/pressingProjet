<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Pressing</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;450;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url('/css/login.css') ?>">
</head>
<body>

<div class="background">
    <div class="overlay"></div>
</div>

<div class="login-container">
    <div class="login-card">

        <div class="logo">
            <img src="<?=  base_url('img/logo/logo.png') ?>" alt="" style="border-radius: 80px;">
        </div>

        <h2>Connexion</h2>
        <p>Bienvenue sur GEST PRESSING</p>

        <form id="loginForm" action="" method="post">

            <div class="input-group">
                <span><i class="bi bi-envelope-at-fill"></i></span>
                <input type="text" name="matricule" id="matricule" placeholder="Matricule" required>
            </div>

            <div class="input-group">
                <span><i class="bi bi-lock-fill"></i></span>
                <input type="password" name="password" id="password" placeholder="Mot de passe" required>
            </div>

            <button type="submit">Se connecter</button>

            <div class="options">
                <label>
                    <input type="checkbox"> Se souvenir de moi
                </label>
                <a href="#">Mot de passe oublié ?</a>
            </div>

        </form>

    </div>
</div>

<script src="<?= base_url('/js/login.js') ?>"></script>
</body>
</html>
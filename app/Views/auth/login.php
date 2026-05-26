<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - GEST PRESSING</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* Styles de base optimisés pour l'image de fond */
        body {
            font-family: Arial, sans-serif;
            /* Intégration de ton image de fond depuis public/imag/fond.jpg */
            background-image: url("<?= base_url('img/fond.jpg') ?>");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 15px;
        }
        
        .login-card {
            /* Effet moderne de verre transparent pour faire ressortir le fond tout en restant lisible */
            background: rgba(255, 255, 255, 0.92); 
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        
        .logo img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .input-group {
            position: relative;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .input-group span {
            position: absolute;
            left: 12px;
            color: #888;
            z-index: 5;
        }
        
        .input-group input {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            outline: none;
            background-color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }
        
        .input-group input:focus {
            border-color: #4B6BFB;
            background-color: #fff;
            box-shadow: 0 0 5px rgba(75, 107, 251, 0.2);
        }
        
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #4B6BFB;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.2s ease;
        }
        
        button[type="submit"]:hover {
            background: #3B5BLB;
        }
        
        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            font-size: 13px;
            color: #333;
        }
        
        .options a {
            color: #4B6BFB;
            text-decoration: none;
            font-weight: 500;
        }
        
        .options a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 10px 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        
        <div class="logo">
            <img src="<?= base_url('img/logo/logo.png') ?>" alt="Logo Pressing">
        </div>

        <h2 style="margin-top: 0; color: #222;">Connexion</h2>
        <p style="color: #555; margin-bottom: 25px; font-size: 14px;">Bienvenue sur GEST PRESSING</p>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> <span><?= session()->getFlashdata('error') ?></span>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('info')): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle-fill"></i> <span><?= session()->getFlashdata('info') ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('login') ?>" method="POST">
            
            <?= csrf_field() ?>

            <div class="input-group">
                <span><i class="bi bi-envelope-at-fill"></i></span>
                <input type="text" name="identifiant" id="matricule" placeholder="Matricule ou E-mail" required value="<?= old('identifiant') ?>">
            </div>

            <div class="input-group">
                <span><i class="bi bi-lock-fill"></i></span>
                <input type="password" name="password" id="password" placeholder="Mot de passe" required>
            </div>

            <button type="submit">Se connecter</button>

            <div class="options">
                <label style="cursor: pointer; display: flex; align-items: center; gap: 4px;">
                    <input type="checkbox" name="remember"> Se souvenir de moi
                </label>
                <a href="#">Mot de passe oublié ?</a>
            </div>

        </form>

    </div>
</div>

</body>
</html>
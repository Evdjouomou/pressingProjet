<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Premier accès - Choisir un mot de passe</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm mt-5">
                <div class="card-body">
                    <h4 class="card-title text-center text-primary mb-4">🔒 Sécurisez votre compte</h4>
                    <p class="text-muted small text-center">C'est votre première connexion. Veuillez définir votre mot de passe définitif.</p>
                    
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                    <?php endif; ?>

                    <form action="<?= site_url('personnel/update-premier-password') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Nouveau mot de passe</label>
                            <input type="password" name="new_password" class="form-control" required placeholder="Minimum 6 caractères">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmez le mot de passe</label>
                            <input type="password" name="confirm_password" class="form-control" required placeholder="Répétez le mot de passe">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Enregistrer et Continuer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
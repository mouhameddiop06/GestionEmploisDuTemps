<!DOCTYPE html>
<html lang="fr">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion et Inscription</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('images/uacdd.jpeg'); /* Remplacez 'images/sectioninfos.jpg' par le chemin de votre image de fond */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Arial', sans-serif;
            flex-direction: column;
        }

        .navbar {
            width: 100%;
            background-color: #003366;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 0;
            box-sizing: border-box;
            position: absolute;
            top: 0;
            left: 0;
        }

        .navbar img {
            height: 50px;
        }

        .navbar h1 {
            color: white;
            font-size: 1.5rem;
            margin: 0;
            flex-grow: 1;
            text-align: center;
        }

        .auth-container {
            background-color: rgba(255, 255, 255, 0.8); /* Transparence pour laisser apparaître l'image de fond */
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 800px;
            display: flex;
            margin-top: 60px;
        }

        .auth-section {
            padding: 30px;
            width: 50%;
        }

        .auth-section h3 {
            margin-bottom: 20px;
            color: #333;
            font-weight: bold;
        }

        .login-section {
            background-color: transparent; /* Transparence pour la section login */
        }

        .register-section {
            background-color: transparent; /* Transparence pour la section inscription */
            border-left: 1px solid rgba(255, 255, 255, 0.7); /* Transparence pour la bordure */
        }

        .form-group label {
            font-weight: 600;
            color: #555;
        }

        .btn-custom {
            padding: 10px 20px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-login {
            background-color: #4267B2;
            border-color: #4267B2;
        }

        .btn-register {
            background-color: #42B72A;
            border-color: #42B72A;
        }

        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <!-- Menu de navigation -->
    <div class="navbar">
        <a href="Authentification.php"><img src="images/logoUcadLT3.png" alt="Logo UCAD"></a>
        <h1>PLATEFORME DE GESTION DES EDT ET DES SALLES DE LA SECTION INFORMATIQUE</h1>
    </div>

    <div class="auth-container">
        <!-- Section de connexion -->
        <div class="auth-section login-section">
            <h3 class="text-center">Connexion</h3>
            <?php if (isset($_GET['error'])): ?>
                <div class='alert alert-danger'><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="role">Rôle</label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="Professeur">Professeur</option>
                        <option value="Enseignant">Enseignant</option>
                        <option value="Etudiant">Etudiant</option>
                        <option value="Gestionnaire">Gestionnaire</option>
                        <option value="Administrateur">Administrateur</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-custom btn-login">Se connecter</button>
            </form>
        </div>

        <!-- Section d'inscription étudiant -->
        <div class="auth-section register-section">
            <h3 class="text-center">Inscription Étudiant</h3>
            <form method="POST" action="inscription_etudiant.php">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" required>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" class="form-control" id="prenom" name="prenom" required>
                </div>
                <div class="form-group">
                    <label for="email_inscription">Email</label>
                    <input type="email" class="form-control" id="email_inscription" name="email_inscription" required>
                </div>
                <div class="form-group">
                    <label for="password_inscription">Mot de passe</label>
                    <input type="password" class="form-control" id="password_inscription" name="password_inscription" required>
                </div>
                <button type="submit" class="btn btn-success btn-block btn-custom btn-register">S'inscrire</button>
            </form>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</body>
</html>

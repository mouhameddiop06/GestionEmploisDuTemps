<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    header("Location: ../authentification.php");
    exit();
}

include '../config.php';

// Obtenir les informations du gestionnaire à modifier
if (isset($_GET['id'])) {
    $gestionnaire_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE id = ? AND role = 'Gestionnaire'");
    $stmt->execute([$gestionnaire_id]);
    $gestionnaire = $stmt->fetch();

    if (!$gestionnaire) {
        echo "Gestionnaire non trouvé.";
        exit();
    }
} else {
    echo "ID de gestionnaire non spécifié.";
    exit();
}

// Gérer la modification des informations du gestionnaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];

    $stmt = $pdo->prepare("UPDATE Utilisateur SET nom = ?, prenom = ?, email = ?, mot_de_passe = ? WHERE id = ? AND role = 'Gestionnaire'");
    $stmt->execute([$nom, $prenom, $email, $mot_de_passe, $gestionnaire_id]);

    $message = "Informations du gestionnaire mises à jour avec succès.";
    header("Location: gestion_gestionnaires.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Gestionnaire</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #84fab0 0%, #8fd3f4 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .card-header {
            background: linear-gradient(45deg, #5e72e4, #825ee4);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            font-weight: bold;
        }
        .btn-custom {
            background: linear-gradient(45deg, #5e72e4, #825ee4);
            border: none;
            color: white;
        }
        .btn-custom:hover {
            background: linear-gradient(45deg, #4a5cd1, #7046d1);
            color: white;
        }
        .module-form {
            background-color: #f8f9fe;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .input-group-text {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary"><i class="fas fa-edit me-3"></i>Modifier Gestionnaire</h1>
        </div>

        <div class="card module-form">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-user-edit me-2"></i>Modifier les Informations du Gestionnaire</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="modifier_gestionnaire.php?id=<?= $gestionnaire_id ?>">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($gestionnaire['nom']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="prenom" class="form-label">Prénom</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($gestionnaire['prenom']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($gestionnaire['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="mot_de_passe" class="form-label">Mot de Passe</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" value="<?= htmlspecialchars($gestionnaire['mot_de_passe']) ?>" required>
                            <span class="input-group-text" id="togglePassword"><i class="fas fa-eye"></i></span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-custom w-100"><i class="fas fa-save me-2"></i>Enregistrer les Modifications</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#mot_de_passe');

        togglePassword.addEventListener('click', function (e) {
            // Toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            // Toggle the eye icon
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>

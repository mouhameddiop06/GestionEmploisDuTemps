<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un enseignant
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Enseignant') {
    header("Location: ../authentification.php");
    exit();
}

include '../config.php';

// Obtenir l'ID de l'enseignant à partir de la session
$enseignant_id = $_SESSION['user_id'];

// Mettre à jour les informations du profil
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];

    if (!empty($mot_de_passe)) {
        // Mettre à jour le mot de passe
        $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE Utilisateur SET nom = ?, prenom = ?, email = ?, mot_de_passe = ? WHERE id = ?");
        $stmt->execute([$nom, $prenom, $email, $hashed_password, $enseignant_id]);
    } else {
        // Mettre à jour sans changer le mot de passe
        $stmt = $pdo->prepare("UPDATE Utilisateur SET nom = ?, prenom = ?, email = ? WHERE id = ?");
        $stmt->execute([$nom, $prenom, $email, $enseignant_id]);
    }

    // Mettre à jour les informations de session
    $_SESSION['user_name'] = $nom . ' ' . $prenom;
}

// Obtenir les informations actuelles de l'enseignant
$stmt = $pdo->prepare("SELECT nom, prenom, email FROM Utilisateur WHERE id = ?");
$stmt->execute([$enseignant_id]);
$enseignant = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer le Profil</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
            max-width: 600px;
        }
        .form-group label {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center my-4">Gérer le Profil</h2>
        
        <form method="POST" action="gestion_profil.php">
            <div class="form-group">
                <label for="nom">Nom :</label>
                <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($enseignant['nom']) ?>" required>
            </div>
            <div class="form-group">
                <label for="prenom">Prénom :</label>
                <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($enseignant['prenom']) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($enseignant['email']) ?>" required>
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Nouveau mot de passe :</label>
                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" placeholder="Laissez vide si vous ne souhaitez pas changer le mot de passe">
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary btn-block">Mettre à jour</button>
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

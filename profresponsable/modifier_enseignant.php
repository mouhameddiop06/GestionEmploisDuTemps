<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un professeur responsable
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Professeur') {
    header("Location: ../authentification.php");
    exit();
}

include '../config.php';

// Obtenir l'ID de l'enseignant à modifier à partir de la requête GET
if (!isset($_GET['id'])) {
    header("Location: gestion_enseignants.php");
    exit();
}

$enseignant_id = $_GET['id'];

// Obtenir les informations de l'enseignant
$stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE id = ? AND role = 'Enseignant'");
$stmt->execute([$enseignant_id]);
$enseignant = $stmt->fetch();

if (!$enseignant) {
    echo "Enseignant non trouvé.";
    exit();
}

// Mettre à jour les informations de l'enseignant
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier_enseignant'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];

    $stmt = $pdo->prepare("UPDATE Utilisateur SET nom = ?, prenom = ?, email = ?, mot_de_passe = ? WHERE id = ?");
    $stmt->execute([$nom, $prenom, $email, $mot_de_passe, $enseignant_id]);

    header("Location: gestion_enseignants.php?message=" . urlencode("Enseignant modifié avec succès."));
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'Enseignant</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
            max-width: 600px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center my-4">Modifier l'Enseignant</h2>

        <form method="POST" action="modifier_enseignant.php?id=<?= htmlspecialchars($enseignant_id) ?>">
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
                <label for="mot_de_passe">Mot de passe :</label>
                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" value="<?= htmlspecialchars($enseignant['mot_de_passe']) ?>" required>
            </div>
            <button type="submit" name="modifier_enseignant" class="btn btn-primary btn-block">Modifier Enseignant</button>
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

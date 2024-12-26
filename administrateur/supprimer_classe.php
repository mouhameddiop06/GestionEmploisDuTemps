<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    header("Location: ../authentification.php");
    exit();
}

include '../config.php';

// Obtenir l'ID de la classe à supprimer
$classe_id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$classe_id) {
    header("Location: gestion_classes.php");
    exit();
}

// Supprimer la classe si la suppression est confirmée
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_delete'])) {
    // Supprimer les cours associés à la classe
    $stmt = $pdo->prepare("DELETE FROM Cours WHERE module_id IN (SELECT id FROM Module WHERE classe_id = ?)");
    $stmt->execute([$classe_id]);

    // Supprimer les modules associés à la classe
    $stmt = $pdo->prepare("DELETE FROM Module WHERE classe_id = ?");
    $stmt->execute([$classe_id]);

    // Supprimer la classe
    $stmt = $pdo->prepare("DELETE FROM Classe WHERE id = ?");
    $stmt->execute([$classe_id]);

    header("Location: gestion_classes.php");
    exit();
}

// Obtenir les informations de la classe à supprimer
$stmt = $pdo->prepare("SELECT nom FROM Classe WHERE id = ?");
$stmt->execute([$classe_id]);
$classe = $stmt->fetch();
if (!$classe) {
    header("Location: gestion_classes.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer Classe</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #dc3545;
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px;
        }
        .btn-danger {
            background-color: #dc3545;
            border: none;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Supprimer Classe</h3>
            </div>
            <div class="card-body">
                <p>Êtes-vous sûr de vouloir supprimer la classe suivante ?</p>
                <p><strong><?= htmlspecialchars($classe['nom']) ?></strong></p>
                <form method="POST" action="supprimer_classe.php?id=<?= $classe_id ?>">
                    <button type="submit" name="confirm_delete" class="btn btn-danger">Supprimer</button>
                    <a href="gestion_classes.php" class="btn btn-secondary">Annuler</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

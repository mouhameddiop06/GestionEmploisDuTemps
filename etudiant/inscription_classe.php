<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un étudiant
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Etudiant') {
    header("Location: ../authentification.php");
    exit();
}

include '../config.php';

// Obtenir l'ID de l'étudiant à partir de la session
$etudiant_id = $_SESSION['user_id'];

// Inscrire l'étudiant à une classe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['inscrire_classe'])) {
    $classe_id = $_POST['classe_id'];

    $stmt = $pdo->prepare("INSERT INTO Inscription (etudiant_id, classe_id) VALUES (?, ?)");
    $stmt->execute([$etudiant_id, $classe_id]);

    $_SESSION['classe_id'] = $classe_id;

    header("Location: dashboardetudiant.php");
    exit();
}

// Obtenir la liste des classes disponibles
$stmt = $pdo->query("SELECT id, nom FROM Classe");
$classes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription aux Classes</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            max-width: 400px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center my-4">Inscription aux Classes</h2>

        <form method="POST" action="inscription_classe.php">
            <div class="form-group">
                <label for="classe_id">Choisir une classe :</label>
                <select class="form-control" id="classe_id" name="classe_id" required>
                    <?php foreach ($classes as $classe): ?>
                        <option value="<?= $classe['id'] ?>"><?= htmlspecialchars($classe['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="inscrire_classe" class="btn btn-primary btn-block">S'inscrire</button>
        </form>
    </div>
</body>
</html>

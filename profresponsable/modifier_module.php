<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un professeur responsable
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Professeur') {
    header("Location: ../authentification.php");
    exit();
}

include '../config.php';

// Obtenir l'ID du module à modifier
$module_id = $_GET['id'];

// Vérifier si le module appartient à la classe dont le professeur est responsable
$stmt = $pdo->prepare("SELECT * FROM Module WHERE id = ? AND classe_id = (SELECT id FROM Classe WHERE professeur_responsable_id = ?)");
$stmt->execute([$module_id, $_SESSION['user_id']]);
$module = $stmt->fetch();

if (!$module) {
    echo "Module non trouvé ou vous n'êtes pas autorisé à le modifier.";
    exit();
}

// Mettre à jour le module
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $module_nom = $_POST['module_nom'];
    $module_volume = $_POST['module_volume'];
    $enseignant_id = $_POST['enseignant_id'];

    $stmt = $pdo->prepare("UPDATE Module SET nom = ?, volume_horaire = ?, enseignant_id = ? WHERE id = ?");
    $stmt->execute([$module_nom, $module_volume, $enseignant_id, $module_id]);

    header("Location: gestion_modules.php");
    exit();
}

// Obtenir la liste des enseignants disponibles
$stmt = $pdo->query("SELECT id, nom, prenom FROM Utilisateur WHERE role = 'Enseignant'");
$enseignants_disponibles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Module</title>
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
        <h2 class="text-center my-4">Modifier le Module</h2>

        <form method="POST" action="modifier_module.php?id=<?= $module_id ?>">
            <div class="form-group">
                <label for="module_nom">Nom du Module :</label>
                <input type="text" class="form-control" id="module_nom" name="module_nom" value="<?= htmlspecialchars($module['nom']) ?>" required>
            </div>
            <div class="form-group">
                <label for="module_volume">Volume Horaire :</label>
                <input type="number" class="form-control" id="module_volume" name="module_volume" value="<?= htmlspecialchars($module['volume_horaire']) ?>" required>
            </div>
            <div class="form-group">
                <label for="enseignant_id">Enseignant :</label>
                <select class="form-control" id="enseignant_id" name="enseignant_id" required>
                    <?php foreach ($enseignants_disponibles as $enseignant): ?>
                        <option value="<?= $enseignant['id'] ?>" <?= $enseignant['id'] == $module['enseignant_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($enseignant['nom'] . ' ' . $enseignant['prenom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Mettre à jour</button>
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

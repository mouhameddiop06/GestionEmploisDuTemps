<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    header("Location: ../authentification.php");
    exit();
}

include '../config.php';

// Gérer la modification de la classe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier_classe'])) {
    if (isset($_POST['classe_id']) && isset($_POST['nom'])) {
        $classe_id = $_POST['classe_id'];
        $nom = $_POST['nom'];

        $stmt = $pdo->prepare("UPDATE Classe SET nom = ? WHERE id = ?");
        $stmt->execute([$nom, $classe_id]);

        $message = "Classe modifiée avec succès.";
    } else {
        $message = "Tous les champs ne sont pas remplis.";
    }
}

// Obtenir les informations de la classe
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT id, nom FROM Classe WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $classe = $stmt->fetch();

    if (!$classe) {
        echo "Classe non trouvée.";
        exit();
    }
} else {
    echo "ID de classe manquant.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la Classe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-4">Modifier la Classe</h1>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="modifier_classe.php?id=<?= $classe['id'] ?>">
            <input type="hidden" name="classe_id" value="<?= $classe['id'] ?>">
            
            <div class="mb-3">
                <label for="nom" class="form-label">Nom de la Classe</label>
                <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($classe['nom']) ?>" required>
            </div>
            
            <button type="submit" name="modifier_classe" class="btn btn-primary">Modifier</button>
        </form>
    </div>
</body>
</html>

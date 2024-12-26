<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    header("Location: ../authentification.php");
    exit();
}

include '../config.php';

// Obtenir l'ID de l'enseignant à modifier depuis l'URL
if (isset($_GET['id'])) {
    $enseignant_id = $_GET['id'];

    // Récupérer les informations de l'enseignant depuis la base de données
    $stmt = $pdo->prepare("SELECT nom, prenom, email FROM Utilisateur WHERE id = ? AND role = 'Enseignant'");
    $stmt->execute([$enseignant_id]);
    $enseignant = $stmt->fetch();

    if (!$enseignant) {
        echo "Enseignant non trouvé.";
        exit();
    }
} else {
    echo "Aucun enseignant sélectionné.";
    exit();
}

// Gérer la modification de l'enseignant
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];

    // Mettre à jour les informations de l'enseignant dans la base de données
    $stmt = $pdo->prepare("UPDATE Utilisateur SET nom = ?, prenom = ?, email = ? WHERE id = ?");
    $stmt->execute([$nom, $prenom, $email, $enseignant_id]);

    $message = "Informations de l'enseignant mises à jour avec succès.";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Enseignant</title>
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
        .btn-custom {
            background: linear-gradient(45deg, #5e72e4, #825ee4);
            border: none;
            color: white;
        }
        .btn-custom:hover {
            background: linear-gradient(45deg, #4a5cd1, #7046d1);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-5">Modifier Enseignant</h1>

        <?php if (isset($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="modifier_enseignant.php?id=<?= $enseignant_id ?>">
            <div class="mb-3">
                <label for="nom" class="form-label">Nom</label>
                <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($enseignant['nom']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="prenom" class="form-label">Prénom</label>
                <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($enseignant['prenom']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($enseignant['email']) ?>" required>
            </div>
            <button type="submit" class="btn btn-custom w-100">Mettre à jour</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

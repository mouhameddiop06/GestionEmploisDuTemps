<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un professeur responsable
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Professeur') {
    header("Location: ../authentification.php");
    exit();
}

include '../config.php';

// Obtenir l'ID du professeur responsable à partir de la session
$professeur_id = $_SESSION['user_id'];

// Obtenir la classe dont le professeur est responsable
$stmt = $pdo->prepare("SELECT id FROM Classe WHERE professeur_responsable_id = ?");
$stmt->execute([$professeur_id]);
$classe = $stmt->fetch();
if (!$classe) {
    echo "Vous n'êtes responsable d'aucune classe.";
    exit();
}
$classe_id = $classe['id'];

// Obtenir la liste des étudiants approuvés pour cette classe, triés par nom
$stmt = $pdo->prepare("
    SELECT * 
    FROM Utilisateur 
    JOIN Inscription ON Utilisateur.id = Inscription.etudiant_id 
    WHERE Inscription.classe_id = ? AND Utilisateur.statut = 'approuvé' AND Utilisateur.role = 'Etudiant'
    ORDER BY Utilisateur.nom ASC
");
$stmt->execute([$classe_id]);
$etudiants = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Étudiants</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center my-4">Gestion des Étudiants</h2>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($etudiants) > 0): ?>
                    <?php foreach ($etudiants as $etudiant): ?>
                        <tr>
                            <td><?= htmlspecialchars($etudiant['nom']) ?></td>
                            <td><?= htmlspecialchars($etudiant['prenom']) ?></td>
                            <td><?= htmlspecialchars($etudiant['email']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">Aucun étudiant approuvé trouvé pour cette classe.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

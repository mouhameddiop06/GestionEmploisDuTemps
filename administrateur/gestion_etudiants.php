<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    header("Location: ../authentification.php");
    exit();
}

include '../config.php';

// Mettre à jour le statut d'un étudiant
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['etudiant_id'])) {
    $etudiant_id = $_POST['etudiant_id'];
    $nouveau_statut = $_POST['action'] === 'approuver' ? 'approuvé' : 'rejeté';

    $stmt = $pdo->prepare("UPDATE Utilisateur SET statut = ? WHERE id = ? AND role = 'Etudiant'");
    $stmt->execute([$nouveau_statut, $etudiant_id]);
}

// Obtenir la liste des étudiants inscrits
$stmt = $pdo->query("SELECT * FROM Utilisateur WHERE role = 'Etudiant'");
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
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($etudiants) > 0): ?>
                    <?php foreach ($etudiants as $etudiant): ?>
                        <tr>
                            <td><?= htmlspecialchars($etudiant['nom']) ?></td>
                            <td><?= htmlspecialchars($etudiant['prenom']) ?></td>
                            <td><?= htmlspecialchars($etudiant['email']) ?></td>
                            <td>
                                <?php if ($etudiant['statut'] == 'en attente'): ?>
                                    <span class="badge badge-warning">En attente</span>
                                <?php elseif ($etudiant['statut'] == 'approuvé'): ?>
                                    <span class="badge badge-success">Approuvé</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Rejeté</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($etudiant['statut'] == 'en attente'): ?>
                                    <form method="POST" action="gestion_etudiants.php" style="display:inline;">
                                        <input type="hidden" name="etudiant_id" value="<?= $etudiant['id'] ?>">
                                        <button type="submit" name="action" value="approuver" class="btn btn-success btn-sm">Approuver</button>
                                    </form>
                                    <form method="POST" action="gestion_etudiants.php" style="display:inline;">
                                        <input type="hidden" name="etudiant_id" value="<?= $etudiant['id'] ?>">
                                        <button type="submit" name="action" value="rejeter" class="btn btn-danger btn-sm">Rejeter</button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm" disabled>Statut Finalisé</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Aucun étudiant trouvé</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

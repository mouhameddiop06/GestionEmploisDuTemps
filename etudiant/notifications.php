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

// Récupérer les notifications pour l'étudiant
$stmt = $pdo->prepare("SELECT * FROM Notification WHERE utilisateur_id = ? ORDER BY date_heure DESC");
$stmt->execute([$etudiant_id]);
$notifications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 900px;
            margin-top: 30px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px;
        }
        .list-group-item {
            border: none;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="fas fa-bell me-2"></i>Vos Notifications</h2>
            </div>
            <div class="card-body">
                <?php if (count($notifications) > 0): ?>
                    <ul class="list-group">
                        <?php foreach ($notifications as $notification): ?>
                            <li class="list-group-item">
                                <div>
                                    <?= htmlspecialchars($notification['message']) ?>
                                    <span class="text-muted float-end"><?= date('d/m/Y H:i', strtotime($notification['date_heure'])) ?></span>
                                </div>
                                <small class="text-muted">Vu: <?= $notification['vu'] ? 'Oui' : 'Non' ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="alert alert-info">Vous n'avez aucune notification.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

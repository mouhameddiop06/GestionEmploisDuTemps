<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../authentification.php");
    exit();
}

include '../config.php';

// Obtenir l'ID de l'utilisateur connecté
$utilisateur_id = $_SESSION['user_id'];

// Obtenir les notifications de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM Notification WHERE utilisateur_id = ? ORDER BY date_heure DESC");
$stmt->execute([$utilisateur_id]);
$notifications = $stmt->fetchAll();

// Marquer les notifications comme lues
$stmt = $pdo->prepare("UPDATE Notification SET vu = TRUE WHERE utilisateur_id = ?");
$stmt->execute([$utilisateur_id]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .notification {
            padding: 15px;
            margin-bottom: 10px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .notification.unread {
            background-color: #e9ecef;
        }
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .notification-time {
            font-size: 0.9em;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center my-4">Notifications</h2>

        <?php if (count($notifications) > 0): ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification <?= !$notification['vu'] ? 'unread' : '' ?>">
                    <div class="notification-header">
                        <strong>Notification</strong>
                        <span class="notification-time"><?= htmlspecialchars($notification['date_heure']) ?></span>
                    </div>
                    <p><?= htmlspecialchars($notification['message']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">Aucune notification</p>
        <?php endif; ?>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

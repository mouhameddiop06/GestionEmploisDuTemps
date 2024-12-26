<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

include '../config.php';

// Marquer une notification comme lue
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_as_read'])) {
    $notification_id = $_POST['notification_id'];

    $stmt = $pdo->prepare("UPDATE Notification SET vu = 1 WHERE id = ?");
    $stmt->execute([$notification_id]);
    header("Location: notifications.php");
}

// Obtenir la liste des notifications pour l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM Notification WHERE utilisateur_id = ? ORDER BY date_heure DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications de Fin de Cours</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2 class="my-4">Notifications de Fin de Cours</h2>

        <!-- Liste des notifications -->
        <?php if ($notifications) : ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Message</th>
                        <th>Date et Heure</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notifications as $notification) : ?>
                        <tr <?= $notification['vu'] ? '' : 'class="table-warning"' ?>>
                            <td><?= htmlspecialchars($notification['message']) ?></td>
                            <td><?= htmlspecialchars($notification['date_heure']) ?></td>
                            <td>
                                <?php if (!$notification['vu']) : ?>
                                    <form method="POST" action="notifications.php" style="display:inline;">
                                        <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                                        <button type="submit" name="mark_as_read" class="btn btn-success btn-sm">Marquer comme lue</button>
                                    </form>
                                <?php else : ?>
                                    <span class="badge badge-secondary">Lue</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <div class="alert alert-info">Aucune notification trouvée.</div>
        <?php endif; ?>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

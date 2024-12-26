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

// Vérifier si l'étudiant est inscrit à une classe
$stmt = $pdo->prepare("SELECT classe_id FROM Inscription WHERE etudiant_id = ?");
$stmt->execute([$etudiant_id]);
$inscription = $stmt->fetch();

if (!$inscription) {
    header("Location: inscription_classe.php");
    exit();
}

$classe_id = $inscription['classe_id'];

// Obtenir le nom de la classe
$stmt = $pdo->prepare("SELECT nom FROM Classe WHERE id = ?");
$stmt->execute([$classe_id]);
$classe = $stmt->fetch();

if (!$classe) {
    echo "Erreur : Classe non trouvée.";
    exit();
}

// Obtenir le nombre de notifications non lues
$stmt = $pdo->prepare("SELECT COUNT(*) FROM Notification WHERE utilisateur_id = ? AND vu = 0");
$stmt->execute([$etudiant_id]);
$notification_count = $stmt->fetchColumn();

$user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Étudiant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .header {
            background: linear-gradient(135deg, #00c6ff, #0072ff);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        .menu-item {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            position: relative;
        }
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        .menu-item i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .menu-item h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        .menu-item p {
            font-size: 0.9rem;
            color: #666;
        }
        .badge-notification {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 0.8rem;
        }
        .logout-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <h1>Tableau de Bord Étudiant</h1>
            <p>Bienvenue, <?= htmlspecialchars($user_name) ?> - Classe <?= htmlspecialchars($classe['nom']) ?></p>
        </div>

        <div class="menu-grid">
            <div class="menu-item" onclick="navigateTo('visualiser_edt.php')">
                <i class="fas fa-calendar-alt" style="color: #007bff;"></i>
                <h3>Emplois du Temps</h3>
                <p>Consultez vos emplois du temps</p>
            </div>
            <div class="menu-item" onclick="navigateTo('gerer_profil.php')">
                <i class="fas fa-user" style="color: #28a745;"></i>
                <h3>Gérer le Profil</h3>
                <p>Mettez à jour vos informations</p>
            </div>
            <div class="menu-item" onclick="navigateTo('notifications.php')">
                <i class="fas fa-bell" style="color: #dc3545;"></i>
                <h3>Notifications</h3>
                <p>Recevez les dernières notifications</p>
                <?php if ($notification_count > 0): ?>
                    <span class="badge-notification"><?= $notification_count ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="logout-btn">
        <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>

    <script>
        function navigateTo(page) {
            window.location.href = page;
        }
    </script>
</body>
</html>

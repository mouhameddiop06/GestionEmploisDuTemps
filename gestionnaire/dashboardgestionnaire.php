<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Gestionnaire') {
    header("Location: ../authentification.php");
    exit();
}
include '../config.php';
$user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Gestionnaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .header {
            background: linear-gradient(135deg, #3a7bd5, #00d2ff);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
            cursor: pointer;
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
            <h1>Tableau de Bord Gestionnaire</h1>
            <p>Bienvenue, <?php echo htmlspecialchars($user_name); ?></p>
        </div>
        
        <div class="menu-grid">
            <div class="menu-item" onclick="navigateTo('ajouter_salle.php')">
                <i class="fas fa-plus-square" style="color: #4CAF50;"></i>
                <h3>Ajouter une Salle</h3>
                <p>Ajoutez de nouvelles salles</p>
            </div>
            <div class="menu-item" onclick="navigateTo('attribuer_salle.php')">
                <i class="fas fa-chalkboard-teacher" style="color: #2196F3;"></i>
                <h3>Attribuer une Salle</h3>
                <p>Attribuez des salles aux cours</p>
            </div>
            <div class="menu-item" onclick="navigateTo('visualiser_edt.php')">
                <i class="fas fa-calendar-alt" style="color: #FFC107;"></i>
                <h3>Visualiser les EDT</h3>
                <p>Consultez les emplois du temps</p>
            </div>
            <div class="menu-item" onclick="navigateTo('modifier_edt.php')">
                <i class="fas fa-edit" style="color: #9C27B0;"></i>
                <h3>Modifier les EDT</h3>
                <p>Modifiez les emplois du temps</p>
            </div>
            <div class="menu-item" onclick="navigateTo('notifications.php')">
                <i class="fas fa-bell" style="color: #FF5722;"></i>
                <h3>Notifications</h3>
                <p>Gérez les notifications</p>
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
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Enseignant') {
    header("Location: ../authentification.php");
    exit();
}
$user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Enseignant</title>
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
            <h1>Tableau de Bord Enseignant</h1>
            <p>Bienvenue, <?php echo htmlspecialchars($user_name); ?></p>
        </div>
        
        <div class="menu-grid">
            <div class="menu-item" onclick="navigateTo('gestion_disponibilites.php')">
                <i class="fas fa-calendar-check" style="color: #4CAF50;"></i>
                <h3>Gestion des Disponibilités</h3>
                <p>Gérez vos disponibilités</p>
            </div>
            <div class="menu-item" onclick="navigateTo('visualiser_edt.php')">
                <i class="fas fa-table" style="color: #2196F3;"></i>
                <h3>Visualisation des Emplois du Temps</h3>
                <p>Consultez vos emplois du temps</p>
            </div>
            <div class="menu-item" onclick="navigateTo('gestion_evolution_cours.php')">
                <i class="fas fa-chart-line" style="color: #FFC107;"></i>
                <h3>Suivi de l'Évolution des Cours</h3>
                <p>Suivez la progression des cours</p>
            </div>
            <div class="menu-item" onclick="navigateTo('gestion_profil.php')">
                <i class="fas fa-user" style="color: #9C27B0;"></i>
                <h3>Gestion du Profil</h3>
                <p>Modifiez vos informations personnelles</p>
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

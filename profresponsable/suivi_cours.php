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
$stmt = $pdo->prepare("SELECT id, nom FROM Classe WHERE professeur_responsable_id = ?");
$stmt->execute([$professeur_id]);
$classe = $stmt->fetch();
if (!$classe) {
    echo "Vous n'êtes responsable d'aucune classe.";
    exit();
}
$classe_id = $classe['id'];

// Obtenir le suivi des cours pour la classe, ainsi que le volume horaire total du module
$stmt = $pdo->prepare("
    SELECT Module.nom AS module_nom, Module.volume_horaire AS volume_horaire_total, SUM(Cours.volume_horaire_fait) AS volume_horaire_fait
    FROM Cours
    JOIN Module ON Cours.module_id = Module.id
    WHERE Module.classe_id = ?
    GROUP BY Module.nom, Module.volume_horaire
");
$stmt->execute([$classe_id]);
$suivi_cours = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi des Cours - <?= htmlspecialchars($classe['nom']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 900px;
            margin-top: 50px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px;
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            background-color: #f1f3f5;
            border-top: none;
        }
        .progress {
            height: 20px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="fas fa-chart-line me-2"></i>Suivi des Cours - <?= htmlspecialchars($classe['nom']) ?></h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Module</th>
                                <th>Volume Horaire Fait</th>
                                <th>Progression</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suivi_cours as $cours): ?>
                                <?php 
                                    $total_hours = $cours['volume_horaire_total'];
                                    $progress = min(($cours['volume_horaire_fait'] / $total_hours) * 100, 100);
                                ?>
                                <tr>
                                    <td><i class="fas fa-book me-2"></i><?= htmlspecialchars($cours['module_nom']) ?></td>
                                    <td><?= htmlspecialchars($cours['volume_horaire_fait']) ?> heures</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                                 role="progressbar" 
                                                 style="width: <?= $progress ?>%;" 
                                                 aria-valuenow="<?= $progress ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                <?= round($progress) ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($suivi_cours)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">Aucun cours trouvé.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un enseignant
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Enseignant') {
    header("Location: ../authentification.php");
    exit();
}

include '../config.php';

// Obtenir l'ID de l'enseignant à partir de la session
$enseignant_id = $_SESSION['user_id'];

// Mettre à jour le volume horaire fait et envoyer la notification si nécessaire
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cours_id'])) {
    $cours_id = $_POST['cours_id'];

    // Récupérer les informations sur le cours et le module
    $stmt = $pdo->prepare("SELECT c.volume_horaire_fait, m.volume_horaire 
                           FROM Cours c 
                           JOIN Module m ON c.module_id = m.id 
                           WHERE c.id = ? AND c.enseignant_id = ?");
    $stmt->execute([$cours_id, $enseignant_id]);
    $cours = $stmt->fetch();

    if ($cours) {
        // Incrémenter le volume horaire fait de 2 heures
        $nouveau_volume_horaire_fait = $cours['volume_horaire_fait'] + 2;
        $stmt = $pdo->prepare("UPDATE Cours SET volume_horaire_fait = ? WHERE id = ?");
        $stmt->execute([$nouveau_volume_horaire_fait, $cours_id]);

        // Vérifier si le volume horaire total est atteint
        if ($nouveau_volume_horaire_fait >= $cours['volume_horaire']) {
            // Envoyer une notification
            $message = "Le module '" . $cours['module_nom'] . "' a atteint le volume horaire total.";
            $stmt = $pdo->prepare("INSERT INTO Notification (utilisateur_id, message, date_heure) VALUES (?, ?, NOW())");
            $stmt->execute([$enseignant_id, $message]);
        }
    }
}

// Obtenir la liste des cours programmés pour cet enseignant
$stmt = $pdo->prepare("
    SELECT c.id, DATE_FORMAT(c.date_heure, '%Y-%m-%d') AS date_cours, c.heure_debut, c.heure_fin, c.volume_horaire_fait, m.nom AS module_nom, s.nom AS salle_nom, m.volume_horaire
    FROM Cours c
    JOIN Module m ON c.module_id = m.id
    JOIN Salle s ON c.salle_id = s.id
    WHERE c.enseignant_id = ?
    ORDER BY c.date_heure
");
$stmt->execute([$enseignant_id]);
$cours = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer l'Évolution des Cours</title>
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
            text-align: center;
        }
        .table td {
            text-align: center;
        }
        .form-control-inline {
            display: inline-block;
            width: auto;
            vertical-align: middle;
        }
        .btn-custom {
            background-color: #007bff;
            color: white;
            border: none;
        }
        .btn-custom:hover {
            background-color: #0056b3;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="fas fa-clock me-2"></i>Gérer l'Évolution des Cours</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Heure Début</th>
                                <th>Heure Fin</th>
                                <th>Module</th>
                                <th>Salle</th>
                                <th>Volume Horaire Fait / Total</th>
                                <th>Effectué</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($cours) > 0): ?>
                                <?php foreach ($cours as $cours_item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cours_item['date_cours']) ?></td>
                                        <td><?= htmlspecialchars($cours_item['heure_debut']) ?></td>
                                        <td><?= htmlspecialchars($cours_item['heure_fin']) ?></td>
                                        <td><?= htmlspecialchars($cours_item['module_nom']) ?></td>
                                        <td><?= htmlspecialchars($cours_item['salle_nom']) ?></td>
                                        <td><?= htmlspecialchars($cours_item['volume_horaire_fait']) ?> / <?= htmlspecialchars($cours_item['volume_horaire']) ?> heures</td>
                                        <td>
                                            <?php 
                                            $current_date_time = date('Y-m-d H:i');
                                            $course_date_time = $cours_item['date_cours'] . ' ' . $cours_item['heure_fin'];
                                            
                                            if ($cours_item['volume_horaire_fait'] < $cours_item['volume_horaire'] && $current_date_time >= $course_date_time): ?>
                                                <form method="POST" action="gestion_evolution_cours.php">
                                                    <input type="hidden" name="cours_id" value="<?= $cours_item['id'] ?>">
                                                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i></button>
                                                </form>
                                            <?php elseif ($current_date_time < $course_date_time): ?>
                                                <span class="text-warning">À venir</span>
                                            <?php else: ?>
                                                <span class="text-success">Terminé</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Aucun cours trouvé</td>
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

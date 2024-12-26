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

// Obtenir la classe de l'étudiant approuvé
$stmt = $pdo->prepare("SELECT classe_id FROM Inscription WHERE etudiant_id = ?");
$stmt->execute([$etudiant_id]);
$inscription = $stmt->fetch();
if (!$inscription) {
    echo "Vous n'êtes inscrit à aucune classe approuvée.";
    exit();
}
$classe_id = $inscription['classe_id'];

// Obtenir la liste des emplois du temps pour la classe de l'étudiant
$stmt = $pdo->prepare("
    SELECT Cours.*, Module.nom AS module_nom, Utilisateur.nom AS enseignant_nom, Utilisateur.prenom AS enseignant_prenom, Salle.nom AS salle_nom, EmploiDuTemps.date_heure, EmploiDuTemps.heure_debut, EmploiDuTemps.heure_fin
    FROM EmploiDuTemps
    JOIN Cours ON EmploiDuTemps.cours_id = Cours.id
    JOIN Module ON Cours.module_id = Module.id
    JOIN Utilisateur ON Cours.enseignant_id = Utilisateur.id
    JOIN Salle ON Cours.salle_id = Salle.id
    WHERE EmploiDuTemps.classe_id = ?
    ORDER BY EmploiDuTemps.date_heure, EmploiDuTemps.heure_debut
");
$stmt->execute([$classe_id]);
$emplois_du_temps = $stmt->fetchAll();

// Préparation des jours et des heures
$jours = ['Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi', 'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi'];
$heures = ['08:00-10:00', '10:00-12:00', '12:00-14:00', '14:00-16:00', '16:00-18:00'];

// Générer des couleurs aléatoires pour les modules
$couleurs = [];
foreach ($emplois_du_temps as $cours) {
    if (!isset($couleurs[$cours['module_nom']])) {
        $couleurs[$cours['module_nom']] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emploi du temps - Étudiant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 1200px;
            margin-top: 30px;
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
        .table-responsive {
            border-radius: 0 0 15px 15px;
            overflow: hidden;
        }
        .schedule-table {
            margin-bottom: 0;
        }
        .schedule-table th {
            background-color: #f1f3f5;
            border-top: none;
        }
        .cell-course {
            font-weight: bold;
            padding: 10px;
            border-radius: 5px;
            margin: 2px;
            font-size: 0.9em;
        }
        .cell-empty {
            background-color: #ffffff;
        }
        .cell-pause {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Emploi du temps</h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table schedule-table table-bordered">
                        <thead>
                            <tr>
                                <th>Heure</th>
                                <?php foreach ($jours as $jour_fr): ?>
                                    <th><?= $jour_fr ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($heures as $heure): ?>
                                <tr>
                                    <td class="fw-bold"><?= $heure ?></td>
                                    <?php foreach ($jours as $jour_en => $jour_fr): ?>
                                        <?php if ($heure == '12:00-14:00'): ?>
                                            <td class="cell-pause"></td>
                                        <?php else: ?>
                                            <?php
                                            $cours_affiche = false;
                                            foreach ($emplois_du_temps as $cours) {
                                                $jour_cours = date('l', strtotime($cours['date_heure']));
                                                $heure_debut_cours = date('H:i', strtotime($cours['heure_debut']));
                                                $heure_fin_cours = date('H:i', strtotime($cours['heure_fin']));
                                                $heure_format = $heure_debut_cours . '-' . $heure_fin_cours;

                                                if ($jour_cours === $jour_en && $heure_format === $heure) {
                                                    $couleur = $couleurs[$cours['module_nom']];
                                                    echo '<td class="p-0"><div class="cell-course" style="background-color:' . $couleur . '">'
                                                        . '<div>' . htmlspecialchars($cours['module_nom']) . '</div>'
                                                        . '<small>' . htmlspecialchars($cours['enseignant_nom'] . ' ' . $cours['enseignant_prenom']) . '</small><br>'
                                                        . '<small><i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($cours['salle_nom']) . '</small></div></td>';
                                                    $cours_affiche = true;
                                                    break;
                                                }
                                            }
                                            if (!$cours_affiche) {
                                                echo '<td class="cell-empty"></td>';
                                            }
                                            ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

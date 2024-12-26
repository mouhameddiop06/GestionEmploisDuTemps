<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Gestionnaire') {
    header("Location: ../authentification.php");
    exit();
}

include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier_salle'])) {
    $cours_id = $_POST['cours_id'];
    $salle_id = $_POST['salle_id'];

    $stmt = $pdo->prepare("UPDATE Cours SET salle_id = ? WHERE id = ?");
    $stmt->execute([$salle_id, $cours_id]);

    header("Location: modifier_edt.php");
    exit();
}

$stmt = $pdo->query("
    SELECT c.id, m.nom AS module_nom, s.nom AS salle_nom, c.date_heure, c.heure_debut, c.heure_fin
    FROM Cours c
    JOIN Module m ON c.module_id = m.id
    LEFT JOIN Salle s ON c.salle_id = s.id
    ORDER BY c.date_heure, c.heure_debut
");
$cours = $stmt->fetchAll();

$salles_stmt = $pdo->query("SELECT id, nom FROM Salle");
$salles = $salles_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Emploi du Temps</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f4f7fc;
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
            background: linear-gradient(135deg, #3a7bd5, #00d2ff);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .table {
            background-color: white;
        }
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background-color: #ffca2c;
            border-color: #ffc720;
            color: #212529;
        }
        .modal-content {
            border-radius: 15px;
        }
        .modal-header {
            background: linear-gradient(135deg, #3a7bd5, #00d2ff);
            color: white;
            border-radius: 15px 15px 0 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Modifier l'Emploi du Temps des Salles</h2>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th><i class="fas fa-book me-2"></i>Module</th>
                            <th><i class="fas fa-door-open me-2"></i>Salle</th>
                            <th><i class="fas fa-calendar-day me-2"></i>Date</th>
                            <th><i class="fas fa-clock me-2"></i>Heure Début</th>
                            <th><i class="fas fa-clock me-2"></i>Heure Fin</th>
                            <th><i class="fas fa-cogs me-2"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cours as $cour): ?>
                            <tr>
                                <td><?= htmlspecialchars($cour['module_nom']) ?></td>
                                <td><?= htmlspecialchars($cour['salle_nom']) ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($cour['date_heure']))) ?></td>
                                <td><?= htmlspecialchars($cour['heure_debut']) ?></td>
                                <td><?= htmlspecialchars($cour['heure_fin']) ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" onclick="openModal(<?= $cour['id'] ?>)">
                                        <i class="fas fa-edit me-1"></i>Modifier
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel"><i class="fas fa-edit me-2"></i>Modifier la Salle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST" action="modifier_edt.php">
                    <div class="modal-body">
                        <input type="hidden" id="editCoursId" name="cours_id">
                        <div class="mb-3">
                            <label for="salle_id" class="form-label"><i class="fas fa-door-open me-2"></i>Salle :</label>
                            <select class="form-select" id="salle_id" name="salle_id" required>
                                <?php foreach ($salles as $salle): ?>
                                    <option value="<?= $salle['id'] ?>"><?= htmlspecialchars($salle['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="modifier_salle" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openModal(coursId) {
            document.getElementById('editCoursId').value = coursId;
            var editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        }
    </script>
</body>
</html>
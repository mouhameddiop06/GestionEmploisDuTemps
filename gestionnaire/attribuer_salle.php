<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Gestionnaire') {
    header("Location: ../authentification.php");
    exit();
}

include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['attribuer_salle'])) {
    $cours_id = $_POST['cours_id'];
    $salle_id = $_POST['salle_id'];
    
    $stmt = $pdo->prepare("UPDATE Cours SET salle_id = ? WHERE id = ?");
    $stmt->execute([$salle_id, $cours_id]);
    
    $message = "Salle attribuée au cours avec succès.";
}

// Obtenir la liste des cours
$stmt = $pdo->query("SELECT c.id, m.nom AS nom_cours FROM Cours c JOIN Module m ON c.module_id = m.id");
$cours = $stmt->fetchAll();

// Obtenir la liste des salles
$stmt = $pdo->query("SELECT id, nom FROM Salle");
$salles = $stmt->fetchAll();

// Obtenir la liste des cours avec leurs salles attribuées
$stmt = $pdo->query("
    SELECT c.id, m.nom AS nom_cours, s.nom AS nom_salle
    FROM Cours c
    JOIN Module m ON c.module_id = m.id
    LEFT JOIN Salle s ON c.salle_id = s.id
");
$cours_attribues = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attribuer une Salle à un Cours</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 800px;
            margin-top: 50px;
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
        .form-control, .btn {
            border-radius: 10px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3a7bd5, #00d2ff);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #3a7bd5, #00d2ff);
            opacity: 0.9;
        }
        .table-container {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2 class="text-center mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>Attribuer une Salle à un Cours</h2>
            </div>
            <div class="card-body p-4">
                <?php if (isset($message)): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="attribuer_salle.php">
                    <div class="mb-3">
                        <label for="cours_id" class="form-label"><i class="fas fa-book me-2"></i>Cours :</label>
                        <select class="form-select" id="cours_id" name="cours_id" required>
                            <?php foreach ($cours as $cour): ?>
                                <option value="<?= $cour['id'] ?>"><?= htmlspecialchars($cour['nom_cours']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="salle_id" class="form-label"><i class="fas fa-door-open me-2"></i>Salle :</label>
                        <select class="form-select" id="salle_id" name="salle_id" required>
                            <?php foreach ($salles as $salle): ?>
                                <option value="<?= $salle['id'] ?>"><?= htmlspecialchars($salle['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="attribuer_salle" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-check me-2"></i>Attribuer Salle
                    </button>
                </form>

                <div class="table-container">
                    <h3 class="mt-4">Liste des Salles Attribuées</h3>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Cours</th>
                                <th>Salle Attribuée</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cours_attribues as $cours_attribue): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cours_attribue['nom_cours']) ?></td>
                                    <td><?= htmlspecialchars($cours_attribue['nom_salle'] ?? 'Non attribuée') ?></td>
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

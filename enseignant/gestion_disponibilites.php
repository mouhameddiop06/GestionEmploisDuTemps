<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Enseignant') {
    header("Location: ../authentification.php");
    exit();
}

include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_disponibilite'])) {
    $jour = $_POST['jour'];
    $heure_debut = $_POST['heure_debut'];
    $heure_fin = $_POST['heure_fin'];
    $enseignant_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO Disponibilite (enseignant_id, jour, heure_debut, heure_fin) VALUES (?, ?, ?, ?)");
    $stmt->execute([$enseignant_id, $jour, $heure_debut, $heure_fin]);
    header("Location: gestion_disponibilites.php");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_disponibilite'])) {
    $disponibilite_id = $_POST['disponibilite_id'];
    $stmt = $pdo->prepare("DELETE FROM Disponibilite WHERE id = ?");
    $stmt->execute([$disponibilite_id]);
    header("Location: gestion_disponibilites.php");
}

$enseignant_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM Disponibilite WHERE enseignant_id = ? ORDER BY FIELD(jour, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'), heure_debut");
$stmt->execute([$enseignant_id]);
$disponibilites = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Disponibilités</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 900px;
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
        .btn-primary {
            background: linear-gradient(135deg, #3a7bd5, #00d2ff);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #3a7bd5, #00d2ff);
            opacity: 0.9;
        }
        .table {
            background-color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4"><i class="fas fa-calendar-alt me-2"></i>Gestion des Disponibilités</h2>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Ajouter une Disponibilité</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="gestion_disponibilites.php">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="jour" class="form-label">Jour :</label>
                            <select class="form-select" id="jour" name="jour" required>
                                <option value="Lundi">Lundi</option>
                                <option value="Mardi">Mardi</option>
                                <option value="Mercredi">Mercredi</option>
                                <option value="Jeudi">Jeudi</option>
                                <option value="Vendredi">Vendredi</option>
                                <option value="Samedi">Samedi</option>
                                <option value="Dimanche">Dimanche</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="heure_debut" class="form-label">Heure de Début :</label>
                            <input type="time" class="form-control" id="heure_debut" name="heure_debut" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="heure_fin" class="form-label">Heure de Fin :</label>
                            <input type="time" class="form-control" id="heure_fin" name="heure_fin" required>
                        </div>
                    </div>
                    <button type="submit" name="add_disponibilite" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Ajouter</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Liste des Disponibilités</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th><i class="fas fa-calendar-day me-2"></i>Jour</th>
                            <th><i class="fas fa-clock me-2"></i>Heure de Début</th>
                            <th><i class="fas fa-clock me-2"></i>Heure de Fin</th>
                            <th><i class="fas fa-cogs me-2"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($disponibilites as $disponibilite) : ?>
                            <tr>
                                <td><?= htmlspecialchars($disponibilite['jour']) ?></td>
                                <td><?= htmlspecialchars($disponibilite['heure_debut']) ?></td>
                                <td><?= htmlspecialchars($disponibilite['heure_fin']) ?></td>
                                <td>
                                    <form method="POST" action="gestion_disponibilites.php" style="display:inline;">
                                        <input type="hidden" name="disponibilite_id" value="<?= $disponibilite['id'] ?>">
                                        <button type="submit" name="delete_disponibilite" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash-alt me-1"></i>Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un gestionnaire
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Gestionnaire') {
    header("Location: ../authentification.php");
    exit();
}

include '../config.php';

// Ajouter une salle
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_salle'])) {
    $nom_salle = $_POST['nom_salle'];
    $capacite_salle = $_POST['capacite_salle'];

    $stmt = $pdo->prepare("INSERT INTO Salle (nom, capacite) VALUES (?, ?)");
    $stmt->execute([$nom_salle, $capacite_salle]);

    $message = "Salle ajoutée avec succès.";
}

// Obtenir la liste des salles
$salles_stmt = $pdo->query("SELECT * FROM Salle");
$salles = $salles_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Salle</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #f6d365 0%, #fda085 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(45deg, #d53369, #daae51);
            color: white;
            border-radius: 15px 15px 0 0;
            font-weight: bold;
        }
        .btn-custom {
            background: linear-gradient(45deg, #d53369, #daae51);
            border: none;
            color: white;
        }
        .btn-custom:hover {
            background: linear-gradient(45deg, #b82e57, #d49345);
            color: white;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        .table thead {
            background-color: #d53369;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-5">Ajouter une Salle</h1>

        <?php if (isset($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Ajouter une nouvelle salle</div>
                    <div class="card-body">
                        <form method="POST" action="ajouter_salle.php">
                            <div class="mb-3">
                                <label for="nom_salle" class="form-label">Nom de la Salle</label>
                                <input type="text" class="form-control" id="nom_salle" name="nom_salle" required>
                            </div>
                            <div class="mb-3">
                                <label for="capacite_salle" class="form-label">Capacité</label>
                                <input type="number" class="form-control" id="capacite_salle" name="capacite_salle" required>
                            </div>
                            <button type="submit" name="ajouter_salle" class="btn btn-custom w-100">Ajouter</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Liste des Salles</div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Capacité</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($salles as $salle): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($salle['nom']) ?></td>
                                        <td><?= htmlspecialchars($salle['capacite']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

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

// Obtenir les informations de la classe et du professeur responsable
$stmt = $pdo->prepare("SELECT c.id AS classe_id, c.nom AS classe_nom, u.nom AS professeur_nom, u.prenom AS professeur_prenom 
                       FROM Classe c 
                       JOIN Utilisateur u ON c.professeur_responsable_id = u.id 
                       WHERE c.professeur_responsable_id = ?");
$stmt->execute([$professeur_id]);
$classe = $stmt->fetch();
if (!$classe) {
    echo "Vous n'êtes responsable d'aucune classe.";
    exit();
}
$classe_id = $classe['classe_id'];
$classe_nom = $classe['classe_nom'];
$professeur_nom = $classe['professeur_nom'];
$professeur_prenom = $classe['professeur_prenom'];

// Gérer les modules de la classe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_module'])) {
    $module_nom = $_POST['module_nom'];
    $module_volume = $_POST['module_volume'];
    $enseignant_id = $_POST['enseignant_id'];

    $stmt = $pdo->prepare("INSERT INTO Module (nom, volume_horaire, classe_id, enseignant_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$module_nom, $module_volume, $classe_id, $enseignant_id]);

    $message = "Module ajouté avec succès.";
}

// Obtenir la liste des modules de la classe
$stmt = $pdo->prepare("SELECT m.id, m.nom, m.volume_horaire, u.nom AS enseignant_nom, u.prenom AS enseignant_prenom 
                       FROM Module m 
                       JOIN Utilisateur u ON m.enseignant_id = u.id 
                       WHERE m.classe_id = ?");
$stmt->execute([$classe_id]);
$modules = $stmt->fetchAll();

// Obtenir la liste des enseignants disponibles
$stmt = $pdo->query("SELECT id, nom, prenom FROM Utilisateur WHERE role = 'Enseignant'");
$enseignants_disponibles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Modules - <?= htmlspecialchars($classe_nom) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #84fab0 0%, #8fd3f4 100%);
            min-height: 100vh;
            padding: 20px 0;
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
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .card-header {
            background: linear-gradient(45deg, #5e72e4, #825ee4);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            font-weight: bold;
        }
        .btn-custom {
            background: linear-gradient(45deg, #5e72e4, #825ee4);
            border: none;
            color: white;
        }
        .btn-custom:hover {
            background: linear-gradient(45deg, #4a5cd1, #7046d1);
            color: white;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        .table thead {
            background-color: #5e72e4;
            color: white;
        }
        .module-form {
            background-color: #f8f9fe;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-primary"><i class="fas fa-book-open me-3"></i>Gestion des Modules</h1>
            <div class="text-end">
                <h4 class="text-muted">Prof. <?= htmlspecialchars($professeur_nom . ' ' . $professeur_prenom) ?></h4>
                <p class="text-muted">Responsable de <?= htmlspecialchars($classe_nom) ?></p>
            </div>
        </div>

        <?php if (isset($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card module-form">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Ajouter un Module</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="gestion_modules.php">
                            <div class="mb-3">
                                <label for="module_nom" class="form-label">Nom du Module</label>
                                <input type="text" class="form-control" id="module_nom" name="module_nom" required>
                            </div>
                            <div class="mb-3">
                                <label for="module_volume" class="form-label">Volume Horaire</label>
                                <input type="number" class="form-control" id="module_volume" name="module_volume" required>
                            </div>
                            <div class="mb-3">
                                <label for="enseignant_id" class="form-label">Enseignant</label>
                                <select class="form-select" id="enseignant_id" name="enseignant_id" required>
                                    <?php foreach ($enseignants_disponibles as $enseignant): ?>
                                        <option value="<?= $enseignant['id'] ?>"><?= htmlspecialchars($enseignant['nom'] . ' ' . $enseignant['prenom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="ajouter_module" class="btn btn-custom w-100"><i class="fas fa-plus-circle me-2"></i>Ajouter Module</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-list-alt me-2"></i>Modules de la Classe</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Volume Horaire</th>
                                        <th>Enseignant</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($modules) > 0): ?>
                                        <?php foreach ($modules as $module): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($module['nom']) ?></td>
                                                <td><?= htmlspecialchars($module['volume_horaire']) ?> heures</td>
                                                <td><?= htmlspecialchars($module['enseignant_nom'] . ' ' . $module['enseignant_prenom']) ?></td>
                                                <td>
                                                    <a href="modifier_module.php?id=<?= $module['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                                    <a href="supprimer_module.php?id=<?= $module['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce module ?');"><i class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Aucun module trouvé</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
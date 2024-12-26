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

// Obtenir la liste des modules pour la classe
$stmt = $pdo->prepare("
    SELECT Module.*, Utilisateur.nom AS enseignant_nom, Utilisateur.prenom AS enseignant_prenom
    FROM Module
    LEFT JOIN Utilisateur ON Module.enseignant_id = Utilisateur.id
    WHERE Module.classe_id = ?
");
$stmt->execute([$classe_id]);
$modules = $stmt->fetchAll();

// Ajouter un module
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
    $nom = $_POST['nom'];
    $volume_horaire = $_POST['volume_horaire'];
    $enseignant_id = $_POST['enseignant_id'];

    $stmt = $pdo->prepare("INSERT INTO Module (nom, volume_horaire, classe_id, enseignant_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nom, $volume_horaire, $classe_id, $enseignant_id]);

    header("Location: affectation_modules.php");
    exit();
}

// Supprimer un module
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $module_id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM Module WHERE id = ?");
    $stmt->execute([$module_id]);

    header("Location: affectation_modules.php");
    exit();
}

// Fonctionnalité de mise à jour du module
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier'])) {
    $module_id = $_POST['module_id'];
    $enseignant_id = $_POST['enseignant_id'];

    $stmt = $pdo->prepare("UPDATE Module SET enseignant_id = ? WHERE id = ?");
    $stmt->execute([$enseignant_id, $module_id]);

    header("Location: affectation_modules.php");
    exit();
}

// Obtenir la liste des enseignants disponibles
$stmt = $pdo->query("SELECT id, nom, prenom FROM Utilisateur WHERE role = 'Enseignant'");
$enseignants_disponibles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Modules - <?= htmlspecialchars($classe['nom']) ?></title>
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
            margin-bottom: 30px;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 15px 20px;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            background-color: #f1f3f5;
        }
        .modal-content {
            border-radius: 15px;
        }
        .modal-header {
            background-color: #007bff;
            color: white;
            border-radius: 15px 15px 0 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center my-4">Gestion des Modules - <?= htmlspecialchars($classe['nom']) ?></h1>

        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Ajouter un nouveau module</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="affectation_modules.php">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="nom" class="form-label">Nom du Module</label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                        <div class="col-md-4">
                            <label for="volume_horaire" class="form-label">Volume Horaire</label>
                            <input type="number" class="form-control" id="volume_horaire" name="volume_horaire" required>
                        </div>
                        <div class="col-md-4">
                            <label for="enseignant_id" class="form-label">Enseignant</label>
                            <select class="form-select" id="enseignant_id" name="enseignant_id" required>
                                <?php foreach ($enseignants_disponibles as $enseignant): ?>
                                    <option value="<?= $enseignant['id'] ?>"><?= htmlspecialchars($enseignant['nom'] . ' ' . $enseignant['prenom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="ajouter" class="btn btn-primary mt-3"><i class="fas fa-plus me-2"></i>Ajouter Module</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-list-ul me-2"></i>Liste des modules</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nom du Module</th>
                                <th>Enseignant</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modules as $module): ?>
                                <tr>
                                    <td><?= htmlspecialchars($module['nom']) ?></td>
                                    <td><?= htmlspecialchars($module['enseignant_nom'] . ' ' . $module['enseignant_prenom']) ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalModifier<?= $module['id'] ?>"><i class="fas fa-edit me-1"></i>Modifier</button>
                                        <a href="affectation_modules.php?action=supprimer&id=<?= $module['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Voulez-vous vraiment supprimer ce module ?')"><i class="fas fa-trash-alt me-1"></i>Supprimer</a>
                                    </td>
                                </tr>

                                <!-- Modal Modifier -->
                                <div class="modal fade" id="modalModifier<?= $module['id'] ?>" tabindex="-1" aria-labelledby="modalModifierLabel<?= $module['id'] ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalModifierLabel<?= $module['id'] ?>">Modifier Affectation</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="POST" action="affectation_modules.php">
                                                    <input type="hidden" name="module_id" value="<?= $module['id'] ?>">
                                                    <div class="mb-3">
                                                        <label for="enseignant_id" class="form-label">Enseignant</label>
                                                        <select class="form-select" id="enseignant_id" name="enseignant_id" required>
                                                            <?php foreach ($enseignants_disponibles as $enseignant): ?>
                                                                <option value="<?= $enseignant['id'] ?>" <?= ($enseignant['id'] == $module['enseignant_id']) ? 'selected' : '' ?>><?= htmlspecialchars($enseignant['nom'] . ' ' . $enseignant['prenom']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <button type="submit" name="modifier" class="btn btn-primary"><i class="fas fa-save me-2"></i>Enregistrer les modifications</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($modules)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">Aucun module trouvé.</td>
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
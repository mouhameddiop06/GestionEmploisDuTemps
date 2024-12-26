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

// Obtenir la liste des modules et enseignants pour la classe
$stmt = $pdo->prepare("
    SELECT Module.*, Utilisateur.nom AS enseignant_nom, Utilisateur.prenom AS enseignant_prenom
    FROM Module
    LEFT JOIN Utilisateur ON Module.enseignant_id = Utilisateur.id
    WHERE Module.classe_id = ?
");
$stmt->execute([$classe_id]);
$modules = $stmt->fetchAll();

// Supprimer un module
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $module_id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM Module WHERE id = ?");
    $stmt->execute([$module_id]);

    header("Location: gestion_enseignants.php");
    exit();
}

// Fonctionnalité de mise à jour du module
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier'])) {
    $module_id = $_POST['module_id'];
    $nom = $_POST['nom'];
    $volume_horaire = $_POST['volume_horaire'];
    $enseignant_id = $_POST['enseignant_id'];

    $stmt = $pdo->prepare("UPDATE Module SET nom = ?, volume_horaire = ?, enseignant_id = ? WHERE id = ?");
    $stmt->execute([$nom, $volume_horaire, $enseignant_id, $module_id]);

    header("Location: gestion_enseignants.php");
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
    <title>Gestion des Modules et Enseignants - <?= htmlspecialchars($classe['nom']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
        }
        .header {
            background: linear-gradient(135deg, #3a7bd5, #00d2ff);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: none;
            font-weight: bold;
        }
        .btn-custom {
            border-radius: 20px;
            padding: 0.375rem 1rem;
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
        <div class="header">
            <h1 class="mb-0">Gestion des Modules et Enseignants</h1>
            <p class="mb-0">Classe : <?= htmlspecialchars($classe['nom']) ?></p>
        </div>

        <div class="row">
            <?php foreach ($modules as $module): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?= htmlspecialchars($module['nom']) ?></h5>
                            <span class="badge bg-primary"><?= htmlspecialchars($module['volume_horaire']) ?> heures</span>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><strong>Enseignant :</strong> <?= htmlspecialchars($module['enseignant_nom'] . ' ' . $module['enseignant_prenom']) ?></p>
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-warning btn-sm btn-custom" data-bs-toggle="modal" data-bs-target="#modalModifier<?= $module['id'] ?>">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                                <a href="gestion_enseignants.php?action=supprimer&id=<?= $module['id'] ?>" class="btn btn-danger btn-sm btn-custom" onclick="return confirm('Voulez-vous vraiment supprimer ce module ?')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Modifier -->
                <div class="modal fade" id="modalModifier<?= $module['id'] ?>" tabindex="-1" aria-labelledby="modalModifierLabel<?= $module['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalModifierLabel<?= $module['id'] ?>">Modifier Module</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" action="gestion_enseignants.php">
                                    <input type="hidden" name="module_id" value="<?= $module['id'] ?>">
                                    <div class="mb-3">
                                        <label for="nom" class="form-label">Nom du Module</label>
                                        <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($module['nom']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="volume_horaire" class="form-label">Volume Horaire</label>
                                        <input type="number" class="form-control" id="volume_horaire" name="volume_horaire" value="<?= htmlspecialchars($module['volume_horaire']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="enseignant_id" class="form-label">Enseignant</label>
                                        <select class="form-select" id="enseignant_id" name="enseignant_id" required>
                                            <?php foreach ($enseignants_disponibles as $enseignant): ?>
                                                <option value="<?= $enseignant['id'] ?>" <?= ($enseignant['id'] == $module['enseignant_id']) ? 'selected' : '' ?>><?= htmlspecialchars($enseignant['nom'] . ' ' . $enseignant['prenom']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="submit" name="modifier" class="btn btn-primary btn-custom w-100">Enregistrer les modifications</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($modules)): ?>
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        Aucun module trouvé pour cette classe.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
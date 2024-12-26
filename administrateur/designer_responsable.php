<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    header("Location: ../authentification.php");
    exit();
}

include '../config.php';

// Gérer la désignation du professeur responsable
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['designer_responsable'])) {
    $classe_id = $_POST['classe_id'];
    $enseignant_id = $_POST['enseignant_id'];

    // Mettre à jour la classe avec le professeur responsable
    $stmt = $pdo->prepare("UPDATE Classe SET professeur_responsable_id = ? WHERE id = ?");
    $stmt->execute([$enseignant_id, $classe_id]);

    // Vérifier si l'enseignant a déjà le rôle 'profresponsable'
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs_roles WHERE utilisateur_id = ? AND role = 'profresponsable'");
    $stmt->execute([$enseignant_id]);
    $roleExists = $stmt->fetch();

    // Ajouter le rôle 'profresponsable' si ce n'est pas déjà fait
    if (!$roleExists) {
        $stmt = $pdo->prepare("INSERT INTO utilisateurs_roles (utilisateur_id, role) VALUES (?, 'profresponsable')");
        $stmt->execute([$enseignant_id]);
    }

    $message = "Professeur responsable désigné avec succès.";
}

// Gérer la suppression du professeur responsable
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['classe_id'])) {
    $classe_id = $_GET['classe_id'];

    // Retirer le professeur responsable de la classe
    $stmt = $pdo->prepare("UPDATE Classe SET professeur_responsable_id = NULL WHERE id = ?");
    $stmt->execute([$classe_id]);

    // Supprimer le rôle 'profresponsable' de l'utilisateur si aucune autre classe n'est assignée
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Classe WHERE professeur_responsable_id = (SELECT professeur_responsable_id FROM Classe WHERE id = ?)");
    $stmt->execute([$classe_id]);
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $stmt = $pdo->prepare("DELETE FROM utilisateurs_roles WHERE utilisateur_id = (SELECT professeur_responsable_id FROM Classe WHERE id = ?) AND role = 'profresponsable'");
        $stmt->execute([$classe_id]);
    }

    $message = "Professeur responsable retiré avec succès.";
}

// Obtenir la liste des classes
$stmt = $pdo->query("SELECT id, nom FROM Classe");
$classes = $stmt->fetchAll();

// Obtenir la liste des enseignants
$stmt = $pdo->query("SELECT id, nom, prenom FROM Utilisateur WHERE role = 'Enseignant'");
$enseignants = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Désignation des Responsables</title>
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
            <h1 class="text-primary"><i class="fas fa-user-cog me-3"></i>Désignation des Responsables</h1>
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
                        <h3 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Désigner un Responsable</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="designer_responsable.php">
                            <div class="mb-3">
                                <label for="classe_id" class="form-label">Classe</label>
                                <select class="form-select" id="classe_id" name="classe_id" required>
                                    <?php foreach ($classes as $classe): ?>
                                        <option value="<?= $classe['id'] ?>"><?= htmlspecialchars($classe['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="enseignant_id" class="form-label">Enseignant</label>
                                <select class="form-select" id="enseignant_id" name="enseignant_id" required>
                                    <?php foreach ($enseignants as $enseignant): ?>
                                        <option value="<?= $enseignant['id'] ?>"><?= htmlspecialchars($enseignant['nom'] . ' ' . $enseignant['prenom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="designer_responsable" class="btn btn-custom w-100"><i class="fas fa-check-circle me-2"></i>Désigner Responsable</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-list-alt me-2"></i>Liste des Classes et Responsables</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Classe</th>
                                        <th>Responsable</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($classes as $classe): ?>
                                        <?php
                                        $stmt = $pdo->prepare("SELECT u.nom, u.prenom FROM Utilisateur u JOIN Classe c ON u.id = c.professeur_responsable_id WHERE c.id = ?");
                                        $stmt->execute([$classe['id']]);
                                        $responsable = $stmt->fetch();

                                        if ($responsable) {
                                            $responsable_nom = $responsable['nom'];
                                            $responsable_prenom = $responsable['prenom'];
                                        } else {
                                            $responsable_nom = "Pas encore de responsable";
                                            $responsable_prenom = "";
                                        }
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($classe['nom']) ?></td>
                                            <td><?= htmlspecialchars($responsable_nom . ' ' . $responsable_prenom) ?></td>
                                            <td>
                                                <?php if ($responsable_nom !== "Pas encore de responsable"): ?>
                                                    <a href="designer_responsable.php?action=supprimer&classe_id=<?= $classe['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir retirer ce responsable ?');"><i class="fas fa-trash"></i></a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
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

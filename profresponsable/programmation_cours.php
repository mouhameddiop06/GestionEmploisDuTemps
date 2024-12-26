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

// Obtenir les modules, enseignants et salles
$modules_stmt = $pdo->prepare("SELECT id, nom FROM Module WHERE classe_id = ?");
$modules_stmt->execute([$classe_id]);
$modules = $modules_stmt->fetchAll();

$enseignants_stmt = $pdo->query("SELECT id, nom, prenom FROM Utilisateur WHERE role = 'Enseignant'");
$enseignants = $enseignants_stmt->fetchAll();

$salles_stmt = $pdo->query("SELECT id, nom FROM Salle");
$salles = $salles_stmt->fetchAll();

// Ajouter un cours
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter'])) {
    $module_id = $_POST['module_id'];
    $enseignant_id = $_POST['enseignant_id'];
    $salle_id = $_POST['salle_id'];
    $date_heure = $_POST['date_heure'];
    $heure_debut = $_POST['heure_debut'];
    $heure_fin = $_POST['heure_fin'];

    $stmt = $pdo->prepare("INSERT INTO Cours (module_id, enseignant_id, salle_id, date_heure, heure_debut, heure_fin) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$module_id, $enseignant_id, $salle_id, $date_heure, $heure_debut, $heure_fin]);

    $cours_id = $pdo->lastInsertId();
    $stmt = $pdo->prepare("INSERT INTO EmploiDuTemps (classe_id, cours_id, date_heure, heure_debut, heure_fin) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$classe_id, $cours_id, $date_heure, $heure_debut, $heure_fin]);

    header("Location: programmation_cours.php");
    exit();
}

// Supprimer un cours
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $cours_id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM EmploiDuTemps WHERE cours_id = ?");
    $stmt->execute([$cours_id]);
    $stmt = $pdo->prepare("DELETE FROM Cours WHERE id = ?");
    $stmt->execute([$cours_id]);

    header("Location: programmation_cours.php");
    exit();
}

// Obtenir la liste des cours programmés
$stmt = $pdo->prepare("
    SELECT c.id, m.nom AS module, u.nom AS enseignant_nom, u.prenom AS enseignant_prenom, s.nom AS salle, ed.date_heure, ed.heure_debut, ed.heure_fin
    FROM EmploiDuTemps ed
    JOIN Cours c ON ed.cours_id = c.id
    JOIN Module m ON c.module_id = m.id
    JOIN Utilisateur u ON c.enseignant_id = u.id
    JOIN Salle s ON c.salle_id = s.id
    WHERE ed.classe_id = ?
    ORDER BY ed.date_heure, ed.heure_debut
");
$stmt->execute([$classe_id]);
$cours_programmes = $stmt->fetchAll();

// Récupération des disponibilités avec informations supplémentaires
if (isset($_GET['enseignant_id'])) {
    $enseignant_id = $_GET['enseignant_id'];
    $stmt = $pdo->prepare("
        SELECT d.jour, d.heure_debut, d.heure_fin, u.nom, u.prenom
        FROM Disponibilite d
        JOIN Utilisateur u ON d.enseignant_id = u.id
        WHERE d.enseignant_id = ?
    ");
    $stmt->execute([$enseignant_id]);
    $disponibilites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($disponibilites);
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planification des Cours - <?= htmlspecialchars($classe['nom']) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(45deg, #3498db, #8e44ad);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .btn-custom {
            background: linear-gradient(45deg, #3498db, #8e44ad);
            border: none;
            color: white;
        }
        .btn-custom:hover {
            background: linear-gradient(45deg, #2980b9, #7d3c98);
            color: white;
        }
        .table-custom {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
        }
        .table-custom thead {
            background-color: #3498db;
            color: white;
        }
        .animate-hover {
            transition: all 0.3s ease;
        }
        .animate-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        #disponibilites {
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f5f7fa;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="text-center mb-5 text-primary">Planification des Cours - <?= htmlspecialchars($classe['nom']) ?></h1>
        
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card animate-hover">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Ajouter un Cours</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="programmation_cours.php">
                            <div class="mb-3">
                                <label for="module_id" class="form-label">Module</label>
                                <select id="module_id" name="module_id" class="form-select" required>
                                    <?php foreach ($modules as $module): ?>
                                        <option value="<?= $module['id'] ?>"><?= htmlspecialchars($module['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="enseignant_id" class="form-label">Enseignant</label>
                                <select id="enseignant_id" name="enseignant_id" class="form-select" required onchange="loadDisponibilites(this.value)">
                                    <option value="">-- Sélectionnez un enseignant --</option>
                                    <?php foreach ($enseignants as $enseignant): ?>
                                        <option value="<?= $enseignant['id'] ?>">Mr/Mme <?= htmlspecialchars($enseignant['nom'] . ' ' . $enseignant['prenom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div id="disponibilites"></div>
                            <div class="mb-3">
                                <label for="salle_id" class="form-label">Salle</label>
                                <select id="salle_id" name="salle_id" class="form-select" required>
                                    <?php foreach ($salles as $salle): ?>
                                        <option value="<?= $salle['id'] ?>"><?= htmlspecialchars($salle['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="date_heure" class="form-label">Date</label>
                                <input type="date" id="date_heure" name="date_heure" class="form-control" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="heure_debut" class="form-label">Début</label>
                                    <input type="time" id="heure_debut" name="heure_debut" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="heure_fin" class="form-label">Fin</label>
                                    <input type="time" id="heure_fin" name="heure_fin" class="form-control" required>
                                </div>
                            </div>
                            <button type="submit" name="ajouter" class="btn btn-custom w-100"><i class="fas fa-calendar-plus me-2"></i>Planifier</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="card animate-hover">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Cours Programmés</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-custom">
                                <thead>
                                    <tr>
                                        <th>Module</th>
                                        <th>Enseignant</th>
                                        <th>Salle</th>
                                        <th>Date & Heure</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cours_programmes as $cours): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($cours['module']) ?></td>
                                            <td><?= htmlspecialchars($cours['enseignant_nom'] . ' ' . $cours['enseignant_prenom']) ?></td>
                                            <td><?= htmlspecialchars($cours['salle']) ?></td>
                                            <td>
                                                <?= htmlspecialchars(date('d/m/Y', strtotime($cours['date_heure']))) ?><br>
                                                <small><?= htmlspecialchars($cours['heure_debut'] . ' - ' . $cours['heure_fin']) ?></small>
                                            </td>
                                            <td>
                                                <a href="modifier_cours.php?id=<?= $cours['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                                <a href="programmation_cours.php?action=supprimer&id=<?= $cours['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce cours ?');"><i class="fas fa-trash"></i></a>
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
    <script>
        function loadDisponibilites(enseignant_id) {
            if (enseignant_id === "") {
                document.getElementById('disponibilites').innerHTML = "";
                return;
            }

            fetch("programmation_cours.php?enseignant_id=" + enseignant_id)
                .then(response => response.json())
                .then(data => {
                    let disponibilitesHTML = "<h5>Disponibilités :</h5>";
                    if (data.length > 0) {
                        data.forEach(disponibilite => {
                            disponibilitesHTML += `
                                <p>
                                    <strong>${disponibilite.jour}</strong> : Mr/Mme ${disponibilite.nom} ${disponibilite.prenom}
                                    de ${disponibilite.heure_debut} à ${disponibilite.heure_fin}
                                </p>`;
                        });
                    } else {
                        disponibilitesHTML += "<p>Aucune disponibilité trouvée pour cet enseignant.</p>";
                    }
                    document.getElementById('disponibilites').innerHTML = disponibilitesHTML;
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>

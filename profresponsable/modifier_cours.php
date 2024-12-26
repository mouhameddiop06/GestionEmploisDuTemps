<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un professeur responsable
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Professeur') {
    header("Location: ../authentification.php");
    exit();
}

include '../config.php';

// Obtenir l'ID du cours à modifier
$cours_id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$cours_id) {
    header("Location: programmation_cours.php");
    exit();
}

// Obtenir les informations du cours à modifier
$stmt = $pdo->prepare("SELECT * FROM Cours WHERE id = ?");
$stmt->execute([$cours_id]);
$cours = $stmt->fetch();
if (!$cours) {
    header("Location: programmation_cours.php");
    exit();
}

// Obtenir la classe à partir du module
$stmt = $pdo->prepare("SELECT classe_id FROM Module WHERE id = ?");
$stmt->execute([$cours['module_id']]);
$classe_id = $stmt->fetchColumn();

// Obtenir les modules, enseignants et salles
$modules_stmt = $pdo->prepare("SELECT id, nom FROM Module WHERE classe_id = ?");
$modules_stmt->execute([$classe_id]);
$modules = $modules_stmt->fetchAll();

$enseignants_stmt = $pdo->query("SELECT id, nom, prenom FROM Utilisateur WHERE role = 'Enseignant'");
$enseignants = $enseignants_stmt->fetchAll();

$salles_stmt = $pdo->query("SELECT id, nom FROM Salle");
$salles = $salles_stmt->fetchAll();

// Mettre à jour le cours
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier'])) {
    $module_id = $_POST['module_id'];
    $enseignant_id = $_POST['enseignant_id'];
    $salle_id = $_POST['salle_id'];
    $date_heure = $_POST['date_heure'];
    $heure_debut = $_POST['heure_debut'];
    $heure_fin = $_POST['heure_fin'];

    $stmt = $pdo->prepare("UPDATE Cours SET module_id = ?, enseignant_id = ?, salle_id = ?, date_heure = ?, heure_debut = ?, heure_fin = ? WHERE id = ?");
    $stmt->execute([$module_id, $enseignant_id, $salle_id, $date_heure, $heure_debut, $heure_fin, $cours_id]);

    $stmt = $pdo->prepare("UPDATE EmploiDuTemps SET date_heure = ?, heure_debut = ?, heure_fin = ? WHERE cours_id = ?");
    $stmt->execute([$date_heure, $heure_debut, $heure_fin, $cours_id]);



    $stmt = $pdo->prepare("SELECT id FROM Utilisateur WHERE role = 'Etudiant' AND id IN (SELECT etudiant_id FROM Inscription WHERE classe_id = ?)");
    $stmt->execute([$classe_id]);
    $etudiants = $stmt->fetchAll();

    foreach ($etudiants as $etudiant) {
        $message = "Le cours '" . htmlspecialchars($cours['nom']) . "' a été modifié. Veuillez vérifier le nouvel emploi du temps.";
        $stmt = $pdo->prepare("INSERT INTO Notification (utilisateur_id, message, date_heure) VALUES (?, ?, NOW())");
        $stmt->execute([$etudiant['id'], $message]);
    }

    header("Location: programmation_cours.php");
    exit();
}




if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier'])) {
    // Code pour mettre à jour le cours...

    // Code pour ajouter une notification pour les gestionnaires
    $gestionnaires_stmt = $pdo->query("SELECT id FROM Utilisateur WHERE role = 'Gestionnaire'");
    $gestionnaires = $gestionnaires_stmt->fetchAll();

    foreach ($gestionnaires as $gestionnaire) {
        $message = "Un cours a été modifié pour la classe " . htmlspecialchars($classe['nom']);
        $stmt = $pdo->prepare("INSERT INTO Notifications (user_id, message) VALUES (?, ?)");
        $stmt->execute([$gestionnaire['id'], $message]);
    }

    header("Location: programmation_cours.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Cours</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2 class="text-center my-4">Modifier Cours</h2>

        <form method="POST" action="modifier_cours.php?id=<?= $cours_id ?>">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="module_id">Module</label>
                    <select id="module_id" name="module_id" class="form-control" required>
                        <?php foreach ($modules as $module): ?>
                            <option value="<?= $module['id'] ?>" <?= ($module['id'] == $cours['module_id']) ? 'selected' : '' ?>><?= htmlspecialchars($module['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="enseignant_id">Enseignant</label>
                    <select id="enseignant_id" name="enseignant_id" class="form-control" required>
                        <?php foreach ($enseignants as $enseignant): ?>
                            <option value="<?= $enseignant['id'] ?>" <?= ($enseignant['id'] == $cours['enseignant_id']) ? 'selected' : '' ?>><?= htmlspecialchars($enseignant['nom'] . ' ' . $enseignant['prenom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="salle_id">Salle</label>
                    <select id="salle_id" name="salle_id" class="form-control" required>
                        <?php foreach ($salles as $salle): ?>
                            <option value="<?= $salle['id'] ?>" <?= ($salle['id'] == $cours['salle_id']) ? 'selected' : '' ?>><?= htmlspecialchars($salle['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="date_heure">Date</label>
                    <input type="date" id="date_heure" name="date_heure" class="form-control" value="<?= htmlspecialchars(date('Y-m-d', strtotime($cours['date_heure']))) ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="heure_debut">Heure Début</label>
                    <input type="time" id="heure_debut" name="heure_debut" class="form-control" value="<?= htmlspecialchars($cours['heure_debut']) ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="heure_fin">Heure Fin</label>
                    <input type="time" id="heure_fin" name="heure_fin" class="form-control" value="<?= htmlspecialchars($cours['heure_fin']) ?>" required>
                </div>
            </div>
            <button type="submit" name="modifier" class="btn btn-primary btn-block">Modifier Cours</button>
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

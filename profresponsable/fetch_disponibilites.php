<?php
include '../config.php';

if (isset($_GET['enseignant_id'])) {
    $enseignant_id = $_GET['enseignant_id'];

    $stmt = $pdo->prepare("SELECT id, jour, heure_debut, heure_fin FROM Disponibilite WHERE enseignant_id = ?");
    $stmt->execute([$enseignant_id]);
    $disponibilites = $stmt->fetchAll();

    if (count($disponibilites) > 0) {
        foreach ($disponibilites as $disponibilite) {
            echo "<option value='" . $disponibilite['id'] . "'>" . $disponibilite['jour'] . " - " . $disponibilite['heure_debut'] . " à " . $disponibilite['heure_fin'] . "</option>";
        }
    } else {
        echo "<option value=''>Aucune disponibilité trouvée</option>";
    }
}
?>

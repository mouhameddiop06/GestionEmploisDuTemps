<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) { 
    header("Location: authentification.php");
    exit();
}

// Si l'utilisateur a choisi un rôle
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['role'])) {
    $_SESSION['user_role'] = $_POST['role'];

    if ($_SESSION['user_role'] == 'Professeur') {
        header("Location: profresponsable/dashboard.php");
        exit();
    } elseif ($_SESSION['user_role'] == 'Enseignant') {
        header("Location: enseignant/dashboardenseignant.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir le Rôle</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Choisissez votre rôle</h2>
        <form method="POST" action="choisir_role.php">
            <div class="d-grid gap-2 col-6 mx-auto mt-4">
                <button type="submit" name="role" value="Professeur" class="btn btn-primary">Professeur Responsable</button>
                <button type="submit" name="role" value="Enseignant" class="btn btn-secondary">Enseignant</button>
            </div>
        </form>
    </div>
</body>
</html>

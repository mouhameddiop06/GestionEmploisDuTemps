<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'Enseignant') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];
    $_SESSION['user_role'] = $role;
    header("Location: dashboard{$role}.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Choisir Rôle</title>
</head>
<body>
    <h1>Choisissez votre rôle</h1>
    <form method="post">
        <button type="submit" name="role" value="Enseignant">Enseignant</button>
        <button type="submit" name="role" value="ProfResponsable">Professeur Responsable</button>
    </form>
</body>
</html>

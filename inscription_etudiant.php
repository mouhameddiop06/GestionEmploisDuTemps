<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $nom = $_POST['nom']; 
    $prenom = $_POST['prenom'];
    $email = $_POST['email_inscription'];
    $password = $_POST['password_inscription'];

    // Vérifier si l'utilisateur existe déjà
    $stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        // Rediriger avec un message d'erreur si l'utilisateur existe déjà
        header("Location: authentification.php?error=" . urlencode("L'utilisateur existe déjà"));
        exit();
    }

    // Insérer le nouvel étudiant dans la base de données
    $stmt = $pdo->prepare("INSERT INTO Utilisateur (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'Etudiant')");
    $stmt->execute([$nom, $prenom, $email, $password]);

    // Rediriger vers la page de connexion avec un message de succès
    header("Location: authentification.php?success=" . urlencode("Inscription réussie. Veuillez vous connecter."));
    exit();
}
?>

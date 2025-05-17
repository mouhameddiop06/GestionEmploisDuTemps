<?php
include 'config.php';

// Récupérer tous les utilisateurs
$stmt = $pdo->query("SELECT id, role FROM Utilisateur");
$users = $stmt->fetchAll();

foreach ($users as $user) {
    $utilisateur_id = $user['id'];
    $role = strtolower($user['role']); // Convertir en minuscule pour correspondre aux rôles

    // Vérifier si le rôle existe déjà dans utilisateurs_roles
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs_roles WHERE utilisateur_id = ? AND role = ?");
    $stmt->execute([$utilisateur_id, $role]);
    $roleExists = $stmt->fetch();

    // Si le rôle n'existe pas, l'ajouter
    if (!$roleExists) {
        $stmt = $pdo->prepare("INSERT INTO utilisateurs_roles (utilisateur_id, role) VALUES (?, ?)");
        $stmt->execute([$utilisateur_id, $role]);
        echo "Rôle '$role' ajouté pour l'utilisateur ID $utilisateur_id<br>";
    }
}

echo "Migration des rôles terminée.";

<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Administrateur') {
    header("Location: ../authentification.php");
    exit();
}

include '../config.php';

// Vérifier si l'ID de l'enseignant est fourni
if (isset($_GET['id'])) {
    $enseignant_id = $_GET['id'];

    // Supprimer l'enseignant de la table utilisateurs_roles
    $stmt = $pdo->prepare("DELETE FROM utilisateurs_roles WHERE utilisateur_id = ?");
    $stmt->execute([$enseignant_id]);

    // Supprimer l'enseignant de la table Utilisateur
    $stmt = $pdo->prepare("DELETE FROM Utilisateur WHERE id = ? AND role = 'Enseignant'");
    $stmt->execute([$enseignant_id]);

    $message = "Enseignant supprimé avec succès.";

    // Rediriger vers la page de gestion des enseignants avec un message de succès
    header("Location: gestion_enseignants.php?message=" . urlencode($message));
    exit();
} else {
    // Rediriger avec un message d'erreur si l'ID n'est pas fourni
    $error = "Aucun enseignant sélectionné pour la suppression.";
    header("Location: gestion_enseignants.php?error=" . urlencode($error));
    exit();
}

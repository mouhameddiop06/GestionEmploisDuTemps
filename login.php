<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];  
    $role = $_POST['role'];

    // Préparation et exécution de la requête SQL
    $stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE email = ? AND role = ?");
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch();

    if ($user && $password === $user['mot_de_passe']) {
        // Stocker les informations de l'utilisateur dans la session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nom'] . ' ' . $user['prenom'];
        $_SESSION['user_role'] = $user['role'];

        // Vérifier si l'utilisateur a plusieurs rôles
        $stmt_roles = $pdo->prepare("SELECT role FROM Utilisateur WHERE email = ?");
        $stmt_roles->execute([$email]);
        $roles = $stmt_roles->fetchAll(PDO::FETCH_COLUMN);

        if (count($roles) > 1) {
            // Si l'utilisateur a plusieurs rôles, rediriger vers choisir_role.php
            $_SESSION['roles'] = $roles;
            header("Location: choisir_role.php");
        } else {
            // Sinon, rediriger vers le tableau de bord approprié
            switch ($user['role']) {
                case 'Professeur':
                    header("Location: profresponsable/dashboard.php");
                    break;
                case 'Enseignant':
                    header("Location: enseignant/dashboardenseignant.php");
                    break;
                case 'Etudiant':
                    if ($user['statut'] == 'approuvé') {
                        header("Location: etudiant/dashboardetudiant.php");
                    } else {
                        $error = "Votre inscription n'a pas encore été approuvée.";
                        header("Location: authentification.php?error=" . urlencode($error));
                    }
                    break;
                case 'Gestionnaire':
                    header("Location: gestionnaire/dashboardgestionnaire.php");
                    break;
                case 'Administrateur':
                    header("Location: administrateur/dashboardadministrateur.php");
                    break;
                default:
                    $error = "Rôle non reconnu.";
                    header("Location: authentification.php?error=" . urlencode($error));
                    break;
            }
        }
        exit();
    } else {
        $error = "Identifiants incorrects";
        header("Location: authentification.php?error=" . urlencode($error));
        exit();
    }
}
?>

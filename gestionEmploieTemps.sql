CREATE DATABASE IF NOT EXISTS GestionEmploisDuTemps;
USE GestionEmploisDuTemps;


-- Table Utilisateur
CREATE TABLE Utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('Administrateur', 'Professeur', 'Enseignant', 'Etudiant', 'Gestionnaire') NOT NULL
);


-- Insérer des utilisateurs (les professeurs responsables)
INSERT INTO Utilisateur (nom, prenom, email, mot_de_passe, role) VALUES 
('NDONG', 'Joseph', 'joe@gmail.com', 'mmmmm', 'Administrateur'),
('BAME', 'Ndiouma', 'bameadmin@gmail.com', 'mmmmm', 'Administrateur'), 
('GUEYE', 'BAMBA', 'bamba@gmail.com', 'mmmmm', 'Professeur'),
('BAME', 'Ndiouma', 'bame@gmail.com', 'mmmmm', 'Professeur');

-- Table Classe
CREATE TABLE Classe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    professeur_responsable_id INT,
    FOREIGN KEY (professeur_responsable_id) REFERENCES Utilisateur(id)
);

-- Ajouter des classes avec ID, nom et professeur responsable
INSERT INTO Classe (id, nom, professeur_responsable_id) VALUES 
(1, 'Licence1', 3),  -- ID 1 fait référence au premier professeur inséré
(2, 'Licence2', 4);  -- ID 2 fait référence au deuxième professeur inséré

-- Table Module
CREATE TABLE Module (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    volume_horaire INT NOT NULL,
    classe_id INT,
    FOREIGN KEY (classe_id) REFERENCES Classe(id),
    enseignant_id INT,
    FOREIGN KEY (enseignant_id) REFERENCES Utilisateur(id)
);

-- Table Salle
CREATE TABLE Salle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    capacite INT NOT NULL
);

-- Table Cours
CREATE TABLE Cours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT,
    FOREIGN KEY (module_id) REFERENCES Module(id),
    enseignant_id INT,
    FOREIGN KEY (enseignant_id) REFERENCES Utilisateur(id),
    salle_id INT,
    FOREIGN KEY (salle_id) REFERENCES Salle(id),
    date_heure DATETIME NOT NULL,
    volume_horaire_fait INT NOT NULL
);

-- Table Emploi du Temps
CREATE TABLE EmploiDuTemps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    classe_id INT,
    FOREIGN KEY (classe_id) REFERENCES Classe(id),
    cours_id INT,
    FOREIGN KEY (cours_id) REFERENCES Cours(id),
    date_heure DATETIME NOT NULL
);

-- Table Notifications
CREATE TABLE Notification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT,
    FOREIGN KEY (utilisateur_id) REFERENCES Utilisateur(id),
    message TEXT NOT NULL,
    date_heure DATETIME NOT NULL,
    vu BOOLEAN NOT NULL DEFAULT FALSE
);

-- Table Disponibilites
CREATE TABLE Disponibilite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enseignant_id INT,
    FOREIGN KEY (enseignant_id) REFERENCES Utilisateur(id),
    jour ENUM('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche') NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL
);

-- Table Inscription
CREATE TABLE Inscription (
    id INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id INT,
    cours_id INT,
    FOREIGN KEY (etudiant_id) REFERENCES Utilisateur(id),
    FOREIGN KEY (cours_id) REFERENCES Cours(id)
);

-- Table EnseignantClasse
CREATE TABLE EnseignantClasse (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enseignant_id INT,
    classe_id INT,
    FOREIGN KEY (enseignant_id) REFERENCES Utilisateur(id),
    FOREIGN KEY (classe_id) REFERENCES Classe(id)
);

-- Ajouter des colonnes et des contraintes
ALTER TABLE Cours ADD heure_debut TIME NOT NULL, ADD heure_fin TIME NOT NULL;
ALTER TABLE EmploiDuTemps ADD heure_debut TIME NOT NULL, ADD heure_fin TIME NOT NULL;
ALTER TABLE Utilisateur ADD statut ENUM('en attente', 'approuvé', 'rejeté') NOT NULL DEFAULT 'en attente';
ALTER TABLE EmploiDuTemps ADD salle_id INT, ADD FOREIGN KEY (salle_id) REFERENCES Salle(id);
ALTER TABLE Cours ADD COLUMN disponibilite_id INT;
ALTER TABLE Cours ADD FOREIGN KEY (disponibilite_id) REFERENCES Disponibilite(id);

-- Table utilisateurs_roles
CREATE TABLE utilisateurs_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT,
    role ENUM('admin', 'profresponsable', 'enseignant', 'etudiant', 'gestionnaire') NOT NULL,
    FOREIGN KEY (utilisateur_id) REFERENCES Utilisateur(id)
);


INSERT INTO utilisateurs_roles (utilisateur_id, role) 
SELECT id, 'admin' FROM Utilisateur WHERE email = 'joe@gmail.com';

INSERT INTO utilisateurs_roles (utilisateur_id, role) 
SELECT id, 'admin' FROM Utilisateur WHERE email = 'bameadmin@gmail.com';



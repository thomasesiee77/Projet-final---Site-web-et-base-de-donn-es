CREATE DATABASE IF NOT EXISTS carnet_peche;
USE carnet_peche;
CREATE TABLE PECHEUR (
    id_pecheur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    niveau VARCHAR(20)
);

CREATE TABLE LIEU (
    id_lieu INT AUTO_INCREMENT PRIMARY KEY,
    nom_lieu VARCHAR(100) NOT NULL,
    ville VARCHAR(100),
    categorie_piscicole VARCHAR(50)
);

CREATE TABLE ESPECE (
    id_espece INT AUTO_INCREMENT PRIMARY KEY,
    nom_espece VARCHAR(50) NOT NULL,
    taille_legale INT -- en cm
);

CREATE TABLE CAPTURE (
    id_capture INT AUTO_INCREMENT PRIMARY KEY,
    date_capture DATE NOT NULL,
    poids INT, -- en grammes
    taille INT, -- en cm
    id_pecheur INT,
    id_lieu INT,
    id_espece INT,
    FOREIGN KEY (id_pecheur) REFERENCES PECHEUR(id_pecheur) ON DELETE CASCADE,
    FOREIGN KEY (id_lieu) REFERENCES LIEU(id_lieu),
    FOREIGN KEY (id_espece) REFERENCES ESPECE(id_espece)
);

-- Jeu de données de test (Exemples)
INSERT INTO LIEU (nom_lieu, ville, categorie_piscicole) VALUES 
('Étang du Ru de Nesles', 'Champs-sur-Marne', '2ème catégorie'),
('La Marne', 'Noisiel', '2ème catégorie');

INSERT INTO ESPECE (nom_espece, taille_legale) VALUES 
('Brochet', 60),
('Carpe Commune', 0),
('Truite Fario', 23);

-- Note : Le mot de passe ici est "peche123" haché en bcrypt comme demandé dans le Cours 2
INSERT INTO PECHEUR (nom, prenom, email, mot_de_passe, niveau) VALUES 
('Dupont', 'Jean', 'jean.dupont@edu.esiee.fr', '$2y$10$7R8gMvH2eK8Xv...', 'Confirmé');
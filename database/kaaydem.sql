CREATE DATABASE IF NOT EXISTS kaaydem CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kaaydem;

CREATE TABLE utilisateurs (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom              VARCHAR(100)  NOT NULL,
    prenom           VARCHAR(100)  NOT NULL,
    email            VARCHAR(180)  NOT NULL UNIQUE,
    mot_de_passe_hash VARCHAR(255) NOT NULL,
    telephone        VARCHAR(20)   DEFAULT '',
    statut_compte    ENUM('en_attente','actif','suspendu') NOT NULL DEFAULT 'en_attente',
    role             ENUM('membre','admin')                NOT NULL DEFAULT 'membre',
    created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE profils_conducteur (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    membre_id           INT UNSIGNED NOT NULL UNIQUE,
    numero_permis       VARCHAR(50)  NOT NULL,
    marque_vehicule     VARCHAR(80)  NOT NULL,
    modele_vehicule     VARCHAR(80)  NOT NULL,
    immatriculation     VARCHAR(20)  NOT NULL,
    nb_places_vehicule  TINYINT UNSIGNED NOT NULL DEFAULT 4,
    statut_validation   ENUM('non_demande','en_attente','valide','refuse') NOT NULL DEFAULT 'en_attente',
    note_moyenne        DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    nombre_evaluations  INT UNSIGNED NOT NULL DEFAULT 0,
    date_validation     DATETIME     DEFAULT NULL,
    created_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (membre_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE trajets (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conducteur_id    INT UNSIGNED NOT NULL,
    ville_depart     VARCHAR(120) NOT NULL,
    ville_arrivee    VARCHAR(120) NOT NULL,
    date_heure_depart DATETIME   NOT NULL,
    nb_places_total  TINYINT UNSIGNED NOT NULL,
    nb_places_dispo  TINYINT UNSIGNED NOT NULL,
    prix_par_place   DECIMAL(10,2) NOT NULL,
    statut           ENUM('ouvert','complet','cloture','annule') NOT NULL DEFAULT 'ouvert',
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (conducteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_depart_arrivee_date (ville_depart, ville_arrivee, date_heure_depart),
    INDEX idx_statut (statut)
) ENGINE=InnoDB;

CREATE TABLE arrets (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trajet_id INT UNSIGNED NOT NULL,
    libelle   VARCHAR(120) NOT NULL,
    ordre     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (trajet_id) REFERENCES trajets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reservations (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    passager_id         INT UNSIGNED NOT NULL,
    trajet_id           INT UNSIGNED NOT NULL,
    nb_places_reservees TINYINT UNSIGNED NOT NULL DEFAULT 1,
    statut              ENUM('en_attente','confirmee','terminee','annulee') NOT NULL DEFAULT 'en_attente',
    date_reservation    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (passager_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (trajet_id)   REFERENCES trajets(id)      ON DELETE CASCADE,
    INDEX idx_passager (passager_id),
    INDEX idx_trajet   (trajet_id)
) ENGINE=InnoDB;

CREATE TABLE historique_transitions (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT UNSIGNED NOT NULL,
    statut_avant   VARCHAR(20)  NOT NULL,
    statut_apres   VARCHAR(20)  NOT NULL,
    date_transition DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE evaluations (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reservation_id  INT UNSIGNED NOT NULL UNIQUE,
    evaluateur_id   INT UNSIGNED NOT NULL,
    evalue_id       INT UNSIGNED NOT NULL,
    note            TINYINT UNSIGNED NOT NULL CHECK (note BETWEEN 1 AND 5),
    commentaire     TEXT         NOT NULL,
    date_evaluation DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluateur_id)  REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (evalue_id)      REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE signalements (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    auteur_id        INT UNSIGNED NOT NULL,
    cible_id         INT UNSIGNED NOT NULL,
    motif            VARCHAR(120) NOT NULL,
    description      TEXT         NOT NULL,
    date_signalement DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    traite           TINYINT(1)   NOT NULL DEFAULT 0,
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (auteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (cible_id)  REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;


SET @mdp = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe_hash, telephone, statut_compte, role) VALUES
('Diallo', 'Admin', 'admin@kaaydem.sn', @mdp, '771000000', 'actif', 'admin');


INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe_hash, telephone, statut_compte, role) VALUES
('Seye',    'Fatou Bintou', 'bintou@kaaydem.sn',  @mdp, '771111111', 'actif', 'membre'),
('Dia',     'Khadidiatou',  'khadidiatou@kaaydem.sn', @mdp, '772222222', 'actif', 'membre'),
('Seck',    'Woude Nene',   'woude@kaaydem.sn',   @mdp, '773333333', 'actif', 'membre'),
('Sall',    'Coumba',       'coumba@kaaydem.sn',  @mdp, '774444444', 'actif', 'membre'),
('Ndiaye',  'Moussa',       'moussa@kaaydem.sn',  @mdp, '775555555', 'actif', 'membre'),
('Fall',    'Aissatou',     'aissatou@kaaydem.sn',@mdp, '776666666', 'actif', 'membre');


INSERT INTO profils_conducteur (membre_id, numero_permis, marque_vehicule, modele_vehicule, immatriculation, nb_places_vehicule, statut_validation, note_moyenne, nombre_evaluations, date_validation) VALUES
(2, 'SN-2020-001', 'Toyota',  'Corolla', 'DK-1234-A', 4, 'valide', 4.50, 2, NOW()),
(3, 'SN-2021-002', 'Peugeot', '308',     'DK-5678-B', 4, 'valide', 4.00, 1, NOW()),
(5, 'SN-2022-003', 'Renault', 'Clio',    'DK-9012-C', 3, 'en_attente', 0, 0, NULL);


INSERT INTO trajets (conducteur_id, ville_depart, ville_arrivee, date_heure_depart, nb_places_total, nb_places_dispo, prix_par_place, statut) VALUES
(2, 'Dakar',      'Diamniadio', DATE_ADD(NOW(), INTERVAL 1 DAY),    4, 3, 1500.00, 'ouvert'),
(2, 'Diamniadio', 'Dakar',      DATE_ADD(NOW(), INTERVAL 2 DAY),    4, 4, 1500.00, 'ouvert'),
(3, 'Dakar',      'Rufisque',   DATE_ADD(NOW(), INTERVAL 1 DAY),    4, 2, 800.00,  'ouvert'),
(3, 'Rufisque',   'Diamniadio', DATE_ADD(NOW(), INTERVAL 3 DAY),    4, 4, 700.00,  'ouvert'),
(2, 'Dakar',      'Thiès',      DATE_ADD(NOW(), INTERVAL 5 DAY),    4, 4, 2500.00, 'ouvert'),
(3, 'Dakar',      'Diamniadio', DATE_SUB(NOW(), INTERVAL 3 DAY),    4, 0, 1500.00, 'cloture');


INSERT INTO arrets (trajet_id, libelle, ordre) VALUES
(1, 'Rufisque',   1),
(1, 'Bargny',     2),
(1, 'Sébikotane', 3),
(3, 'Pikine',     1),
(3, 'Thiaroye',   2);


INSERT INTO reservations (passager_id, trajet_id, nb_places_reservees, statut, date_reservation) VALUES
(4, 1, 1, 'confirmee',  NOW()),
(5, 3, 2, 'en_attente', NOW()),
(4, 6, 1, 'terminee',   DATE_SUB(NOW(), INTERVAL 3 DAY)),
(6, 6, 1, 'terminee',   DATE_SUB(NOW(), INTERVAL 3 DAY));


INSERT INTO historique_transitions (reservation_id, statut_avant, statut_apres, date_transition) VALUES
(1, 'en_attente', 'confirmee',  NOW()),
(3, 'en_attente', 'confirmee',  DATE_SUB(NOW(), INTERVAL 3 DAY)),
(3, 'confirmee',  'terminee',   DATE_SUB(NOW(), INTERVAL 3 DAY)),
(4, 'en_attente', 'confirmee',  DATE_SUB(NOW(), INTERVAL 3 DAY)),
(4, 'confirmee',  'terminee',   DATE_SUB(NOW(), INTERVAL 3 DAY));


INSERT INTO evaluations (reservation_id, evaluateur_id, evalue_id, note, commentaire, date_evaluation) VALUES
(3, 4, 2, 5, 'Excellent conducteur, très ponctuel et agréable. Je recommande !', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(4, 6, 2, 4, 'Bon trajet, voiture propre. Légèrement en retard mais sympa.', DATE_SUB(NOW(), INTERVAL 2 DAY));


UPDATE profils_conducteur
SET note_moyenne = (SELECT AVG(note) FROM evaluations WHERE evalue_id = membre_id),
    nombre_evaluations = (SELECT COUNT(*) FROM evaluations WHERE evalue_id = membre_id)
WHERE membre_id = 2;


INSERT INTO signalements (auteur_id, cible_id, motif, description, traite) VALUES
(4, 5, 'Comportement inapproprié', 'Le conducteur a eu un comportement irrespectueux pendant le trajet.', 0);


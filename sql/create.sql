-- ============================================================
--  FoyerConnect — Script SQL complet
--  Base de données : foyerconnect
-- ============================================================

CREATE DATABASE IF NOT EXISTS foyerconnect
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE foyerconnect;

-- -----------------------------------------------------------
-- TABLE 1 : foyers
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS foyers (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(150)   NOT NULL,
    ville       VARCHAR(100)   NOT NULL,
    adresse     VARCHAR(255)   NOT NULL,
    telephone   VARCHAR(20)    NOT NULL,
    email       VARCHAR(150)   NOT NULL,
    capacite    INT            NOT NULL DEFAULT 0,
    prix_min    DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    prix_max    DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    photo       VARCHAR(255)   DEFAULT 'default-foyer.jpg',
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- TABLE 2 : candidats (étudiants)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS candidats (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    prenom          VARCHAR(100)  NOT NULL,
    nom             VARCHAR(100)  NOT NULL,
    date_naissance  DATE          NOT NULL,
    email           VARCHAR(150)  NOT NULL UNIQUE,
    telephone       VARCHAR(20)   NOT NULL,
    adresse         VARCHAR(255)  NOT NULL,
    etablissement   VARCHAR(200)  NOT NULL,
    niveau          ENUM('L1','L2','L3','CP1','CP2','CI1','CI2','CI3','M1','M2','Doctorat') NOT NULL DEFAULT 'L1',
    num_etudiant    VARCHAR(50)   NOT NULL UNIQUE,
    cin             VARCHAR(20)   NOT NULL,
    photo           VARCHAR(255)  DEFAULT 'default-avatar.jpg',
    mot_de_passe    VARCHAR(255)  NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- TABLE 3 : demandes (inscriptions)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS demandes (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    candidat_id     INT           NOT NULL,
    foyer_id        INT           NOT NULL,
    type_chambre    ENUM('simple','double','studio') NOT NULL DEFAULT 'simple',
    date_arrivee    DATE          NOT NULL,
    duree_mois      INT           NOT NULL DEFAULT 1,
    statut          ENUM('en_attente','acceptee','refusee') NOT NULL DEFAULT 'en_attente',
    commentaire     TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (candidat_id) REFERENCES candidats(id) ON DELETE CASCADE,
    FOREIGN KEY (foyer_id)    REFERENCES foyers(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- TABLE 4 : contacts (messages du formulaire Contact)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS contacts (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(200)  NOT NULL,
    email       VARCHAR(150)  NOT NULL,
    sujet       VARCHAR(255)  NOT NULL,
    message     TEXT          NOT NULL,
    lu          TINYINT(1)    NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


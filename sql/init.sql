USE foyerconnect;

-- Désactiver temporairement les vérifications de clés étrangères
SET FOREIGN_KEY_CHECKS = 0;

-- Vider les tables
DELETE FROM demandes;
DELETE FROM contacts;
DELETE FROM candidats;
DELETE FROM foyers;

-- Réinitialiser les auto-increment
ALTER TABLE demandes AUTO_INCREMENT = 1;
ALTER TABLE contacts AUTO_INCREMENT = 1;
ALTER TABLE candidats AUTO_INCREMENT = 1;
ALTER TABLE foyers AUTO_INCREMENT = 1;

-- Réactiver les vérifications
SET FOREIGN_KEY_CHECKS = 1;
-- -----------------------------------------------------------
-- DONNÉES : foyers (6 enregistrements)
-- -----------------------------------------------------------
INSERT INTO foyers (nom, ville, adresse, telephone, email, capacite, prix_min, prix_max, photo, description) VALUES
('Foyer Universitaire Moncef Bey',   'Tunis',  '12 Rue Moncef Bey, Tunis 1000',        '+216 71 658 857', 'moncefbey@foyerconnect.tn',    120, 220.00, 290.00, 'foyer1.jpg', 'Foyer mixte au cœur de Tunis, proche de toutes les universités.'),
('Foyer Universitaire Privé Filles', 'Sfax',   '5 Rue de l\'Académie, Sfax El Jadida', '+216 74 221 100', 'filles.sfax@foyerconnect.tn',   80, 200.00, 300.00, 'foyer2.jpg', 'Résidence réservée aux étudiantes, environnement sécurisé.'),
('Foyer El Ikbel',                   'Ariana', '8 Avenue de la Liberté, Ariana 2080',  '+216 71 701 450', 'elikbel@foyerconnect.tn',      100, 250.00, 395.00, 'foyer3.jpg', 'Foyer moderne à proximité immédiate de Tunis.'),
('Résidence Estudiante Monastir',    'Monastir','14 Rue Ibn Khaldoun, Monastir 5000',  '+216 73 460 230', 'monastir@foyerconnect.tn',      60, 180.00, 260.00, 'foyer4.jpg', 'Idéalement situé près des facultés de Monastir.'),
('Foyer Universitaire Sousse',       'Sousse', '22 Boulevard du 14 Janvier, Sousse',   '+216 73 225 678', 'sousse@foyerconnect.tn',        90, 190.00, 280.00, 'foyer5.jpg', 'Foyer équipé avec connexion Wi-Fi et cafétéria.'),
('Résidence Carthage',               'Tunis',  '3 Rue de Carthage, Tunis 1000',        '+216 71 332 900', 'carthage@foyerconnect.tn',      70, 300.00, 450.00, 'foyer6.jpg', 'Résidence premium avec studios individuels.');

-- -----------------------------------------------------------
-- DONNÉES : candidats (6 enregistrements)
-- Les mots de passe sont des hashes SHA2 fictifs pour la démo
-- -----------------------------------------------------------
INSERT INTO candidats (prenom, nom, date_naissance, email, telephone, adresse, etablissement, niveau, num_etudiant, cin, photo, mot_de_passe) VALUES
('Ahmed',   'Ben Saleh',  '2002-03-15', 'ahmed.bensaleh@etudiant.tn',  '+216 22 111 222', '7 Rue des Roses, Tunis',     'Université de Tunis El Manar',  'L2',  'ETU20240001', '12345678', 'ahmed.jpg',   SHA2('Pass@1234',256)),
('Mariem',  'Ben Ahmed',  '2001-07-22', 'mariem.benahmed@etudiant.tn', '+216 95 333 444', '15 Av. Habib Bourguiba, Sfax','Université de Sfax',           'L3',  'ETU20240002', '23456789', 'mariem.jpg',  SHA2('Pass@5678',256)),
('Iheb',    'Hamdi',      '2003-01-10', 'iheb.hamdi@etudiant.tn',      '+216 55 555 666', '3 Rue de la Paix, Ariana',   'Ecole Polytechnique de Tunisie','CP2', 'ETU20240003', '34567890', 'iheb.jpg',    SHA2('Secure#99',256)),
('Sarra',   'Trabelsi',   '2000-11-05', 'sarra.trabelsi@etudiant.tn',  '+216 27 777 888', '20 Rue Ibn Sina, Sousse',    'Université de Sousse',         'M1',  'ETU20240004', '45678901', 'sarra.jpg',   SHA2('MyPass!2024',256)),
('Youssef', 'Maalej',     '2002-06-30', 'youssef.maalej@etudiant.tn',  '+216 98 999 000', '11 Av. du 7 Novembre, Sfax', 'Université de Sfax',           'L1',  'ETU20240005', '56789012', 'youssef.jpg', SHA2('Youssef@2024',256)),
('Nour',    'Chaabane',   '2001-09-18', 'nour.chaabane@etudiant.tn',   '+216 50 123 456', '8 Rue du Jasmin, Monastir',  'Université de Monastir',       'L2',  'ETU20240006', '67890123', 'nour.jpg',    SHA2('Nour1234!',256));

-- -----------------------------------------------------------
-- DONNÉES : demandes (6 enregistrements)
-- -----------------------------------------------------------
INSERT INTO demandes (candidat_id, foyer_id, type_chambre, date_arrivee, duree_mois, statut, commentaire) VALUES
(1, 1, 'simple',  '2026-09-01', 12, 'en_attente', 'Demande initiale'),
(2, 2, 'studio',  '2026-09-14', 10, 'en_attente', 'Besoin d\'un studio individuel'),
(3, 1, 'double',  '2026-09-13', 12, 'acceptee',   'Dossier complet validé'),
(4, 5, 'simple',  '2026-09-20', 9,  'acceptee',   NULL),
(5, 4, 'studio',  '2026-10-01', 6,  'refusee',    'Dossier incomplet'),
(6, 3, 'simple',  '2026-09-05', 12, 'en_attente', NULL);

-- -----------------------------------------------------------
-- DONNÉES : contacts (5 enregistrements)
-- -----------------------------------------------------------
INSERT INTO contacts (nom, email, sujet, message, lu) VALUES
('Ali Mejri',       'ali.mejri@mail.tn',      'Information disponibilité',  'Bonjour, je souhaite savoir si des chambres sont disponibles en septembre.', 1),
('Fatma Ben Ali',   'fatma.benali@mail.tn',   'Problème de connexion',      'Je n\'arrive pas à accéder à mon espace candidat.', 0),
('Karim Dridi',     'karim.dridi@mail.tn',    'Partenariat foyer',          'Bonjour, je suis gestionnaire d\'un foyer à Bizerte et je souhaite rejoindre la plateforme.', 0),
('Amira Nasri',     'amira.nasri@mail.tn',    'Question sur les tarifs',    'Quels sont les frais de dossier exacts pour une chambre double ?', 1),
('Slim Boughanmi',  'slim.boughanmi@mail.tn', 'Demande de remboursement',   'Suite à l\'annulation de ma demande, je souhaite être remboursé des frais payés.', 0);
CREATE DATABASE IF NOT EXISTS ctm_platform;
USE ctm_platform;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    profile_picture VARCHAR(255),
    bio TEXT,
    user_type ENUM('monteur', 'employeur', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE skills (
    skill_id INT AUTO_INCREMENT PRIMARY KEY,
    skill_name VARCHAR(50) NOT NULL UNIQUE,
    category VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE editors (
    editor_id INT PRIMARY KEY,
    user_id INT NOT NULL,
    years_experience INT,
    daily_rate DECIMAL(10,2),
    availability ENUM('disponible', 'occupé', 'indisponible') DEFAULT 'disponible',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE editor_skills (
    editor_skill_id INT AUTO_INCREMENT PRIMARY KEY,
    editor_id INT NOT NULL,
    skill_id INT NOT NULL,
    proficiency_level ENUM('débutant', 'intermédiaire', 'avancé', 'expert') NOT NULL,
    FOREIGN KEY (editor_id) REFERENCES editors(editor_id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(skill_id) ON DELETE CASCADE,
    UNIQUE KEY (editor_id, skill_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE employers (
    employer_id INT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(100),
    company_size ENUM('1-10', '11-50', '51-200', '201-500', '500+'),
    industry VARCHAR(50),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE projects (
    project_id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    project_type ENUM('court-métrage', 'documentaire', 'publicité', 'film institutionnel', 'série', 'autre') NOT NULL,
    budget DECIMAL(10,2),
    start_date DATE,
    end_date DATE,
    status ENUM('brouillon', 'publié', 'en cours', 'terminé', 'annulé') DEFAULT 'brouillon',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES employers(employer_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE project_requirements (
    requirement_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    skill_id INT NOT NULL,
    importance ENUM('requis', 'important', 'optionnel') DEFAULT 'important',
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(skill_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    editor_id INT NOT NULL,
    message TEXT,
    proposed_rate DECIMAL(10,2),
    status ENUM('en attente', 'accepté', 'refusé', 'annulé') DEFAULT 'en attente',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
    FOREIGN KEY (editor_id) REFERENCES editors(editor_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE portfolios (
    portfolio_id INT AUTO_INCREMENT PRIMARY KEY,
    editor_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    video_url VARCHAR(255),
    thumbnail_url VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (editor_id) REFERENCES editors(editor_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    reviewer_id INT NOT NULL, 
    reviewed_id INT NOT NULL, 
    project_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    project_id INT,
    content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    notification_type ENUM('application', 'message', 'project', 'review', 'system') NOT NULL,
    related_id INT, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO skills (skill_name, category) VALUES
('Premiere Pro', 'Logiciel'),
('Final Cut Pro', 'Logiciel'),
('DaVinci Resolve', 'Logiciel'),
('After Effects', 'Logiciel'),
('Motion Design', 'Compétence'),
('Color Grading', 'Compétence'),
('Étalonnage', 'Compétence'),
('Sound Design', 'Compétence'),
('Montage narratif', 'Style'),
('Montage documentaire', 'Style'),
('Montage publicitaire', 'Style'),
('Montage cinéma', 'Style'),
('VFX basiques', 'Compétence'),
('Animation 2D', 'Compétence'),
('Sous-titrage', 'Compétence');



INSERT INTO users (email, password, first_name, last_name, phone, profile_picture, bio, user_type) VALUES
('monteur1@example.com', 'salutlesmecs', 'Jean', 'Dupont', '0612345678', 'profile1.jpg', 'Monteur professionnel avec 5 ans d\'expérience dans le documentaire et la publicité.', 'monteur'),
('monteur2@example.com', 'jenesaispas', 'Marie', 'Martin', '0698765432', 'profile2.jpg', 'Spécialisée en motion design et montage créatif pour les réseaux sociaux.', 'monteur'),
('employeur1@example.com', 'motdepassetemporaire', 'Pierre', 'Durand', '0687654321', 'company1.jpg', 'Producteur indépendant de courts-métrages et documentaires.', 'employeur'),
('employeur2@example.com', 'oiseau', 'Sophie', 'Leroy', '0678912345', 'company2.jpg', 'Directrice de production dans une agence de publicité.', 'employeur');


INSERT INTO editors (editor_id, user_id, years_experience, daily_rate, availability) VALUES
(1, 1, 5, 350.00, 'disponible'),
(2, 2, 3, 280.00, 'disponible');

INSERT INTO editor_skills (editor_id, skill_id, proficiency_level) VALUES
(1, 1, 'expert'), 
(1, 4, 'avancé'), 
(1, 5, 'intermédiaire'), 
(1, 10, 'avancé'), 
(2, 2, 'avancé'), 
(2, 4, 'expert'), 
(2, 5, 'expert'), 
(2, 11, 'avancé'); 


INSERT INTO employers (employer_id, user_id, company_name, company_size, industry) VALUES
(1, 3, 'Films Indépendants', '1-10', 'Cinéma'),
(2, 4, 'CréaPub', '51-200', 'Publicité');


INSERT INTO projects (employer_id, title, description, project_type, budget, start_date, end_date, status) VALUES
(1, 'Documentaire sur le changement climatique', 'Documentaire de 52 minutes sur les effets du changement climatique en Europe. Recherche monteur expérimenté en documentaire.', 'documentaire', 15000.00, '2023-09-01', '2023-12-15', 'publié'),
(2, 'Campagne publicitaire pour une marque de sport', 'Montage de 5 spots publicitaires de 30 secondes pour une nouvelle campagne. Recherche monteur créatif avec expérience en motion design.', 'publicité', 8000.00, '2023-08-15', '2023-09-30', 'publié');


INSERT INTO project_requirements (project_id, skill_id, importance) VALUES
(1, 1, 'requis'), 
(1, 10, 'requis'), 
(1, 6, 'important'), 
(2, 4, 'requis'), 
(2, 5, 'requis'), 
(2, 11, 'important'); 


INSERT INTO applications (project_id, editor_id, message, proposed_rate, status) VALUES
(1, 1, 'Bonjour, je suis très intéressé par votre projet. J\'ai une grande expérience dans le montage documentaire comme vous pouvez le voir dans mon portfolio.', 14500.00, 'en attente'),
(2, 2, 'Votre campagne publicitaire correspond parfaitement à mes compétences en motion design. Je propose un tarif de 7500€ pour les 5 spots.', 7500.00, 'en attente');

INSERT INTO portfolios (editor_id, title, description, video_url, thumbnail_url, is_featured) VALUES
(1, 'Documentaire "Les Oubliés"', 'Documentaire primé sur les sans-abris à Paris', 'https://vimeo.com/123456', 'docu_thumbnail.jpg', TRUE),
(1, 'Reportage Arte', 'Montage d\'un reportage de 26 minutes pour Arte', 'https://vimeo.com/234567', 'arte_thumbnail.jpg', FALSE),
(2, 'Campagne Nike', 'Motion design pour une campagne Nike', 'https://vimeo.com/345678', 'nike_thumbnail.jpg', TRUE),
(2, 'Spot publicitaire Renault', 'Montage et animation d\'un spot de 30 secondes', 'https://vimeo.com/456789', 'renault_thumbnail.jpg', TRUE);

INSERT INTO reviews (reviewer_id, reviewed_id, project_id, rating, comment) VALUES
(3, 1, 1, 5, 'Excellent travail de montage, très professionnel et respect des délais. Je recommande vivement !'),
(4, 2, 2, 4, 'Très bon montage et créativité, juste quelques retards mineurs dans les rendus.');


INSERT INTO messages (sender_id, receiver_id, project_id, content, is_read) VALUES
(3, 1, 1, 'Bonjour Jean, merci pour votre candidature. Pourriez-vous nous envoyer des références supplémentaires ?', FALSE),
(1, 3, 1, 'Bien sûr, je vous envoie cela dès ce soir. J\'ai travaillé sur plusieurs documentaires similaires.', TRUE),
(4, 2, 2, 'Nous avons bien reçu votre proposition et sommes très intéressés. Pouvez-vous commencer lundi prochain ?', FALSE);

CREATE DATABASE IF NOT EXISTS ctmdata;
USE ctmdata;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    profile_picture VARCHAR(255),
    bio TEXT,
    user_type enum('monteur','graphiste','manager','développeur','beatmaker') NOT NULL,
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



INSERT INTO `users` (`user_id`, `email`, `password`, `first_name`, `last_name`, `phone`, `profile_picture`, `bio`, `user_type`, `created_at`, `updated_at`) VALUES
(1, 'monteur1@example.com', 'maxpass23', 'Max', 'Castagne', '0678452310', 'profile1.jpg', 'Spécialiste du montage court-métrage avec un sens aigu du rythme.', 'monteur', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(2, 'monteur2@example.com', 'jackedit99', 'Jack', 'Chrass', '0770213456', 'profile1.jpg', 'Monteur avec une grande expérience dans le clip musical et les transitions dynamiques.', 'monteur', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(3, 'monteur3@example.com', 'pierrevideo88', 'Pierre', 'Calvasse', '0679981203', 'profile1.jpg', 'Expert en storytelling vidéo, adepte des logiciels Adobe Premiere et DaVinci.', 'monteur', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(4, 'monteur4@example.com', 'quentinpro', 'Quentin', 'Michel', '0765123498', 'profile1.jpg', 'Monteur passionné par le documentaire, toujours à la recherche de l’émotion juste.', 'monteur', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(5, 'graphiste1@example.com', 'lucdesign', 'Luc', 'Pliuc', '0670012398', 'profile1.jpg', 'Graphiste expert en branding et création d’identités visuelles fortes.', 'graphiste', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(6, 'graphiste2@example.com', 'khaledart77', 'Khaled', 'Prime', '0679981123', 'profile1.jpg', 'Spécialisé en motion design, j’anime vos idées avec fluidité et style.', 'graphiste', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(7, 'graphiste3@example.com', 'martinviz', 'Martin', 'Jiopo', '0776542312', 'profile1.jpg', 'Graphiste polyvalent avec un œil affuté pour le détail et la composition.', 'graphiste', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(8, 'graphiste4@example.com', 'luciencrea', 'Lucien', 'Dekune', '0678459901', 'profile1.jpg', 'Illustrateur et graphiste, je donne vie à vos projets avec originalité.', 'graphiste', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(9, 'manager1@example.com', 'julienlead', 'Julien', 'Martin', '0670034567', 'profile1.jpg', 'Manager expérimenté, je coordonne les talents pour des projets efficaces.', 'manager', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(10, 'manager2@example.com', 'elodiemgmt', 'Élodie', 'Dupont', '0778234510', 'profile1.jpg', 'Experte en gestion de production, je fais le lien entre la vision et l’exécution.', 'manager', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(11, 'manager3@example.com', 'antoineorga', 'Antoine', 'Bernard', '0678945623', 'profile1.jpg', 'Chef de projet rigoureux, j’assure la qualité et le respect des délais.', 'manager', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(12, 'manager4@example.com', 'claireboss', 'Claire', 'Lefèvre', '0767894501', 'profile1.jpg', 'Manager créative, j’encourage la collaboration et l’innovation.', 'manager', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(13, 'dev1@example.com', 'lucasdev', 'Lucas', 'Girard', '0671123987', 'profile1.jpg', 'Développeur full-stack spécialisé en React et Node.js, passionné de tech.', 'développeur', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(14, 'dev2@example.com', 'camillecode', 'Camille', 'Moreau', '0770034598', 'profile1.jpg', 'Développeuse web amoureuse du clean code et des expériences fluides.', 'développeur', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(15, 'dev3@example.com', 'thomasroot', 'Thomas', 'Dubois', '0678324590', 'profile1.jpg', 'Backend dev orienté performance et sécurité, avec une touche DevOps.', 'développeur', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(16, 'dev4@example.com', 'sophiehtml', 'Sophie', 'Laurent', '0765432109', 'profile1.jpg', 'Développeuse front-end, experte UI/UX avec une sensibilité design.', 'développeur', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(17, 'beatmaker1@example.com', 'antoinebeat', 'Antoine', 'Perrot', '0678912345', 'profile1.jpg', 'Beatmaker influencé par le boom bap et les textures vintage.', 'beatmaker', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(18, 'beatmaker2@example.com', 'juliebass', 'Julie', 'Barbier', '0778891234', 'profile1.jpg', 'Productrice musicale, je crée des sons doux et percutants à la fois.', 'beatmaker', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(19, 'beatmaker3@example.com', 'maximeloop', 'Maxime', 'Richard', '0670098765', 'profile1.jpg', 'Spécialiste des instrumentales trap et RnB, je façonne des hits sur mesure.', 'beatmaker', '2025-04-08 13:42:52', '2025-04-08 13:42:52'),
(20, 'beatmaker4@example.com', 'sarahprod', 'Sarah', 'Gauthier', '0767801234', 'profile1.jpg', 'Beatmakeuse polyvalente, de l’électro chill au hip-hop expérimental.', 'beatmaker', '2025-04-08 13:42:52', '2025-04-08 13:42:52');


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

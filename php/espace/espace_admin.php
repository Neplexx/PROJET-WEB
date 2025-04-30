<!DOCTYPE html>
<?php
session_start();
require_once('../verification.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$servername = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'ctmdata';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT user_type FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user || $user['user_type'] !== 'admin') {
        header("Location: ../login.php");
        exit();
    }

    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $total_users = $stmt->fetch()['total_users'];

    $stmt = $pdo->query("SELECT COUNT(*) as total_projects FROM projects");
    $total_projects = $stmt->fetch()['total_projects'];

    $stmt = $pdo->query("SELECT COUNT(*) as active_projects FROM projects WHERE status = 'en cours'");
    $active_projects = $stmt->fetch()['active_projects'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['delete_user'])) {
            $user_to_delete = $_POST['user_id'];
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_to_delete]);
            header("Location: ".$_SERVER['PHP_SELF']); 
            exit();
        }
        
    }

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;

    $stmt = $pdo->prepare("SELECT * FROM users LIMIT :offset, :per_page");
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':per_page', $per_page, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total_users = $stmt->fetchColumn();
    $total_pages = ceil($total_users / $per_page);

    $search_results = [];
    if (isset($_GET['search'])) {
        $search_term = '%'.$_GET['search'].'%';
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email LIKE ? OR first_name LIKE ? OR last_name LIKE ?");
        $stmt->execute([$search_term, $search_term, $search_term]);
        $search_results = $stmt->fetchAll();
    }
        // Ajoutez cette requête après les autres requêtes SQL
    $stmt = $pdo->prepare("SELECT n.*, u.first_name, u.last_name, u.email 
    FROM notifications n
    LEFT JOIN users u ON n.related_id = u.user_id
    ORDER BY n.created_at DESC LIMIT 10");
    $stmt->execute();
    $notifications = $stmt->fetchAll();

    // Marquer les notifications comme lues
    if (!empty($notifications)) {
        $unread_ids = array_column(array_filter($notifications, function($n) { return !$n['is_read']; }), 'notification_id');
    }
    if (!empty($unread_ids)) {
        $placeholders = implode(',', array_fill(0, count($unread_ids), '?'));
        $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id IN ($placeholders)")
        ->execute($unread_ids);
    }

} catch(PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}
?>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Administrateur | CTM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #1a237e; 
            --secondary: #3949ab; 
            --accent: #d81b60; 
            --light: #e8eaf6; 
            --dark: #0d47a1; 
            --text: #1a237e; 
            --text-light: #5c6bc0; 
            --white: #ffffff;
            --success: #43a047; 
            --warning: #fb8c00; 
            --danger: #e53935; 
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text);
            margin: 0;
            padding: 0;
            background-color: var(--light);
        }

        .main-header {
            background-color: var(--white);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10;
        }

        .logo {
            height: 60px;
            transition: transform 0.3s;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .user-nav {
            display: flex;
            align-items: center;
        }

        .user-profile {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--primary);
            transition: all 0.3s;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            border: 1px solid rgba(26, 35, 126, 0.1);
        }

        .user-profile:hover {
            color: var(--accent);
            background-color: rgba(216, 27, 96, 0.1);
            transform: translateY(-2px);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 0.5rem;
            border: 2px solid var(--light);
            transition: all 0.3s;
            object-fit: cover;
        }

        .user-profile:hover .user-avatar {
            border-color: var(--accent);
        }

        .user-name {
            font-weight: 500;
        }

        .main-nav {
            background-color: var(--primary);
            padding: 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .nav-list {
            display: flex;
            justify-content: center;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-item {
            margin: 0 1rem;
            position: relative;
        }

        .nav-link {
            display: block;
            padding: 1rem 1.5rem;
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
        }

        .nav-link:hover {
            color: var(--accent);
        }

        .nav-link.active {
            color: var(--accent);
            font-weight: 600;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--accent);
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .admin-title {
            color: var(--primary);
            margin: 0;
            font-size: 2rem;
        }

        .admin-subtitle {
            color: var(--text-light);
            margin-top: 0.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            border-top: 3px solid var(--secondary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            font-size: 2rem;
            color: var(--secondary);
            margin-bottom: 1rem;
        }

        .stat-value {
            color: var(--primary);
            font-size: 2.2rem;
            font-weight: bold;
            margin: 0.5rem 0;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .admin-card {
            background: var(--white);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--light);
        }

        .card-title {
            color: var(--primary);
            margin: 0;
            font-size: 1.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background-color: var(--secondary);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--dark);
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: var(--danger);
            color: var(--white);
        }

        .btn-danger:hover {
            background-color: #c62828;
            transform: translateY(-2px);
        }

        .btn-success {
            background-color: var(--success);
            color: var(--white);
        }

        .btn-success:hover {
            background-color: #2e7d32;
            transform: translateY(-2px);
        }

        .btn i {
            margin-right: 0.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--light);
        }

        th {
            background-color: var(--primary);
            color: var(--white);
            font-weight: 500;
        }

        tr:hover {
            background-color: rgba(57, 73, 171, 0.05);
        }

        .badge {
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-admin {
            background-color: var(--accent);
            color: white;
        }

        .badge-employeur {
            background-color: var(--success);
            color: white;
        }

        .badge-monteur {
            background-color: var(--secondary);
            color: white;
        }

        .badge-graphiste {
            background-color: #8e24aa;
            color: white;
        }

        .badge-client {
            background-color: var(--warning);
            color: white;
        }

        .search-form {
            display: flex;
            margin-bottom: 1.5rem;
        }

        .search-input {
            flex: 1;
            padding: 0.6rem 1rem;
            border: 1px solid var(--light);
            border-radius: 6px 0 0 6px;
            font-size: 1rem;
        }

        .search-btn {
            padding: 0 1.5rem;
            background-color: var(--secondary);
            color: white;
            border: none;
            border-radius: 0 6px 6px 0;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-btn:hover {
            background-color: var(--dark);
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }

        .pagination a {
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
            border: 1px solid var(--light);
            border-radius: 4px;
            text-decoration: none;
            color: var(--primary);
            transition: all 0.3s;
        }

        .pagination a:hover {
            background-color: var(--secondary);
            color: white;
            border-color: var(--secondary);
        }

        .pagination .active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--primary);
        }

        .form-control {
            width: 100%;
            padding: 0.6rem 1rem;
            border: 1px solid var(--light);
            border-radius: 6px;
            font-size: 1rem;
        }

        .form-select {
            width: 100%;
            padding: 0.6rem 1rem;
            border: 1px solid var(--light);
            border-radius: 6px;
            font-size: 1rem;
            background-color: white;
        }


        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
        }


        .action-btns {
            display: flex;
            gap: 0.5rem;
        }
        .notifications-container {
            max-height: 600px;
            overflow-y: auto;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            background: white;
            margin-top: 20px;
        }

        .notifications-container::-webkit-scrollbar {
            width: 8px;
        }

        .notifications-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .notifications-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .notifications-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .unread {
            background-color: rgba(25, 118, 210, 0.05);
            position: relative;
        }

        .unread::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background-color: #1976d2;
            border-radius: 0 4px 4px 0;
        }

        .notifications-container table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .notifications-container th, 
        .notifications-container td {
            padding: 16px 24px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;
        }

        .notifications-container th {
            background-color: #fafafa;
            position: sticky;
            top: 0;
            font-weight: 600;
            color: #424242;
            text-transform: uppercase;
            font-size: 0.8em;
            letter-spacing: 0.5px;
            backdrop-filter: blur(5px);
        }

        .notifications-container tr:last-child td {
            border-bottom: none;
        }

        .notifications-container tr:hover {
            background-color: rgba(25, 118, 210, 0.03);
        }

        .notifications-container td:first-child {
            font-weight: 500;
            color: #1976d2;
        }

        .notifications-container .notification-time {
            color: #757575;
            font-size: 0.85em;
            white-space: nowrap;
        }

        .notifications-container .notification-content {
            line-height: 1.6;
            color: #424242;
        }

        .notifications-container .user-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75em;
            font-weight: 500;
            margin-top: 4px;
        }

        .notifications-container .user-badge.admin {
            background-color: #d81b60;
            color: white;
        }

        .notifications-container .user-badge.monteur {
            background-color: #3949ab;
            color: white;
        }

        .notifications-container .user-badge.employeur {
            background-color: #43a047;
            color: white;
        }

        .notifications-container .user-badge.client {
            background-color: #fb8c00;
            color: white;
        }

        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-list {
                flex-wrap: wrap;
            }
            
            .nav-item {
                margin: 0.5rem;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="logo-container">
            <a href="../../index.html" class="logo-link">
                <img src="../../pictures/logorond.png" alt="Logo CTM" class="logo">
            </a>
        </div>
        <nav class="user-nav">
            <a href="../espclient.php" class="user-profile">
                <img src="../../pictures/photoDeProfil.png" alt="Photo de profil" class="user-avatar">
                <span class="user-name">Administrateur</span>
            </a>
        </nav>
    </header>

    <nav class="main-nav">
        <ul class="nav-list">
            <li class="nav-item"><a href="../accueil.php" class="nav-link">Accueil</a></li>
            <li class="nav-item"><a href="admin.php" class="nav-link active">Tableau de bord</a></li>
        </ul>
    </nav>

    <div class="dashboard-container">
        <div class="admin-header">
            <div>
                <h1 class="admin-title">Tableau de bord administrateur</h1>
                <p class="admin-subtitle">Gestion complète de la plateforme CTM</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-value"><?php echo $total_users; ?></div>
                <div class="stat-label">Utilisateurs</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-project-diagram"></i></div>
                <div class="stat-value"><?php echo $total_projects; ?></div>
                <div class="stat-label">Projets</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                <div class="stat-value"><?php echo $active_projects; ?></div>
                <div class="stat-label">Projets actifs</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <div class="stat-value">98%</div>
                <div class="stat-label">Satisfaction</div>
            </div>
        </div>

        <div class="admin-card">
            <div class="card-header">
                <h2 class="card-title">Gestion des utilisateurs</h2>
                <form method="GET" class="search-form">
                    <input type="text" name="search" class="search-input" placeholder="Rechercher un utilisateur...">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            <?php if (!empty($search_results)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($search_results as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php 
                                        $badge_class = '';
                                        switch($user['user_type']) {
                                            case 'admin': $badge_class = 'badge-admin'; break;
                                            case 'employeur': $badge_class = 'badge-employeur'; break;
                                            case 'monteur': $badge_class = 'badge-monteur'; break;
                                            case 'graphiste': $badge_class = 'badge-graphiste'; break;
                                            default: $badge_class = 'badge-client';
                                        }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars($user['user_type']); ?>
                                    </span>
                                </td>
                                <td class="action-btns">
                                    <button class="btn btn-primary" onclick="openEditModal(
                                        '<?php echo $user['user_id']; ?>',
                                        '<?php echo htmlspecialchars($user['email'], ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars($user['user_type'], ENT_QUOTES); ?>'
                                    )">
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php 
                                        $badge_class = '';
                                        switch($user['user_type']) {
                                            case 'admin': $badge_class = 'badge-admin'; break;
                                            case 'employeur': $badge_class = 'badge-employeur'; break;
                                            case 'monteur': $badge_class = 'badge-monteur'; break;
                                            case 'graphiste': $badge_class = 'badge-graphiste'; break;
                                            default: $badge_class = 'badge-client';
                                        }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars($user['user_type']); ?>
                                    </span>
                                </td>
                                <td class="action-btns">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                        <button type="submit" name="delete_user" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>">&laquo; Précédent</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" <?php if ($i === $page) echo 'class="active"'; ?>>
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">Suivant &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-card">
            <div class="card-header">
                <h2 class="card-title">Notifications de support</h2>
            </div>
            
            <div class="notifications-container">
                <?php if (empty($notifications)): ?>
                    <p>Aucune notification pour le moment.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Expéditeur</th>
                                <th>Message</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notifications as $notification): ?>
                                <tr class="<?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                                    <td>
                                        <?php if ($notification['first_name']): ?>
                                            <?php echo htmlspecialchars($notification['first_name']) . ' ' . htmlspecialchars($notification['last_name']); ?>
                                            <br>
                                            <?php echo htmlspecialchars($notification['email']); ?>
                                        <?php else: ?>
                                            Utilisateur inconnu
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo nl2br(htmlspecialchars($notification['content'])); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>               
        </div>
    </div>
</body>
</html>


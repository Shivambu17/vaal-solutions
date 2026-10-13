<?php
require_once 'config.php';

// ---------------------------------------------------------------
// AUTHENTICATION
// ---------------------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT id, name, email, role, status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

if (!$currentUser || !$currentUser['status']) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

$_SESSION['user_name'] = $currentUser['name'];
$_SESSION['user_role'] = $currentUser['role'];

$isAdmin  = ($currentUser['role'] === 'admin');
$isEditor = ($currentUser['role'] === 'editor');

if (!$isAdmin && !$isEditor) {
    die('Access denied: your account has no valid role. Contact an administrator.');
}

$page = $_GET['page'] ?? 'dashboard';
if ($page === 'users' && !$isAdmin) $page = 'dashboard';

// ---------------------------------------------------------------
// HANDLE POST ACTIONS
// ---------------------------------------------------------------
$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entity'])) {
    $entity = $_POST['entity'];
    $action = $_POST['action'] ?? '';

    if ($entity === 'user' && !$isAdmin) die('Access denied: only admins can manage users.');

    try {
        if ($entity === 'user') {
            if ($action === 'add') {
                $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)")
                    ->execute([trim($_POST['name']), trim($_POST['email']), password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['role']]);
                $flash = 'User created successfully.';
            } elseif ($action === 'edit') {
                $id = (int)$_POST['id'];
                $status = isset($_POST['status']) ? 1 : 0;
                if (!empty($_POST['password'])) {
                    $pdo->prepare("UPDATE users SET name=?, email=?, password=?, role=?, status=? WHERE id=?")
                        ->execute([trim($_POST['name']), trim($_POST['email']), password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['role'], $status, $id]);
                } else {
                    $pdo->prepare("UPDATE users SET name=?, email=?, role=?, status=? WHERE id=?")
                        ->execute([trim($_POST['name']), trim($_POST['email']), $_POST['role'], $status, $id]);
                }
                $flash = 'User updated successfully.';
            } elseif ($action === 'delete' && (int)$_POST['id'] !== (int)$_SESSION['user_id']) {
                $pdo->prepare("DELETE FROM users WHERE id=?")->execute([(int)$_POST['id']]);
                $flash = 'User deleted.';
            }
        }

        if ($entity === 'service') {
            if ($action === 'add') {
                $pdo->prepare("INSERT INTO services (icon, title, description, sort_order) VALUES (?, ?, ?, ?)")
                    ->execute([$_POST['icon'], $_POST['title'], $_POST['description'], (int)$_POST['sort_order']]);
                $flash = 'Service added.';
            } elseif ($action === 'edit') {
                $pdo->prepare("UPDATE services SET icon=?, title=?, description=?, sort_order=? WHERE id=?")
                    ->execute([$_POST['icon'], $_POST['title'], $_POST['description'], (int)$_POST['sort_order'], (int)$_POST['id']]);
                $flash = 'Service updated.';
            } elseif ($action === 'delete') {
                $pdo->prepare("DELETE FROM services WHERE id=?")->execute([(int)$_POST['id']]);
                $flash = 'Service deleted.';
            }
        }

        if ($entity === 'project') {
            if ($action === 'add') {
                $pdo->prepare("INSERT INTO projects (image, title, sort_order) VALUES (?, ?, ?)")
                    ->execute([$_POST['image'], $_POST['title'], (int)$_POST['sort_order']]);
                $flash = 'Project added.';
            } elseif ($action === 'edit') {
                $pdo->prepare("UPDATE projects SET image=?, title=?, sort_order=? WHERE id=?")
                    ->execute([$_POST['image'], $_POST['title'], (int)$_POST['sort_order'], (int)$_POST['id']]);
                $flash = 'Project updated.';
            } elseif ($action === 'delete') {
                $pdo->prepare("DELETE FROM projects WHERE id=?")->execute([(int)$_POST['id']]);
                $flash = 'Project deleted.';
            }
        }

        if ($entity === 'testimonial') {
            if ($action === 'add') {
                $pdo->prepare("INSERT INTO testimonials (quote, author, sort_order) VALUES (?, ?, ?)")
                    ->execute([$_POST['quote'], $_POST['author'], (int)$_POST['sort_order']]);
                $flash = 'Testimonial added.';
            } elseif ($action === 'edit') {
                $pdo->prepare("UPDATE testimonials SET quote=?, author=?, sort_order=? WHERE id=?")
                    ->execute([$_POST['quote'], $_POST['author'], (int)$_POST['sort_order'], (int)$_POST['id']]);
                $flash = 'Testimonial updated.';
            } elseif ($action === 'delete') {
                $pdo->prepare("DELETE FROM testimonials WHERE id=?")->execute([(int)$_POST['id']]);
                $flash = 'Testimonial deleted.';
            }
        }

        if ($entity === 'stat') {
            if ($action === 'add') {
                $pdo->prepare("INSERT INTO stats (target, label, sort_order) VALUES (?, ?, ?)")
                    ->execute([(int)$_POST['target'], $_POST['label'], (int)$_POST['sort_order']]);
                $flash = 'Stat added.';
            } elseif ($action === 'edit') {
                $pdo->prepare("UPDATE stats SET target=?, label=?, sort_order=? WHERE id=?")
                    ->execute([(int)$_POST['target'], $_POST['label'], (int)$_POST['sort_order'], (int)$_POST['id']]);
                $flash = 'Stat updated.';
            } elseif ($action === 'delete') {
                $pdo->prepare("DELETE FROM stats WHERE id=?")->execute([(int)$_POST['id']]);
                $flash = 'Stat deleted.';
            }
        }

        if ($entity === 'pricing') {
            if ($action === 'add') {
                $pdo->prepare("INSERT INTO pricing (name, price, features, featured, sort_order) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$_POST['name'], $_POST['price'], $_POST['features'], isset($_POST['featured']) ? 1 : 0, (int)$_POST['sort_order']]);
                $flash = 'Plan added.';
            } elseif ($action === 'edit') {
                $pdo->prepare("UPDATE pricing SET name=?, price=?, features=?, featured=?, sort_order=? WHERE id=?")
                    ->execute([$_POST['name'], $_POST['price'], $_POST['features'], isset($_POST['featured']) ? 1 : 0, (int)$_POST['sort_order'], (int)$_POST['id']]);
                $flash = 'Plan updated.';
            } elseif ($action === 'delete') {
                $pdo->prepare("DELETE FROM pricing WHERE id=?")->execute([(int)$_POST['id']]);
                $flash = 'Plan deleted.';
            }
        }

        if ($entity === 'message') {
            if ($action === 'delete') {
                $pdo->prepare("DELETE FROM contact_messages WHERE id=?")->execute([(int)$_POST['id']]);
                $flash = 'Message deleted.';
            } elseif ($action === 'read') {
                $pdo->prepare("UPDATE contact_messages SET is_read=1 WHERE id=?")->execute([(int)$_POST['id']]);
                $flash = 'Marked as read.';
            }
        }
    } catch (Exception $e) {
        $flash = 'Error: ' . $e->getMessage();
    }

    $_SESSION['flash'] = $flash;
    header("Location: admin.php?page=$page");
    exit;
}

if (!empty($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// ---------------------------------------------------------------
// FETCH DATA
// ---------------------------------------------------------------
$userCount    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$serviceCount = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
$projectCount = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$messageCount = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();

$users        = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
$services     = $pdo->query("SELECT * FROM services ORDER BY sort_order ASC")->fetchAll();
$projects     = $pdo->query("SELECT * FROM projects ORDER BY sort_order ASC")->fetchAll();
$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY sort_order ASC")->fetchAll();
$stats        = $pdo->query("SELECT * FROM stats ORDER BY sort_order ASC")->fetchAll();
$pricing      = $pdo->query("SELECT * FROM pricing ORDER BY sort_order ASC")->fetchAll();
$messages     = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
$recentMsgs   = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

$pageMeta = [
    'dashboard'    => ['Dashboard', 'Overview of your site activity', 'fa-gauge-high'],
    'users'        => ['User Management', 'Manage accounts and permissions', 'fa-users'],
    'services'     => ['Services', 'Manage your service offerings', 'fa-cogs'],
    'projects'     => ['Projects', 'Showcase your latest work', 'fa-folder-open'],
    'testimonials' => ['Testimonials', 'Client feedback and quotes', 'fa-comment-dots'],
    'stats'        => ['Statistics', 'Key numbers displayed on the site', 'fa-chart-simple'],
    'pricing'      => ['Pricing Plans', 'Your service packages', 'fa-tags'],
    'messages'     => ['Contact Messages', 'Enquiries from your visitors', 'fa-envelope'],
];
[$pageTitle, $pageDesc, $pageIcon] = $pageMeta[$page] ?? $pageMeta['dashboard'];
$initial = strtoupper(substr($currentUser['name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | Vaal Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
    /* ============================================================
       VAAL ADMIN — PROFESSIONAL CONTROL PANEL
       ============================================================ */
    :root {
        --a-ink:        #0a1020;
        --a-ink-2:      #111931;
        --a-ink-3:      #1b2544;
        --a-line:       #e8ecf3;
        --a-line-2:     #f1f5fb;
        --a-bg:         #f7f9fd;
        --a-white:      #ffffff;
        --a-brand:      #0a84ff;
        --a-brand-2:    #7c3aed;
        --a-brand-3:    #ec4899;
        --a-cyan:       #06b6d4;
        --a-emerald:    #10b981;
        --a-amber:      #f59e0b;
        --a-rose:       #ef4444;
        --a-grad:       linear-gradient(135deg, #0a84ff 0%, #7c3aed 100%);
        --a-grad-soft:  linear-gradient(135deg, rgba(10,132,255,.08), rgba(124,58,237,.08));
        --a-grad-ink:   linear-gradient(165deg, #0a1020 0%, #131a2b 50%, #1b2544 100%);
        --a-shadow-sm:  0 1px 2px rgba(10,16,32,.04), 0 2px 6px rgba(10,16,32,.04);
        --a-shadow:     0 4px 12px rgba(10,16,32,.06), 0 12px 32px rgba(10,16,32,.04);
        --a-shadow-lg:  0 12px 32px rgba(10,16,32,.10), 0 30px 70px rgba(10,16,32,.08);
        --a-shadow-xl:  0 30px 80px rgba(10,16,32,.22);
        --a-radius-sm:  10px;
        --a-radius:     14px;
        --a-radius-lg:  20px;
        --a-radius-xl:  24px;
        --a-topbar:     72px;
        --a-sidebar:    264px;
        --a-ease:       cubic-bezier(.22,.61,.36,1);
    }

    * { box-sizing: border-box; }

    body.admin {
        margin: 0;
        background: var(--a-bg);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        color: var(--a-ink);
        font-size: 15px;
        -webkit-font-smoothing: antialiased;
    }

    /* ===== TOPBAR ===== */
    .a-topbar {
        position: fixed;
        top: 0; left: 0; right: 0;
        height: var(--a-topbar);
        background: rgba(255,255,255,.85);
        backdrop-filter: saturate(180%) blur(20px);
        -webkit-backdrop-filter: saturate(180%) blur(20px);
        border-bottom: 1px solid var(--a-line);
        display: flex;
        align-items: center;
        padding: 0 28px;
        gap: 20px;
        z-index: 900;
    }

    .hamburger {
        display: none;
        width: 42px; height: 42px;
        border: none;
        background: transparent;
        border-radius: 12px;
        color: var(--a-ink);
        font-size: 1.1rem;
        cursor: pointer;
        transition: all .25s var(--a-ease);
        align-items: center;
        justify-content: center;
    }
    .hamburger:hover { background: var(--a-grad-soft); color: var(--a-brand); }

    .topbar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        font-family: 'Sora', sans-serif;
        font-weight: 800;
        font-size: 1.05rem;
        letter-spacing: -0.025em;
        text-decoration: none;
        color: var(--a-ink);
    }

    .brand-mark {
        width: 40px; height: 40px;
        border-radius: 12px;
        background: var(--a-grad);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1rem;
        box-shadow: 0 8px 20px rgba(10,132,255,.32);
    }

    .brand-name {
        background: var(--a-grad);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .brand-sub {
        display: block;
        font-family: 'Inter', sans-serif;
        font-size: .68rem;
        font-weight: 600;
        letter-spacing: .15em;
        text-transform: uppercase;
        color: #94a3b8;
        margin-top: 1px;
    }

    .topbar-divider {
        width: 1px;
        height: 28px;
        background: var(--a-line);
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: .85rem;
        color: #64748b;
        font-weight: 500;
    }
    .breadcrumb i.fa-house { color: #94a3b8; font-size: .8rem; }
    .breadcrumb .sep { color: #cbd5e1; font-size: .7rem; }
    .breadcrumb strong { color: var(--a-ink); font-weight: 600; }

    .topbar-spacer { flex: 1; }

    .topbar-icon-btn {
        position: relative;
        width: 42px; height: 42px;
        border-radius: 12px;
        border: 1px solid var(--a-line);
        background: #fff;
        color: #475569;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
        transition: all .25s var(--a-ease);
    }
    .topbar-icon-btn:hover {
        border-color: rgba(10,132,255,.35);
        color: var(--a-brand);
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(10,132,255,.12);
    }
    .topbar-icon-btn .dot {
        position: absolute;
        top: 8px; right: 8px;
        width: 8px; height: 8px;
        border-radius: 50%;
        background: var(--a-rose);
        border: 2px solid #fff;
    }

    .topbar-user {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 5px 6px 5px 14px;
        border-radius: 999px;
        background: #fff;
        border: 1px solid var(--a-line);
        box-shadow: var(--a-shadow-sm);
        cursor: pointer;
        transition: all .25s var(--a-ease);
    }
    .topbar-user:hover {
        border-color: rgba(10,132,255,.3);
        box-shadow: 0 6px 16px rgba(10,132,255,.1);
    }

    .topbar-user .user-meta { text-align: right; line-height: 1.15; }
    .topbar-user .user-meta strong { display: block; font-size: .84rem; font-weight: 600; color: var(--a-ink); }
    .topbar-user .user-meta small {
        font-size: .68rem;
        color: var(--a-brand);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
    }

    .user-avatar {
        width: 38px; height: 38px;
        border-radius: 50%;
        background: var(--a-grad);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: .92rem;
        box-shadow: 0 6px 16px rgba(10,132,255,.3);
        flex-shrink: 0;
    }

    /* ===== SHELL ===== */
    .a-shell {
        display: flex;
        padding-top: var(--a-topbar);
        min-height: 100vh;
    }

    /* ===== SIDEBAR ===== */
    .a-sidebar {
        width: var(--a-sidebar);
        background: var(--a-grad-ink);
        color: #fff;
        flex-shrink: 0;
        padding: 24px 16px;
        position: fixed;
        top: var(--a-topbar);
        bottom: 0;
        left: 0;
        overflow-y: auto;
        transition: transform .4s var(--a-ease);
        z-index: 800;
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,.15) transparent;
        display: flex;
        flex-direction: column;
    }
    .a-sidebar::-webkit-scrollbar { width: 5px; }
    .a-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 6px; }

    .a-sidebar::before {
        content: '';
        position: absolute;
        top: -80px; right: -80px;
        width: 240px; height: 240px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(124,58,237,.35), transparent 70%);
        pointer-events: none;
    }
    .a-sidebar::after {
        content: '';
        position: absolute;
        bottom: -100px; left: -100px;
        width: 280px; height: 280px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(10,132,255,.28), transparent 70%);
        pointer-events: none;
    }

    .sidebar-profile {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px;
        background: rgba(255,255,255,.04);
        border: 1px solid rgba(255,255,255,.08);
        border-radius: var(--a-radius);
        margin-bottom: 22px;
        backdrop-filter: blur(10px);
    }
    .sidebar-profile-avatar {
        width: 44px; height: 44px;
        border-radius: 12px;
        background: var(--a-grad);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 8px 20px rgba(10,132,255,.35);
    }
    .sidebar-profile-info {
        flex: 1;
        min-width: 0;
    }
    .sidebar-profile-info strong {
        display: block;
        font-size: .88rem;
        font-weight: 600;
        color: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sidebar-profile-info small {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: .68rem;
        color: #4fc3f7;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        margin-top: 2px;
    }
    .sidebar-profile-info small::before {
        content: '';
        width: 6px; height: 6px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 3px rgba(16,185,129,.2);
    }

    .nav-label {
        position: relative;
        z-index: 1;
        font-size: .65rem;
        text-transform: uppercase;
        letter-spacing: .18em;
        color: rgba(255,255,255,.32);
        padding: 16px 14px 8px;
        font-weight: 700;
    }

    .a-nav {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .a-nav a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 12px;
        color: rgba(255,255,255,.68);
        font-size: .88rem;
        font-weight: 500;
        text-decoration: none;
        transition: all .25s var(--a-ease);
        position: relative;
    }

    .a-nav a .nav-icon {
        width: 34px; height: 34px;
        border-radius: 9px;
        background: rgba(255,255,255,.05);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .82rem;
        flex-shrink: 0;
        transition: all .3s var(--a-ease);
        color: rgba(255,255,255,.7);
    }

    .a-nav a span { flex: 1; }

    .a-nav a:hover {
        background: rgba(255,255,255,.06);
        color: #fff;
    }
    .a-nav a:hover .nav-icon {
        background: rgba(255,255,255,.1);
        color: #fff;
        transform: scale(1.05);
    }

    .a-nav a.active {
        background: rgba(255,255,255,.08);
        color: #fff;
        box-shadow: 0 8px 22px rgba(10,132,255,.18);
    }

    .a-nav a.active .nav-icon {
        background: var(--a-grad);
        color: #fff;
        box-shadow: 0 6px 16px rgba(10,132,255,.45);
    }

    .a-nav a.active::before {
        content: '';
        position: absolute;
        left: -16px;
        top: 50%;
        transform: translateY(-50%);
        width: 3px;
        height: 22px;
        background: var(--a-grad);
        border-radius: 0 3px 3px 0;
    }

    .nav-badge {
        margin-left: auto;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #fff;
        font-size: .65rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 8px;
        line-height: 1.7;
        min-width: 22px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(239,68,68,.4);
    }

    .sidebar-footer {
        position: relative;
        z-index: 1;
        margin-top: auto;
        padding-top: 20px;
    }

    .sidebar-logout {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 12px;
        color: rgba(255,255,255,.6);
        font-size: .88rem;
        font-weight: 500;
        transition: all .25s var(--a-ease);
        text-decoration: none;
    }
    .sidebar-logout .nav-icon {
        width: 34px; height: 34px;
        border-radius: 9px;
        background: rgba(239,68,68,.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .82rem;
        color: #fca5a5;
        flex-shrink: 0;
    }
    .sidebar-logout:hover {
        background: rgba(239,68,68,.12);
        color: #fca5a5;
    }
    .sidebar-logout:hover .nav-icon {
        background: rgba(239,68,68,.25);
        color: #fecaca;
    }

    /* ===== BACKDROP ===== */
    .sidebar-backdrop {
        position: fixed;
        inset: var(--a-topbar) 0 0 0;
        background: rgba(10,16,32,.55);
        backdrop-filter: blur(3px);
        opacity: 0;
        pointer-events: none;
        transition: opacity .3s;
        z-index: 750;
    }
    .sidebar-backdrop.show { opacity: 1; pointer-events: auto; }

    /* ===== CONTENT ===== */
    .a-content {
        flex: 1;
        margin-left: var(--a-sidebar);
        padding: 32px 40px 80px;
        min-width: 0;
    }

    .page-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 30px;
    }
    .page-head-left { display: flex; align-items: center; gap: 16px; }

    .page-icon {
        width: 56px; height: 56px;
        border-radius: 16px;
        background: var(--a-grad);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.3rem;
        box-shadow: 0 12px 28px rgba(10,132,255,.3);
        flex-shrink: 0;
    }

    .page-head h1 {
        margin: 0;
        font-family: 'Sora', sans-serif;
        font-size: 1.65rem;
        font-weight: 800;
        letter-spacing: -0.025em;
        color: var(--a-ink);
        line-height: 1.15;
    }

    .page-head p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: .9rem;
    }

    /* ===== STAT CARDS ===== */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 18px;
        margin-bottom: 32px;
    }

    .stat-card {
        position: relative;
        background: #fff;
        padding: 22px;
        border-radius: var(--a-radius-lg);
        border: 1px solid var(--a-line);
        box-shadow: var(--a-shadow-sm);
        overflow: hidden;
        transition: all .35s var(--a-ease);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 140px; height: 140px;
        background: var(--a-grad-soft);
        border-radius: 50%;
        transform: translate(40%, -40%);
        pointer-events: none;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--a-shadow-lg);
        border-color: rgba(10,132,255,.2);
    }

    .stat-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        position: relative;
        z-index: 1;
    }

    .stat-icon {
        width: 46px; height: 46px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.05rem;
        flex-shrink: 0;
    }
    .stat-icon.blue    { background: linear-gradient(135deg, #0a84ff, #0062cc); box-shadow: 0 8px 20px rgba(10,132,255,.32); }
    .stat-icon.purple  { background: linear-gradient(135deg, #7c3aed, #ec4899); box-shadow: 0 8px 20px rgba(124,58,237,.32); }
    .stat-icon.emerald { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 8px 20px rgba(16,185,129,.32); }
    .stat-icon.amber   { background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 8px 20px rgba(245,158,11,.32); }
    .stat-icon.cyan    { background: linear-gradient(135deg, #06b6d4, #0891b2); box-shadow: 0 8px 20px rgba(6,182,212,.32); }
    .stat-icon.rose    { background: linear-gradient(135deg, #ef4444, #b91c1c); box-shadow: 0 8px 20px rgba(239,68,68,.32); }

    .stat-trend {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: .72rem;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        background: rgba(16,185,129,.1);
        color: #059669;
    }
    .stat-trend i { font-size: .6rem; }

    .stat-body { position: relative; z-index: 1; }
    .stat-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .12em;
        color: #94a3b8;
        font-weight: 700;
        margin: 0 0 6px;
    }
    .stat-value {
        font-family: 'Sora', sans-serif;
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--a-ink);
        line-height: 1;
    }

    /* ===== CARD ===== */
    .card {
        background: #fff;
        border-radius: var(--a-radius-lg);
        border: 1px solid var(--a-line);
        box-shadow: var(--a-shadow-sm);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .card-head {
        padding: 22px 26px;
        border-bottom: 1px solid var(--a-line);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .card-head-left { display: flex; align-items: center; gap: 14px; }

    .card-head-icon {
        width: 40px; height: 40px;
        border-radius: 11px;
        background: var(--a-grad-soft);
        color: var(--a-brand);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
        flex-shrink: 0;
    }

    .card-head h2 {
        margin: 0;
        font-family: 'Sora', sans-serif;
        font-size: 1.05rem;
        font-weight: 700;
        letter-spacing: -0.015em;
        color: var(--a-ink);
    }

    .card-head small {
        display: block;
        font-family: 'Inter', sans-serif;
        font-size: .78rem;
        color: #94a3b8;
        font-weight: 500;
        margin-top: 2px;
    }

    /* ===== TABLE ===== */
    .table-wrap { width: 100%; overflow-x: auto; }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: .88rem;
    }

    thead th {
        background: #fafbfe;
        color: #64748b;
        font-weight: 700;
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .1em;
        padding: 14px 22px;
        text-align: left;
        border-bottom: 1px solid var(--a-line);
        white-space: nowrap;
    }

    tbody td {
        padding: 16px 22px;
        border-bottom: 1px solid var(--a-line-2);
        vertical-align: middle;
        color: var(--a-ink);
    }

    tbody tr { transition: background .2s; }
    tbody tr:hover { background: #fafbfe; }
    tbody tr:last-child td { border-bottom: none; }

    /* ===== BADGES ===== */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .01em;
        line-height: 1.5;
    }
    .badge::before {
        content: '';
        width: 6px; height: 6px;
        border-radius: 50%;
        background: currentColor;
        flex-shrink: 0;
    }
    .badge-admin     { background: rgba(124,58,237,.1); color: #7c3aed; }
    .badge-editor    { background: rgba(10,132,255,.1); color: #0a84ff; }
    .badge-active    { background: rgba(16,185,129,.1); color: #059669; }
    .badge-inactive  { background: rgba(148,163,184,.15); color: #64748b; }
    .badge-read      { background: rgba(16,185,129,.1); color: #059669; }
    .badge-unread    { background: rgba(239,68,68,.1); color: #dc2626; }
    .badge-featured  { background: rgba(245,158,11,.12); color: #b45309; }
    .badge-featured::before { background: #f59e0b; }
    .badge-count {
        background: var(--a-grad-soft);
        color: var(--a-brand);
        padding: 3px 10px;
        border-radius: 8px;
        font-weight: 700;
        font-size: .75rem;
    }
    .badge-count::before { display: none; }

    /* ===== AVATAR CELLS ===== */
    .cell-user { display: flex; align-items: center; gap: 12px; }
    .avatar-circle {
        width: 38px; height: 38px;
        border-radius: 50%;
        background: var(--a-grad);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: .88rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(10,132,255,.24);
    }
    .avatar-circle.soft {
        background: var(--a-grad-soft);
        color: var(--a-brand);
        box-shadow: none;
    }
    .cell-user strong { display: block; font-weight: 600; color: var(--a-ink); }
    .cell-user small { color: #94a3b8; font-size: .78rem; }

    /* ===== BUTTONS ===== */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 18px;
        border: none;
        border-radius: 11px;
        font-family: inherit;
        font-size: .85rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
        transition: all .25s var(--a-ease);
        line-height: 1.3;
    }

    .btn-primary {
        background: var(--a-grad);
        color: #fff;
        box-shadow: 0 8px 20px rgba(10,132,255,.32);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(10,132,255,.45);
    }
    .btn-primary:active { transform: translateY(0); }

    .btn-ghost {
        background: #fff;
        color: #334155;
        border: 1px solid var(--a-line);
    }
    .btn-ghost:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: var(--a-brand);
        transform: translateY(-1px);
    }

    .btn-icon {
        width: 38px; height: 38px;
        padding: 0;
        border-radius: 10px;
        font-size: .85rem;
    }
    .btn-icon.btn-ghost {
        background: #fff;
        color: #64748b;
        border: 1px solid var(--a-line);
    }
    .btn-icon.btn-ghost:hover {
        background: var(--a-grad-soft);
        color: var(--a-brand);
        border-color: rgba(10,132,255,.35);
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #fff;
        box-shadow: 0 8px 20px rgba(239,68,68,.28);
    }
    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 26px rgba(239,68,68,.4);
    }
    .btn-icon.btn-danger {
        width: 38px; height: 38px;
        padding: 0;
        border-radius: 10px;
    }

    .action-group { display: flex; gap: 6px; justify-content: flex-end; }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 70px 20px;
        color: #94a3b8;
    }
    .empty-state .icon {
        width: 72px; height: 72px;
        margin: 0 auto 18px;
        border-radius: 20px;
        background: var(--a-grad-soft);
        color: var(--a-brand);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
    }
    .empty-state h3 {
        margin: 0 0 6px;
        font-family: 'Sora', sans-serif;
        font-size: 1rem;
        color: var(--a-ink);
        font-weight: 700;
    }
    .empty-state p { margin: 0; font-size: .88rem; }

    /* ===== THUMBNAILS ===== */
    .thumb {
        width: 64px;
        height: 46px;
        object-fit: cover;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(10,16,32,.1);
    }

    /* ===== MESSAGE PREVIEW ===== */
    .msg-preview {
        color: #64748b;
        font-size: .85rem;
        line-height: 1.5;
        max-width: 420px;
    }

    /* ===== MODAL ===== */
    .modal {
        position: fixed;
        inset: 0;
        background: rgba(10,16,32,.55);
        backdrop-filter: blur(6px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        opacity: 0;
        pointer-events: none;
        transition: opacity .3s var(--a-ease);
        z-index: 1100;
    }
    .modal.show { opacity: 1; pointer-events: auto; }

    .modal-content {
        background: #fff;
        width: 100%;
        max-width: 540px;
        border-radius: var(--a-radius-xl);
        box-shadow: var(--a-shadow-xl);
        transform: translateY(16px) scale(.97);
        transition: transform .35s var(--a-ease);
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .modal.show .modal-content { transform: translateY(0) scale(1); }

    .modal-head {
        padding: 22px 26px;
        border-bottom: 1px solid var(--a-line);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }
    .modal-head-left { display: flex; align-items: center; gap: 14px; }
    .modal-head-icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        background: var(--a-grad);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
        box-shadow: 0 8px 20px rgba(10,132,255,.3);
        flex-shrink: 0;
    }
    .modal-head h3 {
        margin: 0;
        font-family: 'Sora', sans-serif;
        font-size: 1.05rem;
        font-weight: 700;
        letter-spacing: -0.015em;
    }
    .modal-head small {
        display: block;
        font-family: 'Inter', sans-serif;
        font-size: .78rem;
        color: #94a3b8;
        font-weight: 500;
        margin-top: 2px;
    }

    .modal-close {
        width: 36px; height: 36px;
        border: none;
        background: #f1f5f9;
        border-radius: 10px;
        cursor: pointer;
        color: #64748b;
        font-size: .95rem;
        transition: all .2s;
        flex-shrink: 0;
    }
    .modal-close:hover { background: #fee2e2; color: #dc2626; }

    .modal-body { padding: 24px 26px; overflow-y: auto; }

    .form-row { margin-bottom: 18px; }
    .form-row:last-child { margin-bottom: 0; }

    .form-label {
        display: block;
        font-size: .8rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 7px;
        letter-spacing: .01em;
    }
    .form-label small {
        color: #94a3b8;
        font-weight: 400;
        font-size: .75rem;
        margin-left: 4px;
    }

    .form-control {
        width: 100%;
        padding: 11px 14px;
        border: 1.5px solid var(--a-line);
        border-radius: 11px;
        font-size: .9rem;
        font-family: inherit;
        background: #fafbfd;
        outline: none;
        transition: all .2s var(--a-ease);
        color: var(--a-ink);
    }
    .form-control:focus {
        border-color: var(--a-brand);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(10,132,255,.1);
    }
    textarea.form-control { min-height: 100px; resize: vertical; font-family: inherit; }

    .form-check {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border: 1.5px solid var(--a-line);
        border-radius: 11px;
        background: #fafbfd;
        cursor: pointer;
        transition: all .2s;
    }
    .form-check:hover { border-color: rgba(10,132,255,.35); }
    .form-check input { width: 16px; height: 16px; accent-color: var(--a-brand); cursor: pointer; }
    .form-check label { margin: 0; cursor: pointer; font-size: .88rem; font-weight: 500; color: #334155; }

    .modal-foot {
        padding: 18px 26px;
        border-top: 1px solid var(--a-line);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        background: #fafbfe;
    }

    /* ===== TOAST ===== */
    #toastContainer {
        position: fixed;
        bottom: 26px;
        right: 26px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        z-index: 1200;
        max-width: 340px;
    }

    .toast {
        background: #fff;
        color: var(--a-ink);
        padding: 14px 18px;
        border-radius: 14px;
        font-size: .88rem;
        font-weight: 500;
        box-shadow: var(--a-shadow-lg);
        display: flex;
        align-items: center;
        gap: 12px;
        animation: toastIn .4s var(--a-ease);
        border: 1px solid var(--a-line);
        position: relative;
        overflow: hidden;
    }
    .toast::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 4px;
    }
    .toast.success::before { background: var(--a-emerald); }
    .toast.error::before   { background: var(--a-rose); }
    .toast i { font-size: 1.05rem; }
    .toast.success i { color: var(--a-emerald); }
    .toast.error i   { color: var(--a-rose); }

    @keyframes toastIn {
        from { opacity: 0; transform: translateX(40px) scale(.95); }
        to   { opacity: 1; transform: translateX(0) scale(1); }
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1000px) {
        .breadcrumb { display: none; }
        .topbar-divider { display: none; }
    }

    @media (max-width: 900px) {
        .hamburger { display: flex; }
        .brand-sub { display: none; }

        .a-sidebar {
            transform: translateX(-100%);
            box-shadow: 8px 0 40px rgba(0,0,0,.35);
        }
        .a-sidebar.open { transform: translateX(0); }

        .a-content { margin-left: 0; padding: 24px 20px 80px; }

        .page-head h1 { font-size: 1.3rem; }
        .page-icon { width: 48px; height: 48px; font-size: 1.1rem; }
    }

    @media (max-width: 600px) {
        .a-topbar { padding: 0 16px; gap: 12px; }
        .topbar-user .user-meta { display: none; }
        .topbar-user { padding: 4px; }
        .topbar-icon-btn { width: 38px; height: 38px; }
        .topbar-icon-btn:not(:last-of-type) { display: none; }

        .a-content { padding: 20px 14px 70px; }

        .stat-grid { grid-template-columns: 1fr; gap: 12px; }

        .card-head { padding: 16px 18px; }
        thead th, tbody td { padding: 12px 16px; }
        thead th { font-size: .66rem; }
        tbody td { font-size: .82rem; }

        .modal-head, .modal-body, .modal-foot { padding-left: 20px; padding-right: 20px; }
    }
    </style>
</head>
<body class="admin">

<!-- =================== TOPBAR =================== -->
<header class="a-topbar">
    <button class="hamburger" id="hamburger" aria-label="Toggle menu">
        <i class="fa fa-bars"></i>
    </button>

    <a href="admin.php?page=dashboard" class="topbar-brand">
        <span class="brand-mark"><i class="fa fa-bolt"></i></span>
        <span>
            <span class="brand-name">Vaal Admin</span>
            <span class="brand-sub">Control Panel</span>
        </span>
    </a>

    <div class="topbar-divider"></div>

    <div class="breadcrumb">
        <i class="fa fa-house"></i>
        <span class="sep"><i class="fa fa-chevron-right"></i></span>
        <span>Admin</span>
        <span class="sep"><i class="fa fa-chevron-right"></i></span>
        <strong><?= htmlspecialchars($pageTitle) ?></strong>
    </div>

    <div class="topbar-spacer"></div>

    <button class="topbar-icon-btn" title="Search"><i class="fa fa-magnifying-glass"></i></button>
    <button class="topbar-icon-btn" title="Notifications">
        <i class="fa fa-bell"></i>
        <?php if ($messageCount): ?><span class="dot"></span><?php endif; ?>
    </button>

    <div class="topbar-user">
        <div class="user-meta">
            <strong><?= htmlspecialchars($currentUser['name']) ?></strong>
            <small><?= htmlspecialchars($currentUser['role']) ?></small>
        </div>
        <div class="user-avatar"><?= htmlspecialchars($initial) ?></div>
    </div>
</header>

<!-- =================== SHELL =================== -->
<div class="a-shell">

    <!-- SIDEBAR -->
    <aside class="a-sidebar" id="sidebar">

        <div class="sidebar-profile">
            <div class="sidebar-profile-avatar"><?= htmlspecialchars($initial) ?></div>
            <div class="sidebar-profile-info">
                <strong><?= htmlspecialchars($currentUser['name']) ?></strong>
                <small><?= htmlspecialchars($currentUser['role']) ?></small>
            </div>
        </div>

        <div class="nav-label">Main</div>
        <nav class="a-nav">
            <a href="admin.php?page=dashboard" class="<?= $page=='dashboard'?'active':'' ?>">
                <span class="nav-icon"><i class="fa fa-gauge-high"></i></span>
                <span>Dashboard</span>
            </a>
            <?php if ($isAdmin): ?>
            <a href="admin.php?page=users" class="<?= $page=='users'?'active':'' ?>">
                <span class="nav-icon"><i class="fa fa-users"></i></span>
                <span>Users</span>
            </a>
            <?php endif; ?>
            <a href="admin.php?page=messages" class="<?= $page=='messages'?'active':'' ?>">
                <span class="nav-icon"><i class="fa fa-envelope"></i></span>
                <span>Messages</span>
                <?php if ($messageCount): ?><span class="nav-badge"><?= $messageCount ?></span><?php endif; ?>
            </a>
        </nav>

        <div class="nav-label">Content</div>
        <nav class="a-nav">
            <a href="admin.php?page=services" class="<?= $page=='services'?'active':'' ?>">
                <span class="nav-icon"><i class="fa fa-cogs"></i></span>
                <span>Services</span>
            </a>
            <a href="admin.php?page=projects" class="<?= $page=='projects'?'active':'' ?>">
                <span class="nav-icon"><i class="fa fa-folder-open"></i></span>
                <span>Projects</span>
            </a>
            <a href="admin.php?page=testimonials" class="<?= $page=='testimonials'?'active':'' ?>">
                <span class="nav-icon"><i class="fa fa-comment-dots"></i></span>
                <span>Testimonials</span>
            </a>
            <a href="admin.php?page=stats" class="<?= $page=='stats'?'active':'' ?>">
                <span class="nav-icon"><i class="fa fa-chart-simple"></i></span>
                <span>Stats</span>
            </a>
            <a href="admin.php?page=pricing" class="<?= $page=='pricing'?'active':'' ?>">
                <span class="nav-icon"><i class="fa fa-tags"></i></span>
                <span>Pricing</span>
            </a>
        </nav>

        <div class="nav-label">Account</div>
        <nav class="a-nav">
            <a href="index.php" target="_blank">
                <span class="nav-icon"><i class="fa fa-globe"></i></span>
                <span>View Site</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="sidebar-logout">
                <span class="nav-icon"><i class="fa fa-right-from-bracket"></i></span>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- =================== CONTENT =================== -->
    <main class="a-content">

        <div class="page-head">
            <div class="page-head-left">
                <div class="page-icon"><i class="fa <?= $pageIcon ?>"></i></div>
                <div>
                    <h1><?= htmlspecialchars($pageTitle) ?></h1>
                    <p><?= htmlspecialchars($pageDesc) ?></p>
                </div>
            </div>
            <div class="page-head-actions">
                <?php if ($page === 'dashboard'): ?>
                    <a href="admin.php?page=messages" class="btn btn-primary">
                        <i class="fa fa-inbox"></i> View Messages <?= $messageCount ? "($messageCount)" : '' ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($page === 'dashboard'): ?>

            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-head">
                        <div class="stat-icon blue"><i class="fa fa-users"></i></div>
                        <span class="stat-trend"><i class="fa fa-arrow-up"></i> Live</span>
                    </div>
                    <div class="stat-body">
                        <p class="stat-label">Total Users</p>
                        <p class="stat-value"><?= number_format($userCount) ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-head">
                        <div class="stat-icon purple"><i class="fa fa-cogs"></i></div>
                        <span class="stat-trend"><i class="fa fa-arrow-up"></i> Live</span>
                    </div>
                    <div class="stat-body">
                        <p class="stat-label">Services</p>
                        <p class="stat-value"><?= number_format($serviceCount) ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-head">
                        <div class="stat-icon emerald"><i class="fa fa-folder-open"></i></div>
                        <span class="stat-trend"><i class="fa fa-arrow-up"></i> Live</span>
                    </div>
                    <div class="stat-body">
                        <p class="stat-label">Projects</p>
                        <p class="stat-value"><?= number_format($projectCount) ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-head">
                        <div class="stat-icon amber"><i class="fa fa-envelope"></i></div>
                        <?php if ($messageCount): ?>
                            <span class="stat-trend" style="background:rgba(239,68,68,.1);color:#dc2626;">
                                <i class="fa fa-circle" style="font-size:.5rem;"></i> New
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="stat-body">
                        <p class="stat-label">Unread Messages</p>
                        <p class="stat-value"><?= number_format($messageCount) ?></p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-head-icon"><i class="fa fa-clock-rotate-left"></i></div>
                        <div>
                            <h2>Recent Messages</h2>
                            <small>Last 5 enquiries received</small>
                        </div>
                    </div>
                    <a href="admin.php?page=messages" class="btn btn-ghost">
                        View all <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
                <div class="table-wrap">
                    <?php if (empty($recentMsgs)): ?>
                        <div class="empty-state">
                            <div class="icon"><i class="fa fa-inbox"></i></div>
                            <h3>No messages yet</h3>
                            <p>Messages from the contact form will appear here.</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead><tr><th>From</th><th>Message</th><th>Date</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php foreach ($recentMsgs as $m): ?>
                                <tr>
                                    <td>
                                        <div class="cell-user">
                                            <div class="avatar-circle"><?= htmlspecialchars(strtoupper(substr($m['name'], 0, 1))) ?></div>
                                            <div>
                                                <strong><?= htmlspecialchars($m['name']) ?></strong>
                                                <small><?= htmlspecialchars($m['email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="msg-preview"><?= htmlspecialchars(substr($m['message'], 0, 70)) ?><?= strlen($m['message']) > 70 ? '…' : '' ?></td>
                                    <td><small style="color:#94a3b8;"><?= htmlspecialchars(date('M d, Y', strtotime($m['created_at']))) ?></small></td>
                                    <td>
                                        <?php if ($m['is_read']): ?>
                                            <span class="badge badge-read">Read</span>
                                        <?php else: ?>
                                            <span class="badge badge-unread">Unread</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($page === 'users' && $isAdmin): ?>

            <div class="card">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-head-icon"><i class="fa fa-users"></i></div>
                        <div>
                            <h2>All Users <span class="badge-count"><?= count($users) ?></span></h2>
                            <small>Manage accounts and permissions</small>
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="openUserModal()"><i class="fa fa-plus"></i> Add User</button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Joined</th><th style="text-align:right">Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div class="cell-user">
                                        <div class="avatar-circle"><?= htmlspecialchars(strtoupper(substr($u['name'], 0, 1))) ?></div>
                                        <div>
                                            <strong><?= htmlspecialchars($u['name']) ?></strong>
                                            <small><?= htmlspecialchars($u['email']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                                <td>
                                    <?php if ($u['status']): ?>
                                        <span class="badge badge-active">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><small style="color:#94a3b8;"><?= htmlspecialchars(date('M d, Y', strtotime($u['created_at'] ?? 'now'))) ?></small></td>
                                <td>
                                    <div class="action-group">
                                        <button class="btn btn-ghost btn-icon" title="Edit"
                                            onclick='openUserModal(<?= json_encode($u, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>
                                            <i class="fa fa-pen"></i>
                                        </button>
                                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <form method="post" style="display:inline" onsubmit="return confirm('Delete this user?');">
                                            <input type="hidden" name="entity" value="user">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button class="btn btn-danger btn-icon" title="Delete"><i class="fa fa-trash"></i></button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($page === 'services'): ?>

            <div class="card">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-head-icon"><i class="fa fa-cogs"></i></div>
                        <div>
                            <h2>All Services <span class="badge-count"><?= count($services) ?></span></h2>
                            <small>Manage your service offerings</small>
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="openServiceModal()"><i class="fa fa-plus"></i> Add Service</button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th style="width:70px">Icon</th><th>Title</th><th>Description</th><th>Order</th><th style="text-align:right">Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($services as $item): ?>
                            <tr>
                                <td>
                                    <div class="stat-icon blue" style="width:42px;height:42px;font-size:1rem;border-radius:11px">
                                        <i class="<?= htmlspecialchars($item['icon'] ?: 'fa fa-cog') ?>"></i>
                                    </div>
                                </td>
                                <td><strong><?= htmlspecialchars($item['title']) ?></strong></td>
                                <td class="msg-preview"><?= htmlspecialchars($item['description']) ?></td>
                                <td><span class="badge-count"><?= $item['sort_order'] ?></span></td>
                                <td>
                                    <div class="action-group">
                                        <button class="btn btn-ghost btn-icon" onclick='openServiceModal(<?= json_encode($item, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'><i class="fa fa-pen"></i></button>
                                        <form method="post" style="display:inline" onsubmit="return confirm('Delete?');">
                                            <input type="hidden" name="entity" value="service">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <button class="btn btn-danger btn-icon"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($page === 'projects'): ?>

            <div class="card">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-head-icon"><i class="fa fa-folder-open"></i></div>
                        <div>
                            <h2>All Projects <span class="badge-count"><?= count($projects) ?></span></h2>
                            <small>Showcase your latest work</small>
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="openProjectModal()"><i class="fa fa-plus"></i> Add Project</button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th style="width:90px">Preview</th><th>Title</th><th>Order</th><th style="text-align:right">Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($projects as $item): ?>
                            <tr>
                                <td><img src="<?= htmlspecialchars($item['image']) ?>" alt="" class="thumb"></td>
                                <td><strong><?= htmlspecialchars($item['title']) ?></strong></td>
                                <td><span class="badge-count"><?= $item['sort_order'] ?></span></td>
                                <td>
                                    <div class="action-group">
                                        <button class="btn btn-ghost btn-icon" onclick='openProjectModal(<?= json_encode($item, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'><i class="fa fa-pen"></i></button>
                                        <form method="post" style="display:inline" onsubmit="return confirm('Delete?');">
                                            <input type="hidden" name="entity" value="project">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <button class="btn btn-danger btn-icon"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($page === 'testimonials'): ?>

            <div class="card">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-head-icon"><i class="fa fa-comment-dots"></i></div>
                        <div>
                            <h2>All Testimonials <span class="badge-count"><?= count($testimonials) ?></span></h2>
                            <small>Client feedback and quotes</small>
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="openTestimonialModal()"><i class="fa fa-plus"></i> Add Testimonial</button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Quote</th><th>Author</th><th>Order</th><th style="text-align:right">Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($testimonials as $item): ?>
                            <tr>
                                <td style="max-width:480px;font-style:italic;color:#475569;">"<?= htmlspecialchars($item['quote']) ?>"</td>
                                <td>
                                    <div class="cell-user">
                                        <div class="avatar-circle soft"><?= htmlspecialchars(strtoupper(substr($item['author'], 0, 1))) ?></div>
                                        <strong><?= htmlspecialchars($item['author']) ?></strong>
                                    </div>
                                </td>
                                <td><span class="badge-count"><?= $item['sort_order'] ?></span></td>
                                <td>
                                    <div class="action-group">
                                        <button class="btn btn-ghost btn-icon" onclick='openTestimonialModal(<?= json_encode($item, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'><i class="fa fa-pen"></i></button>
                                        <form method="post" style="display:inline" onsubmit="return confirm('Delete?');">
                                            <input type="hidden" name="entity" value="testimonial">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <button class="btn btn-danger btn-icon"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($page === 'stats'): ?>

            <div class="card">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-head-icon"><i class="fa fa-chart-simple"></i></div>
                        <div>
                            <h2>All Stats <span class="badge-count"><?= count($stats) ?></span></h2>
                            <small>Key numbers displayed on the site</small>
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="openStatModal()"><i class="fa fa-plus"></i> Add Stat</button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Target</th><th>Label</th><th>Order</th><th style="text-align:right">Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($stats as $item): ?>
                            <tr>
                                <td>
                                    <span style="font-family:'Sora',sans-serif;font-size:1.4rem;font-weight:800;background:var(--a-grad);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;">
                                        <?= number_format($item['target']) ?>
                                    </span>
                                </td>
                                <td><strong><?= htmlspecialchars($item['label']) ?></strong></td>
                                <td><span class="badge-count"><?= $item['sort_order'] ?></span></td>
                                <td>
                                    <div class="action-group">
                                        <button class="btn btn-ghost btn-icon" onclick='openStatModal(<?= json_encode($item, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'><i class="fa fa-pen"></i></button>
                                        <form method="post" style="display:inline" onsubmit="return confirm('Delete?');">
                                            <input type="hidden" name="entity" value="stat">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <button class="btn btn-danger btn-icon"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($page === 'pricing'): ?>

            <div class="card">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-head-icon"><i class="fa fa-tags"></i></div>
                        <div>
                            <h2>Pricing Plans <span class="badge-count"><?= count($pricing) ?></span></h2>
                            <small>Your service packages</small>
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="openPricingModal()"><i class="fa fa-plus"></i> Add Plan</button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Plan</th><th>Price</th><th>Features</th><th>Status</th><th>Order</th><th style="text-align:right">Actions</th></tr></thead>
                        <tbody>
                        <?php foreach ($pricing as $item): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                                <td><span style="color:#0a84ff;font-weight:700;font-size:1rem;"><?= htmlspecialchars($item['price']) ?></span></td>
                                <td class="msg-preview">
                                    <?= nl2br(htmlspecialchars(substr($item['features'], 0, 90))) ?><?= strlen($item['features']) > 90 ? '…' : '' ?>
                                </td>
                                <td>
                                    <?php if ($item['featured']): ?>
                                        <span class="badge badge-featured">Featured</span>
                                    <?php else: ?>
                                        <span class="badge badge-inactive">Standard</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge-count"><?= $item['sort_order'] ?></span></td>
                                <td>
                                    <div class="action-group">
                                        <button class="btn btn-ghost btn-icon" onclick='openPricingModal(<?= json_encode($item, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'><i class="fa fa-pen"></i></button>
                                        <form method="post" style="display:inline" onsubmit="return confirm('Delete?');">
                                            <input type="hidden" name="entity" value="pricing">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <button class="btn btn-danger btn-icon"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($page === 'messages'): ?>

            <div class="card">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-head-icon"><i class="fa fa-envelope"></i></div>
                        <div>
                            <h2>All Messages <span class="badge-count"><?= count($messages) ?></span></h2>
                            <small>Enquiries from your visitors</small>
                        </div>
                    </div>
                </div>
                <div class="table-wrap">
                    <?php if (empty($messages)): ?>
                        <div class="empty-state">
                            <div class="icon"><i class="fa fa-inbox"></i></div>
                            <h3>No messages yet</h3>
                            <p>Messages from the contact form will appear here.</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead><tr><th>From</th><th>Message</th><th>Date</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead>
                            <tbody>
                            <?php foreach ($messages as $m): ?>
                                <tr>
                                    <td>
                                        <div class="cell-user">
                                            <div class="avatar-circle"><?= htmlspecialchars(strtoupper(substr($m['name'], 0, 1))) ?></div>
                                            <div>
                                                <strong><?= htmlspecialchars($m['name']) ?></strong>
                                                <small><?= htmlspecialchars($m['email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="msg-preview"><?= nl2br(htmlspecialchars($m['message'])) ?></td>
                                    <td><small style="color:#94a3b8;"><?= htmlspecialchars(date('M d, Y', strtotime($m['created_at']))) ?></small></td>
                                    <td>
                                        <?php if ($m['is_read']): ?>
                                            <span class="badge badge-read">Read</span>
                                        <?php else: ?>
                                            <span class="badge badge-unread">Unread</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-group">
                                            <?php if (!$m['is_read']): ?>
                                            <form method="post" style="display:inline">
                                                <input type="hidden" name="entity" value="message">
                                                <input type="hidden" name="action" value="read">
                                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                                <button class="btn btn-ghost btn-icon" title="Mark as read"><i class="fa fa-check"></i></button>
                                            </form>
                                            <?php endif; ?>
                                            <form method="post" style="display:inline" onsubmit="return confirm('Delete message?');">
                                                <input type="hidden" name="entity" value="message">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                                <button class="btn btn-danger btn-icon"><i class="fa fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

        <?php endif; ?>

    </main>
</div>

<!-- =================== MODALS =================== -->

<!-- USER MODAL -->
<div class="modal" id="userModal">
    <div class="modal-content">
        <div class="modal-head">
            <div class="modal-head-left">
                <div class="modal-head-icon"><i class="fa fa-user-plus"></i></div>
                <div>
                    <h3 id="userModalTitle">Add User</h3>
                    <small>Create or update an account</small>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('userModal')"><i class="fa fa-times"></i></button>
        </div>
        <form method="post">
            <div class="modal-body">
                <input type="hidden" name="entity" value="user">
                <input type="hidden" name="action" id="userAction" value="add">
                <input type="hidden" name="id" id="userId">
                <div class="form-row"><label class="form-label">Full Name</label><input type="text" class="form-control" name="name" id="userName" required></div>
                <div class="form-row"><label class="form-label">Email</label><input type="email" class="form-control" name="email" id="userEmail" required></div>
                <div class="form-row">
                    <label class="form-label">Password <small id="pwdHint">(required for new user)</small></label>
                    <input type="password" class="form-control" name="password" id="userPassword">
                </div>
                <div class="form-row">
                    <label class="form-label">Role</label>
                    <select class="form-control" name="role" id="userRole">
                        <option value="editor">Editor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-row" id="userStatusWrap" style="display:none">
                    <label class="form-check">
                        <input type="checkbox" name="status" id="userStatus" checked>
                        <label for="userStatus">Active account</label>
                    </label>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" onclick="closeModal('userModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save User</button>
            </div>
        </form>
    </div>
</div>

<!-- SERVICE MODAL -->
<div class="modal" id="serviceModal">
    <div class="modal-content">
        <div class="modal-head">
            <div class="modal-head-left">
                <div class="modal-head-icon"><i class="fa fa-cogs"></i></div>
                <div>
                    <h3 id="serviceModalTitle">Add Service</h3>
                    <small>Define a service offering</small>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('serviceModal')"><i class="fa fa-times"></i></button>
        </div>
        <form method="post">
            <div class="modal-body">
                <input type="hidden" name="entity" value="service">
                <input type="hidden" name="action" id="serviceAction" value="add">
                <input type="hidden" name="id" id="serviceId">
                <div class="form-row"><label class="form-label">Icon (Font Awesome class)</label><input type="text" class="form-control" name="icon" id="serviceIcon" placeholder="fa-solid fa-code"></div>
                <div class="form-row"><label class="form-label">Title</label><input type="text" class="form-control" name="title" id="serviceTitle" required></div>
                <div class="form-row"><label class="form-label">Description</label><textarea class="form-control" name="description" id="serviceDescription"></textarea></div>
                <div class="form-row"><label class="form-label">Sort Order</label><input type="number" class="form-control" name="sort_order" id="serviceOrder" value="0"></div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" onclick="closeModal('serviceModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save Service</button>
            </div>
        </form>
    </div>
</div>

<!-- PROJECT MODAL -->
<div class="modal" id="projectModal">
    <div class="modal-content">
        <div class="modal-head">
            <div class="modal-head-left">
                <div class="modal-head-icon"><i class="fa fa-folder-open"></i></div>
                <div>
                    <h3 id="projectModalTitle">Add Project</h3>
                    <small>Showcase a piece of work</small>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('projectModal')"><i class="fa fa-times"></i></button>
        </div>
        <form method="post">
            <div class="modal-body">
                <input type="hidden" name="entity" value="project">
                <input type="hidden" name="action" id="projectAction" value="add">
                <input type="hidden" name="id" id="projectId">
                <div class="form-row"><label class="form-label">Image path</label><input type="text" class="form-control" name="image" id="projectImage" placeholder="images/software.jpg"></div>
                <div class="form-row"><label class="form-label">Title</label><input type="text" class="form-control" name="title" id="projectTitle" required></div>
                <div class="form-row"><label class="form-label">Sort Order</label><input type="number" class="form-control" name="sort_order" id="projectOrder" value="0"></div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" onclick="closeModal('projectModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save Project</button>
            </div>
        </form>
    </div>
</div>

<!-- TESTIMONIAL MODAL -->
<div class="modal" id="testimonialModal">
    <div class="modal-content">
        <div class="modal-head">
            <div class="modal-head-left">
                <div class="modal-head-icon"><i class="fa fa-comment-dots"></i></div>
                <div>
                    <h3 id="testimonialModalTitle">Add Testimonial</h3>
                    <small>Client feedback entry</small>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('testimonialModal')"><i class="fa fa-times"></i></button>
        </div>
        <form method="post">
            <div class="modal-body">
                <input type="hidden" name="entity" value="testimonial">
                <input type="hidden" name="action" id="testimonialAction" value="add">
                <input type="hidden" name="id" id="testimonialId">
                <div class="form-row"><label class="form-label">Quote</label><textarea class="form-control" name="quote" id="testimonialQuote" required></textarea></div>
                <div class="form-row"><label class="form-label">Author</label><input type="text" class="form-control" name="author" id="testimonialAuthor"></div>
                <div class="form-row"><label class="form-label">Sort Order</label><input type="number" class="form-control" name="sort_order" id="testimonialOrder" value="0"></div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" onclick="closeModal('testimonialModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save Testimonial</button>
            </div>
        </form>
    </div>
</div>

<!-- STAT MODAL -->
<div class="modal" id="statModal">
    <div class="modal-content">
        <div class="modal-head">
            <div class="modal-head-left">
                <div class="modal-head-icon"><i class="fa fa-chart-simple"></i></div>
                <div>
                    <h3 id="statModalTitle">Add Stat</h3>
                    <small>Number displayed on the site</small>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('statModal')"><i class="fa fa-times"></i></button>
        </div>
        <form method="post">
            <div class="modal-body">
                <input type="hidden" name="entity" value="stat">
                <input type="hidden" name="action" id="statAction" value="add">
                <input type="hidden" name="id" id="statId">
                <div class="form-row"><label class="form-label">Target Number</label><input type="number" class="form-control" name="target" id="statTarget" required></div>
                <div class="form-row"><label class="form-label">Label</label><input type="text" class="form-control" name="label" id="statLabel" required></div>
                <div class="form-row"><label class="form-label">Sort Order</label><input type="number" class="form-control" name="sort_order" id="statOrder" value="0"></div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" onclick="closeModal('statModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save Stat</button>
            </div>
        </form>
    </div>
</div>

<!-- PRICING MODAL -->
<div class="modal" id="pricingModal">
    <div class="modal-content">
        <div class="modal-head">
            <div class="modal-head-left">
                <div class="modal-head-icon"><i class="fa fa-tags"></i></div>
                <div>
                    <h3 id="pricingModalTitle">Add Plan</h3>
                    <small>Pricing plan details</small>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('pricingModal')"><i class="fa fa-times"></i></button>
        </div>
        <form method="post">
            <div class="modal-body">
                <input type="hidden" name="entity" value="pricing">
                <input type="hidden" name="action" id="pricingAction" value="add">
                <input type="hidden" name="id" id="pricingId">
                <div class="form-row"><label class="form-label">Plan Name</label><input type="text" class="form-control" name="name" id="pricingName" required></div>
                <div class="form-row"><label class="form-label">Price</label><input type="text" class="form-control" name="price" id="pricingPrice" placeholder="R999"></div>
                <div class="form-row"><label class="form-label">Features (one per line)</label><textarea class="form-control" name="features" id="pricingFeatures"></textarea></div>
                <div class="form-row">
                    <label class="form-check">
                        <input type="checkbox" name="featured" id="pricingFeatured">
                        <label for="pricingFeatured">Mark as featured</label>
                    </label>
                </div>
                <div class="form-row"><label class="form-label">Sort Order</label><input type="number" class="form-control" name="sort_order" id="pricingOrder" value="0"></div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-ghost" onclick="closeModal('pricingModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save Plan</button>
            </div>
        </form>
    </div>
</div>

<!-- TOAST -->
<div id="toastContainer"></div>

<script>
/* ========== HAMBURGER ========== */
const sidebar = document.getElementById('sidebar');
const backdrop = document.getElementById('sidebarBackdrop');
document.getElementById('hamburger').addEventListener('click', () => {
    sidebar.classList.toggle('open');
    backdrop.classList.toggle('show');
});
backdrop.addEventListener('click', () => {
    sidebar.classList.remove('open');
    backdrop.classList.remove('show');
});

/* ========== MODAL HELPERS ========== */
function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
document.querySelectorAll('.modal').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('show'); });
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.modal.show').forEach(m => m.classList.remove('show'));
});

/* ========== USER MODAL ========== */
function openUserModal(user = null) {
    const isEdit = !!user;
    document.getElementById('userModalTitle').textContent = isEdit ? 'Edit User' : 'Add User';
    document.getElementById('userAction').value = isEdit ? 'edit' : 'add';
    document.getElementById('userId').value = isEdit ? user.id : '';
    document.getElementById('userName').value = isEdit ? user.name : '';
    document.getElementById('userEmail').value = isEdit ? user.email : '';
    document.getElementById('userPassword').value = '';
    document.getElementById('userPassword').required = !isEdit;
    document.getElementById('userRole').value = isEdit ? user.role : 'editor';
    document.getElementById('userStatus').checked = isEdit ? user.status == 1 : true;
    document.getElementById('userStatusWrap').style.display = isEdit ? 'block' : 'none';
    document.getElementById('pwdHint').textContent = isEdit ? '(leave blank to keep current)' : '(required for new user)';
    openModal('userModal');
}

/* ========== SERVICE MODAL ========== */
function openServiceModal(item = null) {
    const isEdit = !!item;
    document.getElementById('serviceModalTitle').textContent = isEdit ? 'Edit Service' : 'Add Service';
    document.getElementById('serviceAction').value = isEdit ? 'edit' : 'add';
    document.getElementById('serviceId').value = isEdit ? item.id : '';
    document.getElementById('serviceIcon').value = isEdit ? item.icon : '';
    document.getElementById('serviceTitle').value = isEdit ? item.title : '';
    document.getElementById('serviceDescription').value = isEdit ? item.description : '';
    document.getElementById('serviceOrder').value = isEdit ? item.sort_order : 0;
    openModal('serviceModal');
}

/* ========== PROJECT MODAL ========== */
function openProjectModal(item = null) {
    const isEdit = !!item;
    document.getElementById('projectModalTitle').textContent = isEdit ? 'Edit Project' : 'Add Project';
    document.getElementById('projectAction').value = isEdit ? 'edit' : 'add';
    document.getElementById('projectId').value = isEdit ? item.id : '';
    document.getElementById('projectImage').value = isEdit ? item.image : '';
    document.getElementById('projectTitle').value = isEdit ? item.title : '';
    document.getElementById('projectOrder').value = isEdit ? item.sort_order : 0;
    openModal('projectModal');
}

/* ========== TESTIMONIAL MODAL ========== */
function openTestimonialModal(item = null) {
    const isEdit = !!item;
    document.getElementById('testimonialModalTitle').textContent = isEdit ? 'Edit Testimonial' : 'Add Testimonial';
    document.getElementById('testimonialAction').value = isEdit ? 'edit' : 'add';
    document.getElementById('testimonialId').value = isEdit ? item.id : '';
    document.getElementById('testimonialQuote').value = isEdit ? item.quote : '';
    document.getElementById('testimonialAuthor').value = isEdit ? item.author : '';
    document.getElementById('testimonialOrder').value = isEdit ? item.sort_order : 0;
    openModal('testimonialModal');
}

/* ========== STAT MODAL ========== */
function openStatModal(item = null) {
    const isEdit = !!item;
    document.getElementById('statModalTitle').textContent = isEdit ? 'Edit Stat' : 'Add Stat';
    document.getElementById('statAction').value = isEdit ? 'edit' : 'add';
    document.getElementById('statId').value = isEdit ? item.id : '';
    document.getElementById('statTarget').value = isEdit ? item.target : '';
    document.getElementById('statLabel').value = isEdit ? item.label : '';
    document.getElementById('statOrder').value = isEdit ? item.sort_order : 0;
    openModal('statModal');
}

/* ========== PRICING MODAL ========== */
function openPricingModal(item = null) {
    const isEdit = !!item;
    document.getElementById('pricingModalTitle').textContent = isEdit ? 'Edit Plan' : 'Add Plan';
    document.getElementById('pricingAction').value = isEdit ? 'edit' : 'add';
    document.getElementById('pricingId').value = isEdit ? item.id : '';
    document.getElementById('pricingName').value = isEdit ? item.name : '';
    document.getElementById('pricingPrice').value = isEdit ? item.price : '';
    document.getElementById('pricingFeatures').value = isEdit ? item.features : '';
    document.getElementById('pricingFeatured').checked = isEdit ? item.featured == 1 : false;
    document.getElementById('pricingOrder').value = isEdit ? item.sort_order : 0;
    openModal('pricingModal');
}

/* ========== TOAST ========== */
function showToast(msg, type = 'success') {
    const t = document.createElement('div');
    t.className = 'toast ' + type;
    t.innerHTML = `<i class="fa fa-${type === 'error' ? 'circle-exclamation' : 'circle-check'}"></i> <span>${msg}</span>`;
    document.getElementById('toastContainer').appendChild(t);
    setTimeout(() => {
        t.style.opacity = '0';
        t.style.transform = 'translateX(40px) scale(.95)';
        setTimeout(() => t.remove(), 350);
    }, 3200);
}

<?php if (!empty($flash)): ?>
showToast(<?= json_encode($flash) ?>, <?= stripos($flash, 'error') !== false ? "'error'" : "'success'" ?>);
<?php endif; ?>
</script>
</body>
</html>
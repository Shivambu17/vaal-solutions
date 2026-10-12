<?php
require_once 'config.php';

// Auth check
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
if ($_SESSION['user_role'] !== 'admin') { die('Access denied. Admins only.'); }

$page = $_GET['page'] ?? 'dashboard';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entity'])) {
    $entity = $_POST['entity'];
    $action = $_POST['action'] ?? '';

    if ($entity === 'user') {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                trim($_POST['name']),
                trim($_POST['email']),
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $_POST['role']
            ]);
        } elseif ($action === 'edit') {
            $id = (int)$_POST['id'];
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $role = $_POST['role'];
            $status = isset($_POST['status']) ? 1 : 0;
            if (!empty($_POST['password'])) {
                $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, password=?, role=?, status=? WHERE id=?");
                $stmt->execute([$name, $email, password_hash($_POST['password'], PASSWORD_DEFAULT), $role, $status, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=?, status=? WHERE id=?");
                $stmt->execute([$name, $email, $role, $status, $id]);
            }
        } elseif ($action === 'delete') {
            $pdo->prepare("DELETE FROM users WHERE id=?")->execute([(int)$_POST['id']]);
        }
    }

    if ($entity === 'service') {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO services (icon, title, description, sort_order) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['icon'], $_POST['title'], $_POST['description'], (int)$_POST['sort_order']]);
        } elseif ($action === 'edit') {
            $stmt = $pdo->prepare("UPDATE services SET icon=?, title=?, description=?, sort_order=? WHERE id=?");
            $stmt->execute([$_POST['icon'], $_POST['title'], $_POST['description'], (int)$_POST['sort_order'], (int)$_POST['id']]);
        } elseif ($action === 'delete') {
            $pdo->prepare("DELETE FROM services WHERE id=?")->execute([(int)$_POST['id']]);
        }
    }

    if ($entity === 'project') {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO projects (image, title, sort_order) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['image'], $_POST['title'], (int)$_POST['sort_order']]);
        } elseif ($action === 'edit') {
            $stmt = $pdo->prepare("UPDATE projects SET image=?, title=?, sort_order=? WHERE id=?");
            $stmt->execute([$_POST['image'], $_POST['title'], (int)$_POST['sort_order'], (int)$_POST['id']]);
        } elseif ($action === 'delete') {
            $pdo->prepare("DELETE FROM projects WHERE id=?")->execute([(int)$_POST['id']]);
        }
    }

    if ($entity === 'testimonial') {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO testimonials (quote, author, sort_order) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['quote'], $_POST['author'], (int)$_POST['sort_order']]);
        } elseif ($action === 'edit') {
            $stmt = $pdo->prepare("UPDATE testimonials SET quote=?, author=?, sort_order=? WHERE id=?");
            $stmt->execute([$_POST['quote'], $_POST['author'], (int)$_POST['sort_order'], (int)$_POST['id']]);
        } elseif ($action === 'delete') {
            $pdo->prepare("DELETE FROM testimonials WHERE id=?")->execute([(int)$_POST['id']]);
        }
    }

    if ($entity === 'stat') {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO stats (target, label, sort_order) VALUES (?, ?, ?)");
            $stmt->execute([(int)$_POST['target'], $_POST['label'], (int)$_POST['sort_order']]);
        } elseif ($action === 'edit') {
            $stmt = $pdo->prepare("UPDATE stats SET target=?, label=?, sort_order=? WHERE id=?");
            $stmt->execute([(int)$_POST['target'], $_POST['label'], (int)$_POST['sort_order'], (int)$_POST['id']]);
        } elseif ($action === 'delete') {
            $pdo->prepare("DELETE FROM stats WHERE id=?")->execute([(int)$_POST['id']]);
        }
    }

    if ($entity === 'pricing') {
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO pricing (name, price, features, featured, sort_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['name'], $_POST['price'], $_POST['features'], isset($_POST['featured']) ? 1 : 0, (int)$_POST['sort_order']]);
        } elseif ($action === 'edit') {
            $stmt = $pdo->prepare("UPDATE pricing SET name=?, price=?, features=?, featured=?, sort_order=? WHERE id=?");
            $stmt->execute([$_POST['name'], $_POST['price'], $_POST['features'], isset($_POST['featured']) ? 1 : 0, (int)$_POST['sort_order'], (int)$_POST['id']]);
        } elseif ($action === 'delete') {
            $pdo->prepare("DELETE FROM pricing WHERE id=?")->execute([(int)$_POST['id']]);
        }
    }

    if ($entity === 'message') {
        if ($action === 'delete') {
            $pdo->prepare("DELETE FROM contact_messages WHERE id=?")->execute([(int)$_POST['id']]);
        } elseif ($action === 'read') {
            $pdo->prepare("UPDATE contact_messages SET is_read=1 WHERE id=?")->execute([(int)$_POST['id']]);
        }
    }

    header("Location: admin.php?page=$page");
    exit;
}

// Dashboard counts
$userCount    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$serviceCount = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
$messageCount = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Vaal Solutions</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="admin">
<div class="admin-wrapper">
    <aside class="admin-sidebar">
        <h2>Vaal Admin</h2>
        <nav>
            <a href="admin.php?page=dashboard" class="<?= $page=='dashboard'?'active':'' ?>"><i class="fa fa-tachometer-alt"></i> Dashboard</a>
            <a href="admin.php?page=users" class="<?= $page=='users'?'active':'' ?>"><i class="fa fa-users"></i> Users</a>
            <a href="admin.php?page=services" class="<?= $page=='services'?'active':'' ?>"><i class="fa fa-cogs"></i> Services</a>
            <a href="admin.php?page=projects" class="<?= $page=='projects'?'active':'' ?>"><i class="fa fa-folder"></i> Projects</a>
            <a href="admin.php?page=testimonials" class="<?= $page=='testimonials'?'active':'' ?>"><i class="fa fa-comment"></i> Testimonials</a>
            <a href="admin.php?page=stats" class="<?= $page=='stats'?'active':'' ?>"><i class="fa fa-chart-bar"></i> Stats</a>
            <a href="admin.php?page=pricing" class="<?= $page=='pricing'?'active':'' ?>"><i class="fa fa-tags"></i> Pricing</a>
            <a href="admin.php?page=messages" class="<?= $page=='messages'?'active':'' ?>"><i class="fa fa-envelope"></i> Messages</a>
            <a href="index.php"><i class="fa fa-globe"></i> View Site</a>
            <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>
    <main class="admin-content">

    <?php if ($page === 'dashboard'): ?>
        <h1>Dashboard</h1>
        <div class="stats-grid">
            <div class="stat-card"><h3>Users</h3><p><?= $userCount ?></p></div>
            <div class="stat-card"><h3>Services</h3><p><?= $serviceCount ?></p></div>
            <div class="stat-card"><h3>Unread Messages</h3><p><?= $messageCount ?></p></div>
        </div>
        <h2>Recent Messages</h2>
        <table>
            <tr><th>Name</th><th>Email</th><th>Message</th><th>Date</th></tr>
            <?php foreach ($pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5") as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['name']) ?></td>
                <td><?= htmlspecialchars($m['email']) ?></td>
                <td><?= htmlspecialchars(substr($m['message'], 0, 60)) ?>...</td>
                <td><?= htmlspecialchars($m['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

    <?php elseif ($page === 'users'): ?>
        <h1>Manage Users</h1>
        <button class="btn" onclick="document.getElementById('addUser').style.display='block'">Add User</button>
        <div id="addUser" class="add-form">
            <h3>Add New User</h3>
            <form method="post">
                <input type="hidden" name="entity" value="user">
                <input type="hidden" name="action" value="add">
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="role">
                    <option value="editor">Editor</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
        <table>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr>
            <?php foreach ($pdo->query("SELECT * FROM users ORDER BY id DESC") as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td><?= $u['status'] ? 'Active' : 'Inactive' ?></td>
                <td>
                    <form method="post" class="form-inline">
                        <input type="hidden" name="entity" value="user">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <input type="text" name="name" value="<?= htmlspecialchars($u['name']) ?>" required>
                        <input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" required>
                        <input type="password" name="password" placeholder="New pass">
                        <select name="role">
                            <option value="editor" <?= $u['role']=='editor'?'selected':'' ?>>Editor</option>
                            <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                        </select>
                        <label><input type="checkbox" name="status" <?= $u['status']?'checked':'' ?>> Active</label>
                        <button type="submit" class="btn">Update</button>
                    </form>
                    <form method="post" class="form-inline" onsubmit="return confirm('Delete user?');">
                        <input type="hidden" name="entity" value="user">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

    <?php elseif ($page === 'services'): ?>
        <h1>Services</h1>
        <button class="btn" onclick="document.getElementById('addService').style.display='block'">Add Service</button>
        <div id="addService" class="add-form">
            <form method="post">
                <input type="hidden" name="entity" value="service">
                <input type="hidden" name="action" value="add">
                <input type="text" name="icon" placeholder="Font Awesome class (e.g. fa-solid fa-code)">
                <input type="text" name="title" placeholder="Title" required>
                <textarea name="description" placeholder="Description"></textarea>
                <input type="number" name="sort_order" value="0">
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
        <table>
            <tr><th>ID</th><th>Icon</th><th>Title</th><th>Description</th><th>Order</th><th>Actions</th></tr>
            <?php foreach ($pdo->query("SELECT * FROM services ORDER BY sort_order ASC") as $item): ?>
            <tr>
                <td><?= $item['id'] ?></td>
                <td><i class="<?= htmlspecialchars($item['icon']) ?>"></i></td>
                <td><?= htmlspecialchars($item['title']) ?></td>
                <td><?= htmlspecialchars($item['description']) ?></td>
                <td><?= $item['sort_order'] ?></td>
                <td>
                    <form method="post" class="form-inline">
                        <input type="hidden" name="entity" value="service">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <input type="text" name="icon" value="<?= htmlspecialchars($item['icon']) ?>">
                        <input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" required>
                        <textarea name="description"><?= htmlspecialchars($item['description']) ?></textarea>
                        <input type="number" name="sort_order" value="<?= $item['sort_order'] ?>">
                        <button type="submit" class="btn">Update</button>
                    </form>
                    <form method="post" class="form-inline" onsubmit="return confirm('Delete?');">
                        <input type="hidden" name="entity" value="service">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

    <?php elseif ($page === 'projects'): ?>
        <h1>Projects</h1>
        <button class="btn" onclick="document.getElementById('addProject').style.display='block'">Add Project</button>
        <div id="addProject" class="add-form">
            <form method="post">
                <input type="hidden" name="entity" value="project">
                <input type="hidden" name="action" value="add">
                <input type="text" name="image" placeholder="Image path (e.g. images/software.jpg)">
                <input type="text" name="title" placeholder="Title" required>
                <input type="number" name="sort_order" value="0">
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
        <table>
            <tr><th>ID</th><th>Image</th><th>Title</th><th>Order</th><th>Actions</th></tr>
            <?php foreach ($pdo->query("SELECT * FROM projects ORDER BY sort_order ASC") as $item): ?>
            <tr>
                <td><?= $item['id'] ?></td>
                <td><img src="<?= htmlspecialchars($item['image']) ?>" width="60" alt=""></td>
                <td><?= htmlspecialchars($item['title']) ?></td>
                <td><?= $item['sort_order'] ?></td>
                <td>
                    <form method="post" class="form-inline">
                        <input type="hidden" name="entity" value="project">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <input type="text" name="image" value="<?= htmlspecialchars($item['image']) ?>">
                        <input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" required>
                        <input type="number" name="sort_order" value="<?= $item['sort_order'] ?>">
                        <button type="submit" class="btn">Update</button>
                    </form>
                    <form method="post" class="form-inline" onsubmit="return confirm('Delete?');">
                        <input type="hidden" name="entity" value="project">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

    <?php elseif ($page === 'testimonials'): ?>
        <h1>Testimonials</h1>
        <button class="btn" onclick="document.getElementById('addT').style.display='block'">Add Testimonial</button>
        <div id="addT" class="add-form">
            <form method="post">
                <input type="hidden" name="entity" value="testimonial">
                <input type="hidden" name="action" value="add">
                <textarea name="quote" placeholder="Quote" required></textarea>
                <input type="text" name="author" placeholder="Author">
                <input type="number" name="sort_order" value="0">
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
        <table>
            <tr><th>ID</th><th>Quote</th><th>Author</th><th>Order</th><th>Actions</th></tr>
            <?php foreach ($pdo->query("SELECT * FROM testimonials ORDER BY sort_order ASC") as $item): ?>
            <tr>
                <td><?= $item['id'] ?></td>
                <td><?= htmlspecialchars($item['quote']) ?></td>
                <td><?= htmlspecialchars($item['author']) ?></td>
                <td><?= $item['sort_order'] ?></td>
                <td>
                    <form method="post" class="form-inline">
                        <input type="hidden" name="entity" value="testimonial">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <textarea name="quote" required><?= htmlspecialchars($item['quote']) ?></textarea>
                        <input type="text" name="author" value="<?= htmlspecialchars($item['author']) ?>">
                        <input type="number" name="sort_order" value="<?= $item['sort_order'] ?>">
                        <button type="submit" class="btn">Update</button>
                    </form>
                    <form method="post" class="form-inline" onsubmit="return confirm('Delete?');">
                        <input type="hidden" name="entity" value="testimonial">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

    <?php elseif ($page === 'stats'): ?>
        <h1>Stats</h1>
        <button class="btn" onclick="document.getElementById('addStat').style.display='block'">Add Stat</button>
        <div id="addStat" class="add-form">
            <form method="post">
                <input type="hidden" name="entity" value="stat">
                <input type="hidden" name="action" value="add">
                <input type="number" name="target" placeholder="Target Number" required>
                <input type="text" name="label" placeholder="Label" required>
                <input type="number" name="sort_order" value="0">
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
        <table>
            <tr><th>ID</th><th>Target</th><th>Label</th><th>Order</th><th>Actions</th></tr>
            <?php foreach ($pdo->query("SELECT * FROM stats ORDER BY sort_order ASC") as $item): ?>
            <tr>
                <td><?= $item['id'] ?></td>
                <td><?= $item['target'] ?></td>
                <td><?= htmlspecialchars($item['label']) ?></td>
                <td><?= $item['sort_order'] ?></td>
                <td>
                    <form method="post" class="form-inline">
                        <input type="hidden" name="entity" value="stat">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <input type="number" name="target" value="<?= $item['target'] ?>" required>
                        <input type="text" name="label" value="<?= htmlspecialchars($item['label']) ?>" required>
                        <input type="number" name="sort_order" value="<?= $item['sort_order'] ?>">
                        <button type="submit" class="btn">Update</button>
                    </form>
                    <form method="post" class="form-inline" onsubmit="return confirm('Delete?');">
                        <input type="hidden" name="entity" value="stat">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

    <?php elseif ($page === 'pricing'): ?>
        <h1>Pricing Plans</h1>
        <button class="btn" onclick="document.getElementById('addP').style.display='block'">Add Plan</button>
        <div id="addP" class="add-form">
            <form method="post">
                <input type="hidden" name="entity" value="pricing">
                <input type="hidden" name="action" value="add">
                <input type="text" name="name" placeholder="Plan Name" required>
                <input type="text" name="price" placeholder="Price (e.g. R999)">
                <textarea name="features" placeholder="Features (one per line)"></textarea>
                <label><input type="checkbox" name="featured"> Featured</label>
                <input type="number" name="sort_order" value="0">
                <button type="submit" class="btn">Save</button>
            </form>
        </div>
        <table>
            <tr><th>ID</th><th>Name</th><th>Price</th><th>Features</th><th>Featured</th><th>Order</th><th>Actions</th></tr>
            <?php foreach ($pdo->query("SELECT * FROM pricing ORDER BY sort_order ASC") as $item): ?>
            <tr>
                <td><?= $item['id'] ?></td>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= htmlspecialchars($item['price']) ?></td>
                <td><?= nl2br(htmlspecialchars($item['features'])) ?></td>
                <td><?= $item['featured'] ? 'Yes' : 'No' ?></td>
                <td><?= $item['sort_order'] ?></td>
                <td>
                    <form method="post" class="form-inline">
                        <input type="hidden" name="entity" value="pricing">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
                        <input type="text" name="price" value="<?= htmlspecialchars($item['price']) ?>">
                        <textarea name="features"><?= htmlspecialchars($item['features']) ?></textarea>
                        <label><input type="checkbox" name="featured" <?= $item['featured']?'checked':'' ?>> Featured</label>
                        <input type="number" name="sort_order" value="<?= $item['sort_order'] ?>">
                        <button type="submit" class="btn">Update</button>
                    </form>
                    <form method="post" class="form-inline" onsubmit="return confirm('Delete?');">
                        <input type="hidden" name="entity" value="pricing">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

    <?php elseif ($page === 'messages'): ?>
        <h1>Contact Messages</h1>
        <table>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Date</th><th>Status</th><th>Actions</th></tr>
            <?php foreach ($pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC") as $m): ?>
            <tr>
                <td><?= $m['id'] ?></td>
                <td><?= htmlspecialchars($m['name']) ?></td>
                <td><?= htmlspecialchars($m['email']) ?></td>
                <td><?= nl2br(htmlspecialchars($m['message'])) ?></td>
                <td><?= htmlspecialchars($m['created_at']) ?></td>
                <td><?= $m['is_read'] ? 'Read' : 'Unread' ?></td>
                <td>
                    <?php if (!$m['is_read']): ?>
                    <form method="post" class="form-inline">
                        <input type="hidden" name="entity" value="message">
                        <input type="hidden" name="action" value="read">
                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                        <button type="submit" class="btn">Mark Read</button>
                    </form>
                    <?php endif; ?>
                    <form method="post" class="form-inline" onsubmit="return confirm('Delete message?');">
                        <input type="hidden" name="entity" value="message">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    </main>
</div>
</body>
</html>
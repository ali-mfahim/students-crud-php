<?php
require 'db.php';

// Handle delete action
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: list.php?msg=deleted");
    exit;
}

// Fetch all students
$students = $pdo->query("SELECT * FROM students ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students — List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --accent: #f97316;
            --accent-hover: #ea6c0a;
            --dark: #0f0f0f;
            --surface: #1a1a1a;
            --card: #242424;
            --border: #2e2e2e;
            --text: #f0f0f0;
            --muted: #888;
        }

        .table {
            --bs-table-bg: #0e0e0e;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--dark);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar-custom {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0.9rem 2rem;
        }

        .navbar-brand-text {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 1.4rem;
            color: var(--accent) !important;
            letter-spacing: -0.5px;
        }

        .navbar-brand-text span {
            color: var(--text);
        }

        /* Page header */
        .page-header {
            padding: 2.5rem 0 1.5rem;
        }

        .page-title {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 2rem;
            color: var(--text);
        }

        .page-title span {
            color: var(--accent);
        }

        /* Alert */
        .alert-custom {
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
            background: #f97316 !important;
            color: white !important;
        }

        /* Add button */
        .btn-add {
            background: var(--accent);
            color: #fff;
            font-family: 'Syne', sans-serif;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            padding: 0.55rem 1.3rem;
            transition: background 0.2s, transform 0.15s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-add:hover {
            background: var(--accent-hover);
            color: #fff;
            transform: translateY(-1px);
        }

        /* Table card */
        .table-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
        }

        .table-custom {
            margin: 0;
            color: var(--text);
        }

        .table-custom thead th {
            background: var(--surface);
            color: var(--accent);
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 0.78rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 1rem 1.2rem;
            border-bottom: 1px solid var(--border);
            border-top: none;
        }

        .table-custom tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }

        .table-custom tbody tr:last-child {
            border-bottom: none;
        }

        .table-custom tbody tr:hover {
            background: rgba(249, 115, 22, 0.06);
        }

        .table-custom tbody td {
            padding: 1rem 1.2rem;
            vertical-align: middle;
            font-size: 0.92rem;
            color: var(--text);
            border: none;
        }

        /* Badge for class */
        .badge-class {
            background: rgba(249, 115, 22, 0.15);
            color: var(--accent);
            font-family: 'Syne', sans-serif;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 6px;
        }

        /* Roll number */
        .roll-pill {
            background: var(--surface);
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 500;
            padding: 3px 9px;
            border-radius: 5px;
            border: 1px solid var(--border);
            font-family: monospace;
        }

        /* Action buttons */
        .btn-edit {
            background: transparent;
            border: 1px solid #3b82f6;
            color: #3b82f6;
            border-radius: 7px;
            padding: 4px 12px;
            font-size: 0.82rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-edit:hover {
            background: #3b82f6;
            color: #fff;
        }

        .btn-del {
            background: transparent;
            border: 1px solid #ef4444;
            color: #ef4444;
            border-radius: 7px;
            padding: 4px 12px;
            font-size: 0.82rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-del:hover {
            background: #ef4444;
            color: #fff;
        }

        /* Empty state */
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
            color: var(--muted);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--border);
        }

        .empty-state p {
            font-size: 1rem;
        }

        /* Stats bar */
        .stats-bar {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.8rem 1.4rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.88rem;
            color: var(--muted);
            margin-bottom: 1.2rem;
        }

        .stats-bar strong {
            color: var(--accent);
            font-size: 1.1rem;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar-custom d-flex align-items-center justify-content-between">
        <a class="navbar-brand-text text-decoration-none" href="list.php">
            Student<span>DB</span>
        </a>
        <div class="d-flex gap-2">
            <a href="list.php" class="btn-add" style="background:#2e2e2e; color:var(--text);">
                <i class="bi bi-list-ul"></i> Students
            </a>
            <a href="create.php" class="btn-add">
                <i class="bi bi-plus-lg"></i> Add Student
            </a>
        </div>
    </nav>

    <div class="container-lg py-0">

        <!-- Page header -->
        <div class="page-header">
            <h1 class="page-title">All <span>Students</span></h1>
            <p class="text-secondary mt-1" style="font-size:0.92rem;">Manage student records below.</p>
        </div>

        <!-- Flash message -->
        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] === 'created'): ?>
                <div class="alert alert-success alert-custom alert-dismissible fade show mb-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Student added successfully!
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['msg'] === 'updated'): ?>
                <div class="alert alert-info alert-custom alert-dismissible fade show mb-3" role="alert">
                    <i class="bi bi-pencil-fill me-2"></i> Student updated successfully!
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['msg'] === 'deleted'): ?>
                <div class="alert alert-danger alert-custom alert-dismissible fade show mb-3" role="alert">
                    <i class="bi bi-trash-fill me-2"></i> Student deleted.
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-bar">
            <i class="bi bi-people-fill" style="color:var(--accent)"></i>
            Total Students: <strong><?= count($students) ?></strong>
        </div>

        <!-- Table -->
        <div class="table-card">
            <?php if (empty($students)): ?>
                <div class="empty-state">
                    <i class="bi bi-person-x-fill d-block"></i>
                    <p>No students found. <a href="create.php" style="color:var(--accent)">Add one now.</a></p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Pofile</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Roll No.</th>
                                <th>Class</th>
                                <th>Teacher</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $i => $s): ?>
                                <tr>
                                    <td style="color:var(--muted); font-size:0.8rem;"><?= $i + 1 ?></td>
                                    <td>
                                        <?php if (!empty($s['profile_image'])): ?>
                                            <img src="uploads/<?= htmlspecialchars($s['profile_image']) ?>"
                                                alt="photo"
                                                style="width:38px; height:38px; object-fit:cover; border-radius:8px; border:1px solid var(--border);">
                                        <?php else: ?>
                                            <div style="width:38px; height:38px; border-radius:8px; background:var(--surface);
                    border:1px solid var(--border); display:flex; align-items:center;
                    justify-content:center; color:var(--muted);">
                                                <i class="bi bi-person-fill" style="font-size:1rem;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($s['name']) ?></strong>
                                    </td>
                                    <td style="color:var(--muted); font-size:0.88rem;">
                                        <?= htmlspecialchars($s['email']) ?>
                                    </td>
                                    <td>
                                        <span class="roll-pill"><?= htmlspecialchars($s['roll_number']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge-class"><?= htmlspecialchars($s['class']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($s['teacher']) ?></td>
                                    <td><?= date('d M Y, h:i A', strtotime($s['created_at'])) ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="edit.php?id=<?= $s['id'] ?>" class="btn-edit">
                                                <i class="bi bi-pencil-fill"></i> Edit
                                            </a>
                                            <a href="list.php?delete=<?= $s['id'] ?>" class="btn-del"
                                                onclick="return confirm('Delete this student?')">
                                                <i class="bi bi-trash3-fill"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <p class="mt-3" style="color:var(--muted); font-size:0.8rem; text-align:center;">
            StudentDB &copy; <?= date('Y') ?>
        </p>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(".btn-close").on("click", function() {
            $(this).closest(".alert").fadeOut(300, function() {
                $(this).remove();
                setTimeout(() => {
                    window.location.href = "list.php";
                }, 500);
            });
        });
    </script>
</body>

</html>
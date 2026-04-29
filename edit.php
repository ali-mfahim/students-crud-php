<?php
require 'db.php';

// Get student by ID
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!$id) {
  header("Location: list.php");
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();
if (!$student) {
  header("Location: list.php");
  exit;
}

$errors = [];
$data = $student;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data['name']        = trim($_POST['name'] ?? '');
  $data['email']       = trim($_POST['email'] ?? '');
  $data['roll_number'] = trim($_POST['roll_number'] ?? '');
  $data['class']       = trim($_POST['class'] ?? '');
  $data['teacher']     = trim($_POST['teacher'] ?? '');

  // Validation
  if (empty($data['name'])) {
    $errors['name'] = 'Name is required.';
  }

  // email validation
  if (empty($data['email'])) {
    $errors['email'] = 'Email is required.';
  } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email format.';
  }


  // roll numeber validation
  if (empty($data['roll_number'])) {
    $errors['roll_number'] = 'Roll number is required.';
  }


  //  class validation
  if (empty($data['class'])) {
    $errors['class'] = 'Class is required.';
  }


  // teacher validation
  if (empty($data['teacher'])) {
    $errors['teacher']     = 'Teacher name is required.';
  }


  // file upload validation
  if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
    $allowed  = ['jpg', 'jpeg', 'png', 'webp'];
    $filename = $_FILES['profile_image']['name'];
    $tmp      = $_FILES['profile_image']['tmp_name'];
    $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $size     = $_FILES['profile_image']['size'];

    if (!in_array($ext, $allowed)) {
      $errors['profile_image'] = 'Only JPG, JPEG, PNG, WEBP allowed.';
    } elseif ($size > 2 * 1024 * 1024) {
      $errors['profile_image'] = 'File size must be under 2MB.';
    } else {
      // Delete old image if exists
      if (!empty($student['profile_image']) && file_exists('uploads/' . $student['profile_image'])) {
        unlink('uploads/' . $student['profile_image']);
      }
      $newFilename = uniqid('student_', true) . '.' . $ext;
      move_uploaded_file($tmp, 'uploads/' . $newFilename);
      $data['profile_image'] = $newFilename;
    }
  }




  // Duplicate checks (exclude current record)
  if (empty($errors['email'])) {
    $chk = $pdo->prepare("SELECT id FROM students WHERE email = ? AND id != ?");
    $chk->execute([$data['email'], $id]);
    if ($chk->fetch()) $errors['email'] = 'This email already exists.';
  }
  if (empty($errors['roll_number'])) {
    $chk = $pdo->prepare("SELECT id FROM students WHERE roll_number = ? AND id != ?");
    $chk->execute([$data['roll_number'], $id]);
    if ($chk->fetch()) $errors['roll_number'] = 'This roll number already exists.';
  }

  if (empty($errors)) {
    $upd = $pdo->prepare("UPDATE students SET name=?, email=?, roll_number=?, class=?, teacher=?, profile_image=? , updated_at=? WHERE id=?");
    $upd->execute([$data['name'], $data['email'], $data['roll_number'], $data['class'], $data['teacher'], $data['profile_image'], date('Y-m-d H:i:s'), $id]);
    header("Location: list.php?msg=updated");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Student — <?= htmlspecialchars($student['name']) ?></title>
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

    * {
      box-sizing: border-box;
    }

    body {
      background: var(--dark);
      color: var(--text);
      font-family: 'DM Sans', sans-serif;
      min-height: 100vh;
    }

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

    .btn-nav {
      background: #2e2e2e;
      color: var(--text);
      font-size: 0.88rem;
      border: none;
      border-radius: 8px;
      padding: 0.45rem 1rem;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: background 0.15s;
    }

    .btn-nav:hover {
      background: #3a3a3a;
      color: var(--text);
    }

    .form-wrapper {
      max-width: 660px;
      margin: 3rem auto;
      padding: 0 1rem;
    }

    .form-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 2.2rem 2.5rem;
    }

    .form-title {
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      font-size: 1.7rem;
      margin-bottom: 0.3rem;
    }

    .form-title span {
      color: var(--accent);
    }

    .form-subtitle {
      color: var(--muted);
      font-size: 0.88rem;
      margin-bottom: 2rem;
    }

    .id-badge {
      background: rgba(249, 115, 22, 0.12);
      color: var(--accent);
      font-family: monospace;
      font-size: 0.82rem;
      padding: 3px 10px;
      border-radius: 6px;
      border: 1px solid rgba(249, 115, 22, 0.25);
    }

    .form-label {
      font-size: 0.82rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: var(--muted);
      margin-bottom: 6px;
    }

    .form-control {
      background: var(--surface) !important;
      border: 1px solid var(--border) !important;
      color: var(--text) !important;
      border-radius: 9px;
      padding: 0.6rem 0.9rem;
      font-size: 0.93rem;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-control::placeholder {
      color: var(--muted);
    }

    .form-control:focus {
      border-color: var(--accent) !important;
      box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15) !important;
      outline: none;
    }

    .form-control.is-invalid {
      border-color: #ef4444 !important;
    }

    .invalid-feedback {
      font-size: 0.8rem;
      color: #ef4444;
    }

    .input-group-text {
      background: var(--surface) !important;
      border: 1px solid var(--border) !important;
      border-right: none !important;
      color: var(--muted) !important;
      border-radius: 9px 0 0 9px !important;
    }

    .input-group .form-control {
      border-left: none !important;
      border-radius: 0 9px 9px 0 !important;
    }

    .input-group .form-control:focus {
      border-left: 1px solid var(--accent) !important;
    }

    .divider {
      border: none;
      border-top: 1px solid var(--border);
      margin: 1.8rem 0;
    }

    .section-label {
      font-family: 'Syne', sans-serif;
      font-weight: 700;
      font-size: 0.78rem;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: var(--accent);
      margin-bottom: 1rem;
    }

    .btn-submit {
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: 9px;
      padding: 0.65rem 1.8rem;
      font-family: 'Syne', sans-serif;
      font-weight: 700;
      font-size: 0.95rem;
      cursor: pointer;
      transition: background 0.2s, transform 0.15s;
      display: inline-flex;
      align-items: center;
      gap: 7px;
    }

    .btn-submit:hover {
      background: var(--accent-hover);
      transform: translateY(-1px);
    }

    .btn-cancel {
      background: transparent;
      color: var(--muted);
      border: 1px solid var(--border);
      border-radius: 9px;
      padding: 0.65rem 1.4rem;
      font-size: 0.9rem;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 7px;
      transition: all 0.15s;
    }

    .btn-cancel:hover {
      border-color: var(--muted);
      color: var(--text);
    }

    /* Changed indicator */
    .form-control:not(:placeholder-shown) {
      border-color: var(--border);
    }
  </style>
</head>

<body>

  <!-- Navbar -->
  <nav class="navbar-custom d-flex align-items-center justify-content-between">
    <a class="navbar-brand-text text-decoration-none" href="list.php">
      Student<span>DB</span>
    </a>
    <a href="list.php" class="btn-nav">
      <i class="bi bi-arrow-left"></i> Back to List
    </a>
  </nav>

  <!-- Form -->
  <div class="form-wrapper">
    <div class="form-card">
      <div class="d-flex align-items-start justify-content-between mb-1">
        <h2 class="form-title">Edit <span>Student</span></h2>
        <span class="id-badge">ID: #<?= $id ?></span>
      </div>
      <p class="form-subtitle">Update the information for <strong style="color:var(--text)"><?= htmlspecialchars($student['name']) ?></strong>.</p>

      <form method="POST" action="edit.php?id=<?= $id ?>" enctype="multipart/form-data" novalidate>

        <!-- Personal Info -->
        <div class="section-label">Personal Info</div>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Full Name</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
              <input type="text" name="name"
                class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                value="<?= htmlspecialchars($data['name']) ?>" placeholder="Full name">
            </div>
            <?php if (isset($errors['name'])): ?>
              <div class="invalid-feedback d-block mt-1"><?= $errors['name'] ?></div>
            <?php endif; ?>
          </div>

          <div class="col-12">
            <label class="form-label">Email Address</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
              <input type="email" name="email"
                class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                value="<?= htmlspecialchars($data['email']) ?>" placeholder="Email">
            </div>
            <?php if (isset($errors['email'])): ?>
              <div class="invalid-feedback d-block mt-1"><?= $errors['email'] ?></div>
            <?php endif; ?>
          </div>


          <div class="col-12">
            <label class="form-label">Profile Image</label>

            <!-- Show current image if exists -->
            <?php if (!empty($data['profile_image'])): ?>
              <div class="mb-2">
                <img src="uploads/<?= htmlspecialchars($data['profile_image']) ?>"
                  alt="Current Photo"
                  style="width:70px; height:70px; object-fit:cover; border-radius:10px; border:2px solid var(--border);">
                <span style="font-size:0.8rem; color:var(--muted); margin-left:8px;">Current photo</span>
              </div>
            <?php endif; ?>

            <input type="file" name="profile_image"
              class="form-control <?= isset($errors['profile_image']) ? 'is-invalid' : '' ?>"
              accept=".jpg,.jpeg,.png,.webp">
            <div class="form-text" style="color:var(--muted); font-size:0.78rem;">
              Leave empty to keep current image. JPG, JPEG, PNG, WEBP — max 2MB
            </div>
            <?php if (isset($errors['profile_image'])): ?>
              <div class="invalid-feedback d-block mt-1"><?= $errors['profile_image'] ?></div>
            <?php endif; ?>
          </div>
        </div>

        <hr class="divider">

        <!-- Academic Info -->
        <div class="section-label">Academic Info</div>
        <div class="row g-3">
          <div class="col-sm-6">
            <label class="form-label">Roll Number</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-hash"></i></span>
              <input type="text" name="roll_number"
                class="form-control <?= isset($errors['roll_number']) ? 'is-invalid' : '' ?>"
                value="<?= htmlspecialchars($data['roll_number']) ?>" placeholder="Roll number">
            </div>
            <?php if (isset($errors['roll_number'])): ?>
              <div class="invalid-feedback d-block mt-1"><?= $errors['roll_number'] ?></div>
            <?php endif; ?>
          </div>

          <div class="col-sm-6">
            <label class="form-label">Class</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-mortarboard-fill"></i></span>
              <input type="text" name="class"
                class="form-control <?= isset($errors['class']) ? 'is-invalid' : '' ?>"
                value="<?= htmlspecialchars($data['class']) ?>" placeholder="Class">
            </div>
            <?php if (isset($errors['class'])): ?>
              <div class="invalid-feedback d-block mt-1"><?= $errors['class'] ?></div>
            <?php endif; ?>
          </div>

          <div class="col-12">
            <label class="form-label">Teacher / Instructor</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person-badge-fill"></i></span>
              <input type="text" name="teacher"
                class="form-control <?= isset($errors['teacher']) ? 'is-invalid' : '' ?>"
                value="<?= htmlspecialchars($data['teacher']) ?>" placeholder="Teacher name">
            </div>
            <?php if (isset($errors['teacher'])): ?>
              <div class="invalid-feedback d-block mt-1"><?= $errors['teacher'] ?></div>
            <?php endif; ?>
          </div>
        </div>

        <hr class="divider">

        <div class="d-flex gap-3 align-items-center">
          <button type="submit" class="btn-submit">
            <i class="bi bi-floppy-fill"></i> Save Changes
          </button>
          <a href="list.php" class="btn-cancel">
            <i class="bi bi-x-lg"></i> Cancel
          </a>
        </div>

      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
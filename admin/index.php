<?php
session_start();
require '../config.php';

// Initialize an empty array in session for store error
if (!isset($_SESSION['errors'])) {
    $_SESSION['errors'] = [];
}


// Generate CSRF Token
if (empty($_SESSION['__csrf'])) {
    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issSubmitted'])) {
    if (!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        $_SESSION['errors'][] = 'CSRF Token Error';
        header('Location: ' . basename(__FILE__));
        exit;
    }
    


    $userName = filter_var(trim($_POST['username']), FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_var(trim($_POST['password']));

    // Validations
    if (empty($userName) || empty($password)) {
        $_SESSION['errors'][] = 'All Fields are required';
        header('Location: ' . basename(__FILE__));
        exit;
    }

    try {

        $stmt = $conn->prepare('SELECT * FROM admin_tbl WHERE user_name = :uname');
        $stmt->bindParam(':uname', $userName);
        $stmt->execute();
        $admin = $stmt->fetch();

        if (!$admin) {
            $_SESSION['errors'][] = 'Invalid User name or password';
            header('Location: ' . basename(__FILE__));
            exit;
        }


        if (!password_verify($password, $admin['user_password'])) {
            $_SESSION['errors'][] = 'Invalid User name or password';
            header('Location: ' . basename(__FILE__));
            exit;
        }


      
        // Store Admin Data into session variable
        $_SESSION['LoggedIn']      = true;
        $_SESSION['AdminId']       = $admin['id'];
        $_SESSION['adminName']     = $admin['user_name'];

        // Redirected to quiz page
        header('Location: quiz/index.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['errors'][] = 'Admin Login Error ' . $e->getMessage();
        header('Location: ' . basename(__FILE__));
        exit;
    }
    }

// Get errors from session and clear them
$errors = $_SESSION['errors'] ?? [];
$_SESSION['errors'] = [];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container-fluid">
        <div class="row min-vh-100 align-items-center justify-content-center">
            <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                <div class="card shadow-sm">
                    <div class="card-body p-5 py-5">
                        <div class="text-center mb-5">
                            <h3 class="fw-bold">Admin Login</h3>
                        </div>
                        <!-- Display Errors -->
                        <?php if (!empty($errors)): ?>
                        <div class="row text-center justify-content-center">
                            <div class="col-md-12 col-lg-12">
                                <?php foreach($errors as $error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?= htmlspecialchars($error) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <form method="post" action="<?= htmlspecialchars(basename(__FILE__)) ?>">
                            <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
                            <div class="mb-4">
                                <label for="inputEmailAddress" class="form-label">User Name</label>
                                <input type="text" name="username" class="form-control form-control-lg"
                                    id="inputEmailAddress" placeholder="User Name">
                            </div>

                            <div class="mb-5">
                                <label for="inputChoosePassword" class="form-label">Enter Password</label>
                                <div class="input-group input-group-lg">
                                    <input type="password" name="password" class="form-control" id="inputChoosePassword"
                                        placeholder="Enter Password">
                                    <span class="input-group-text">
                                        <i class="bi bi-eye-slash"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="issSubmitted" class="btn btn-primary btn-lg py-3">
                                    <i class="bi bi-lock-fill me-2"></i>Sign in
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
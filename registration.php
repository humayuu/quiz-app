<?php
session_start();
if(isset($_SESSION['LoggedIn']) == true){
    header('Location: quiz/select_exam.php');
    exit;
}
require 'config.php';

// Generate CSRF Token
if (empty($_SESSION['__csrf'])) {
    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
}

// Initialize errors in session for persistence across redirects
if (!isset($_SESSION['errors'])) {
    $_SESSION['errors'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $_SESSION['errors'] = []; // Clear previous errors
    
    if (!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        $_SESSION['errors'][] = 'CSRF Token Error';
        header('Location: ' . basename(__FILE__));
        exit;
    }

    $fullName = filter_var(trim($_POST['name']), FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    // Validations
    if (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $_SESSION['errors'][] = 'All fields are required';
        header('Location: ' . basename(__FILE__));
        exit;
    }
    
    if ($email === false) {
        $_SESSION['errors'][] = 'Invalid email format';
        header('Location: ' . basename(__FILE__));
        exit;
    }
    
    if (strlen($fullName) > 500) {
        $_SESSION['errors'][] = 'Full name must not exceed 500 characters';
        header('Location: ' . basename(__FILE__));
        exit;
    }
    
    if (strlen($password) < 8) {
        $_SESSION['errors'][] = 'Password must be at least 8 characters';
        header('Location: ' . basename(__FILE__));
        exit;
    }
    
    if ($password !== $confirmPassword) {
        $_SESSION['errors'][] = 'Password and confirm password must match';
        header('Location: ' . basename(__FILE__));
        exit;
    }

    $sql = $conn->prepare('SELECT COUNT(*) FROM user_tbl WHERE user_email = :email');
    $sql->bindParam(':email', $email, PDO::PARAM_STR);
    $sql->execute();
    
    if ($sql->fetchColumn() > 0) {
        $_SESSION['errors'][] = 'User already exists';
        header('Location: ' . basename(__FILE__));
        exit;
    }

    // Insert user to database
    try {
        $conn->beginTransaction();

        $hashPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare('INSERT INTO user_tbl (user_fullname, user_email, user_password) VALUES (:ufname, :uemail, :upassword)');
        $stmt->bindParam(':ufname', $fullName, PDO::PARAM_STR);
        $stmt->bindParam(':uemail', $email, PDO::PARAM_STR);
        $stmt->bindParam(':upassword', $hashPassword, PDO::PARAM_STR);
        $result = $stmt->execute();

        if ($result) {
            $conn->commit();
            
            // Clear any errors and redirect to login page
            $_SESSION['errors'] = [];
            $_SESSION['success'] = 'Registration successful! Please login.';
            header('Location: index.php');
            exit;
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['errors'][] = 'Registration error: ' . $e->getMessage();
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <div class="container mt-5">
        <!-- Display Errors -->
        <?php if (!empty($errors)): ?>
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4">Quiz App</h2>
                        <h5 class="text-center mb-4">Create an Account</h5>

                        <form method="POST" action="<?= htmlspecialchars(basename(__FILE__)) ?>">
                            <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">

                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small class="form-text text-muted">Minimum 8 characters</small>
                            </div>

                            <div class="mb-4">
                                <label for="confirmPassword" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                                    required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="submit" class="btn btn-primary btn-lg">Register</button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <small>Already have an account? <a href="index.php">Login here</a></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
</body>

</html>
<?php $conn = null; ?>
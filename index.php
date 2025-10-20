<?php 
session_start();
if(isset($_SESSION['success'])){
    $success = $_SESSION['success'];
    unset($_SESSION['success']); 
}

// Generate CSRF Token
if(empty($_SESSION['__csrf'])){
    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
}


// Initialize errors in session for persistence across redirects
if(!isset($_SESSION['errors'])){
    $_SESSION['errors'] = [];
}



if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])){
    if(!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])){
        $_SESSION['errors'][] = 'CSRF token error';
        header('Location: ' . basename(__FILE__));
        exit;
    }

}







?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <div class="container mt-5">
        <!-- Display Errors -->
        <?php if (!empty($success)): ?>
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4">Quiz App</h2>
                        <h5 class="text-center mb-4">Login Your Account</h5>

                        <form method="POST" action="<?= htmlspecialchars(basename(__FILE__))  ?>">
                            <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <input type="email" class="form-control" id="email" name="email">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password">
                                </div>
                            </div>

                            <div class="mb-3 text-end">
                                <a href="#" class="text-decoration-none small">Forgot Password?</a>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="submit" class="btn btn-primary btn-lg">Login</button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <small>Don't have an account? <a href="registration.php">Register here</a></small>
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
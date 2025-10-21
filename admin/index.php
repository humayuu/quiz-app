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

                        <form>
                            <div class="mb-4">
                                <label for="inputEmailAddress" class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control form-control-lg"
                                    id="inputEmailAddress" placeholder="Email Address">
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
                                <button type="submit" class="btn btn-primary btn-lg py-3">
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
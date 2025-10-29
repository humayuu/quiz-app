<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body class="d-flex flex-column min-vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand text-light fw-bold" href="#">Quiz App</a>
            <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link fs-6 text-light" aria-current="page" href=".././quiz/select_exam.php">Select
                            Exam</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fs-6 text-light" href=".././quiz/user_result.php">Last Results</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-light d-flex align-items-center" href="#" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <span
                                class="bg-light text-primary rounded-circle d-inline-flex align-items-center justify-content-center me-2"
                                style="width: 35px; height: 35px; font-weight: bold;">U</span>
                            User
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Main Content -->
    <div class="container my-4 flex-grow-1">
        <div class="row g-3 g-md-4">
            <!-- Countdown Heading (Top Left) -->
            <!-- <div class="col-12">
                <h3 class="text-primary mb-3">Countdown Timer</h3>
            </div> -->
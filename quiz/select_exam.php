<?php
session_start();
if(!isset($_SESSION['LoggedIn']) || $_SESSION['LoggedIn'] !== true){
    header('Location: ../index.php');
    exit;
}
// Connection to database
require '../config.php';

// Initialize errors in session for persistence across redirects
if (!isset($_SESSION['errors'])) {
    $_SESSION['errors'] = [];
}

try{
    $stmt = $conn->prepare('SELECT * FROM exam_category_tbl ORDER BY id DESC');
    $stmt->execute();
    $exams = $stmt->fetchAll();

}catch(Exception $e){
    $_SESSION['errors'][] = 'Category Fetch Error ' . $e->getMessage();
}

// Get errors from session and clear them
$errors = $_SESSION['errors'] ?? [];
$_SESSION['errors'] = [];

require '.././layout/header.php';
?>
<div class="container mt-5">
    <!-- Display Errors -->
    <?php if (!empty($errors)): ?>
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <?php foreach($errors as $error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php foreach($exams as $exam): ?>
    <a href="quiz.php?id=<?= htmlspecialchars($exam['id'])?>"
        class="btn btn-lg fs-5 btn-primary m-5"><?= htmlspecialchars($exam['exam_category']) ?></a>
    <?php endforeach; ?>
</div>

<?php require '.././layout/footer.php'; ?>
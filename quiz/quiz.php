<?php
session_start();
// Connection to database
require '../config.php';

// Initialize errors in session for persistence across redirects
if (!isset($_SESSION['errors'])) {
    $_SESSION['errors'] = [];
}

// Generate CSRF Token
if (empty($_SESSION['__csrf'])) {
    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
}

try{
    $id = filter_var(trim($_GET['id']), FILTER_VALIDATE_INT);
    
    if ($id === false) {
        throw new Exception('Invalid category ID');
    }
    
    $stmt = $conn->prepare('SELECT * FROM questions_tbl WHERE category_id = :id ORDER BY id DESC');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

}catch(Exception $e){
    $_SESSION['errors'][] = 'Category Fetch Error: ' . $e->getMessage();
    $exams = [];
}

// Get errors from session and clear them
$errors = $_SESSION['errors'] ?? [];
$_SESSION['errors'] = [];

require '.././layout/header.php';
?>

<div class="container mt-5">
    <!-- Display Errors -->
    <?php if (!empty($errors)): ?>
    <div class="row justify-content-center mb-4">
        <div class="col-md-10 col-lg-8">
            <?php foreach($errors as $error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Quiz Questions</h4>
                </div>
                <div class="card-body">
                    <form method="post" action="result.php">
                        <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf'])?>">
                        <input type="hidden" name="category_id" value="<?= htmlspecialchars($id) ?>">
                        <?php if ($exams && count($exams) > 0): ?>
                        <?php $sl = 1; foreach($exams as $exam): ?>
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title text-primary mb-3">
                                    <span class="badge bg-primary me-2">Q<?= $sl++ ?></span>
                                    <?= htmlspecialchars($exam['question']) ?>
                                </h5>

                                <div class="list-group">
                                    <div class="list-group-item list-group-item-action">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="question_<?= $exam['id'] ?>"
                                                value="<?= htmlspecialchars($exam['opt_1']) ?>"
                                                id="q<?= $exam['id'] ?>_opt1" required>
                                            <label class="form-check-label w-100" for="q<?= $exam['id'] ?>_opt1">
                                                <strong>A.</strong> <?= htmlspecialchars($exam['opt_1']) ?>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="list-group-item list-group-item-action">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="question_<?= $exam['id'] ?>"
                                                value="<?= htmlspecialchars($exam['opt_2']) ?>"
                                                id="q<?= $exam['id'] ?>_opt2" required>
                                            <label class="form-check-label w-100" for="q<?= $exam['id'] ?>_opt2">
                                                <strong>B.</strong> <?= htmlspecialchars($exam['opt_2']) ?>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="list-group-item list-group-item-action">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="question_<?= $exam['id'] ?>"
                                                value="<?= htmlspecialchars($exam['opt_3']) ?>"
                                                id="q<?= $exam['id'] ?>_opt3" required>
                                            <label class="form-check-label w-100" for="q<?= $exam['id'] ?>_opt3">
                                                <strong>C.</strong> <?= htmlspecialchars($exam['opt_3']) ?>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="list-group-item list-group-item-action">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                name="question_<?= $exam['id'] ?>"
                                                value="<?= htmlspecialchars($exam['opt_4']) ?>"
                                                id="q<?= $exam['id'] ?>_opt4" required>
                                            <label class="form-check-label w-100" for="q<?= $exam['id'] ?>_opt4">
                                                <strong>D.</strong> <?= htmlspecialchars($exam['opt_4']) ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" name="issSubmitted" class="btn btn-success btn-lg">Submit
                                Quiz</button>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="alert alert-info text-center py-5">
                        <i class="bi bi-info-circle fs-1 d-block mb-3"></i>
                        <h5>No Quiz Found!</h5>
                        <p class="mb-0">There are no questions available for this category.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require '.././layout/footer.php'; ?>
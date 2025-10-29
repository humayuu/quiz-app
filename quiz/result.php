<?php 
session_start();
if(!isset($_SESSION['LoggedIn']) || $_SESSION['LoggedIn'] !== true){
    header('Location: ../index.php');
    exit;
}
// Connection to database
require '../config.php';

// Initialize errors in session
if (!isset($_SESSION['errors'])) {
    $_SESSION['errors'] = [];
}

$score = 0;
$totalQuestions = 0;
$categoryId = null;

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issSubmitted'])){
    
    // Verify CSRF Token
    if (!isset($_POST['__csrf']) || !isset($_SESSION['__csrf']) || !hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        $_SESSION['errors'][] = 'CSRF token error';
        header('Location: index.php');
        exit;
    }
    
    // Get and validate category ID
    $categoryId = filter_var($_POST['category_id'] ?? 0, FILTER_VALIDATE_INT);
    
    if ($categoryId === false || $categoryId === 0) {
        $_SESSION['errors'][] = 'Invalid category ID';
        header('Location: index.php');
        exit;
    }
    
    try {
        // Fetch ALL questions for this category
        $stmt = $conn->prepare('SELECT id, question, answer FROM questions_tbl WHERE category_id = :catId ORDER BY id ASC');
        $stmt->bindParam(':catId', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
        $questions = $stmt->fetchAll();
        
        $totalQuestions = count($questions);
        
        if ($totalQuestions === 0) {
            $_SESSION['errors'][] = 'No questions found';
            header('Location: index.php');
            exit;
        }
        
        // Loop through EACH question and check the answer
        foreach ($questions as $question) {
            $questionId = $question['id'];
            $correctAnswer = trim($question['answer']);
            
            // Get user's answer for THIS specific question
            $userAnswer = isset($_POST['question_' . $questionId]) ? trim($_POST['question_' . $questionId]) : null;
            
            // Check if answer is correct
            if ($userAnswer !== null && $userAnswer === $correctAnswer) {
                $score++; // Increment score for each correct answer
            }
        }

        // Insert Result data into database
        $sql = $conn->prepare('INSERT INTO result_tbl (user_id, category_id, user_result) VALUES (:userid, :categoryId, :userResult)');
        $sql->bindParam(':userid', $_SESSION['userId']);
        $sql->bindParam(':categoryId', $categoryId);
        $sql->bindParam(':userResult',  $score);
        $sql->execute();

        
    } catch(Exception $e) {
        $_SESSION['errors'][] = 'Error: ' . $e->getMessage();
        header('Location: index.php');
        exit;
    }
    
}


// Get errors from session and clear them
$errors = $_SESSION['errors'] ?? [];
$_SESSION['errors'] = [];

require '../layout/header.php';
?>

<div class="container mt-5">
    <!-- Display Errors -->
    <?php if (!empty($errors)): ?>
    <div class="row justify-content-center mb-4">
        <div class="col-md-10 col-lg-8">
            <?php foreach($errors as $error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Results Display -->
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Quiz Results</h4>
                </div>
                <div class="card-body text-center py-5">
                    <h1 class="display-1 text-primary"><?= $score ?>/<?= $totalQuestions ?></h1>
                    <h3 class="mb-4">Score: <?= $totalQuestions > 0 ? round(($score / $totalQuestions) * 100, 2) : 0 ?>%
                    </h3>

                    <?php 
                    $percentage = $totalQuestions > 0 ? ($score / $totalQuestions) * 100 : 0;
                    if ($percentage >= 80): 
                    ?>
                    <div class="alert alert-success">
                        <h4>🏆 Excellent Work!</h4>
                    </div>
                    <?php elseif ($percentage >= 60): ?>
                    <div class="alert alert-info">
                        <h4>👍 Good Job!</h4>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <h4>📚 Keep Practicing!</h4>
                    </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <a href="quiz.php?id=<?= htmlspecialchars($categoryId) ?>" class="btn btn-primary me-2">Try
                            Again</a>
                        <a href="select_exam.php" class="btn btn-secondary">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require '../layout/footer.php'; ?>
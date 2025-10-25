<?php
session_start();
// Connection to database
require '../../config.php';


// Function fo Check if the image is Exists or not
function isImage($value)
{
    if (empty($value)) {
        return false;
    }

    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = strtolower(pathinfo($value, PATHINFO_EXTENSION));

    return in_array($extension, $imageExtensions);
}

$sl = 1;


// Generate CSRF Token
if (empty($_SESSION['__csrf'])) {
    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
}

// Initialize errors in session for persistence across redirects
if (!isset($_SESSION['errors']) && !isset($_SESSION['success'])) {
    $_SESSION['errors'] = [];
    $_SESSION['success'] = [];
}


// First Fetch data For and Update 
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editBtn'])){
        // Verify CSRF Token
    if (!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        $_SESSION['errors'][] = 'Invalid CSRF Token';
        header('Location: exam_questions.php');
        exit;
    }

    $categoryId = filter_var(trim($_POST['category_id']), FILTER_VALIDATE_INT);

    // Validate category ID from POST
    if ($categoryId === false || $categoryId === null || $categoryId <= 0) {
        $_SESSION['errors'][] = 'Invalid Category ID';
        header('Location: exam_questions.php');
        exit;
    }

    $id = filter_var(trim($_POST['id']), FILTER_VALIDATE_INT);
    
    try{

        $sql = $conn->prepare('SELECT * FROM questions_tbl WHERE id = :id');
        $sql->bindParam(':id', $id);
        $sql->execute();
        $question = $sql->fetch();

    }catch(Exception $e){
        $_SESSION['errors'][] = 'Question fetch error ' . $e->getMessage();
        header('Location: add_edit_questions.php?id=' . urlencode($categoryId));
        exit;
    }
    
}



// Update Data

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issSubmitted'])){
    // Verify CSRF Token 
       if (!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        $_SESSION['errors'][] = 'Invalid CSRF Token';
        header('Location: exam_questions.php');
        exit;
    }
}






















require 'layout/header.php';
?>

<!--page-wrapper-->
<div class="page-wrapper">
    <!--page-content-wrapper-->
    <div class="page-content-wrapper">
        <div class="page-content">
            <div class="row">
                <div class="col-12">
                    <h4 class="mb-4">Edit Exam Questions</h4>
                </div>
            </div>

            <div class="row">
                <!-- Display Errors -->
                <?php if (!empty($errors)): ?>
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <?php foreach ($errors as $error): ?>
                        <div class="alert alert-danger fade show" role="alert">
                            <?= htmlspecialchars($error) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-primary mb-3">
                                <i class="bx bx-plus-circle me-2"></i>Edit Exam Questions
                            </h5>
                            <hr>
                            <form method="post" action="<?= htmlspecialchars(basename(__FILE__))  ?>"
                                enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($question['id']) ?>">
                                <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
                                <input type="hidden" name="category_id" value="<?= htmlspecialchars($categoryId) ?>">

                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Questions</label>
                                    <input type="text" class="form-control" name="question"
                                        placeholder="Enter Questions"
                                        value="<?= htmlspecialchars($question['question']) ?>">
                                </div>
                                <?php if(isImage($question['opt_1'])): ?>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt1</label>
                                    <input type="file" class="form-control" name="opt1">
                                    <img class="img-thumbnail" src="<?= htmlspecialchars($question['opt_1']) ?>"
                                        alt="Option 2" style="max-width: 100px; height: auto;">
                                </div>
                                <?php else: ?>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt1</label>
                                    <input type="text" class="form-control" name="opt1" placeholder="Enter Opt1"
                                        value="<?= htmlspecialchars($question['opt_1']) ?>">
                                </div>
                                <?php endif; ?>

                                <?php if(isImage($question['opt_2'])): ?>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt2</label>
                                    <input type="file" class="form-control" name="opt2">
                                    <img class="img-thumbnail" src="<?= htmlspecialchars($question['opt_2']) ?>"
                                        alt="Option 2" style="max-width: 100px; height: auto;">
                                </div>
                                <?php else: ?>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt2</label>
                                    <input type="text" class="form-control" name="opt2" placeholder="Enter Opt2"
                                        value="<?= htmlspecialchars($question['opt_2']) ?>">
                                </div>
                                <?php endif; ?>

                                <?php if(isImage($question['opt_3'])): ?>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt3</label>
                                    <input type="file" class="form-control" name="opt3">
                                    <img class="img-thumbnail" src="<?= htmlspecialchars($question['opt_3']) ?>"
                                        alt="Option 2" style="max-width: 100px; height: auto;">
                                </div>
                                <?php else: ?>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt3</label>
                                    <input type="text" class="form-control" name="opt3" placeholder="Enter Opt3"
                                        value="<?= htmlspecialchars($question['opt_3']) ?>">
                                </div>
                                <?php endif; ?>

                                <?php if(isImage($question['opt_4'])): ?>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt4</label>
                                    <input type="file" class="form-control" name="opt4">
                                    <img class="img-thumbnail" src="<?= htmlspecialchars($question['opt_4']) ?>"
                                        alt="Option 2" style="max-width: 100px; height: auto;">
                                </div>
                                <?php else: ?>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt4</label>
                                    <input type="text" class="form-control" name="opt4" placeholder="Enter Opt4"
                                        value="<?= htmlspecialchars($question['opt_4']) ?>">
                                </div>
                                <?php endif; ?>
                                <?php if(isImage($question['answer'])): ?>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Answer</label>
                                    <input type="file" class="form-control" name="answer">
                                    <img class="img-thumbnail" src="<?= htmlspecialchars($question['answer']) ?>"
                                        alt="Option 2" style="max-width: 100px; height: auto;">
                                </div>
                                <?php else: ?>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Answer</label>
                                    <input type="text" class="form-control" name="answer" placeholder="Enter answer"
                                        value="<?= htmlspecialchars($question['answer']) ?>">
                                </div>
                                <?php endif; ?>
                                <div class="d-flex gap-2">
                                    <button type="submit" name="issSubmitted" class="btn btn-secondary">Update
                                        Questions</button>
                                    <a href="add_edit_questions.php?id=<?= htmlspecialchars($categoryId) ?>"
                                        class="btn btn-info">Back</a>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end page-content-wrapper-->
</div>
<!--end page-wrapper-->

<?php require 'layout/footer.php'; ?>
<?php $conn = null; ?>
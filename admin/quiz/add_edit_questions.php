<?php
session_start();
// Connection to database
require '../../config.php';

// Generate CSRF Token
if (empty($_SESSION['__csrf'])) {
    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
}

// Initialize errors in session for persistence across redirects
if (!isset($_SESSION['errors'])) {
    $_SESSION['errors'] = [];
}

// Add Question with text
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['isSubmitted'])) {
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

    $question = filter_var(trim($_POST['question']), FILTER_SANITIZE_SPECIAL_CHARS);
    $option1 = filter_var(trim($_POST['opt1']), FILTER_SANITIZE_SPECIAL_CHARS);
    $option2 = filter_var(trim($_POST['opt2']), FILTER_SANITIZE_SPECIAL_CHARS);
    $option3 = filter_var(trim($_POST['opt3']), FILTER_SANITIZE_SPECIAL_CHARS);
    $option4 = filter_var(trim($_POST['opt4']), FILTER_SANITIZE_SPECIAL_CHARS);
    $answer = filter_var(trim($_POST['answer']), FILTER_SANITIZE_SPECIAL_CHARS);

    // Validation
    if (empty($question) || empty($option1) || empty($option2) || empty($option3) || empty($option4) || empty($answer)) {
        $_SESSION['errors'][] = 'All Fields are required';
        header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
        exit;
    }

    // Insert data into the database
    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare('INSERT INTO questions_tbl (question, opt_1, opt_2, opt_3, opt_4, answer, category_id) VALUES (:question, :opt1, :opt2, :opt3, :opt4, :answer, :catid)');
        $stmt->bindParam(':question', $question);
        $stmt->bindParam(':opt1', $option1);
        $stmt->bindParam(':opt2', $option2);
        $stmt->bindParam(':opt3', $option3);
        $stmt->bindParam(':opt4', $option4);
        $stmt->bindParam(':answer', $answer);
        $stmt->bindParam(':catid', $categoryId, PDO::PARAM_INT);
        $result = $stmt->execute();

        if ($result) {
            $conn->commit();
            $_SESSION['success'] = 'Question added successfully!';
            header('Location: add_edit_questions.php?id=' . $categoryId);
            exit;
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['errors'][] = 'Database insert error ' . $e->getMessage();
        header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
        exit;
    }
}




// Add Question with Image
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['imageSubmit'])) {
    // Verify CSRF Token
    if (!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        $_SESSION['errors'][] = 'Invalid CSRF Token';
        header('Location: exam_questions.php');
        exit;
    }

    $categoryId = filter_var(trim($_POST['category_id']), FILTER_VALIDATE_INT);

    // Validate category ID
    if ($categoryId === false || $categoryId === null || $categoryId <= 0) {
        $_SESSION['errors'][] = 'Invalid Category ID';
        header('Location: exam_questions.php');
        exit;
    }

    $imageQuestion = filter_var(trim($_POST['image_question']), FILTER_SANITIZE_SPECIAL_CHARS);
    $allowedExtension = ['jpeg', 'jpg', 'png'];
    $maxFileSize = 2 * 1024 * 1024; //2MB
    $uploadDir = __DIR__ . '/uploads/images/';

    // Validation
    if (empty($imageQuestion)) {
        $_SESSION['errors'][] = 'Question field is required';
        header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
        exit;
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Check if all files are uploaded
    $fileFields = ['image_opt1', 'image_opt2', 'image_opt3', 'image_opt4', 'image_answer'];
    foreach ($fileFields as $field) {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['errors'][] = "Please upload all 5 images (Options 1-4 and Answer)";
            header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
            exit;
        }
    }

    // Process and upload all images
    $uploadedImages = [];
    
    foreach ($fileFields as $field) {
        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        $size = $_FILES[$field]['size'];
        $tmpName = $_FILES[$field]['tmp_name'];

        // Validate extension
        if (!in_array($ext, $allowedExtension)) {
            $_SESSION['errors'][] = "Invalid file extension for {$field}. Only JPEG, JPG, PNG allowed.";
            header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
            exit;
        }

        // Validate size
        if ($size > $maxFileSize) {
            $_SESSION['errors'][] = "File size for {$field} exceeds 2MB limit";
            header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
            exit;
        }

        // Generate unique filename
        $newName = uniqid('image_') . time() . '_' . rand(1000, 9999) . '.' . $ext;

        // Upload file
        if (!move_uploaded_file($tmpName, $uploadDir . $newName)) {
            $_SESSION['errors'][] = "File upload error for {$field}";
            header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
            exit;
        }

        $uploadedImages[$field] = 'uploads/images/' . $newName;
    }

    // Insert data into the database
    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare('INSERT INTO questions_tbl (question, opt_1, opt_2, opt_3, opt_4, answer, category_id) VALUES (:question, :opt1, :opt2, :opt3, :opt4, :answer, :catid)');
        $stmt->bindParam(':question', $imageQuestion);
        $stmt->bindParam(':opt1', $uploadedImages['image_opt1']);
        $stmt->bindParam(':opt2', $uploadedImages['image_opt2']);
        $stmt->bindParam(':opt3', $uploadedImages['image_opt3']);
        $stmt->bindParam(':opt4', $uploadedImages['image_opt4']);
        $stmt->bindParam(':answer', $uploadedImages['image_answer']);
        $stmt->bindParam(':catid', $categoryId, PDO::PARAM_INT);
        $result = $stmt->execute();

        if ($result) {
            $conn->commit();
            $_SESSION['success'] = 'Question added successfully!';
            header('Location: add_edit_questions.php?id=' . $categoryId);
            exit;
        }
    } catch (Exception $e) {
        $conn->rollBack();
        
        // Delete uploaded images if database insert fails
        foreach ($uploadedImages as $imagePath) {
            $fullPath = __DIR__ . '/' . $imagePath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
        
        $_SESSION['errors'][] = 'Database insert error: ' . $e->getMessage();
        header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
        exit;
    }
}

// Get errors from session and clear them
$errors = $_SESSION['errors'] ?? [];
$_SESSION['errors'] = [];

// Get success from session and clear them
$success = $_SESSION['success'] ?? [];
$_SESSION['success'] = [];

$id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';

// Validate that ID exists only on GET requests
if (empty($id)) {
    $_SESSION['errors'][] = 'Category ID is required';
    header('Location: exam_questions.php');
    exit;
}

require 'layout/header.php';
?>

<!--page-wrapper-->
<div class="page-wrapper">
    <!--page-content-wrapper-->
    <div class="page-content-wrapper">
        < class="page-content">
            <div class="row">
                <div class="col-12">
                    <h4 class="mb-4">Exam Questions</h4>
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
                <!-- Display Success -->
                <?php if (!empty($success)): ?>
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="alert alert-success fade show" role="alert">
                            Questions Successfully add.
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Left Half - Form -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-primary mb-3">
                                <i class="bx bx-plus-circle me-2"></i>Add Exam Questions With Text
                            </h5>
                            <hr>
                            <form method="post" action="<?= htmlspecialchars(basename(__FILE__))  ?>">
                                <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
                                <input type="hidden" name="category_id" value="<?= htmlspecialchars($id) ?>">

                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Questions</label>
                                    <input type="text" class="form-control" name="question"
                                        placeholder="Enter Questions">
                                </div>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt1</label>
                                    <input type="text" class="form-control" name="opt1" placeholder="Enter Opt1">
                                </div>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt2</label>
                                    <input type="text" class="form-control" name="opt2" placeholder="Enter Opt2">
                                </div>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt3</label>
                                    <input type="text" class="form-control" name="opt3" placeholder="Enter Opt3">
                                </div>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt4</label>
                                    <input type="text" class="form-control" name="opt4" placeholder="Enter Opt4">
                                </div>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Answer </label>
                                    <input type="text" class="form-control" name="answer" placeholder="Enter Answer">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" name="isSubmitted" class="btn btn-primary">Add
                                        Questions</button>
                                    <a href="exam_questions.php" class="btn btn-danger">Back</a>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-primary mb-3">
                                <i class="bx bx-plus-circle me-2"></i>Add Exam Questions With Image
                            </h5>
                            <hr>
                            <form method="post" action="<?= htmlspecialchars(basename(__FILE__))  ?>"
                                enctype="multipart/form-data">
                                <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
                                <input type="hidden" name="category_id" value="<?= htmlspecialchars($id) ?>">

                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Questions</label>
                                    <input type="text" class="form-control" name="image_question"
                                        placeholder="Enter Questions">
                                </div>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt1</label>
                                    <input type="file" class="form-control" name="image_opt1">
                                </div>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt2</label>
                                    <input type="file" class="form-control" name="image_opt2">
                                </div>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt3</label>
                                    <input type="file" class="form-control" name="image_opt3">
                                </div>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt4</label>
                                    <input type="file" class="form-control" name="image_opt4">
                                </div>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Answer </label>
                                    <input type="file" class="form-control" name="image_answer">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" name="imageSubmit" class="btn btn-primary">Add
                                        Questions</button>
                                    <a href="exam_questions.php" class="btn btn-danger">Back</a>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
            $sql = $conn->prepare('SELECT * FROM questions_tbl ORDER BY question DESC');
            $sql->execute();
            $rows = $sql->fetchAll();
            ?>
            <div class="row">
                <!-- Display Table-->
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-primary mb-3">
                                <i class="bx bx-plus-circle me-2"></i>Exam Questions
                            </h5>
                            <hr>
                            <?php $sl = 1; if($rows): ?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Questions</th>
                                        <th>Option 1</th>
                                        <th>Option 2</th>
                                        <th>Option 3</th>
                                        <th>Option 4</th>
                                        <th>Answer</th>
                                        <th>Actions</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($rows as $row): ?>
                                    <tr>
                                        <th scope="row"><?= htmlspecialchars($sl++) ?></th>
                                        <td><?= htmlspecialchars($row['question']) ?></td>
                                        <td><?= htmlspecialchars($row['opt_1']) ?></td>
                                        <td><?= htmlspecialchars($row['opt_2']) ?></td>
                                        <td><?= htmlspecialchars($row['opt_3']) ?></td>
                                        <td><?= htmlspecialchars($row['opt_4']) ?></td>
                                        <td><?= htmlspecialchars($row['answer']) ?></td>
                                        <td>
                                            <a href="#" class="btn btn-sm"
                                                style="background-color: #6f42c1; color: white;">Edit</a>
                                            <a href="#" onclick="return confirm('Are you sure ?')"
                                                class="btn btn-sm ms-1"
                                                style="background-color: #e83e8c; color: white;">Delete</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <div class="alert alert-danger" role="alert">
                                No Questions Found!
                            </div>
                            <?php endif; ?>
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
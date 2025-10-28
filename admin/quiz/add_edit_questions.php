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
            $_SESSION['success'][] = 'Question added successfully!';
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

    // Validate category ID from POST
    if ($categoryId === false || $categoryId === null || $categoryId <= 0) {
        $_SESSION['errors'][] = 'Invalid Category ID';
        header('Location: exam_questions.php');
        exit;
    }

    $question = filter_var(trim($_POST['image_question']), FILTER_SANITIZE_SPECIAL_CHARS);
    $allowedExtension = ['jpg', 'jpeg', 'png', 'gif'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB
    $uploadDir = __DIR__ . '/uploads/images/';

    // Validation
    if (empty($question)) {
        $_SESSION['errors'][] = 'All Fields are required';
        header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
        exit;
    }

    // Create a directory if its not exits
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileFields = ['image_opt1', 'image_opt2', 'image_opt3', 'image_opt4', 'image_answer'];

    foreach ($fileFields as $files) {
        if (!isset($_FILES[$files]) || $_FILES[$files]['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['errors'][] = "Please upload all 5 images (Options 1-4 and Answer)";
            header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
            exit;
        }
    }

    // Process and upload all images
    $uploadImages = [];

    foreach ($fileFields as $fields) {
        $ext = strtolower(pathinfo($_FILES[$fields]['name'], PATHINFO_EXTENSION));
        $size = $_FILES[$fields]['size'];
        $tmpName = $_FILES[$fields]['tmp_name'];

        // Validation Extension
        if (!in_array($ext, $allowedExtension)) {
            $_SESSION['errors'][] = "Invalid file extension for {$fields}. Only JPEG, JPG, PNG allowed.";
            header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
            exit;
        }

        // Validation Size
        if ($size > $maxFileSize) {
            $_SESSION['errors'][] = "File size for {$fields} exceeds 2MB limit";
            header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
            exit;
        }

        $newName = uniqid('image_') . time() . '_' . rand(1000, 9999) . '.' . $ext;

        if (!move_uploaded_file($tmpName, $uploadDir . $newName)) {
            $_SESSION['errors'][] = "File upload error for {$fields}";
            header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
            exit;
        }

        $uploadImages[$fields] = 'uploads/images/' . $newName;
    }

    // Insert data into the database
    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare('INSERT INTO questions_tbl (question, opt_1, opt_2, opt_3, opt_4, answer, category_id) VALUES (:question, :opt1, :opt2, :opt3, :opt4, :answer, :catid)');
        $stmt->bindParam(':question', $question);
        $stmt->bindParam(':opt1', $uploadImages['image_opt1']);
        $stmt->bindParam(':opt2', $uploadImages['image_opt2']);
        $stmt->bindParam(':opt3', $uploadImages['image_opt3']);
        $stmt->bindParam(':opt4', $uploadImages['image_opt4']);
        $stmt->bindParam(':answer', $uploadImages['image_answer']);
        $stmt->bindParam(':catid', $categoryId, PDO::PARAM_INT);
        $result = $stmt->execute();

        if ($result) {
            $conn->commit();
            $_SESSION['success'][] = 'Question added successfully!';
            header('Location: add_edit_questions.php?id=' . $categoryId);
            exit;
        }
    } catch (Exception $e) {
        $conn->rollBack();
        // Delete Uploaded images if database inserted fails
        foreach ($uploadImages as $imagePath) {
            $fullPath = __DIR__ . '/' . $imagePath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
        $_SESSION['errors'][] = 'Database insert error ' . $e->getMessage();
        header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
        exit;
    }
}


// Delete Question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    // Verify CSRF Token
    if (!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        $_SESSION['errors'][] = 'Invalid CSRF Token';
        header('Location: exam_questions.php');
        exit;
    }

    $deleteId = filter_var(trim($_POST['deleteId']), FILTER_VALIDATE_INT);
    $categoryId = filter_var(trim($_POST['category_id']), FILTER_VALIDATE_INT);

    // Fetch image if they exists
    $sql = $conn->prepare('SELECT * FROM questions_tbl WHERE id = :id');
    $sql->bindParam(':id', $deleteId);
    $sql->execute();
    $row = $sql->fetch();

    // Validate category ID from POST
    if ($categoryId === false || $categoryId === null || $categoryId <= 0) {
        $_SESSION['errors'][] = 'Invalid Category ID';
        header('Location: exam_questions.php');
        exit;
    }

    try {
        $conn->beginTransaction();
        $delete = $conn->prepare('DELETE FROM questions_tbl WHERE id = :id');
        $delete->bindParam(':id', $deleteId);
        $result = $delete->execute();

        if ($result) {
            $conn->commit();

            // Delete image files if they exist
            if (!empty($row)) {
                foreach ($row as $columnValue) {
                    if (!empty($columnValue) && isImage($columnValue) && file_exists($columnValue)) {
                        unlink($columnValue);
                    }
                }
            }

            $_SESSION['success'][] = 'Question Deleted Successfully';
            header('Location: add_edit_questions.php?id=' . urlencode($categoryId));
            exit;
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['errors'] = 'Error in delete question ' . $e->getMessage();
        header('Location: add_edit_questions.php?id=' . urlencode($categoryId));
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
$sql = $conn->prepare('SELECT * FROM questions_tbl WHERE category_id = :id  ORDER BY question DESC');
$sql->bindParam(':id', $id);
$sql->execute();
$rows = $sql->fetchAll();

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
        <div class="page-content">
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
                        <?php foreach ($success as $message): ?>
                        <div class="alert alert-success fade show" role="alert">
                            <?= htmlspecialchars($message) ?>
                        </div>
                        <?php endforeach; ?>
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

                            <?php if ($rows): ?>
                            <table class="table table-bordered align-middle table-sm">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th style="width: 40px;">#</th>
                                        <th>Question</th>
                                        <th>Option 1</th>
                                        <th>Option 2</th>
                                        <th>Option 3</th>
                                        <th>Option 4</th>
                                        <th>Answer</th>
                                        <th style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $row): ?>
                                    <tr>
                                        <th scope="row"><?= htmlspecialchars($sl++) ?></th>
                                        <td style="white-space: normal; word-wrap: break-word;">
                                            <?= htmlspecialchars($row['question']) ?>
                                        </td>

                                        <?php for ($i = 1; $i <= 4; $i++): ?>
                                        <td class="text-center" style="white-space: normal; word-wrap: break-word;">
                                            <?php $opt = $row['opt_' . $i]; ?>
                                            <?php if (!empty($opt) && isImage($opt)): ?>
                                            <img class="img-fluid img-thumbnail" src="<?= htmlspecialchars($opt) ?>"
                                                alt="Option <?= $i ?>" style="max-width: 60px; height: auto;">
                                            <?php else: ?>
                                            <?= htmlspecialchars($opt) ?>
                                            <?php endif; ?>
                                        </td>
                                        <?php endfor; ?>

                                        <td class="text-center">
                                            <?php if (!empty($row['answer']) && isImage($row['answer'])): ?>
                                            <img class="img-fluid img-thumbnail"
                                                src="<?= htmlspecialchars($row['answer']) ?>" alt="Answer"
                                                style="max-width: 60px; height: auto;">
                                            <?php else: ?>
                                            <?= htmlspecialchars($row['answer']) ?>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            <form class="d-inline" method="post" action="update_questions.php"
                                                enctype="multipart/form-data">
                                                <input type="hidden" name="__csrf"
                                                    value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
                                                <input type="hidden" name="category_id"
                                                    value="<?= htmlspecialchars($id) ?>">
                                                <input type="hidden" name="id"
                                                    value="<?= htmlspecialchars($row['id']) ?>">
                                                <button name="editBtn" class="btn btn-sm btn-primary mb-1">
                                                    Edit
                                                </button>
                                            </form>

                                            <form class="d-inline" method="post"
                                                action="<?= htmlspecialchars(basename(__FILE__)) ?>"
                                                enctype="multipart/form-data">
                                                <input type="hidden" name="__csrf"
                                                    value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
                                                <input type="hidden" name="category_id"
                                                    value="<?= htmlspecialchars($id) ?>">
                                                <input type="hidden" name="deleteId"
                                                    value="<?= htmlspecialchars($row['id']) ?>">
                                                <button name="delete" onclick="return confirm('Are you sure?')"
                                                    class="btn btn-sm btn-danger">
                                                    Delete
                                                </button>
                                            </form>
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
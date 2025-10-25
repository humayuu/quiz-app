<?php
session_start();
// Connection to database
require '../../config.php';

// Function to Check if the image is Exists or not
function isImage($value)
{
    if (empty($value)) {
        return false;
    }

    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $extension = strtolower(pathinfo($value, PATHINFO_EXTENSION));

    return in_array($extension, $imageExtensions);
}

// Generate CSRF Token
if (empty($_SESSION['__csrf'])) {
    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
}

// Initialize errors in session for persistence across redirects
if (!isset($_SESSION['errors'])) {
    $_SESSION['errors'] = [];
}
if (!isset($_SESSION['success'])) {
    $_SESSION['success'] = [];
}

// Initialize variables
$question = null;
$categoryId = null;

// First Fetch data For Update 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editBtn'])) {
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
    
    try {
        $sql = $conn->prepare('SELECT * FROM questions_tbl WHERE id = :id');
        $sql->bindParam(':id', $id, PDO::PARAM_INT);
        $sql->execute();
        $question = $sql->fetch();

        if (!$question) {
            $_SESSION['errors'][] = 'Question not found';
            header('Location: exam_questions.php');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['errors'][] = 'Question fetch error: ' . $e->getMessage();
        header('Location: exam_questions.php');
        exit;
    }
}

// Update Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issSubmitted'])) {
    // Verify CSRF Token 
    if (!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        $_SESSION['errors'][] = 'Invalid CSRF Token';
        header('Location: exam_questions.php');
        exit;
    }

    $id = filter_var(trim($_POST['id']), FILTER_VALIDATE_INT);
    $categoryId = filter_var(trim($_POST['category_id']), FILTER_VALIDATE_INT);
    $questionText = filter_var(trim($_POST['question']), FILTER_SANITIZE_SPECIAL_CHARS);

    // Validate required fields
    if (empty($questionText) || !$id || !$categoryId) {
        $_SESSION['errors'][] = 'Question text and valid IDs are required';
        header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
        exit;
    }

    // Fetch existing question data
    try {
        $stmt = $conn->prepare('SELECT * FROM questions_tbl WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $existingQuestion = $stmt->fetch();

        if (!$existingQuestion) {
            $_SESSION['errors'][] = 'Question not found';
            header('Location: exam_questions.php');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['errors'][] = 'Database error: ' . $e->getMessage();
        header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
        exit;
    }

    $allowedExtension = ['jpg', 'jpeg', 'png', 'gif'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB
    $uploadDir = __DIR__ . '/uploads/images/';

    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileFields = ['opt_1', 'opt_2', 'opt_3', 'opt_4', 'answer'];
    $updatedValues = [];
    $uploadedImages = [];

    // Process each field
    foreach ($fileFields as $field) {
        // Check if it's an image field and a new file is uploaded
        if (isImage($existingQuestion[$field]) && isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            // New image uploaded
            $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
            $size = $_FILES[$field]['size'];
            $tmpName = $_FILES[$field]['tmp_name'];

            // Validate extension
            if (!in_array($ext, $allowedExtension)) {
                $_SESSION['errors'][] = "Invalid file extension for {$field}. Only JPEG, JPG, PNG, GIF allowed.";
                header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
                exit;
            }

            // Validate size
            if ($size > $maxFileSize) {
                $_SESSION['errors'][] = "File size for {$field} exceeds 2MB limit";
                header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
                exit;
            }

            $newName = uniqid('image_') . time() . '_' . rand(1000, 9999) . '.' . $ext;

            if (!move_uploaded_file($tmpName, $uploadDir . $newName)) {
                $_SESSION['errors'][] = "File upload error for {$field}";
                header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
                exit;
            }

            $updatedValues[$field] = 'uploads/images/' . $newName;
            $uploadedImages[] = $uploadDir . $newName;
            
            // Delete old image
            $oldImagePath = __DIR__ . '/' . $existingQuestion[$field];
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        } elseif (!isImage($existingQuestion[$field]) && isset($_POST[$field])) {
            // Text field - get from POST
            $updatedValues[$field] = filter_var(trim($_POST[$field]), FILTER_SANITIZE_SPECIAL_CHARS);
            
            if (empty($updatedValues[$field])) {
                $_SESSION['errors'][] = "Field {$field} cannot be empty";
                header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
                exit;
            }
        } else {
            // Keep existing value (no new upload)
            $updatedValues[$field] = $existingQuestion[$field];
        }
    }

    // Update database
    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare('UPDATE questions_tbl 
                                SET question = :question,
                                    opt_1 = :opt_1,
                                    opt_2 = :opt_2,
                                    opt_3 = :opt_3,
                                    opt_4 = :opt_4,
                                    answer = :answer
                                WHERE id = :id');
        
        $stmt->bindParam(':question', $questionText);
        $stmt->bindParam(':opt_1', $updatedValues['opt_1']);
        $stmt->bindParam(':opt_2', $updatedValues['opt_2']);
        $stmt->bindParam(':opt_3', $updatedValues['opt_3']);
        $stmt->bindParam(':opt_4', $updatedValues['opt_4']);
        $stmt->bindParam(':answer', $updatedValues['answer']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        $result = $stmt->execute();

        if ($result) {
            $conn->commit();
            $_SESSION['success'][] = 'Question updated successfully!';
            // Clear errors on success
            $_SESSION['errors'] = [];
            header('Location: add_edit_questions.php?id=' . $categoryId);
            exit;
        }
    } catch (Exception $e) {
        $conn->rollBack();
        
        // Delete newly uploaded images if database update fails
        foreach ($uploadedImages as $imagePath) {
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $_SESSION['errors'][] = 'Database update error: ' . $e->getMessage();
        header('Location: ' . basename(__FILE__) . '?id=' . urlencode($categoryId));
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
                <?php if (!empty($_SESSION['errors'])): ?>
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endforeach; ?>
                        <?php $_SESSION['errors'] = []; // Clear after display ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Display Success -->
                <?php if (!empty($_SESSION['success'])): ?>
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <?php foreach ($_SESSION['success'] as $success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endforeach; ?>
                        <?php $_SESSION['success'] = []; // Clear after display ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-primary mb-3">
                                <i class="bx bx-edit me-2"></i>Edit Exam Questions
                            </h5>
                            <hr>
                            <?php if ($question): ?>
                            <form method="post" action="<?= htmlspecialchars(basename(__FILE__)) ?>"
                                enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($question['id']) ?>">
                                <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
                                <input type="hidden" name="category_id" value="<?= htmlspecialchars($categoryId) ?>">

                                <div class="mb-3">
                                    <label for="question" class="form-label">Question Text</label>
                                    <input type="text" class="form-control" id="question" name="question"
                                        placeholder="Enter Question" required
                                        value="<?= htmlspecialchars($question['question']) ?>">
                                </div>

                                <?php foreach (['opt_1', 'opt_2', 'opt_3', 'opt_4', 'answer'] as $field): ?>
                                <?php $label = ucfirst(str_replace('_', ' ', $field)); ?>

                                <?php if (isImage($question[$field])): ?>
                                <div class="mb-3">
                                    <label for="<?= $field ?>" class="form-label"><?= $label ?> (Image)</label>
                                    <input type="file" class="form-control" id="<?= $field ?>" name="<?= $field ?>"
                                        accept="image/*">
                                    <small class="text-muted">Current image:</small><br>
                                    <img class="img-thumbnail mt-2" src="<?= htmlspecialchars($question[$field]) ?>"
                                        alt="<?= $label ?>" style="max-width: 150px; height: auto;">
                                    <small class="d-block mt-1 text-info">Leave empty to keep current image</small>
                                </div>
                                <?php else: ?>
                                <div class="mb-3">
                                    <label for="<?= $field ?>" class="form-label"><?= $label ?> (Text)</label>
                                    <input type="text" class="form-control" id="<?= $field ?>" name="<?= $field ?>"
                                        placeholder="Enter <?= $label ?>" required
                                        value="<?= htmlspecialchars($question[$field]) ?>">
                                </div>
                                <?php endif; ?>
                                <?php endforeach; ?>

                                <div class="d-flex gap-2">
                                    <button type="submit" name="issSubmitted" class="btn btn-primary">
                                        <i class="bx bx-save me-1"></i>Update Question
                                    </button>
                                    <a href="add_edit_questions.php?id=<?= htmlspecialchars($categoryId) ?>"
                                        class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i>Back
                                    </a>
                                </div>
                            </form>
                            <?php else: ?>
                            <div class="alert alert-warning">
                                No question data available. Please select a question to edit.
                            </div>
                            <a href="exam_questions.php" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back to Questions
                            </a>
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
<?php 
// Clear messages after display
unset($_SESSION['errors']);
unset($_SESSION['success']);
$conn = null; 
?>
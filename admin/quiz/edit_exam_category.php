<?php
session_start();
if(!isset($_SESSION['AdminId']) || !isset( $_SESSION['AdminLoggedIn']) ||  $_SESSION['AdminLoggedIn'] !== true){
    header('Location: ../../index.php');
    exit;
}
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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Verify CSRF Token
    if (!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        $_SESSION['errors'][] = 'CSRF Token Error';
        header('Location: ' . basename(__FILE__));
        exit;
    }

    $id = filter_var(trim($_POST['id']), FILTER_VALIDATE_INT);
    $categoryName = filter_var(trim($_POST['category']), FILTER_SANITIZE_SPECIAL_CHARS);
    $time = filter_var(trim($_POST['exam_time']), FILTER_VALIDATE_INT);

    // Validations
    if (empty($categoryName) || empty($time)) {
        $_SESSION['errors'][] = 'All Fields are required';
        header('Location: ' . basename(__FILE__));
        exit;
    }

    if (strlen($categoryName) > 300) {
        $_SESSION['errors'][] = 'Category name is too long';
        header('Location: ' . basename(__FILE__));
        exit;
    }

    // Insert data into the database
    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare('UPDATE exam_category_tbl 
                                         SET exam_category       = :ecategory,
                                            exam_time_in_minutes = :emin
                                         WHERE id                = :id');
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':ecategory', $categoryName);
        $stmt->bindParam(':emin', $time);
        $result = $stmt->execute();

        // Redirected to exam page
        if ($result) {
            $conn->commit();
            header('Location: exam_category.php?updateSuccess=1');
            exit;
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['errors'][] = 'Update error ' . $e->getMessage();
        header('Location: ' . basename(__FILE__));
        exit;
    }
}



// Get errors from session and clear them
$errors = $_SESSION['errors'] ?? [];
$_SESSION['errors'] = [];

require 'layout/header.php';


?>
<!--page-wrapper-->
<div class="page-wrapper">
    <!--page-content-wrapper-->
    <div class="page-content-wrapper">
        <div class="page-content">
            <div class="row">
                <div class="col-12">
                    <h4 class="mb-4">Edit Exam Category</h4>
                </div>
            </div>

            <div class="row">
                <!-- Display Success -->
                <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="alert alert-success fade show" role="alert">
                            Exam Category Successfully add.
                        </div>
                    </div>
                </div>
                <?php endif; ?>
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
                <!-- Left Half - Form -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-primary mb-3">
                                <i class="bx bx-plus-circle me-2"></i>Edit Exam Category
                            </h5>
                            <?php 
                            $id = $_GET['id'] ?? null;
                            $sql = $conn->prepare('SELECT * FROM exam_category_tbl WHERE id = :id');
                            $sql->bindParam(':id', $id);
                            $sql->execute();
                            $category = $sql->fetch(); 
                            ?>
                            <hr>
                            <form method="post" action="<?= htmlspecialchars(basename(__FILE__)) ?>">
                                <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($category['id']) ?>">
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Category Name</label>
                                    <input type="text" class="form-control" name="category" id="categoryName"
                                        placeholder="Enter category name"
                                        value="<?= htmlspecialchars($category['exam_category']) ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="examTime" class="form-label">Time (Minutes)</label>
                                    <input type="number" class="form-control" name="exam_time" id="examTime"
                                        placeholder="Enter time in minutes"
                                        value="<?= htmlspecialchars($category['exam_time_in_minutes']) ?>">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" name="submit" class="btn btn-primary">Update Category</button>
                                    <a href="exam_category.php" class="btn btn-danger">Back</a>
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
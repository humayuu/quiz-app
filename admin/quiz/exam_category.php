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

        $stmt = $conn->prepare('INSERT INTO exam_category_tbl (exam_category, exam_time_in_minutes) VALUES (:ecategory, :emin)');
        $stmt->bindParam(':ecategory', $categoryName);
        $stmt->bindParam(':emin', $time);
        $result = $stmt->execute();

        // Redirected to exam page
        if ($result) {
            $conn->commit();
            header('Location: exam_category.php?success=1');
            exit;
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['errors'][] = 'Insert error ' . $e->getMessage();
        header('Location: ' . basename(__FILE__));
        exit;
    }
}



// Get errors from session and clear them
$errors = $_SESSION['errors'] ?? [];
$_SESSION['errors'] = [];



// Delete Category 
if (isset($_GET['id'])) {
    try {
        $conn->beginTransaction();
        $id = htmlspecialchars($_GET['id']) ?? '';
        $delete = $conn->prepare('DELETE FROM exam_category_tbl WHERE id = :id');
        $delete->bindParam(':id', $id);
        $res = $delete->execute();
        if ($res) {
            $conn->commit();
            // Redirected to same page
            header('Location: exam_category.php?deleteSuccess=1');
            exit;
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['errors'][] = 'Insert error ' . $e->getMessage();
        header('Location: ' . basename(__FILE__));
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
                    <h4 class="mb-4">Exam Category</h4>
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
                <!-- Display Update Success -->
                <?php if (isset($_GET['updateSuccess']) && $_GET['updateSuccess'] == 1): ?>
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="alert alert-success fade show" role="alert">
                            Exam Category Successfully Update.
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Display Delete Success -->
                <?php if (isset($_GET['deleteSuccess']) && $_GET['deleteSuccess'] == 1): ?>
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="alert alert-info fade show" role="alert">
                            Exam Category Successfully Deleted.
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
                                <i class="bx bx-plus-circle me-2"></i>Add New Exam Category
                            </h5>
                            <hr>
                            <form method="post" action="<?= htmlspecialchars(basename(__FILE__)) ?>">
                                <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Category Name</label>
                                    <input type="text" class="form-control" name="category" id="categoryName"
                                        placeholder="Enter category name">
                                </div>
                                <div class="mb-3">
                                    <label for="examTime" class="form-label">Time (Minutes)</label>
                                    <input type="number" class="form-control" name="exam_time" id="examTime"
                                        placeholder="Enter time in minutes">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" name="submit" class="btn btn-primary">Add Category</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php
                $sql = $conn->prepare('SELECT * FROM exam_category_tbl ORDER BY exam_category DESC');
                $sql->execute();
                $categories = $sql->fetchAll();
                ?>
                <!-- Right Half - Data Display -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-success mb-3">
                                <i class="bx bx-list-ul me-2"></i>Exam Categories List
                            </h5>
                            <hr>
                            <div class="table-responsive">
                                <?php $sl = 1;
                                if ($categories): ?>
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Category Name</th>
                                            <th>Time (Minutes)</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td class="text-center"><?= htmlspecialchars($sl++) ?></td>
                                            <td><?= htmlspecialchars($category['exam_category']) ?></td>
                                            <td><?= htmlspecialchars($category['exam_time_in_minutes']) ?></td>
                                            <td>
                                                <a href="edit_exam_category.php?id=<?= htmlspecialchars($category['id']) ?>"
                                                    class="btn btn-sm"
                                                    style="background-color: #6f42c1; color: white;">Edit</a>
                                                <a href="<?= htmlspecialchars(basename(__FILE__)) . '?id=' . htmlspecialchars($category['id']) ?>"
                                                    onclick="return confirm('Are you sure ?')" class="btn btn-sm ms-1"
                                                    style="background-color: #e83e8c; color: white;">Delete</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                <div class="alert alert-danger" role="alert">
                                    No Category Found!
                                </div>
                                <?php endif; ?>
                            </div>
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
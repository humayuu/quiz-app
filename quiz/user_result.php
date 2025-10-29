<?php
session_start();
if(!isset($_SESSION['LoggedIn']) || $_SESSION['LoggedIn'] !== true){
    header('Location: ../index.php');
    exit;
}
require '../config.php';

if (!isset($_SESSION['errors'])) {
    $_SESSION['errors'] = [];
}

try {
    $stmt = $conn->prepare('
        SELECT 
            r.*,
            e.exam_category 
        FROM result_tbl r
        LEFT JOIN exam_category_tbl e ON r.category_id = e.id
        WHERE r.user_id = :userid
        ORDER BY r.created_at DESC
    ');
    $stmt->bindParam(':userid', $_SESSION['userId']);
    $stmt->execute();
    $results = $stmt->fetchAll();

} catch(Exception $e) {
    $_SESSION['errors'][] = 'Error: ' . $e->getMessage();
    header('Location: index.php');
    exit;
}

$errors = $_SESSION['errors'] ?? [];
$_SESSION['errors'] = [];

require '../layout/header.php';
?>

<?php if($results): ?>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Category</th>
                <th scope="col">Score</th>
                <th scope="col">Attempt Date</th>
            </tr>
        </thead>
        <tbody>
            <?php $sl = 1; foreach($results as $result): ?>
            <tr>
                <th scope="row"><?= $sl++ ?></th>
                <td><?= htmlspecialchars($result['exam_category']) ?></td>
                <td>
                    <span class="badge bg-primary">20/
                        <?= htmlspecialchars($result['user_result']) ?>
                    </span>
                </td>
                <td>
                    <?php 
                    $date = new DateTime($result['created_at']);
                    echo htmlspecialchars($date->format('M d, Y - h:i A')); 
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="alert alert-info text-center py-5">
    <i class="bi bi-info-circle fs-1 d-block mb-3"></i>
    <h5>No User Result Found!</h5>
    <p class="mb-0">There are no results available for this user.</p>
</div>
<?php endif; ?>

<?php require '../layout/footer.php';?>
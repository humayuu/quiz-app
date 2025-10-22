<?php require 'layout/header.php'; ?>

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
                <!-- Left Half - Form -->
                <div class="col-lg-10">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-primary mb-3">
                                <i class="bx bx-plus-circle me-2"></i>Add New Exam Questions
                            </h5>
                            <hr>
                            <form>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Questions</label>
                                    <input type="text" class="form-control" id="categoryName"
                                        placeholder="Enter category name">
                                </div>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt1</label>
                                    <input type="text" class="form-control" id="categoryName"
                                        placeholder="Enter category name">
                                </div>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt2</label>
                                    <input type="text" class="form-control" id="categoryName"
                                        placeholder="Enter category name">
                                </div>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt3</label>
                                    <input type="text" class="form-control" id="categoryName"
                                        placeholder="Enter category name">
                                </div>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt3</label>
                                    <input type="text" class="form-control" id="categoryName"
                                        placeholder="Enter category name">
                                </div>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Opt4</label>
                                    <input type="text" class="form-control" id="categoryName"
                                        placeholder="Enter category name">
                                </div>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Add Answer </label>
                                    <input type="text" class="form-control" id="categoryName"
                                        placeholder="Enter category name">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Add Questions</button>
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
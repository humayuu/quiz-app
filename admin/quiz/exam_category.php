<?php require 'layout/header.php'; ?>

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
                <!-- Left Half - Form -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-primary mb-3">
                                <i class="bx bx-plus-circle me-2"></i>Add New Exam Category
                            </h5>
                            <hr>
                            <form>
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Category Name</label>
                                    <input type="text" class="form-control" id="categoryName"
                                        placeholder="Enter category name">
                                </div>
                                <div class="mb-3">
                                    <label for="examTime" class="form-label">Time (Minutes)</label>
                                    <input type="number" class="form-control" id="examTime"
                                        placeholder="Enter time in minutes">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Add Category</button>
                                    <button type="reset" class="btn btn-secondary">Reset</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Half - Data Display -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-success mb-3">
                                <i class="bx bx-list-ul me-2"></i>Exam Categories List
                            </h5>
                            <hr>
                            <div class="table-responsive">
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
                                        <tr>
                                            <td>1</td>
                                            <td>Mathematics</td>
                                            <td>60</td>
                                            <td>
                                                <button class="btn btn-sm"
                                                    style="background-color: #6f42c1; color: white;">Edit</button>
                                                <button class="btn btn-sm ms-1"
                                                    style="background-color: #e83e8c; color: white;">Delete</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>Science</td>
                                            <td>90</td>
                                            <td>
                                                <button class="btn btn-sm"
                                                    style="background-color: #6f42c1; color: white;">Edit</button>
                                                <button class="btn btn-sm ms-1"
                                                    style="background-color: #e83e8c; color: white;">Delete</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>English</td>
                                            <td>45</td>
                                            <td>
                                                <button class="btn btn-sm"
                                                    style="background-color: #6f42c1; color: white;">Edit</button>
                                                <button class="btn btn-sm ms-1"
                                                    style="background-color: #e83e8c; color: white;">Delete</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td>Computer Science</td>
                                            <td>120</td>
                                            <td>
                                                <button class="btn btn-sm"
                                                    style="background-color: #6f42c1; color: white;">Edit</button>
                                                <button class="btn btn-sm ms-1"
                                                    style="background-color: #e83e8c; color: white;">Delete</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
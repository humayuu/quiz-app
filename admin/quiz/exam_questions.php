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
                <!-- Right Half - Data Display -->
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-success mb-3">
                                <i class="bx bx-list-ul me-2"></i>Select Exam Category For add and Edit Questions
                            </h5>
                            <hr>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="fs-5">#</th>
                                            <th class="fs-5">Exam Name</th>
                                            <th class="fs-5">Time (Minutes)</th>
                                            <th class="fs-5">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fs-5">1</td>
                                            <td class="fs-5">Mathematics</td>
                                            <td class="fs-5 fw-bold">60</td>
                                            <td>
                                                <a class="btn"
                                                    style="background-color: #6f42c1; color: white;">Select</a>
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
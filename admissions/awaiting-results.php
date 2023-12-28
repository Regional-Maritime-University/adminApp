<?php
session_start();

if (!isset($_SESSION["adminLogSuccess"]) || $_SESSION["adminLogSuccess"] == false || !isset($_SESSION["user"]) || empty($_SESSION["user"])) {
    header("Location: ../index.php");
}

$isUser = false;
if (strtolower($_SESSION["role"]) == "admissions" || strtolower($_SESSION["role"]) == "developers") $isUser = true;

if (isset($_GET['logout']) || !$isUser) {
    session_destroy();
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    header('Location: ../index.php');
}

$_SESSION["lastAccessed"] = time();

require_once('../bootstrap.php');

use Src\Controller\AdminController;

$admin = new AdminController();
require_once('../inc/page-data.php');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?= require_once("../inc/head.php") ?>
</head>

<body>
    <?= require_once("../inc/header.php") ?>

    <?= require_once("../inc/sidebar.php") ?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Awaiting</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Awaiting</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">

                <!-- Recent Sales -->
                <div class="col-12">
                    <div style="width: 100% !important">
                        <div style="display: flex; flex-direction:row; justify-content:center;">
                            <!-- Admitted Students Card -->
                            <div class="col-xxl-3 col-md-3" style="width: 500px">
                                <div class="card info-card">
                                    <div class="card-body">
                                        <h5 class="card-title" style="text-align: center;"> Download/Upload Awaiting Datasheet</h5>
                                        <div style="display: flex; flex-direction:column; align-items: center; justify-content:center;">

                                            <div id="data-upload-form">
                                                <p id="upload-notification" class="text-success"></p>

                                                <label class="btn btn-warning me-4" id="downloadBtn" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Export current list of awaiting applications to an excel file">
                                                    <i class="bi bi-download"></i> Download
                                                </label>

                                                <label for="awaiting-ds" class="btn btn-danger" id="uploadBtn" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Upload data of awaiting applications">
                                                    <i class="bi bi-upload"></i> Upload
                                                </label>

                                                <form id="upload-awaiting-form" action="" method="post">
                                                    <input type="file" name="awaiting-ds" id="awaiting-ds" style="display: none;" accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                                                    <input type="hidden" name="action" value="uad">
                                                    <input type="hidden" name="startRow" value="1">
                                                    <input type="hidden" name="endRow" value="0">
                                                </form>

                                            </div>

                                            <div id="data-process-info" class="mt-4" style="display:none !important; width:100% !important; display:flex; flex-direction:column; align-items:center">
                                                <h5 class="text-center">Upload Summary</h5>
                                                <ol class="list-group list-group-horizontal" style="font-size: 12px !important; font-family: Verdana, Arial, Tahoma, Serif !important;">
                                                    <li class="list-group-item d-flex justify-content-between align-items-start">
                                                        <div class="ms-2 me-2">
                                                            <div class="fw-bold">Total</div>
                                                        </div>
                                                        <span class="badge bg-dark rounded-pill">14</span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between align-items-start">
                                                        <div class="ms-2 me-2">
                                                            <div class="fw-bold">Success</div>
                                                        </div>
                                                        <span class="badge bg-success rounded-pill">14</span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between align-items-start">
                                                        <div class="ms-2 me-2">
                                                            <div class="fw-bold">Errors</div>
                                                        </div>
                                                        <span class="badge bg-danger rounded-pill">14</span>
                                                    </li>
                                                </ol>

                                                <div class="error-info mt-4" style="display:none; font-size: 11px !important; font-family: Verdana, Arial, Tahoma, Serif !important;">
                                                    <h5 class="text-danger text-center">Errors Encountered</h5>
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col" colspan="1">#</th>
                                                                <th scope="col" colspan="1">Index No.</th>
                                                                <th scope="col" colspan="2">Error Message</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>1</td>
                                                                <td>123456789</td>
                                                                <td>Applicant index number doesn't match any record in database</td>
                                                            </tr>
                                                            <tr>
                                                                <td>2</td>
                                                                <td>123456789</td>
                                                                <td>Applicant index number doesn't match any record in database</td>
                                                            </tr>
                                                            <tr>
                                                                <td>2</td>
                                                                <td>123456789</td>
                                                                <td>Applicant index number doesn't match any record in database</td>
                                                            </tr>
                                                            <tr>
                                                                <td>2</td>
                                                                <td>123456789</td>
                                                                <td>Applicant index number doesn't match any record in database</td>
                                                            </tr>
                                                            <tr>
                                                                <td>2</td>
                                                                <td>123456789</td>
                                                                <td>Applicant index number doesn't match any record in database</td>
                                                            </tr>
                                                            <tr>
                                                                <td>2</td>
                                                                <td>123456789</td>
                                                                <td>Applicant index number doesn't match any record in database</td>
                                                            </tr>
                                                            <tr>
                                                                <td>2</td>
                                                                <td>123456789</td>
                                                                <td>Applicant index number doesn't match any record in database</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Admitted Students Card -->
                        </div>
                    </div>
                </div><!-- End Recent Sales -->

                <!-- Right side columns -->
                <!-- End Right side columns -->

            </div>
        </section>

    </main><!-- End #main -->

    <?= require_once("../inc/footer-section.php") ?>

    <script>
        $(document).ready(function() {

            function getUrlVars() {
                var vars = {};
                var parts = window.location.href.replace(
                    /[?&]+([^=&]+)=([^&]*)/gi,
                    function(m, key, value) {
                        vars[key] = value;
                    }
                );
                return vars;
            }

            //Use a default value when param is missing
            function getUrlParam(parameter, defaultvalue) {
                var urlparameter = defaultvalue;
                if (window.location.href.indexOf(parameter) > -1) {
                    urlparameter = getUrlVars()[parameter];
                }
                return urlparameter;
            }

            if (getUrlVars()["status"] != "" || getUrlVars()["status"] != undefined) {
                if (getUrlVars()["exttrid"] != "" || getUrlVars()["exttrid"] != undefined) {}
            }

            $("#awaiting-ds").change(function() {
                $("#upload-notification").text($(this).val()).show("slow");

                // Get the form element
                var form = $('form')[0];

                // Create a new FormData object
                var formData = new FormData(form);

                // Set up ajax request
                $.ajax({
                    type: 'POST',
                    url: "../endpoint/extra-awaiting-data",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        console.log(result);
                        if (result[2].errors_count) {
                            $("#data-process-info").toggle();
                            return;
                        } else if (result[1].success_count) {
                            alert("Data successfully uploaded!");
                            return;
                        } else {
                            alert("Fatal Error: Unexpected error occured during data processing!");
                            return;
                        }
                    },
                    error: function() {
                        alert('Error: Internal server error!');
                    },
                    ajaxStart: function() {
                        $("#uploadBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...');
                    },
                    ajaxStop: function() {
                        $("#uploadBtn").prop("disabled", false).html('Upload');
                    }
                });

            });

            $("#downloadBtn").click(function() {
                let data = {
                    action: "dbs"
                }

                $.ajax({
                    type: "POST",
                    url: "../endpoint/downloadAwaiting",
                    data: data,
                    success: function(result) {
                        console.log(result);
                        if (result.success) window.open(result.message, '_blank');
                        else if (result.message == "logout") window.location.href = "?logout=true";
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            })

        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@2.1.7/dist/loadingoverlay.min.js"></script>
    <script>
        $(document).ready(function() {
            $(document).on({
                ajaxStart: function() {
                    // Show full page LoadingOverlay
                    $.LoadingOverlay("show");
                },
                ajaxStop: function() {
                    // Hide it after 3 seconds
                    $.LoadingOverlay("hide");
                }
            });
        });
    </script>
</body>

</html>
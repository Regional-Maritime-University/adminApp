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
            <h1>Backup Database</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Backup Database</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">

                <!-- Recent Sales -->
                <div class="col-12">
                    <div style="display: flex; flex-direction:row; justify-content:center;">
                        <!-- Admitted Students Card -->
                        <div class="col-xxl-6 col-md-6" style="max-width: 500px">
                            <div class="card info-card">
                                <div class="card-body" style="text-align: center;">
                                    <h5 class="card-title">Backup Database</h5>
                                    <p id="upload-notification" class="text-success mb-4"></p>
                                    <div style="display: flex; flex-direction:column; align-items: center; justify-content:center;">
                                        <p>This process will lock the database to prevent any modification of data during
                                            backup in order to prevent inconsistencies in the backed-up data.</p>
                                        <form id="upload-awaiting-form" method="post">
                                            <button type="submit" id="backupBtn" class="btn btn-primary">
                                                <i class="bi bi-database-down"></i> Backup Now
                                            </button>
                                            <input type="hidden" value="1">
                                        </form>

                                        <div id="data-process-info" class="mt-4" style="display:none !important; width:100% !important; display:flex; flex-direction:column; align-items:center">
                                            <label class="btn btn-warning me-4" id="downloadBtn" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Export current list of awaiting applications to an excel file">
                                                <i class="bi bi-download"></i> Backup Now
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Admitted Students Card -->
                    </div>
                </div><!-- End Recent Sales -->

            </div>
        </section>

    </main><!-- End #main -->

    <?= require_once("../inc/footer-section.php") ?>

    <script>
        $(document).ready(function() {

            $("#upload-awaiting-form").on("submit", function(e) {
                e.preventDefault();
                //$("#upload-notification").text($(this).val()).show("slow");

                // Set up ajax request
                $.ajax({
                    type: 'POST',
                    url: "../endpoint/backup-data",
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        console.log(result);
                        if (result.message == "logout") window.location.href = "?logout=true";
                    },
                    error: function(err) {
                        console.log(err)
                        alert('Error: Internal server error!');
                    },
                    ajaxStart: function() {
                        $("#backupBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Backing-up...');
                    },
                    ajaxStop: function() {
                        $("#backupBtn").prop("disabled", false).html('Upload');
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
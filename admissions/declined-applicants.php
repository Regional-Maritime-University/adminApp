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
            <h1>Declined Applicants</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Declined Applicants</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">

                <!-- Recent Sales -->
                <div class="col-12">

                    <div class="card recent-sales overflow-auto">

                        <div class="filter">
                            <span id="dbs-progress"></span>
                            <a class="icon" id="download-bs" href="javascript:void()" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Download Broadsheets">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title">Declined Applicants</h5>
                            <form id="fetchDataForm" class="mb-4">
                                <div class="row">
                                    <div class="col-3">
                                        <label for="cert-type" class="form-label">Certificate Type</label>
                                        <select name="cert-type" id="cert-type" class="form-select">
                                            <option value="" hidden>Choose</option>
                                            <option value="WASSCE">WASSCE</option>
                                            <option value="SSSCE">SSSCE</option>
                                            <option value="GBCE">GBCE</option>
                                            <option value="NECO">NECO</option>
                                            <option value="DIPLOMA">DIPLOMA</option>
                                            <option value="DEGREE">DEGREE</option>
                                            <option value="MASTERS">MASTERS</option>
                                            <option value="BACCALAUREATE">BACCALAUREATE</option>
                                            <option value="O LEVEL">O LEVEL</option>
                                            <option value="A LEVEL">A LEVEL</option>
                                            <option value="OTHER">OTHER</option>
                                        </select>
                                    </div>
                                    <div class="col-2">
                                        <button type="submit" class="btn mb-4 btn-primary" style="margin-top: 30px;" id="submitBtn">Fetch Data</button>
                                    </div>
                                </div>
                            </form>
                            <div id="info-output"></div>
                            <table class="table table-borderless datatable table-striped table-hover">
                                <thead>
                                    <tr class="table-dark">
                                        <th scope="col">#</th>
                                        <th scope="col">Full Name</th>
                                        <th scope="col">Programme</th>
                                        <th scope="col">Application Term</th>
                                        <th scope="col">Study Stream</th>
                                        <th scope="col"> </th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <div class="clearfix"></div>
                        </div>

                    </div>
                </div><!-- End Recent Sales -->

            </div>
        </section>

    </main><!-- End #main -->

    <?= require_once("../inc/footer-section.php") ?>

    <script>
        $(document).ready(function() {

            var fetchBroadsheet = function() {
                data = {
                    "cert-type": $("#cert-type").val(),
                    "prog-type": $("#prog-type").val(),
                }

                $.ajax({
                    type: "POST",
                    url: "../endpoint/getAllDeclinedApplicants",
                    data: data,
                    success: function(result) {
                        console.log(result);

                        if (result.success) {
                            $("tbody").html('');
                            $.each(result.message, function(index, value) {
                                let programme;
                                if (value.first_prog_qualified == 1) {
                                    programme = value.first_prog;
                                } else if (value.second_prog_qualified == 1) {
                                    programme = value.second_prog;
                                }
                                $("tbody").append(
                                    '<tr>' +
                                    '<th scope="row">' + (index + 1) + '</th>' +
                                    '<td>' + value.first_name + ' ' + value.last_name + '</td>' +
                                    '<td>' + programme + '</td>' +
                                    '<td>' + value.application_term + '</td>' +
                                    '<td>' + value.study_stream + '</td>' +
                                    '<td><b><a href="applicant-info.php?t=' + value.form_id + '&q=' + value.id + '">Open</a></b></td>' +
                                    '</tr>');
                            });
                            $("#info-output").hide();

                        } else {
                            if (result.message == "logout") window.location.href = "?logout=true";
                            $("tbody").html("<tr style='text-align: center'><td colspan='5'>No entries found</td></tr>");
                        }

                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            }

            let triggeredBy = 0;

            $("#fetchDataForm").on("submit", function(e) {
                e.preventDefault();
                triggeredBy = 1;
                fetchBroadsheet();
            });

            $(document).on({
                ajaxStart: function() {
                    if (triggeredBy == 1) $("#submitBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> sending...');
                    if (triggeredBy == 2) $("#admit-all-bs").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
                },
                ajaxStop: function() {
                    if (triggeredBy == 1) $("#submitBtn").prop("disabled", false).html('Fetch Data');
                    if (triggeredBy == 2) $("#admit-all-bs").prop("disabled", false).html('Admit All Qualified');
                }
            });

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
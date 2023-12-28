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
            <h1>Admit Applicants</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Admit Applicants</li>
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
                            <h5 class="card-title">Admit Applicants</h5>
                            <form id="fetchDataForm" class="mb-4">
                                <div class="row">
                                    <div class="col-3">
                                        <label for="cert-type" class="form-label">Certificate Type</label>
                                        <select name="cert-type" id="cert-type" class="form-select">
                                            <option value="" hidden>Choose Certificate</option>
                                            <option value="WASSCE">WASSCE/NECO</option>
                                            <option value="SSSCE">SSSCE/GBCE</option>
                                            <option value="Baccalaureate">BACCALAUREATE</option>
                                            <option value="ALL">ALL</option>
                                        </select>
                                    </div>
                                    <div class="col-3">
                                        <label for="prog-type" class="form-label">Programme Category</label>
                                        <select name="prog-type" id="prog-type" class="form-select">
                                            <option value="" hidden>Choose Category</option>
                                            <option value="first_prog">First Choice</option>
                                            <option value="second_prog">Second Choice</option>
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
                                        <th scope="col" colspan="1">Full Name</th>
                                        <th scope="col" colspan="1">Programme: (<span class="pro-choice">1<sup>st</sup></span>) Choice</th>
                                        <th scope="col" colspan="4" style="text-align: center;">Core Subjects</th>
                                        <th scope="col" colspan="4" style="text-align: center;">Elective Subjects</th>
                                        <!--<th scope="col" colspan="1" style="text-align: center;">Status</th>-->
                                    </tr>
                                    <tr class="table-grey">
                                        <th scope="col"></th>
                                        <th scope="col"></th>
                                        <th scope="col"></th>
                                        <th scope="col" style="background-color: #999; text-align: center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Core Mathematics">CM</th>
                                        <th scope="col" style="background-color: #999; text-align: center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="English Language">EL</th>
                                        <th scope="col" style="background-color: #999; text-align: center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Integrated Science">IS</th>
                                        <th scope="col" style="background-color: #999; text-align: center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Social Studies">SS</th>
                                        <th scope="col" style="background-color: #999; text-align: center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Elective 1">E1</th>
                                        <th scope="col" style="background-color: #999; text-align: center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Elective 2">E2</th>
                                        <th scope="col" style="background-color: #999; text-align: center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Elective 3">E3</th>
                                        <th scope="col" style="background-color: #999; text-align: center" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Elective 4">E4</th>
                                        <!--<th scope="col"></th>-->
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <div class="mt-4" style="float:right">
                                <button class="btn btn-primary" id="admit-all-bs">Admit All Qualified</button>
                            </div>
                            <div class="clearfix"></div>
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

            var fetchBroadsheet = function() {
                data = {
                    "cert-type": $("#cert-type").val(),
                    "prog-type": $("#prog-type").val(),
                }

                $.ajax({
                    type: "POST",
                    url: "../endpoint/getUnadmittedApps",
                    data: data,
                    success: function(result) {
                        console.log(result);

                        if (result.success) {
                            $("tbody").html('');
                            $.each(result.message, function(index, value) {
                                //let status = value.declaration == 1 ? '<span class="badge text-bg-success">Q</span>' : '<span class="badge text-bg-danger">F</span>';
                                $("tbody").append(
                                    '<tr>' +
                                    '<th scope="row">' + (index + 1) + '</th>' +
                                    '<td>' + value.app_pers.first_name + ' ' + value.app_pers.last_name + '</td>' +
                                    '<td>' + value.app_pers.programme + '</td>' +
                                    '<td style="cursor: help; text-align: center" title="' + value.sch_rslt[0].subject + '">' + value.sch_rslt[0].grade + '</td>' +
                                    '<td style="cursor: help; text-align: center" title="' + value.sch_rslt[1].subject + '">' + value.sch_rslt[1].grade + '</td>' +
                                    '<td style="cursor: help; text-align: center" title="' + value.sch_rslt[2].subject + '">' + value.sch_rslt[2].grade + '</td>' +
                                    '<td style="cursor: help; text-align: center" title="' + value.sch_rslt[3].subject + '">' + value.sch_rslt[3].grade + '</td>' +
                                    '<td style="cursor: help; text-align: center" title="' + value.sch_rslt[4].subject + '">' + value.sch_rslt[4].grade + '</td>' +
                                    '<td style="cursor: help; text-align: center" title="' + value.sch_rslt[5].subject + '">' + value.sch_rslt[5].grade + '</td>' +
                                    '<td style="cursor: help; text-align: center" title="' + value.sch_rslt[6].subject + '">' + value.sch_rslt[6].grade + '</td>' +
                                    '<td style="cursor: help; text-align: center" title="' + value.sch_rslt[7].subject + '">' + value.sch_rslt[7].grade + '</td>' +
                                    //'<td style="text-align: center">' + status + '</td>' +
                                    '</tr>');
                            });

                        } else {
                            if (result.message == "logout") {
                                window.location.href = "?logout=true";
                                return;
                            }
                            $("tbody").html('');
                            $("#info-output").html(
                                '<div class="alert alert-info alert-dismissible fade show" role="alert">' +
                                '<i class="bi bi-info-circle me-1"></i>' + result.message +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                '</div>'
                            );
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

            $('#admit-all-bs').click(function() {
                triggeredBy = 2;
                data = {
                    "cert-type": $("#cert-type").val(),
                    "prog-type": $("#prog-type").val(),
                }

                $.ajax({
                    type: "POST",
                    url: "../endpoint/admitAll",
                    data: data,
                    success: function(result) {
                        console.log(result);
                        if (result.success) fetchBroadsheet();
                        else if (result.message == "logout") window.location.href = "?logout=true";

                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            $(document).on({
                ajaxStart: function() {
                    if (triggeredBy == 1) $("#submitBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> sending...');
                    if (triggeredBy == 2) $("#admit-all-bs").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
                },
                ajaxStop: function() {
                    if (triggeredBy == 1) $("#submitBtn").prop("disabled", false).html('Resend code');
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
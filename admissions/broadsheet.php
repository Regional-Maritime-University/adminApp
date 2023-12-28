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
            <h1>Broadsheet</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Broadsheet</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">

                <!-- Recent Sales -->
                <div class="col-12">

                    <div class="card recent-sales overflow-auto">

                        <div class="card-body">
                            <h5 class="card-title">Broadsheet</h5>
                            <form id="fetchDataForm" class="mb-4">
                                <div class="row" style="justify-content: baseline; align-items: center">
                                    <div class="col-3">
                                        <!--<label for="cert-type" class="form-label">Certificate Type</label>-->
                                        <select name="cert-type" id="cert-type" class="form-select">
                                            <option value="" hidden>Choose Category</option>
                                            <option value="MASTERS">MASTERS</option>
                                            <option value="DEGREE">DEGREE</option>
                                            <option value="DIPLOMA">DIPLOMA</option>
                                            <option value="UPGRADERS">UPGRADERS</option>
                                            <option value="MEM">MARINE ENGINE MECHANICS</option>
                                            <option value="CDADILT">CILT, DILT AND ADILT</option>
                                        </select>
                                    </div>
                                    <!--<div class="col-3" style="display: none;" id="bs-masters-prog">
                                        <label for="cert-type" class="form-label">Certificate Type</label>
                                    <select name="cert-type" id="cert-type" class="form-select" data-bs-programme="MASTERS">
                                        <option value="" hidden>Choose Programme</option>
                                    </select>
                                </div>
                                <div class="col-3 mt-4">
                                    <button type="submit" class="btn mb-4 btn-outline-primary">Fetch Data</button>
                                </div>-->
                                </div>
                            </form>
                            <div id="info-output"></div>
                            <table class="table table-borderless table-striped table-hover wassce-apps" style="display: none;">
                                <thead>
                                    <tr class="table-dark">
                                        <th scope="col">#</th>
                                        <th scope="col" colspan="1">FULL NAME</th>
                                        <th scope="col" colspan="1">AGE</th>
                                        <th scope="col" colspan="1">SEX</th>
                                        <th scope="col" colspan="1">NATIONALITY</th>
                                        <th scope="col" colspan="4" style="text-align: center;">CORE SUBJECTS</th>
                                        <th scope="col" colspan="4" style="text-align: center;">ELECTIVE SUBJECTS</th>
                                    </tr>
                                    <tr class="table-grey">
                                        <th scope="col"></th>
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
                                    </tr>
                                </thead>
                                <tbody id="wassce-apps">
                                </tbody>
                            </table>
                            <table class="table table-borderless table-striped table-hover postgrad-apps" style="display: none;">
                                <thead>
                                    <tr class="table-dark">
                                        <th scope="col">S/N</th>
                                        <th scope="col" colspan="1">FULL NAME</th>
                                        <th scope="col" colspan="1">AGE</th>
                                        <th scope="col" colspan="1">SEX</th>
                                        <th scope="col" colspan="1">QUALIFICATION</th>
                                        <th scope="col" colspan="1">NATIONALITY</th>
                                        <th scope="col" colspan="1">PROGRAM</th>
                                    </tr>
                                </thead>
                                <tbody id="postgrad-apps">
                                </tbody>
                            </table>
                            <div class="mt-4" id="down-bs" style="display: none;float:right">
                                <button class="btn btn-primary" id="download-bs">Download Broadsheet</button>
                            </div>
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

            $("#download-bs").click(function() {

                if ($('#cert-type').val() == "") {
                    alert("Choose Certificate type");
                    return;
                }

                let data = {
                    'cert-type': $('#cert-type').val()
                }

                $.ajax({
                    type: "POST",
                    url: "../endpoint/downloadBS",
                    data: data,
                    success: function(result) {
                        console.log(result);
                        if (result.success) window.open(result.message, '_blank');
                        else {
                            if (result.message == "logout") {
                                window.location.href = "?logout=true";
                                return;
                            }
                            alert(result.message);
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            $("#cert-type").change("blur", function() {

                if ($('#cert-type').val() == "") {
                    alert("Choose Certificate type");
                    return;
                }

                data = {
                    "cert-type": $(this).val()
                }

                $.ajax({
                    type: "POST",
                    url: "../endpoint/getBroadsheetData",
                    data: data,
                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            if (data["cert-type"] == "DIPLOMA" || data["cert-type"] == "DEGREE") {
                                $("#wassce-apps").html('');
                                $.each(result.message, function(index, value) {
                                    $("#wassce-apps").append(
                                        '<tr>' +
                                        '<th scope="row">' + (index + 1) + '</th>' +
                                        '<td>' + value.full_name + '</td>' +
                                        '<td>' + value.age + '</td>' +
                                        '<td>' + value.sex + '</td>' +
                                        '<td>' + value.nationality + '</td>' +
                                        '<td style="cursor: help; text-align: center" title="' + value.sch_rslt[0].subject + '">' + value.sch_rslt[0].grade + '</td>' +
                                        '<td style="cursor: help; text-align: center" title="' + value.sch_rslt[1].subject + '">' + value.sch_rslt[1].grade + '</td>' +
                                        '<td style="cursor: help; text-align: center" title="' + value.sch_rslt[2].subject + '">' + value.sch_rslt[2].grade + '</td>' +
                                        '<td style="cursor: help; text-align: center" title="' + value.sch_rslt[3].subject + '">' + value.sch_rslt[3].grade + '</td>' +
                                        '<td style="cursor: help; text-align: center" title="' + value.sch_rslt[4].subject + '">' + value.sch_rslt[4].grade + '</td>' +
                                        '<td style="cursor: help; text-align: center" title="' + value.sch_rslt[5].subject + '">' + value.sch_rslt[5].grade + '</td>' +
                                        '<td style="cursor: help; text-align: center" title="' + value.sch_rslt[6].subject + '">' + value.sch_rslt[6].grade + '</td>' +
                                        '<td style="cursor: help; text-align: center" title="' + value.sch_rslt[7].subject + '">' + value.sch_rslt[7].grade + '</td>' +
                                        '</tr>'
                                    );
                                });
                                $(".wassce-apps").show();
                            } else {
                                $("#postgrad-apps").html('');
                                $.each(result.message, function(index, value) {
                                    $("#postgrad-apps").append(
                                        '<tr>' +
                                        '<th scope="row">' + (index + 1) + '</th>' +
                                        '<td>' + value.full_name + '</td>' +
                                        '<td>' + value.age + '</td>' +
                                        '<td>' + value.sex + '</td>' +
                                        '<td>' + value.academic_background + '</td>' +
                                        '<td>' + value.nationality + '</td>' +
                                        '<td>' + value.first_prog + '</td>' +
                                        '</tr>'
                                    );
                                });
                                $(".postgrad-apps").show();
                            }
                            $("#down-bs").show();
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
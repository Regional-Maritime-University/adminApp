<?php
session_start();

if (!isset($_SESSION["adminLogSuccess"]) || $_SESSION["adminLogSuccess"] == false || !isset($_SESSION["user"]) || empty($_SESSION["user"])) {
    header("Location: ../index.php");
}

$isUser = false;
if (strtolower($_SESSION["role"]) == "vendors" || strtolower($_SESSION["role"]) == "developers") $isUser = true;

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

$vendor_id = isset($_SESSION["vendor_id"]) ? $_SESSION["vendor_id"] : "";

$adminSetup = true;
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
            <h1>Daily Transactions</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Daily Transactions</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section dashboard">

            <div class="row">
                <div class="col-12">
                    <div class="card recent-sales overflow-auto">

                        <?php
                        $summary = isset($_SESSION["vendor_id"]) ? $admin->fetchVendorSummary($_SESSION["admin_period"], $_SESSION["vendor_id"]) : "";
                        ?>

                        <div class="card-body">
                            <h5 class="card-title">Summary</h5>

                            <!-- Form Types -->
                            <div class="form-types">
                                <div class="row">
                                    <?php
                                    if (!empty($summary)) {
                                        foreach ($summary["form-types"] as $form) { ?>
                                            <!-- Masters Card -->
                                            <div class="col-xxl-4 col-md-4">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h6 style="font-size: 18px; font-weight: 650; margin-top:20px"><?= $form["name"] ?></h6>
                                                        <div class="mt-2" style="display:flex; justify-content:space-between">

                                                            <div style="display: flex; flex-direction:column; justify-content:flex-start">
                                                                <span style="font-size: 16px;"><?= $form["total_num"] ?></span>
                                                                <span class="text-muted small">COUNT</span>
                                                            </div>

                                                            <div style="display: flex; flex-direction:column; justify-content:flex-start">
                                                                <h5 style="padding-bottom: 0; margin-bottom:0;">
                                                                    <span class="small">GH</span>&#162;<span class="small"><?= $form["total_amount"] ? $form["total_amount"] : "0.00" ?></span>
                                                                </h5>
                                                                <span class="text-muted small">AMOUNT</span>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div><!-- End Masters Card -->
                                    <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div><!-- End Transactions Summary row -->

            <!-- Transactions Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card recent-sales overflow-auto">

                        <div class="card-body">
                            <h5 class="card-title">Transactions</h5>

                            <form id="reportsForm" method="post">
                                <div class="row">

                                    <div class="col-2 col-md-2 col-sm-12 mt-2">
                                        <label for="admission-period" class="form-label">Admission Period</label>
                                        <select name="admission-period" id="admission-period" class="form-select">
                                            <option value="" hidden>Choose</option>
                                            <option value="All">All</option>
                                            <?php
                                            $result = $admin->fetchAllAdmissionPeriod();
                                            foreach ($result as $value) {
                                            ?>
                                                <option value="<?= $value["id"] ?>" <?= $value["active"] ? "selected" : "" ?>><?= $value["info"] ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-2 col-md-2 col-sm-12 mt-2">
                                        <label for="from-date" class="form-label">From (Date)</label>
                                        <input type="date" name="from-date" id="from-date" class="form-control">
                                    </div>

                                    <div class="col-2 col-md-2 col-sm-12 mt-2">
                                        <label for="to-date" class="form-label">To (Date)</label>
                                        <input type="date" name="to-date" id="to-date" class="form-control">
                                    </div>

                                    <div class="col-2 col-md-2 col-sm-12 mt-2">
                                        <label for="form-type" class="form-label">Form Type</label>
                                        <select name="form-type" id="form-type" class="form-select">
                                            <option value="" hidden>Choose</option>
                                            <option value="All">All</option>
                                            <?php
                                            $result = $admin->getAvailableForms();
                                            foreach ($result as $value) {
                                            ?>
                                                <option value="<?= $value["id"] ?>"><?= $value["name"] ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-2 col-md-2 col-sm-12 mt-2">
                                        <label for="purchase-status" class="form-label">Purchase Status</label>
                                        <select name="purchase-status" id="purchase-status" class="form-select">
                                            <option value="" hidden>Choose</option>
                                            <option value="All">All</option>
                                            <option value="COMPLETED">COMPLETED</option>
                                            <option value="FAILED">FAILED</option>
                                            <option value="PENDING">PENDING</option>
                                        </select>
                                    </div>
                                </div>
                            </form>

                            <?php
                            $currentAdminPeriod = $admin->getCurrentAdmissionPeriodID();

                            $data = array(
                                "admission-period" => $currentAdminPeriod,
                                "from-date" => "", "to-date" => "", "form-type" => "all",
                                "purchase-status" => "all", "vendor-id" => $vendor_id
                            );
                            $purchaseData = $admin->fetchAllVendorFormPurchases($_SESSION["admin_period"], $data);
                            $totalPurchaseData = !empty($purchaseData) ? count($purchaseData) : 0;
                            ?>

                            <div class="mt-4" style="display: flex; justify-content: space-between">
                                <h4>Total: <span id="totalData"><?= $totalPurchaseData ?></span></h4>
                                <div id="alert-output"></div>
                            </div>

                            <div style="margin-top: 10px !important">
                                <table class="table table-borderless table-striped table-hover" id="dataT">

                                    <thead class="table-dark">
                                        <tr>
                                            <th scope="col">S/N</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">Transaction ID</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Phone Number</th>
                                            <th scope="col">Admission Period</th>
                                            <th scope="col">Form Bought</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        if (!empty($purchaseData)) {
                                            $index = 1;
                                            foreach ($purchaseData as $pd) {
                                        ?>
                                                <tr>
                                                    <td> <?= $index ?> </td>
                                                    <td> <?= $pd["added_at"] ?> </td>
                                                    <td> <?= $pd["id"] ?> </td>
                                                    <td> <?= $pd["fullName"] ?> </td>
                                                    <td> <?= $pd["phoneNumber"] ?> </td>
                                                    <td> <?= $pd["admissionPeriod"] ?> </td>
                                                    <td> <?= $pd["formType"] ?> </td>
                                                    <td> <?= $pd["status"] ?> </td>
                                                    <td>
                                                        <button id="<?= $pd["id"] ?>" class="btn btn-xs btn-primary openPurchaseInfo" data-bs-toggle="modal" data-bs-target="#purchaseInfoModal">View</button>
                                                    </td>
                                                </tr>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- Transactions List row -->
            <!-- Purchase info Modal -->
            <div class="modal fade" id="purchaseInfoModal" tabindex="-1" aria-labelledby="purchaseInfoModal" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="purchaseInfoModalTitle">Purchase Information</h1>
                            <a href="#" id="printVoucher" target="_blank" type="button" class="btn btn-primary btn-sm">
                                <span class="bi bi-printer"> PRINT</span>
                            </a>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-dismissible infoFeed" style="display:none" role="alert">
                                <span id="msgContent"></span>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <div class="mb-4 row">
                                <div class="mb-3 col-5">
                                    <div class="input-group">
                                        <span class="input-group-text" id="basic-addon3">Trans. ID: </span>
                                        <input disabled type="text" class="form-control _textD" id="p-transID" aria-describedby="basic-addon3">
                                    </div>
                                </div>
                                <div class="mb-3 col-7">
                                    <div class="input-group">
                                        <span class="input-group-text" id="basic-addon3">Admission Period: </span>
                                        <input disabled type="text" class="form-control _textD" id="p-admisP" aria-describedby="basic-addon3">
                                    </div>
                                </div>
                            </div>
                            <fieldset class="mb-4 mt-4">
                                <legend>Personal</legend>
                                <div class="row">
                                    <div class="mb-3 col">
                                        <label for="p-name" class="form-label">Name</label>
                                        <input disabled type="text" class="form-control _textD" id="p-name">
                                    </div>
                                    <div class="mb-3 col">
                                        <label for="p-country" class="form-label">Country</label>
                                        <input disabled type="text" class="form-control _textD" id="p-country">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="mb-3 col">
                                        <label for="p-email" class="form-label">Email Address</label>
                                        <input disabled type="text" class="form-control _textD" id="p-email">
                                    </div>
                                    <div class="mb-3 col">
                                        <label for="p-phoneN" class="form-label">Phone Number</label>
                                        <input disabled type="text" class="form-control _textD" id="p-phoneN">
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset class="mb-4">
                                <legend>Form</legend>
                                <div class="row">
                                    <div class="mb-3 col">
                                        <label for="p-appN" class="form-label">App Number</label>
                                        <input disabled type="text" class="form-control _textD" id="p-appN">
                                    </div>
                                    <div class="mb-3 col">
                                        <label for="p-pin" class="form-label">PIN</label>
                                        <input disabled type="text" class="form-control _textD" id="p-pin">
                                    </div>
                                    <div class="mb-3 col">
                                        <label for="p-status" class="form-label">Status</label>
                                        <input disabled type="text" class="form-control _textD" id="p-status">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="mb-3 col">
                                        <label for="p-vendor" class="form-label">Vendor</label>
                                        <input disabled type="text" class="form-control _textD" id="p-vendor">
                                    </div>
                                    <div class="mb-3 col">
                                        <label for="p-formT" class="form-label">Form Type</label>
                                        <input disabled type="text" class="form-control _textD" id="p-formT">
                                    </div>
                                    <div class="mb-3 col">
                                        <label for="p-payM" class="form-label">Payment Method</label>
                                        <input disabled type="text" class="form-control _textD" id="p-payM">
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                        <?php
                        if (!isset($_SESSION["api_user"]) || empty($_SESSION["api_user"])) {
                        ?>
                            <div class="modal-footer">
                                <div style="width:100% !important; display:flex; justify-content: space-between">
                                    <form id="genSendPurchaseInfoForm" method="post">
                                        <button type="submit" id="genSendTransIDBtn" class="btn btn-warning btn-sm" style="padding:15px !important">Generate and send new application login info</button>
                                        <input type="hidden" name="genSendTransID" id="genSendTransID" value="">
                                    </form>
                                    <form id="sendPurchaseInfoForm" method="post" style="float: right;">
                                        <button type="submit" id="sendTransIDBtn" class="btn btn-success btn-sm" style="padding:15px !important">Resend application login info</button>
                                        <input type="hidden" name="sendTransID" id="sendTransID" value="">
                                    </form>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>


        </section>

    </main><!-- End #main -->

    <?= require_once("../inc/footer-section.php") ?>
    <script>
        $("dataTable-top").hide();
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

            var triggeredBy = 0;

            // when 
            $(".form-select, .form-control").change("blur", function(e) {
                e.preventDefault();
                $("#reportsForm").submit();
            });

            $("#reportsForm").on("submit", function(e, d) {
                e.preventDefault();
                triggeredBy = 1;

                $.ajax({
                    type: "POST",
                    url: "../endpoint/vendorSalesReport",
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        console.log(result);

                        if (result.success) {
                            $("#totalData").text(result.message.length);
                            $("tbody").html('');
                            $.each(result.message, function(index, value) {
                                $("tbody").append(
                                    '<tr>' +
                                    '<td>' + (index + 1) + '</td>' +
                                    '<td>' + value.added_at + '</td>' +
                                    '<td>' + value.id + '</td>' +
                                    '<td>' + value.fullName + '</td>' +
                                    '<td>' + value.phoneNumber + '</td>' +
                                    '<td>' + value.admissionPeriod + '</td>' +
                                    '<td>' + value.formType + '</td>' +
                                    '<td>' + value.status + '</td>' +
                                    '<td>' +
                                    '<button id="' + value.id + '" class="btn btn-xs btn-primary openPurchaseInfo" data-bs-toggle="modal" data-bs-target="#purchaseInfoModal">View</button>' +
                                    '</td>' +
                                    '</tr>'
                                );
                            });
                        } else {
                            if (result.message == "logout") {
                                window.location.href = "?logout=true";
                                return;
                            }
                            $("#totalData").text(0);
                            $("tbody").html("<tr style='text-align: center'><td colspan='9'>" + result.message + "</td></tr>");
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            $(document).on("click", ".openPurchaseInfo", function() {
                triggeredBy = 2;
                let data = {
                    _data: $(this).attr("id")
                }

                $.ajax({
                    type: "POST",
                    url: "../endpoint/purchaseInfo",
                    data: data,
                    success: function(result) {
                        console.log(result);

                        if (result.success) {
                            $("#p-transID").val(result.message[0].transID);
                            $("#p-admisP").val(result.message[0].admisP);
                            $("#p-name").val(result.message[0].fullName);
                            $("#p-country").val(result.message[0].country);
                            $("#p-email").val(result.message[0].email);
                            $("#p-phoneN").val(result.message[0].phoneN);
                            $("#p-appN").val(result.message[0].appN);
                            $("#p-pin").val(result.message[0].pin);
                            $("#p-status").val(result.message[0].status);
                            $("#p-vendor").val(result.message[0].vendor);
                            $("#p-formT").val(result.message[0].formT);
                            $("#p-payM").val(result.message[0].payM);
                            $("#sendTransID").val(result.message[0].transID);
                            $("#genSendTransID").val(result.message[0].transID);
                            $("#printVoucher").prop("href", "print-form.php?exttrid=" + result.message[0].transID);
                        } else {
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

            $("#genSendPurchaseInfoForm").on("submit", function(e) {
                e.preventDefault();

                var confirmMsg = confirm("Please note that applicant current progress on the application portal will be lost after new login info are generated! Click OK to continue.");
                if (!confirmMsg) return;

                triggeredBy = 3;
                $.ajax({
                    type: "POST",
                    url: "../endpoint/gen-send-purchase-info",
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        console.log(result);

                        $("#msgContent").text(result.message);
                        if (result.success) {
                            $(".infoFeed").removeClass("alert-danger").addClass("alert-success");
                            $(".infoFeed").fadeIn("slow", function() {
                                $(".infoFeed").fadeOut(5000);
                            });
                        } else {
                            if (result.message == "logout") {
                                window.location.href = "?logout=true";
                                return;
                            }
                            $(".infoFeed").removeClass("alert-success").addClass("alert-danger");
                            $(".infoFeed").fadeIn("slow", function() {
                                $(".infoFeed").fadeOut(5000);
                            });
                        }

                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            $("#sendPurchaseInfoForm").on("submit", function(e) {
                e.preventDefault();
                triggeredBy = 4;
                $.ajax({
                    type: "POST",
                    url: "../endpoint/send-purchase-info",
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        console.log(result);

                        $("#msgContent").text(result.message);
                        if (result.success) {
                            $(".infoFeed").removeClass("alert-danger").addClass("alert-success").fadeOut(3000);
                            $(".infoFeed").fadeIn("slow", function() {
                                $(".infoFeed").fadeOut(5000);
                            });
                        } else {
                            if (result.message == "logout") {
                                window.location.href = "?logout=true";
                                return;
                            }
                            $(".infoFeed").removeClass("alert-success").addClass("alert-danger");
                            $(".infoFeed").fadeIn("slow", function() {
                                $(".infoFeed").fadeOut(5000);
                            });
                        }

                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            $(document).on({
                ajaxStart: function() {
                    if (triggeredBy == 3) $("#genSendTransIDBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> processing...');
                    else if (triggeredBy == 4) $("#sendTransIDBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> processing...');
                    else $("#alert-output").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
                },
                ajaxStop: function() {
                    if (triggeredBy == 3) $("#genSendTransIDBtn").prop("disabled", false).html('Generate and send new application login info');
                    else if (triggeredBy == 4) $("#sendTransIDBtn").prop("disabled", false).html('Resend application login info');
                    else $("#alert-output").html('');
                }
            });

            $("#admission-period").change("blur", function(e) {
                data = {
                    "data": $(this).val()
                };
                $.ajax({
                    type: "POST",
                    url: "../endpoint/set-admission-period",
                    data: data,
                    success: function(result) {
                        console.log(result);
                        if (result.message == "logout") {
                            window.location.href = "?logout=true";
                            return;
                        }
                        if (!result.success) alert(result.message);
                        else window.location.reload();
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });
        });
    </script>
</body>

</html>
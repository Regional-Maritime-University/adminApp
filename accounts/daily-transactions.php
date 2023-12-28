<?php
session_start();

if (!isset($_SESSION["adminLogSuccess"]) || $_SESSION["adminLogSuccess"] == false || !isset($_SESSION["user"]) || empty($_SESSION["user"])) {
    header("Location: ../index.php");
}

$isUser = false;
if (strtolower($_SESSION["role"]) == "accounts" || strtolower($_SESSION["role"]) == "developers") $isUser = true;

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

$adminSetup = true;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once("../inc/head.php") ?>
    <style>
        ._textD {
            font-weight: 600;
        }
    </style>
</head>

<body>
    <?php require_once("../inc/header.php") ?>

    <?php require_once("../inc/sidebar.php") ?>

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
                        $summary = $admin->fetchInitialSummaryRecord($_SESSION["admin_period"]);

                        if (!empty($summary)) {
                            //collections
                            $collect_total = $summary["collections"]["collect"]["total_num"] ? $summary["collections"]["collect"]["total_num"] : "0";
                            $collect_amount = $summary["collections"]["collect"]["total_amount"] ? $summary["collections"]["collect"]["total_amount"] : "0.00";

                            $vendor_total = $summary["collections"]["vendor"]["total_num"] ? $summary["collections"]["vendor"]["total_num"] : "0";
                            $vendor_amount = $summary["collections"]["vendor"]["total_amount"] ? $summary["collections"]["vendor"]["total_amount"] : "0.00";

                            $online_total = $summary["collections"]["online"]["total_num"] ? $summary["collections"]["online"]["total_num"] : "0";
                            $online_amount = $summary["collections"]["online"]["total_amount"] ? $summary["collections"]["online"]["total_amount"] : "0.00";

                            $provider_total = $summary["collections"]["provider"]["total_num"] ? $summary["collections"]["provider"]["total_num"] : "0";
                            $provider_amount = $summary["collections"]["provider"]["total_amount"] ? $summary["collections"]["provider"]["total_amount"] : "0.00";

                        ?>

                            <div class="card-body">
                                <p class="card-title">Summary</p>

                                <!-- Transactions cards-->
                                <div class="transactions">
                                    <div class="row">

                                        <?php
                                        $total_trans = (int) $summary["transactions"][0]["total"];
                                        foreach ($summary["transactions"] as $transaction) {

                                            $status = isset($transaction["status"]) ?  $transaction["status"] : "TOTAL";

                                            $status_color = match ($status) {
                                                "TOTAL" => "info",
                                                "COMPLETED" => "success",
                                                "PENDING" => "warning",
                                                "FAILED" => "danger"
                                            };

                                            $trans = $transaction["total"] ? $transaction["total"] : 0;
                                            $trans_percent = $total_trans ? ($trans / $total_trans) * 100 : $total_trans;
                                        ?>

                                            <!-- Pending Transactions Card -->
                                            <div class="col-xxl-3 col-md-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h5 class="card-title" style="font-size: 22px; margin-bottom: 0 !important; padding-bottom: 5px !important; font-weight:300 !important "><?= $trans ?></h5>
                                                        <h6 style="font-size: 18px; font-weight: 650;"><?= $status ?> Transactions</h6>
                                                        <div class="progress mb-2 mt-2" role="progressbar" aria-label="Info example" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                                                            <div class="progress-bar bg-<?= $status_color ?>" style="width: <?= $trans_percent ?>%"></div>
                                                        </div>
                                                        <span class="text-muted mt-4">Daily <?= $status ?> transactions</span>
                                                    </div>
                                                </div>
                                            </div><!-- End Pending Transactions Card -->

                                        <?php
                                        }
                                        ?>

                                    </div>
                                </div>

                                <!-- Collections -->
                                <div class="collections">
                                    <div class="row">

                                        <!-- Successful Collections Card -->
                                        <div class="col-xxl-3 col-md-3">
                                            <div class="card info-card sales-card revenue-card">
                                                <div class="card-body">
                                                    <h6 style="font-size: 18px !important; margin: 20px 0; color: #444 ">Successful Collections</h6>

                                                    <div class="d-flex align-items-center">
                                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                            <img src="../assets/img/icons8-cash-96.png" style="width: 48px;" alt="">
                                                        </div>
                                                        <div class="ps-3">
                                                            <h5><span class="small">GH</span>&#162;<span><?= number_format($collect_amount) ?></span></h5>
                                                            <span class="text-muted pt-1">COUNT: </span>
                                                            <span class="pt-2 ps-1" style="font-size: 16px;"><?= $collect_total ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!-- End Successful Collections Card -->

                                        <!-- Vendors Collections Card -->
                                        <div class="col-xxl-3 col-md-3">
                                            <div class="card info-card sales-card">
                                                <div class="card-body">
                                                    <h6 style="font-size: 18px !important; margin: 20px 0; color: #444">Vendors Collections</h6>

                                                    <div class="d-flex align-items-center">
                                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                            <img src="../assets/img/icons8-sell-48.png" style="width: 48px;" alt="">
                                                        </div>
                                                        <div class="ps-3">
                                                            <h5><span class="small">GH</span>&#162;<span><?= number_format($vendor_amount) ?></span></h5>
                                                            <span class="text-muted pt-1">COUNT: </span>
                                                            <span class="pt-2 ps-1" style="font-size: 16px;"><?= $vendor_total ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!-- End Vendors Collections Card -->

                                        <!-- Online Collections Card -->
                                        <div class="col-xxl-3 col-md-3">
                                            <div class="card info-card sales-card">
                                                <div class="card-body">
                                                    <h6 style="font-size: 18px !important; margin: 20px 0; color: #444">Online Collections</h6>

                                                    <div class="d-flex align-items-center">
                                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                            <img src="../assets/img/icons8-online-payment-64.png" style="width: 48px;" alt="">
                                                        </div>
                                                        <div class="ps-3">
                                                            <h5><span class="small">GH</span>&#162;<span><?= number_format($online_amount) ?></span></h5>
                                                            <span class="text-muted pt-1">COUNT: </span>
                                                            <span class="pt-2 ps-1" style="font-size: 16px;"><?= $online_total ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!-- End Online Collections Card -->

                                        <!-- Provider Payouts Card -->
                                        <div class="col-xxl-3 col-md-3">
                                            <div class="card info-card sales-card">
                                                <div class="card-body">
                                                    <h6 style="font-size: 18px !important; margin: 20px 0; color: #444">Service Provider Payouts</h6>

                                                    <div class="d-flex align-items-center">
                                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                            <img src="../assets/img/icons8-withdrawal-96.png" style="width: 48px;" alt="">
                                                        </div>
                                                        <div class="ps-3">
                                                            <h5><span class="small">GH</span>&#162;<span><?= number_format($provider_amount) ?></span></h5>
                                                            <span class="text-muted pt-1">COUNT: </span>
                                                            <span class="pt-2 ps-1" style="font-size: 16px;"><?= $provider_total ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!-- End Provider Payouts Card -->

                                    </div>
                                </div>

                                <!-- Form Types -->
                                <div class="form-types">
                                    <div class="row">

                                        <?php foreach ($summary["form-types"] as $form) { ?>
                                            <!-- Masters Card -->
                                            <div class="col-xxl-3 col-md-3">
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
                                                                    <span class="small">GH</span>&#162;<span class="small"><?= $form["total_amount"] ? number_format($form["total_amount"]) : "0.00" ?></span>
                                                                </h5>
                                                                <span class="text-muted small">AMOUNT</span>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div><!-- End Masters Card -->
                                        <?php } ?>

                                    </div>
                                </div>

                                <div class="payment-methods">

                                </div>

                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div><!-- End Transactions Summary row -->

            <div class="row">
                <div class="col-12">
                    <div class="card recent-sales overflow-auto">

                        <div class="card-body">
                            <h5 class="card-title">Daily Transactions</h5>
                            <p>Filter transactions by: </p>
                            <!-- Left side columns -->
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
                                                <option value="<?= $value["id"] ?>"><?= $value["info"] ?></option>
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

                                    <div class="col-2 col-md-2 col-sm-12 mt-2">
                                        <label for="payment-method" class="form-label">Payment Method</label>
                                        <select name="payment-method" id="payment-method" class="form-select">
                                            <option value="" hidden>Choose</option>
                                            <option value="All">All</option>
                                            <option value="CARD">CARD</option>
                                            <option value="CASH">CASH</option>
                                            <option value="MOMO">MOMO</option>
                                            <option value="USSD">USSD</option>
                                        </select>
                                    </div>

                                </div>
                            </form>

                            <div class="mt-4" style="display: flex; justify-content: space-between">
                                <h4>Total: <span id="totalData"></span></h4>
                                <div id="alert-output"></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div><!-- End Transactions fitering row -->

            <!-- Transactions Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card recent-sales overflow-auto">

                        <div class="filter">
                            <span style="margin-right: 0 !important" class="icon download-file" id="excelFileDownload" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Export as Excel file">
                                <img src="../assets/img/icons8-microsoft-excel-2019-48.png" alt="Download as Excel file" style="cursor:pointer;width: 24px;">
                            </span>
                            <span style="margin-left: 0 !important" class="icon download-pdf" id="main" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Download as PDF file">
                                <img src="../assets/img/icons8-pdf-48.png" alt="Download as PDF file" style="width: 24px;cursor:pointer;">
                            </span>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title">Transactions</h5>

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
                                            <th scope="col">Payment Method</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
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
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                        <div style="display: flex; justify-content: space-between;">
                                            <input disabled type="text" class="form-control _textD" id="p-status" style="width: 95%; border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important">
                                            <input disabled type="text" class="form-control _textD" id="p-statusColor" style="width: 5%; border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important; border: none !important">
                                        </div>
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
                        <div class="modal-footer">
                            <div style="width:100% !important; display:flex; justify-content: space-between">
                                <ul>
                                    <li>Verify transaction status <label for="verifyTransIDBtn" class="btn btn-primary btn-xs forVerifyTransIDBtn">Verify</label></li>
                                    <li>Resend application login info <label for="sendTransIDBtn" class="btn btn-success btn-xs forSendTransIDBtn">Resend</label></li>
                                    <li>Generate and send new application login info <label for="genSendTransIDBtn" class="btn btn-warning btn-xs forGenSendTransIDBtn">Generate and send</label></li>
                                </ul>
                                <form id="genSendPurchaseInfoForm" method="post" style="display: none;">
                                    <button type="submit" id="genSendTransIDBtn">Generate and send new application login info</button>
                                    <input type="hidden" name="genSendTransID" id="genSendTransID" value="">
                                </form>
                                <form id="sendPurchaseInfoForm" method="post" style="display: none; float: right;">
                                    <button type="submit" id="sendTransIDBtn">Resend application login info</button>
                                    <input type="hidden" name="sendTransID" id="sendTransID" value="">
                                </form>
                                <form id="verifyTransactionStatusForm" method="post" style="display: none; float: right;">
                                    <button type="submit" id="verifyTransIDBtn">Verify transaction status</button>
                                    <input type="hidden" name="verifyTransID" id="verifyTransID" value="">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchase info Modal -->
            <div class="modal fade" id="smsCustomerModal" tabindex="-1" aria-labelledby="smsCustomerModal" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="smsCustomerModalTitle">SMS Customer</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-dismissible infoFeed" style="display:none" role="alert">
                                <span id="smsStatusMsg"></span>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <div class="mb-3">
                                <label for="p-name" class="form-label">Recipient: <span id="sms-recipient"></span></label>
                                <textarea name="sms-message" id="sms-message" cols="30" rows="3" class="form-control" placeholder="Type mesage..."></textarea>
                            </div>
                            <button type="button" id="smsCustomerBtn" class="btn btn-success">Send</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right side columns -->
            <!-- End Right side columns -->

        </section>

    </main><!-- End #main -->

    <?= require_once("../inc/footer-section.php") ?>
    <script src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@2.1.7/dist/loadingoverlay.min.js"></script>
    <script>
        $(document).ready(function() {

            var triggeredBy = 0;

            // when 
            $(".form-select, .form-control").change("blur", function(e) {
                e.preventDefault();
                $("#reportsForm").submit();
            });

            $(document).on("click", ".download-pdf", function() {
                d = $(this).attr("id");
                window.open("../download-pdf-file.php?w=pdfFileDownload&p=daily-transactions&t=" + d, "_blank");
            });

            $("#reportsForm").on("submit", function(e, d) {
                e.preventDefault();
                triggeredBy = 1;

                $.ajax({
                    type: "POST",
                    url: "../endpoint/salesReport",
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
                                    '<td>' + value.paymentMethod + '</td>' +
                                    '<td style="display: flex; justify-content: space-around">' +
                                    '<button id="' + value.id + '" class="btn btn-xs btn-primary openPurchaseInfo" data-bs-toggle="modal" data-bs-target="#purchaseInfoModal" title="View details"><span class="bi bi-eye"></span></button>' +
                                    '<button id="' + value.id + '" class="btn btn-xs btn-success openSmsCustomer" data-bs-toggle="modal" data-bs-target="#smsCustomerModal" data-phonenumber="' + value.phoneNumber + '" title="Send SMS"><span class="bi bi-send"></span></button>' +
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
                            $("tbody").html("<tr style='text-align: center'><td colspan='10'>" + result.message + "</td></tr>");
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
                            $("#verifyTransID").val(result.message[0].transID);
                            $("#sendTransID").val(result.message[0].transID);
                            $("#genSendTransID").val(result.message[0].transID);
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
                        if (result.message == "logout") {
                            window.location.href = "?logout=true";
                            return;
                        }

                        $("#msgContent").text(result.message);
                        if (result.success) {
                            $(".infoFeed").removeClass("alert-danger").addClass("alert-success");
                            $(".infoFeed").fadeIn(1000).fadeOut(500);
                        } else {
                            $(".infoFeed").removeClass("alert-success").addClass("alert-danger");
                            $(".infoFeed").fadeIn(1000).fadeOut(500);
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
                        if (result.message == "logout") {
                            window.location.href = "?logout=true";
                            return;
                        }

                        $("#msgContent").text(result.message);
                        if (result.success) {
                            $(".infoFeed").removeClass("alert-danger").addClass("alert-success");
                            $(".infoFeed").fadeIn(1000).fadeOut(500);
                        } else {
                            $(".infoFeed").removeClass("alert-success").addClass("alert-danger");
                            $(".infoFeed").fadeIn(1000).fadeOut(500);
                        }

                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            $("#verifyTransactionStatusForm").on("submit", function(e) {
                e.preventDefault();
                triggeredBy = 5;
                let formData = new FormData(this);
                formData.append("payMethod", $("#p-payM").val());

                $.ajax({
                    type: "POST",
                    url: "../endpoint/verify-transaction-status",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(result) {
                        console.log(result);
                        if (result.message == "logout") {
                            window.location.href = "?logout=true";
                            return;
                        }

                        if (result.success) {
                            if (result.message == "COMPLETED") document.querySelector("#p-statusColor").setAttribute("class", "form-control _textD bg-success");
                            if (result.message == "PENDING") document.querySelector("#p-statusColor").setAttribute("class", "form-control _textD bg-warning");
                            if (result.message == "FAILED") document.querySelector("#p-statusColor").setAttribute("class", "form-control _textD bg-danger");
                        } else {
                            alert(result.message)
                        }

                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            $(document).on("click", ".openSmsCustomer", function() {
                $("#sms-recipient").text(this.dataset.phonenumber);
            });

            $("#smsCustomerBtn").on("click", function() {
                data = {
                    recipient: $("#sms-recipient").text(),
                    message: $("#sms-message").val()
                }

                $.ajax({
                    type: "POST",
                    url: "../endpoint/sms-customer",
                    data: data,
                    success: function(result) {
                        console.log(result);

                        if (result.message == "logout") {
                            window.location.href = "?logout=true";
                            return;
                        }

                        alert(result.message);
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            $(document).on("click", ".download-file", function() {
                let data = {
                    actionType: $(this).attr("id")
                }
                $("#reportsForm").trigger("submit", $(this).attr("id"));
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

            $(document).on({
                ajaxStart: function() {
                    if (triggeredBy = 3) $(".forGenSendTransIDBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Processing...');
                    else if (triggeredBy = 4) $(".forSendTransIDBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Sending...');
                    else if (triggeredBy = 5) $(".forVerifyTransIDBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Verifying...');
                    else $.LoadingOverlay("show");
                },
                ajaxStop: function() {
                    if (triggeredBy = 3) $(".forGenSendTransIDBtn").prop("disabled", false).html('Generate and send');
                    else if (triggeredBy = 4) $(".forSendTransIDBtn").prop("disabled", false).html('Resend');
                    else if (triggeredBy = 5) $(".forVerifyTransIDBtn").prop("disabled", false).html('Verify');
                    else $.LoadingOverlay("hide");
                }
            });

        });
    </script>

    <script>
        $(document).on({
            ajaxStart: function() {
                $.LoadingOverlay("show");
            },
            ajaxStop: function() {
                $.LoadingOverlay("hide");
            }
        });
    </script>

</body>

</html>
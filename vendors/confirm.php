<?php
session_start();

if (!isset($_GET['status']) || !isset($_GET['exttrid'])) header('Location: index.php?status=invalid');
if (empty($_GET['status']) || empty($_GET['exttrid'])) header('Location: index.php?status=invalid');

if (isset($_SESSION["adminLogSuccess"]) && $_SESSION["adminLogSuccess"] == true && isset($_SESSION["user"]) && !empty($_SESSION["user"])) {
} else {
    header("Location: ../index.php");
}

if (isset($_SESSION["vendor_id"]) && !empty($_SESSION["vendor_id"]))
    $trans_id = isset($_GET["exttrid"]) ? $_GET["exttrid"] : "";
else header("Location: index.php");

if (isset($_GET['logout']) || strtolower($_SESSION["role"]) != "vendors") {
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

use Src\Controller\ExposeDataController;

$expose = new ExposeDataController();

$data = isset($_GET["exttrid"]) ? $expose->getApplicationInfo($_GET["exttrid"]) : "";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?= require_once("../inc/head.php") ?>
    <style>
        .hide {
            display: none;
        }

        .display {
            display: block;
        }

        #wrapper {
            display: flex;
            flex-direction: column;
            flex-wrap: wrap;
            justify-content: space-between;
            width: 100% !important;
            height: 100% !important;
        }

        .flex-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .flex-container>div {
            height: 100% !important;
            width: 100% !important;
        }

        .flex-column {
            display: flex !important;
            flex-direction: column !important;
        }

        .flex-row {
            display: flex !important;
            flex-direction: row !important;
        }

        .justify-center {
            justify-content: center !important;
        }

        .justify-space-between {
            justify-content: space-between !important;
        }

        .align-items-center {
            align-items: center !important;
        }

        .align-items-baseline {
            align-items: baseline !important;
        }

        .flex-card {
            display: flex !important;
            justify-content: center !important;
            flex-direction: row !important;
        }

        .form-card {
            height: 100% !important;
            max-width: 425px !important;
            padding: 15px 10px 20px 10px !important;
        }

        .flex-card>.form-card {
            height: 100% !important;
            width: 100% !important;
        }

        .purchase-card-header {
            padding: 0 !important;
            width: 100% !important;
            height: 40px !important;
        }

        .purchase-card-header>h1 {
            font-size: 22px !important;
            font-weight: 600 !important;
            color: #003262 !important;
            text-align: center;
            width: 100%;
        }

        .purchase-card-step-info {
            color: #003262;
            padding: 0px;
            font-size: 14px;
            font-weight: 400;
            width: 100%;
        }

        .purchase-card-footer {
            width: 100% !important;
        }
    </style>
</head>

<body>
    <?= require_once("../inc/header.php") ?>

    <?= require_once("../inc/sidebar.php") ?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Forms Sale</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Sell Forms</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="flex-card">
                <div class="form-card card" style="max-width: 800px !important;">

                    <div class="purchase-card-header flex-row">
                        <h1>Applicant Receipt</h1>
                        <b><span class="bi bi-x-lg me-5 text-danger" style="cursor: pointer;" onclick="window.location.href = 'sell.php'"></span></b>
                    </div>

                    <hr style="color:#999">

                    <div class="purchase-card-body">
                        <div class="pay-status" style="margin: 0px 5%;" style="align-items: baseline;">
                            <?php if (!empty($data)) { ?>
                                <table style="width:100%;border: 1px solid rgb(155, 155, 155); border-collapse: collapse;" class="mb-4">
                                    <tr>
                                        <td style="width: 120px; background: #f1f1f1;text-align: right; padding: 5px; font-size: 11px;"><b>VENDOR:</b></td>
                                        <td colspan="2" style="text-align: left; padding: 5px; font-size: 11px;"><b><?= $data[0]["company"] ?></b></td>
                                    </tr>
                                    <tr>
                                        <td style="background: #f1f1f1;text-align: right; padding: 5px; font-size: 11px;"><b>PRICE:</b></td>
                                        <td style="text-align: left; padding: 5px; font-size: 11px;"><b><?= $data[0]["amount"] ?></b></td>
                                    </tr>
                                    <tr>
                                        <td style="background: #f1f1f1;text-align: right; padding: 5px; font-size: 11px;"><b>APPLICATION NO:</b></td>
                                        <td style="text-align: left; padding: 5px; font-size: 11px;"><b><?= "RMU-" . $data[0]["app_number"] ?></b></td>
                                    </tr>
                                    <tr>
                                        <td style="background: #f1f1f1;text-align: right; padding: 5px; font-size: 11px;"><b>PIN NO:</b></td>
                                        <td style="text-align: left; padding: 5px; font-size: 11px;"><b><?= $data[0]["pin_number"] ?></b></td>
                                    </tr>
                                    <tr style="border-top: 1px solid rgb(155, 155, 155)">
                                        <td style="background: #f1f1f1;text-align: right; padding: 5px; font-size: 11px; padding-top:30px">INSTITUTION:</td>
                                        <td style="text-align: left; padding: 5px; font-size: 11px;"><b>REGIONAL MARITIME UNIVERSITY</b></td>
                                    </tr>
                                    <tr>
                                        <td style="background: #f1f1f1;text-align: right; padding: 5px; font-size: 11px">FORM NAME:</td>
                                        <td style="text-align: left; padding: 5px; font-size: 11px;"><b><?= $data[0]["info"] . " - " . strtoupper($data[0]["name"]) ?></b></td>
                                    </tr>
                                </table>
                                <center>
                                    <button class="btn btn-primary" id="printReciptBtn"><b>Print</b></button>
                                </center>
                            <?php } else { ?>
                                <div style="width: 100%;height: 100%; text-align:center">No Data available</div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main><!-- End #main -->

    <?= require_once("../inc/footer-section.php") ?>
    <script>
        $(document).ready(function() {
            //get variable(parameters) from url
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

            $("#printReciptBtn").click(function() {
                window.open("print-form.php?exttrid=" + getUrlVars()["exttrid"], "_blank", "width=800,height=600");
            });
        });
    </script>

</body>

</html>
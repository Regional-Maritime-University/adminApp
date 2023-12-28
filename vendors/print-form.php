<?php
session_start();

if (!isset($_GET['exttrid']) || empty($_GET['exttrid'])) header('Location: index.php?msg=Invalid request');
if (!isset($_SESSION["adminLogSuccess"]) || $_SESSION["adminLogSuccess"] == false || !isset($_SESSION["user"]) || empty($_SESSION["user"])) {
    header("Location: ../index.php");
}

if (!isset($_SESSION["vendor_id"]) || empty($_SESSION["vendor_id"])) header("Location: index.php?msg=Access denied!");

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

use Src\Controller\ExposeDataController;

$expose = new ExposeDataController();

$data = $expose->getApplicationInfo($_GET["exttrid"]);
$vendor_info = $admin->fetchFullName($_SESSION["user"]);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <style>
        body {
            /*was 000*/
            font-family: "Ubuntu", sans-serif !important;
            font-weight: 300;
            -webkit-overflow-scrolling: touch;
            overflow: auto;
            line-height: 1;
            color: #282828 !important;
            font-size: 12px !important;
            font-weight: 400;
        }

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

        .purchase-card-footer {
            width: 100% !important;
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500&family=Roboto+Mono:wght@700&family=Ubuntu:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap/css/bootstrap.css">
</head>

<body class="container-fluid">

    <div class="row" style="padding: 0px 0px; margin-top: 25px">
        <div class="col-7">
            <div class="flex-row" style="justify-content: felt; align-items: left;">
                <img src="../assets/img/rmu-logo.png" style="width: 50px; height: 50px" alt="">
                <div class="flex-column">
                    <h5 style="font-weight: 600; font-size: 16px !important">Regional Maritime University</h5>
                    <h5 style="font-size: 16px !important">Form Sale System</h5>
                </div>
            </div>
        </div>
        <div class="col-5" style="display: flex; justify-content: right; align-items: center;">
            <h6 style="float:right; font-size: 16px !important">Receipt No.: <b>RMUHF<?= $data[0]["id"] ?></b></h6>
        </div>
    </div>

    <div class="purchase-card-body">
        <div class="pay-status" style="align-items: baseline; margin-top: 10px">
            <?php if (!empty($data)) { ?>
                <table style=" width:100%;border-collapse: collapse; border: 1px solid #222" class="mb-4">
                    <tr>
                        <td style="width: 150px; background: #f1f1f1;text-align: right; padding: 5px;"><b>CENTER/BRANCH:</b></td>
                        <td colspan="2" style="text-align: left; padding: 5px;"><b><?= $data[0]["company"] . " " . $data[0]["branch"] ?></b></td>
                    </tr>
                    <tr>
                        <td style="background: #f1f1f1;text-align: right; padding: 5px;"><b>PRICE:</b></td>
                        <td style="text-align: left; padding: 5px;"><b><?= $data[0]["amount"] ?></b></td>
                    </tr>
                    <tr>
                        <td style="background: #f1f1f1;text-align: right; padding: 5px;"><b>APPLICATION NO:</b></td>
                        <td style="text-align: left; padding: 5px;"><b><?= "RMU-" . $data[0]["app_number"] ?></b></td>
                    </tr>
                    <tr>
                        <td style="background: #f1f1f1;text-align: right; padding: 5px;"><b>PIN NO:</b></td>
                        <td style="text-align: left; padding: 5px;"><b><?= $data[0]["pin_number"] ?></b></td>
                    </tr>
                    <tr>
                        <td style="background: #f1f1f1;text-align: right; padding: 5px;"><b>Date Issued:</b></td>
                        <td style="text-align: left; padding: 5px;"><b><?= date("jS F, Y") . " - " . date("h:i:s A") ?></b></td>
                    </tr>
                    <tr style="border-top: 1px solid #222">
                        <td style="background: #f1f1f1;text-align: right; padding: 5px;">Institution:</td>
                        <td style="text-align: left; padding: 5px;"><b>REGIONAL MARITIME UNIVERSITY</b></td>
                    </tr>
                    <tr>
                        <td style="background: #f1f1f1;text-align: right; padding: 5px;">Form Name:</td>
                        <td style="text-align: left; padding: 5px;"><b><?= strtoupper($data[0]["info"] . " - " . strtoupper($data[0]["name"])) ?></b></td>
                    </tr>
                    <tr>
                        <td style="background: #f1f1f1;text-align: right; padding: 5px;">Received From:</td>
                        <td style="text-align: left; padding: 5px;"><b><?= strtoupper($data[0]["first_name"] . " " . $data[0]["last_name"]) ?></b></td>
                    </tr>
                    <tr>
                        <td style="background: #f1f1f1;text-align: right; padding: 5px;">Tel:</td>
                        <td style="text-align: left; padding: 5px;"><b><?= strtoupper($data[0]["phone_number"]) ?></b></td>
                    </tr>
                    <tr style="border-top: 1px solid #222">
                        <td style="padding: 5px;" colspan="2">
                            <ol style="line-height: 2;">
                                <li>Go to <b>https://admissions.rmuictonline.com</b> on the internet after you purchase the voucher.</li>
                                <li>Carefully read the <b>EASY STEPS TO APPLY</b> instructions on the login page before login in.</li>
                                <li>Log in to the online Admissiosns System with the e-voucher <b>Application Number</b> and <b>PIN</b>.</li>
                                <li>Follow the steps on the Online Admissions system to complete your application.</li>
                            </ol>
                        </td>
                    </tr>
                </table>
            <?php } ?>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            window.print();
            window.close();
        });
    </script>

</body>

</html>
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

$vendor_id = isset($_SESSION["vendor_id"]) ? (int) $_SESSION["vendor_id"] : "";
$vendorData = $admin->fetchVendor($vendor_id);
if (!empty($vendorData) && !empty($vendorData[0]["api_user"])) {
    $_SESSION["api_user"] = $vendorData[0]["api_user"];
}

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
            <h1>Dashboard</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">

                <!-- Left side columns -->
                <div class="col-lg-12">
                    <div class="row">

                        <?php if (isset($_SESSION["api_user"])) { ?>
                            <!-- Applications Card -->
                            <div class="col-xxl-3 col-md-3">
                                <a href="manageapis.php">
                                    <div class="card info-card sales-card">
                                        <div class="card-body">
                                            <h5 class="card-title">API Keys</h5>
                                            <div class="d-flex align-items-center">
                                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                    <img src="../assets/img/icons8-rest-api-96.png" style="width: 48px;" alt="">
                                                </div>
                                                <div class="ps-3">
                                                    <span class="text-muted small pt-2 ps-1">Manage your API keys</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div><!-- End Applications Card -->

                        <?php } else { ?>

                            <!-- Applications Card -->
                            <div class="col-xxl-3 col-md-3">
                                <a href="sell.php">
                                    <div class="card info-card sales-card">
                                        <div class="card-body">
                                            <h5 class="card-title">Sell Form</h5>
                                            <div class="d-flex align-items-center">
                                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                    <img src="../assets/img/icons8-sell-48.png" style="width: 48px;" alt="">
                                                </div>
                                                <div class="ps-3">
                                                    <span class="text-muted small pt-2 ps-1">forms</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div><!-- End Applications Card -->

                        <?php } ?>

                        <!-- Applications Card -->
                        <div class="col-xxl-3 col-md-3">
                            <a href="stats.php">
                                <div class="card info-card sales-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Transaction & Reports</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <img src="../assets/img/icons8-stocks-growth-96.png" style="width: 48px;" alt="">
                                            </div>
                                            <div class="ps-3">
                                                <span class="text-muted small pt-2 ps-1">See transactions stats and reports</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div><!-- End Applications Card -->

                    </div>
                </div><!-- Forms Sales Card  -->

            </div><!-- End Left side columns -->

            <!-- Right side columns -->
            <!-- End Right side columns -->

        </section>

    </main><!-- End #main -->

    <?= require_once("../inc/footer-section.php") ?>

</body>

</html>
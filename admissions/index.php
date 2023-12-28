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

$adminSetup = true;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?= require_once("../inc/head.php") ?>
    <style>
        .arrow {
            display: inline-block;
            margin-left: 10px;
        }
    </style>
</head>

<body>
    <?= require_once("../inc/header.php") ?>

    <?= require_once("../inc/sidebar.php") ?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Applications</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class=" section dashboard">

            <!-- Dashboard view -->
            <div class="row" <?= isset($_GET["a"]) && isset($_GET["s"]) ? 'style="display:none"' : "" ?>>

                <!-- Left side columns -->
                <div class="col-lg-12">
                    <div class="row">

                        <?php //var_dump($admin->fetchTotalAppsByProgCodeAndAdmisPeriod('MSC', 0)[0]["total"]) 
                        ?>
                        <!-- Applications Card -->
                        <div class="col-xxl-3 col-md-3">
                            <div class="card info-card sales-card">
                                <div class="card-body">
                                    <a href="applications.php?t=1&c=MASTERS">
                                        <h5 class="card-title">MASTERS</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <img src="../assets/img/icons8-masters.png" style="width: 48px;" alt="">
                                            </div>
                                            <div class="ps-3">
                                                <h6><?= $admin->fetchTotalApplicationsForMastersUpgraders($_SESSION["admin_period"], "MASTERS")[0]["total"]; ?></h6>
                                                <span class="text-muted small pt-2 ps-1">Applications</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div><!-- End Applications Card -->

                        <!-- Applications Card -->
                        <div class="col-xxl-3 col-md-3">
                            <div class="card info-card sales-card">
                                <div class="card-body">
                                    <a href="applications.php?t=1&c=UPGRADERS">
                                        <h5 class="card-title">UPGRADERS</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <img src="../assets/img/icons8-captain.png" style="width: 48px;" alt="">
                                            </div>
                                            <div class="ps-3">
                                                <h6><?= $admin->fetchTotalApplicationsForMastersUpgraders($_SESSION["admin_period"], "UPGRADERS")[0]["total"]; ?></h6>
                                                <span class="text-muted small pt-2 ps-1">Applications</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div><!-- End Applications Card -->

                        <?php
                        $form_types = $admin->getAvailableFormsExceptType(1);
                        foreach ($form_types as $form_type) {
                        ?>
                            <!-- Applications Card -->
                            <div class="col-xxl-3 col-md-3">
                                <div class="card info-card sales-card">
                                    <div class="card-body">
                                        <a href="applications.php?t=<?= $form_type["id"] ?>&c=<?= $form_type["name"] ?>">
                                            <h5 class="card-title"><?= $form_type["name"] ?></h5>
                                            <div class="d-flex align-items-center">
                                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                    <img src="../assets/img/icons8-<?= ucfirst(strtolower($form_type["name"])) ?>.png" style="width: 48px;" alt="">
                                                </div>
                                                <div class="ps-3">
                                                    <h6><?= $admin->fetchTotalApplications($_SESSION["admin_period"], $form_type["id"])[0]["total"]; ?></h6>
                                                    <span class="text-muted small pt-2 ps-1">Applications</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div><!-- End Applications Card -->
                        <?php
                        }
                        ?>

                        <!-- Admitted Students Card -->
                        <div class="col-xxl-3 col-md-3">
                            <div class="card info-card text-success">
                                <div class="card-body">
                                    <a href="awaiting-results.php" style="text-decoration: none;">
                                        <h5 class="card-title">Awaiting Results - WASSCE</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <img src="../assets/img/icons8-queue-64.png" style="width: 48px;" alt="">
                                            </div>
                                            <div class="ps-3">
                                                <h6><?= $admin->fetchTotalAwaitingResults($_SESSION["admin_period"])[0]["total"]; ?></h6>
                                                <span class="text-muted small pt-2 ps-1"> applications</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- End Admitted Students Card -->

                        <!-- Broadsheets Card -->
                        <div class="col-xxl-3 col-md-3">
                            <div class="card info-card">
                                <div class="card-body">
                                    <a href="admit-applicants.php" style="text-decoration: none;">
                                        <h5 class="card-title">Admit WAEC Applicants</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <img src="../assets/img/icons8-checked-user-male-96.png" style="width: 48px;" alt="">
                                            </div>
                                            <div class="ps-3">
                                                <span class="text-muted small pt-2 ps-1">Admit (WASSCE, SSSCE, GCBE, NECO) applicants</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div><!-- End Broadsheets Card -->

                        <!-- Admitted Applicants -->
                        <div class="col-xxl-3 col-md-3">
                            <div class="card info-card">
                                <div class="card-body">
                                    <a href="admitted-applicants.php" style="text-decoration: none;">
                                        <h5 class="card-title">Admitted Applicants</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <img src="../assets/img/icons8-users-96.png" style="width: 48px;" alt="">
                                            </div>
                                            <div class="ps-3">
                                                <span class="text-muted small pt-2 ps-1">List of all admitted applicants</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div><!-- End Admitted Applicants -->

                        <!-- Admitted Applicants -->
                        <div class="col-xxl-3 col-md-3">
                            <div class="card info-card">
                                <div class="card-body">
                                    <a href="declined-applicants.php" style="text-decoration: none;">
                                        <h5 class="card-title">Declined Applicants</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <img src="../assets/img/icons8-users-96.png" style="width: 48px;" alt="">
                                            </div>
                                            <div class="ps-3">
                                                <span class="text-muted small pt-2 ps-1">List of all declined applicants</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div><!-- End Admitted Applicants -->

                        <!-- Admitted Students Card -->
                        <div class="col-xxl-3 col-md-3">
                            <div class="card info-card text-success">
                                <div class="card-body">
                                    <a href="broadsheet.php" style="text-decoration: none;">
                                        <h5 class="card-title">Broadsheet</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <img src="../assets/img/icons8-documents-96.png" style="width: 48px;" alt="">
                                            </div>
                                            <div class="ps-3">
                                                <span class="text-muted small pt-2 ps-1">Download broadsheets</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- End Admitted Students Card -->

                        <!-- Applications Card -->
                        <div class="col-xxl-3 col-md-3">
                            <div class="card info-card sales-card">
                                <div class="card-body">
                                    <a href="settings.php">
                                        <h5 class="card-title">Settings</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <img src="../assets/img/icons8-services-96.png" style="width: 48px;" alt="">
                                            </div>
                                            <div class="ps-3">
                                                <span class="text-muted small pt-2 ps-1">General settings</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div><!-- End Applications Card -->

                    </div>
                </div><!-- Forms Sales Card  -->

            </div> <!-- End of Dashboard view -->
        </section>

    </main><!-- End #main -->

    <?= require_once("../inc/footer-section.php") ?>
    <script src="../js/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
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
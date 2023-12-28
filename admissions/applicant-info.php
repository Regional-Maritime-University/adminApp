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
if (!isset($_GET['t']) || empty($_GET['t'])) header("Location: index.php");
if (!isset($_GET['q']) || empty($_GET['q'])) header("Location: applications.php?t={$_GET['t']}");

$_SESSION["lastAccessed"] = time();

require_once('../bootstrap.php');
require_once('../inc/page-data.php');

use Src\Controller\AdminController;
use Src\Controller\UsersController;

$admin = new AdminController();
$user = new UsersController();

$photo = $user->fetchApplicantPhoto($_GET['q']);
$personal = $user->fetchApplicantPersI($_GET['q']);

$pre_uni_rec = $user->fetchApplicantPreUni($_GET['q']);
$academic_BG = $user->fetchApplicantAcaB($_GET['q']);
$app_type = $user->getApplicationType($_GET['q']);

$personal_AB = $user->fetchApplicantProgI($_GET['q']);
$about_us = $user->fetchHowYouKnowUs($_GET['q']);

$uploads = $user->fetchUploadedDocs($_GET['q']);

$form_name = $admin->getFormTypeName($_GET["t"]);
$app_number = $admin->getApplicantAppNum($_GET["q"]);

$admin->updateApplicationStatus($_GET["q"], 'reviewed', 1);

$app_statuses = $admin->fetchApplicationStatus($_GET['q']);
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

        .edu-history {
            width: 100% !important;
            /*height: 120px !important;*/
            background-color: #fff !important;
            border: 1px solid #ccc !important;
            border-radius: 5px !important;
            display: flex !important;
            flex-direction: column !important;
        }

        .edu-history-header {
            width: 100% !important;
            height: 84px !important;
            background-color: #fff !important;
            display: flex !important;
            flex-direction: row !important;
            justify-content: space-between !important;
        }

        .edu-history-header-info {
            width: 80% !important;
            height: 100% !important;
            padding: 10px 20px !important;
        }

        .edu-history-control {
            height: 50px !important;
            background-color: #e6e6e6 !important;
            display: flex !important;
            flex-direction: row !important;
            justify-content: space-between !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .edu-history-footer {
            width: 100% !important;
            height: 100% !important;
            background-color: #ffffb3 !important;
            margin: 0 !important;
            padding: 0 !important;
            display: flex !important;
            flex-direction: row !important;
            padding: 6px 20px !important;
        }

        .photo-display {
            width: 170px !important;
            height: 170px !important;
            min-width: 150px !important;
            min-height: 150px !important;
            /*background: red;*/
            border-radius: 5px;
            border: 1px solid #aaa;
            background: #f1f1f1;
            padding: 5px;
        }

        .photo-display>img {
            width: 100% !important;
        }

        .photo-display>img {
            width: 100% !important;
            height: 100% !important;
            border-radius: 5px;
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
                    <li class="breadcrumb-item"><a href="applications.php?t=<?= $_GET["t"] ?>&c=<?= $_GET["c"] ?>"><?= $form_name[0]["name"] ?></a></li>
                    <li class="breadcrumb-item active"><?= $app_number[0]["app_number"] ?></li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">

                <div class="col-2">
                    <div class="card recent-sales overflow-auto" style="display: flex; justify-content:center; align-items:center; padding: 32px 0px">
                        <div class="photo-display">
                            <img id="app-photo" src="<?= 'https://admissions.rmuictonline.com/apply/photos/' . $personal[0]["photo"] ?>" alt="">
                        </div>
                    </div>
                </div>

                <div class="col-10">
                    <div class="card recent-sales overflow-auto">
                        <div class="card-body" style="padding-top: 15px;padding-bottom: 0 !important; margin-bottom: 0 !important;">

                            <div class="row">
                                <div class="col-6">
                                    <table class="table table-borderless " style="display: flex; justify-content: flex-start; background-color:#e6e6e6; padding: 15px 15px; border-radius:15px">
                                        <tr>
                                            <td style="width: 200px; padding: 4px 8px !important"><b>NAME: </b> </td>
                                            <td style="width: 200px; padding: 4px 8px !important"><?= $personal[0]["first_name"] ?> <?= $personal[0]["middle_name"] ?> <?= $personal[0]["last_name"] ?></td>
                                        </tr>
                                        <tr>
                                            <td style="width: 200px; padding: 4px 8px !important"><b>NATIONAL OF:</b> </td>
                                            <td style="width: 200px; padding: 4px 8px !important"><?= $personal[0]["nationality"] ?></td>
                                        </tr>
                                        <tr>
                                            <td style="width: 200px; padding: 4px 8px !important"><b>APPLICATION MODE:</b> </td>
                                            <td style="width: 200px; padding: 4px 8px !important"><?= $academic_BG[0]["cert_type"] == "OTHER" ? $academic_BG[0]["other_cert_type"] : $academic_BG[0]["cert_type"] ?></td>
                                        </tr>
                                        <tr>
                                            <td style="width: 200px; padding: 4px 8px !important"><b>1<sup>ST</sup> CHOICE PROGRAM:</b> </td>
                                            <td style="width: 200px; padding: 4px 8px !important"><?= $personal_AB[0]["first_prog"] ?></td>
                                        </tr>
                                        <tr>
                                            <td style="width: 200px; padding: 4px 8px !important"><b>TERM APPLIED:</b></td>
                                            <td style="width: 200px; padding: 4px 8px !important"><?= $personal_AB[0]["application_term"] ?></td>
                                        </tr>
                                        <tr>
                                            <td style="width: 200px; padding: 4px 8px !important"><b>STREAM APPLIED:</b></td>
                                            <td style="width: 200px; padding: 4px 8px !important"><?= $personal_AB[0]["study_stream"] ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-6">
                                    <div style="display: flex; flex-direction:column; justify-content: space-between; height: 100%; padding-bottom:15px">
                                        <div style="display: flex; justify-content: space-between; background-color:#e6e6e6; padding: 15px; padding-bottom: 0 !important; border-radius:15px">
                                            <table class="table table-borderless" style="flex-grow: 8;">
                                                <tr>
                                                    <td style="width: 100px; padding: 4px 8px !important"><b>APP. STATUS: </b> </td>
                                                    <td style="padding: 4px 8px !important">
                                                        <?php
                                                        if (!empty($app_statuses)) {
                                                            if ($app_statuses[0]["declined"]) { ?>
                                                                <span class="badge rounded-pill text-bg-danger">DECLINED</span>
                                                            <?php } else if ($app_statuses[0]["admitted"]) { ?>
                                                                <span class="badge rounded-pill text-bg-success">ADMITTED</span>
                                                            <?php } else if ($app_statuses[0]["reviewed"]) { ?>
                                                                <span class="badge rounded-pill text-bg-warning">REVIEWED</span>
                                                            <?php } else if ($app_statuses[0]["reviewed"]) { ?>
                                                                <span class="badge rounded-pill text-bg-primary">ENROLLED</span>
                                                        <?php }
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 100px; padding: 4px 8px !important"><b>PROGRAMME:</b> </td>
                                                    <td style="padding: 4px 8px !important">
                                                        <?php
                                                        if (!empty($app_statuses)) {
                                                            if ($app_statuses[0]["programme_awarded"]) { ?>
                                                                <span><?= $admin->fetchAllFromProgramByID($app_statuses[0]["programme_awarded"])[0]["name"] ?></span>
                                                            <?php } else { ?>
                                                                <span>N/A</span>
                                                        <?php }
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            </table>
                                            <div style="flex-grow: 4;">
                                                <div style="display: flex; flex-direction:column; justify-content: flex-start;">
                                                    <form method="post" style="width:100px;" id="enrollAppForm">
                                                        <button class="btn btn-outline-success btn-xs" id="enroll-app-check" style="width:100%;" type="submit">
                                                            <span class="bi bi-check2-square"></span> <b id="enrollAppBtn-text">Enroll</b>
                                                        </button>
                                                        <input type="hidden" name="app-login" id="app-login" value="<?= $personal_AB[0]["app_login"] ?>">
                                                        <input type="hidden" name="app-prog" value="<?= !empty($app_statuses[0]["programme_awarded"]) ? $app_statuses[0]["programme_awarded"] : 0  ?>">
                                                    </form>
                                                    <form method="post" style="width:100px; margin-top: 10px" id="sendFilesForm">
                                                        <input type="file" name="send-files" id="send-files" multiple style="display: none;">
                                                        <label class="btn btn-outline-dark btn-xs" id="send-files-check" style="width:100%" for="send-files">
                                                            <span class="bi bi-file-text"></span> <b id="sendBtn-text">Send Files</b>
                                                        </label>
                                                        <input type="hidden" name="app-login" id="app-login" value="<?= $personal_AB[0]["app_login"] ?>">
                                                        <input type="hidden" name="programme-awarded" id="programme-awarded" value="<?= !empty($app_statuses[0]["programme_awarded"]) ? $app_statuses[0]["programme_awarded"] : 0  ?>">
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="display: flex; justify-content: flex-end; padding: 15px; border-radius:15px">
                                            <a style="width: 100px" class="btn btn-primary btn-sm" target="_blank" href="../download-appData.php?<?= "t=" . $_GET["t"] . "&q=" . $_GET["q"] ?>">
                                                <span class="bi bi-printer"></span> <b>Print</b>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Sales -->
            <div class="col-12">

                <div class="card recent-sales overflow-auto">

                    <div class="card-body" style="padding-top: 10px;">

                        <div class="row">
                            <div class="col-6" style="border-right: 1px solid #ccc;">
                                <div class="col">
                                    <div style="display: flex;">
                                        <div style="display: flex; flex-direction: column">
                                            <div class="col">
                                                <h3>Personal Information</h3>
                                                <div>
                                                    <p>
                                                        <span><b>Name: </b> </span>
                                                        <span><?= $personal[0]["first_name"] ?> <?= $personal[0]["middle_name"] ?> <?= $personal[0]["last_name"] ?></span>
                                                    </p>
                                                </div>
                                                <div>
                                                    <p>
                                                        <span><b>Gender: </b> </span>
                                                        <span><?= $personal[0]["gender"] ?></span>
                                                    </p>
                                                </div>
                                                <div>
                                                    <p>
                                                        <span><b>Date of Birth: </b> </span>
                                                        <span><?= $personal[0]["dob"] ?></span>
                                                    </p>
                                                </div>
                                                <div>
                                                    <p>
                                                        <span><b>Marital Status:</b> </span>
                                                        <span><?= $personal[0]["marital_status"] ?></span>
                                                    </p>
                                                </div>
                                                <div>
                                                    <p>
                                                        <span><b>Nationality:</b> </span>
                                                        <span><?= $personal[0]["nationality"] ?></span>
                                                    </p>
                                                </div>
                                                <div>
                                                    <p>
                                                        <span><b>Country of residence: </b> </span>
                                                        <span><?= $personal[0]["country_res"] ?></span>
                                                    </p>
                                                </div>
                                                <div>
                                                    <p>
                                                        <span><b>Disabled?: </b> </span>
                                                        <span><?= $personal[0]["disability"] ? "YES" : "NO" ?></span>
                                                        <span> <?= " - " . $personal[0]["disability_descript"] ?> </span>
                                                    </p>
                                                </div>
                                                <div>
                                                    <p>
                                                        <span><b>English Native?: </b> </span>
                                                        <span><?= $personal[0]["english_native"] ? "YES" : "NO" ?></span>
                                                        <span> - <?= $personal[0]["other_language"] ?></span>
                                                    </p>
                                                </div>
                                                <div>
                                                    <p>
                                                        <span><b>Address Line 1: </b> </span>
                                                        <span><?= $personal[0]["postal_addr"] ?> <?= $personal[0]["postal_town"] . ", " ?> <?= $personal[0]["postal_spr"] . " - " ?> <?= $personal[0]["postal_country"] ?></span>
                                                    </p>
                                                </div>
                                                <div>
                                                    <p>
                                                        <span><b>Primary phone number: </b> </span>
                                                        <span><?= $personal[0]["phone_no1_code"] ?> <?= $personal[0]["phone_no1"] ?></span>
                                                    </p>
                                                    <p>
                                                        <span><b>Whatsapp number: </b> </span>
                                                        <span><?= $personal[0]["phone_no2_code"] ?> <?= $personal[0]["phone_no2"] ?></span>
                                                    </p>
                                                    <p>
                                                        <span><b>Email address: </b> </span>
                                                        <span><?= $personal[0]["email_addr"] ?></span>
                                                    </p>
                                                </div>
                                            </div>


                                            <div class="col" style="margin-top: 25px">
                                                <h3>Guardian/Parent Information</h3>
                                                <div>
                                                    <p>
                                                        <span><b>Name: </b> </span>
                                                        <span><?= $personal[0]["p_first_name"] ?> <?= $personal[0]["p_last_name"] ?></span>
                                                    </p>
                                                </div>
                                                <div>
                                                    <p>
                                                        <span><b>Occupation: </b> </span>
                                                        <span><?= $personal[0]["p_occupation"] ?></span>
                                                    </p>
                                                </div>
                                                <div>
                                                    <p>
                                                        <span><b>Phone number: </b> </span>
                                                        <span><?= $personal[0]["p_phone_no_code"] ?> <?= $personal[0]["p_phone_no"] ?></span>
                                                    </p>
                                                    <p>
                                                        <span><b>Email address: </b> </span>
                                                        <span><?= $personal[0]["p_email_addr"] ?></span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="col-6">
                                <!-- Education Background -->
                                <div class="col">
                                    <h3>Education Background</h3>
                                    <div class="col mb-4">
                                        <h5 style="font-size: 16px;" class="form-label mt-4"><b>List of schools</b></h5>
                                        <div class="col">
                                            <?php
                                            if (!empty($academic_BG)) {
                                                foreach ($academic_BG as $edu_hist) {
                                            ?>
                                                    <div class="mb-4 edu-history" id="<?= $edu_hist["s_number"] ?>">
                                                        <div class="edu-history-header">
                                                            <div class="edu-history-header-info">
                                                                <p style="font-size: 16px; font-weight: 600;margin:0;padding:0">
                                                                    <?= htmlspecialchars_decode(html_entity_decode(ucwords(strtolower($edu_hist["school_name"])), ENT_QUOTES), ENT_QUOTES); ?>
                                                                    (<?= htmlspecialchars_decode(html_entity_decode(ucwords(strtolower($edu_hist["course_of_study"])), ENT_QUOTES), ENT_QUOTES); ?>)
                                                                </p>
                                                                <p style="color:#8c8c8c;margin:0;padding:0">
                                                                    <?= ucwords(strtolower($edu_hist["month_started"])) . " " . ucwords(strtolower($edu_hist["year_started"])) . " - " ?>
                                                                    <?= ucwords(strtolower($edu_hist["month_completed"])) . " " . ucwords(strtolower($edu_hist["year_completed"])) ?>
                                                                </p>
                                                            </div>
                                                            <div class="edu-history-control">
                                                                <button type="button" class="btn " name="edit-edu-btn" id="edit<?= $edu_hist["s_number"] ?>">
                                                                    <span class="bi bi-caret-down-fill edit-edu-btn" style="font-size: 20px !important;"></span>
                                                                </button>
                                                                <button type="button" class="btn edit-edu-btn" name="edit-edu-btn" id="edit<?= $edu_hist["s_number"] ?>" style="display: none">
                                                                    <span class="bi bi-caret-up-fill" style="font-size: 20px !important;"></span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="edu-history-footer">
                                                            <table>
                                                                <tbody>
                                                                    <tr>
                                                                        <th scope="row" style="width: 150px;">Country: </th>
                                                                        <td><?= $edu_hist["country"] ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Region: </th>
                                                                        <td><?= $edu_hist["region"] ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Certificate Type: </th>
                                                                        <td><?= $edu_hist["cert_type"] ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row">Awaiting Status: </th>
                                                                        <td><?= $edu_hist["awaiting_result"] ? "YES" : "NO" ?></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>

                                    <div class="col mb-4">
                                        <h5 style="font-size: 16px;" class="form-label mt-4"><b>List of documents</b></h5>
                                        <div class="certificates mb-4">
                                            <?php
                                            if (!empty($uploads)) {
                                            ?>
                                                <table class="table table-striped">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th scope="col">S/N</th>
                                                            <th scope="col">DOC TYPE</th>
                                                            <th scope="col">DOC NAME</th>
                                                            <th scope="col"> </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $ind = 1;
                                                        foreach ($uploads as $cert) {
                                                        ?>
                                                            <tr>
                                                                <th scope="row"><?= $ind ?></th>
                                                                <td><?= ucwords(strtoupper($cert["type"])) ?></td>
                                                                <td><?= (strtoupper($cert["type"]) == "OTHER" && count($academic_BG) == 1) ? ucwords(strtoupper($academic_BG[0]["other_cert_type"])) : ucwords(strtoupper($cert["type"]))  ?></td>
                                                                <td> <button type="button" style="cursor: pointer; float: right" class="btn btn-primary btn-sm open-file" data-doc="<?= $cert["file_name"] ?>" id="file-open-<?= $cert["id"] ?>" title="Open"><span class="bi bi-eye"></span></button></td>
                                                            </tr>
                                                        <?php
                                                            $ind += 1;
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>

                                </div>

                                <!-- Programmes -->
                                <div class="col">
                                    <h3>Programmes</h3>
                                    <div class="certificates mb-4">
                                        <h5 style="font-size: 16px;" class="form-label mt-4"><b>Programmes chosen by applicant</b></h5>
                                        <?php
                                        if (!empty($personal_AB)) {
                                        ?>
                                            <table class="table table-striped">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th scope="col">CHOICE</th>
                                                        <th scope="col">PROGRAMME</th>
                                                        <th scope="col"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>1<sup>st</sup></td>
                                                        <td><?= ucwords(strtoupper($personal_AB[0]["first_prog"])) ?></td>
                                                        <td>
                                                            <div class="form-check">
                                                                <input class="form-check-input app-prog-admit" style="cursor: pointer; float: right" type="radio" name="admit-prog" value="<?= $personal_AB[0]["first_prog"] ?>" data-prog="<?= $personal_AB[0]["first_prog"] ?>">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>2<sup>nd</sup></td>
                                                        <td><?= ucwords(strtoupper($personal_AB[0]["second_prog"])) ?></td>
                                                        <td>
                                                            <div class="form-check">
                                                                <input class="form-check-input app-prog-admit" style="cursor: pointer; float: right" type="radio" name="admit-prog" value="<?= $personal_AB[0]["second_prog"] ?>" data-prog="<?= $personal_AB[0]["second_prog"] ?>">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                            <?php
                                            $programData = $admin->fetchAllFromProgramByName($personal_AB[0]["first_prog"]);
                                            if ($programData[0]["type"] < 4) {
                                            ?>
                                                <h5 style="font-size: 16px;" class="form-label mt-4"><b>Award a different programme</b></h5>
                                                <div class="mb-4">
                                                    <label for="">Programme Type: </label>
                                                    <select name="choose-other-prog" id="choose-other-prog" class="form-select">
                                                        <option value="" hidden>Choose...</option>
                                                        <option value="1">MASTERS</option>
                                                        <option value="2">DEGREE</option>
                                                        <option value="3">DIPLOMA</option>
                                                    </select>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="form-label" for="admit-other-prog">Choose a programme <span class="input-required">*</span></label>
                                                    <select name="admit-other-prog" id="admit-other-prog" class="transform-text form-select mb-3">
                                                        <option hidden value="">Choose...</option>
                                                    </select>
                                                </div>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>

                                <div class="col" style="margin-top:100px">
                                    <div style="display: flex; justify-content: space-around">
                                        <form method="post" id="admit-applicant-form" class="mb-2">
                                            <input type="hidden" name="app-prog" id="app-prog">
                                            <input type="hidden" name="app-login" id="app-login" value="<?= $personal_AB[0]["app_login"] ?>">
                                            <button class="btn btn-success" type="submit" style="min-width: 200px;">Admit</button>
                                        </form>
                                        <form method="post" id="decline-applicant-form">
                                            <input type="hidden" name="app-login" value="<?= $personal_AB[0]["app_login"] ?>">
                                            <button class="btn btn-danger" type="submit" style="min-width: 200px;">Decline</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div><!-- End Recent Sales -->


            <!-- Right side columns -->
            <!-- End Right side columns -->

        </section>

        <!-- Documents display Modal -->
        <div class="modal fade" id="documentDisplay" tabindex="-1" aria-labelledby="addFormTypeLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="documentDisplayLabel">Form Type</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <iframe id="pdfFrame" src="" frameborder="0" style="width: 100%; height: 80vh"></iframe>
                    </div>
                </div>
            </div>
        </div>

    </main><!-- End #main -->

    <?= require_once("../inc/footer-section.php") ?>

    <script>
        // when 
        $(document).ready(function() {

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

            //Use a default value when param is missing
            function getUrlParam(parameter, defaultvalue) {
                var urlparameter = defaultvalue;
                if (window.location.href.indexOf(parameter) > -1) {
                    urlparameter = getUrlVars()[parameter];
                }
                return urlparameter;
            }

            if (getUrlVars()["status"] != "" || getUrlVars()["status"] != undefined) {
                if (getUrlVars()["exttrid"] != "" || getUrlVars()["exttrid"] != undefined) {}
            }

            $(".export-excel").click(function() {
                if (summary_selected !== "") {
                    data = {
                        "action": summary_selected,
                        "country": $("#country").val(),
                        "type": getUrlVars()["t"],
                        "program": $("#program").val(),
                    }
                    window.open("../export-excel.php?w=sdjgskfsd&a=hoh&c=jgkg&t=hjgkj&p=jgksjgks", "_blank");
                }
            });

            $(".download-pdf").click(function() {
                if (summary_selected !== "") {
                    data = {
                        "action": summary_selected,
                        "country": $("#country").val(),
                        "type": getUrlVars()["t"],
                        "program": $("#program").val(),
                    }
                    window.open("../download-pdf.php?w=apps&t=" + getUrlVars()["t"] + "&a=" + data["action"] + "&c=" + data["country"] + "&t=" + data["type"] + "&p=" + data["program"], "_blank");
                }
            });


            $(".app-prog-admit").on("click", function() {
                let prog = $(this).val();
                $("#app-prog").val(prog);
            });

            $("#admit-other-prog").change("blur", function() {
                let prog = $(this).val();
                $("#app-prog").val(prog);
            });

            $("#admit-applicant-form").on("submit", function(e) {
                e.preventDefault();
                var c = confirm("Are you sure you want to admit this applicant?");
                if (c) {
                    $.ajax({
                        type: "POST",
                        url: "../endpoint/admit-individual-applicant",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function(result) {
                            console.log(result);
                            if (result.message == "logout") window.location.href = "?logout=true";
                            else alert(result.message);
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                }
            });

            $("#decline-applicant-form").on("submit", function(e) {
                e.preventDefault();
                var c = confirm("Are you sure you want to decline this applicant's admission?");
                if (c) {
                    $.ajax({
                        type: "POST",
                        url: "../endpoint/decline-individual-applicant",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function(result) {
                            console.log(result);
                            if (result.message == "logout") window.location.href = "?logout=true";
                            else alert(result.message);
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                }
            });

            $(".open-file").on("click", function() {
                var pdfFrame = document.getElementById("pdfFrame");

                // Set the PDF file URL
                var pdfUrl = "https://admissions.rmuictonline.com/apply/docs/" + this.dataset.doc;

                // Set the PDF file URL as the iframe source
                pdfFrame.src = "https://docs.google.com/viewer?url=" + encodeURIComponent(pdfUrl) + "&embedded=true";
                $("#documentDisplay").modal("toggle");
            });

            $("#choose-other-prog").change("blur", function() {
                data = {
                    type: $(this).val()
                }

                $.ajax({
                    type: "GET",
                    url: "../endpoint/programs",
                    data: data,
                    success: function(result) {
                        console.log(result);
                        if (result.message == "logout") {
                            window.location.href = "?logout=true";
                            return;
                        } else {
                            $("#admit-other-prog").html("<option hidden value=''>Choose...</option>");
                            $.each(result.message, function(index, value) {
                                $("#admit-other-prog").append('<option value="' + value.name + '">' + value.name + '</option>');
                            });
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            $("#send-files-check").on("click", function() {
                if ($("#programme-awarded").val() != 0) $("#send-files").click();
                else alert("Sorry, this applicant has not been offered any admission!");
            });

            $("#send-files").change("blur", function() {
                $("#sendBtn-text").text("Sending...");
                $("#sendFilesForm").submit();
                setTimeout(function() {
                    $("#sendBtn-text").text("Send Files");
                }, 1000);
            });

            $("#sendFilesForm").on("submit", function(e) {
                e.preventDefault();
                $.ajax({
                    type: "POST",
                    url: "../endpoint/send-admission-files",
                    data: new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(result) {
                        console.log(result);
                        if (result.message == "logout") {
                            window.location.href = "?logout=true";
                            return;
                        } else {

                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            $("#enrollAppForm").on("submit", function(e) {
                e.preventDefault();
                $("#enrollAppBtn-text").text("Enrolling...");
                $.ajax({
                    type: "POST",
                    url: "../endpoint/enroll-applicant",
                    data: new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
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
                setTimeout(function() {
                    $("#sendBtn-text").text("Enroll");
                }, 1000);
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
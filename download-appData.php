<?php
session_start();

if (!isset($_SESSION["adminLogSuccess"]) || $_SESSION["adminLogSuccess"] == false || !isset($_SESSION["user"]) || empty($_SESSION["user"])) {
    header("Location: index.php");
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

    header('Location: index.php');
}
if (!isset($_GET['t']) || empty($_GET['t'])) header("Location: index.php");
if (!isset($_GET['q']) || empty($_GET['q'])) header("Location: applications.php?t={$_GET['t']}");
?>

<?php
require_once('bootstrap.php');

use Src\Controller\AdminController;
use Src\Controller\UsersController;

$admin = new AdminController();
$user = new UsersController();

$photo = $user->fetchApplicantPhoto($_GET['q']);
$personal = $user->fetchApplicantPersI($_GET['q']);
$appStatus = $user->getApplicationStatus($_GET['q']);

$pre_uni_rec = $user->fetchApplicantPreUni($_GET['q']);
$academic_BG = $user->fetchApplicantAcaB($_GET['q']);
$app_type = $user->getApplicationType($_GET['q']);

$personal_AB = $user->fetchApplicantProgI($_GET['q']);
$about_us = $user->fetchHowYouKnowUs($_GET['q']);

$uploads = $user->fetchUploadedDocs($_GET['q']);

$form_name = $admin->getFormTypeName($_GET["t"]);
$app_number = $admin->getApplicantAppNum($_GET["q"]);

$admin->updateApplicationStatus($_GET["q"], 'reviewed', 1);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="author" content="Francis A. Anlimah">
    <meta name="email" content="francis.ano.anlimah@gmail.com">
    <meta name="website" content="https://linkedin.com/in/francis-anlimah">
    <title>Dashboard - Admissions</title>

    <!-- Favicons -->
    <link href="assets/img/rmu-logo.png" rel="icon">
    <link href="assets/img/rmu-logo.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <!--<link href="https://fonts.gstatic.com" rel="preconnect">-->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/vendor/bootstrap-icons/bootstrap-icons.css">

    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .btn-group-xs>.btn,
        .btn-xs {
            padding: 1px 5px;
            font-size: 12px;
            line-height: 1.5;
            border-radius: 3px;
        }

        input.transform-text,
        select.transform-text,
        textarea.transform-text {
            text-transform: uppercase !important;
        }

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
            background-color: #fff !important;
            display: flex !important;
            flex-direction: row !important;
            justify-content: space-between !important;
        }

        .edu-history-header-info {
            width: 100% !important;
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
            display: flex !important;
            flex-direction: row !important;
            padding: 6px 20px !important;
        }

        .photo-display {
            width: 220px !important;
            height: 220px !important;
            min-width: 150px !important;
            min-height: 150px !important;
            /*background: red;*/
            border-radius: 5px;
            background: #f1f1f1;
            padding: 5px;
        }

        .photo-display>img {
            width: 100% !important;
            height: 100% !important;
        }
    </style>
</head>


<body class="container-fluid">

    <div class="row" style="padding: 0px 0px; margin-top: 25px">
        <div class="col-7">
            <div class="flex-row" style="justify-content: felt; align-items: left;">
                <img src="assets/img/rmu-logo.png" style="width: 50px; height: 50px" alt="">
                <div class="flex-column">
                    <h5 style="font-weight: 600; font-size: 16px !important">REGIONAL MARITIME UNIVERSITY</h5>
                    <h5 style="font-size: 16px !important">Online Application Form</h5>
                </div>
            </div>
        </div>
        <div class="col-5" style="display: flex; justify-content: right;">
            <p>
                <b>
                    The registrar <br>
                    Post Office Box GP1115 <br>
                    Accra - Ghana <br><br>

                    +233 302 712775 <br>
                    +233 302 718225 <br>
                    Email: registrar@rmu.edu.gh <br><br>
                </b>
                <span><?= date("M d, Y"); ?></span>
            </p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col" style="text-align: center;">
            <p style="font-weight: 600;">Keep this printout for any future enquiry</p>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <h6 style="font-size: 16px !important">
                Application Mode: <b><?= strtolower($academic_BG[0]["cert_type"]) == "other" ? $academic_BG[0]["other_cert_type"] : $academic_BG[0]["cert_type"] ?></b>
            </h6>
            <p>Note that your application would be considered under the above mode</p>
        </div>
        <div class="col">
            <h6 style="float:right; font-size: 16px !important">Form Type: <b><?= $form_name[0]["name"] ?></b></h6>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col">
            <h6 style="float:right; font-size: 16px !important">Application No.: <b><?= $app_number[0]["app_number"] ?></b></h6>
        </div>
    </div>

    <hr style="border: 1px dashed #000; padding-top: 0 !important; margin-top: 0 !important;">

    <div class="mb-4">
        <h6><b>Personal</b></h6>

        <fieldset style="width: 100%; border: 1px solid #aaa; padding: 10px 10px">

            <div class="row">
                <div class="col">
                    <p style="width: 100%; border-bottom: 1px solid #aaa; padding: 5px 0px"><b>Personal Information</b></p>
                    <div class="row">
                        <div class="col-7">
                            <table style=" width: 100%;" class="table table-borderless">
                                <tr>
                                    <td style="text-align: right">Title: </td>
                                    <td><b><?= $personal[0]["prefix"] ?></b></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">Name: </td>
                                    <td><b><?= $personal[0]["first_name"] ?> <?= $personal[0]["middle_name"] ?> <?= $personal[0]["last_name"] ?></b></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">Sex: </td>
                                    <td><b><?= $personal[0]["gender"] ?></b></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">Date of Birth: </td>
                                    <td><b><?= $personal[0]["dob"] ?></b></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">Marital Status: </td>
                                    <td><b><?= $personal[0]["marital_status"] ?></b></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">National of: </td>
                                    <td><b><?= $personal[0]["nationality"] ?></b></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">Country of residence: </td>
                                    <td><b><?= $personal[0]["country_res"] ?></b></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">Disabled?: </td>
                                    <td><b><?= $personal[0]["disability"] ? "YES" : "NO" ?> <?= $personal[0]["disability"] ? " - " . $personal[0]["disability_descript"] : "" ?></b></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">English Native?: </td>
                                    <td><b><?= $personal[0]["english_native"] ? "YES" : "NO" ?> <?= !$personal[0]["english_native"]  ? " - " . $personal[0]["other_language"] : "" ?></b></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-5">
                            <div class="photo-display">
                                <img id="app-photo" src="<?= 'https://admissions.rmuictonline.com/apply/photos/' . $personal[0]["photo"] ?>" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col">
                    <p style="width: 100%; border-bottom: 1px solid #aaa; padding: 5px 0px"><b>Contact Information</b></p>
                    <div class="row">
                        <div class="col">
                            <table class="table table-borderless">
                                <tr>
                                    <td style="text-align: right">Postal Address: </td>
                                    <td><b><?= $personal[0]["postal_addr"] ?></b></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">Postal Town: </td>
                                    <td><b><?= $personal[0]["postal_town"] ?></b></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">Postal Region/Province: </td>
                                    <td><b><?= $personal[0]["postal_spr"] ?></b></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">Postal Country: </td>
                                    <td><b><?= $personal[0]["postal_country"] ?></b></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col">
                            <table class="table table-borderless">
                                <tr>
                                    <td style="text-align: right">Primary phone number: </td>
                                    <td><b><?= $personal[0]["phone_no1_code"] ?> <?= $personal[0]["phone_no1"] ?></b></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">Secondary phone number: </td>
                                    <td><b><?= $personal[0]["phone_no2_code"] ?> <?= $personal[0]["phone_no2"] ?></b></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">Email address: </td>
                                    <td><b><?= $personal[0]["email_addr"] ?></b></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </fieldset>
    </div>

    <div class="mb-4">
        <h6><b>Parental</b></h6>
        <fieldset style="width: 100%; border: 1px solid #aaa; padding: 10px 10px">
            <div class="row">
                <div class="col">
                    <p style="width: 100%; border-bottom: 1px solid #aaa; padding: 5px 0px"><b>Guardian / Parent Information</b></p>
                    <div class="row">
                        <div class="col">
                            <table class="table table-borderless">
                                <tr>
                                    <td style="text-align: right">Name: </td>
                                    <td><b><?= $personal[0]["p_first_name"] ?> <?= $personal[0]["p_last_name"] ?></b></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">Occupation: </td>
                                    <td><b><?= $personal[0]["p_occupation"] ?></b></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col">
                            <table class="table table-borderless">
                                <tr>
                                    <td style="text-align: right">Phone number: </td>
                                    <td><b><?= $personal[0]["p_phone_no_code"] ?> <?= $personal[0]["p_phone_no"] ?></b></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">Email address: </td>
                                    <td><b><?= $personal[0]["p_email_addr"] ?></b></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>

    <!-- Education Background -->
    <div class="mb-4">
        <h6><b>Education Background</b></h6>

        <fieldset style="width: 100%; border: 1px solid #aaa; padding: 10px 10px">

            <div class="row">
                <div class="col">

                    <p style="width: 100%; border-bottom: 1px solid #aaa; padding: 5px 0px"><b>List of schools you have attended</b></p>

                    <div class="row">
                        <div class="col">
                            <?php
                            if (!empty($academic_BG)) {
                                foreach ($academic_BG as $edu_hist) {
                            ?>
                                    <div class="mb-4 edu-history" id="<?= $edu_hist["s_number"] ?>">
                                        <div class="edu-history-header">
                                            <div class="edu-history-header-info">
                                                <p style="font-size: 14px; font-weight: 600;margin:0;padding:0">
                                                    <?= htmlspecialchars_decode(html_entity_decode(ucwords(strtolower($edu_hist["school_name"])), ENT_QUOTES), ENT_QUOTES); ?>
                                                    (<?= strtolower($edu_hist["course_of_study"]) == "other" ? htmlspecialchars_decode(html_entity_decode(ucwords(strtolower($edu_hist["other_course_studied"])), ENT_QUOTES), ENT_QUOTES) : htmlspecialchars_decode(html_entity_decode(ucwords(strtolower($edu_hist["course_of_study"])))) ?>)
                                                </p>
                                                <p style="color:#000;margin:0;padding:0; margin-top:8px">
                                                    <?= ucwords(strtolower($edu_hist["month_started"])) . " " . ucwords(strtolower($edu_hist["year_started"])) . " - " ?>
                                                    <?= ucwords(strtolower($edu_hist["month_completed"])) . " " . ucwords(strtolower($edu_hist["year_completed"])) ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="edu-history-footer">
                                            <table class="col">
                                                <tr>
                                                    <td style="text-align: right">Country: </td>
                                                    <td><b><?= $edu_hist["country"] ?></b></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align: right">Region: </td>
                                                    <td><b><?= $edu_hist["region"] ?></b></td>
                                                </tr>
                                            </table>
                                            <table class="col">
                                                <tr>
                                                    <td style="text-align: right">Certificate Type: </td>
                                                    <td><b><?= strtolower($edu_hist["cert_type"]) == "other" ? $edu_hist["other_cert_type"] : $edu_hist["cert_type"] ?></b></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align: right">Awaiting Status: </td>
                                                    <td><b><?= $edu_hist["awaiting_result"] ? "YES" : "NO" ?></b></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>

    <!-- Programmes -->
    <div class="mb-4">
        <h6><b>University Enrollment Information</b></h6>

        <fieldset style="width: 100%; border: 1px solid #aaa; padding: 10px 10px">

            <div class="row">
                <div class="col">
                    <div class="mb-4" style="font-weight: 600;">
                        <p>Term Applied for: <span><b><?= $personal_AB[0]["application_term"] ?></b></span></p>
                        <p>Stream Applied for: <span><b><?= $personal_AB[0]["study_stream"] ?></b></span></p>
                    </div>
                    <div class="row">
                        <?php
                        if (!empty($personal_AB)) {
                        ?>
                            <div class="col-7">

                                <p style="width: 100%; border-bottom: 1px solid #aaa; padding: 5px 0px"><b>Programmes you have chosen to pursue</b></p>
                                <div class="certificates mb-4">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td style="text-align: right">First (1<sup>st</sup>) Choice: </td>
                                            <td><b><?= ucwords(strtoupper($personal_AB[0]["first_prog"])) ?></b></td>
                                        </tr>
                                        <tr style='<?= isset($personal_AB[0]["second_prog"]) && !empty($personal_AB[0]["second_prog"]) ? "none" : "block" ?>'>
                                            <td style="text-align: right">Second (2<sup>nd</sup>) Choice: </td>
                                            <td><b><?= ucwords(strtoupper($personal_AB[0]["second_prog"])) ?></b></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="col-5">
                                <p style="width: 100%; border-bottom: 1px solid #aaa; padding: 5px 0px"><b>Choices for hall of residence</b></p>
                                <table class="table table-borderless">
                                    <tr>
                                        <td style="text-align: right">First (1<sup>st</sup>) Choice: </td>
                                        <td><b><?= !empty($admin->fetchAllFromProgramByName($personal_AB[0]["first_prog"])[0]["cadet_hall"]) ? "CADET HOSTEL" : "NON-CADET HOSTEL" ?></b></td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: right">Second (2<sup>nd</sup>) Choice: </td>
                                        <td><b><?= !empty($admin->fetchAllFromProgramByName($personal_AB[0]["second_prog"])[0]["cadet_hall"]) ? "CADET HOSTEL" : "NON-CADET HOSTEL" ?></b></td>
                                    </tr>
                                </table>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>

        </fieldset>

    </div>

    <div class="mb-4">
        <fieldset style="width: 100%; border: 1px dashed #000;">

            <div class="row">
                <div class="col">
                    <div style="width: 100%; padding: 20px;">
                        <div style="width: 100%; background-color: #036; color: #fff; font-size: smaller; padding: 5px 10px; font-weight:700">
                            <b>DECLARATION</b>
                        </div>
                        <div style="align-items:center; margin-top: 10px">
                            <p>I
                                <label for="">
                                    <b><?= $personal[0]["first_name"] ?> <?= $personal[0]["middle_name"] ?> <?= $personal[0]["last_name"] ?> </b>
                                </label>, certify that the information provided above is valid and will be held personally responsible for its authenticity and will bear any consequences for any invalid information provided.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>

    <!-- Right side columns -->
    <!-- End Right side columns -->

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            window.print();
            //window.close();
        });
    </script>
</body>
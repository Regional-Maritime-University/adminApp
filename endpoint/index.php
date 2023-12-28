<?php
session_start();

if (!isset($_SESSION["lastAccessed"])) $_SESSION["lastAccessed"] = time();
$_SESSION["currentAccess"] = time();

$diff = $_SESSION["currentAccess"] - $_SESSION["lastAccessed"];

if ($diff >  1800) die(json_encode(array("success" => false, "message" => "logout")));

/*
* Designed and programmed by
* @Author: Francis A. Anlimah
*/

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require "../bootstrap.php";

use Src\Controller\AdminController;
use Src\Controller\DownloadExcelDataController;
use Src\Controller\DownloadAllExcelDataController;
use Src\Controller\UploadExcelDataController;
use Src\Controller\ExposeDataController;

$expose = new ExposeDataController();
$admin = new AdminController();

$data = [];
$errors = [];

// All GET request will be sent here
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if ($_GET["url"] == "programs") {
        if (isset($_GET["type"])) {
            $t = 0;
            if ($_GET["type"] != "All") {
                $t = (int) $_GET["type"];
            }
            $result = $admin->fetchPrograms($t);
            if (!empty($result)) {
                $data["success"] = true;
                $data["message"] = $result;
            } else {
                $data["success"] = false;
                $data["message"] = "No result found!";
            }
        }
        die(json_encode($data));
    } elseif ($_GET["url"] == "form-price") {
        if (!isset($_GET["form_key"]) || empty($_GET["form_key"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field")));
        }
        $rslt = $admin->fetchFormPrice($_GET["form_key"]);
        if (!$rslt) die(json_encode(array("success" => false, "message" => "Error fetching form price details!")));
        die(json_encode(array("success" => true, "message" => $rslt)));
    }
    //
    elseif ($_GET["url"] == "vendor-form") {
        if (!isset($_GET["vendor_key"]) || empty($_GET["vendor_key"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field")));
        }
        $rslt = $admin->fetchVendor($_GET["vendor_key"]);
        if (!$rslt) die(json_encode(array("success" => false, "message" => "Error fetching vendor details!")));
        die(json_encode(array("success" => true, "message" => $rslt)));
    }
    //
    elseif ($_GET["url"] == "prog-form") {
        if (!isset($_GET["prog_key"]) || empty($_GET["prog_key"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field")));
        }
        $rslt = $admin->fetchProgramme($_GET["prog_key"]);
        if (!$rslt) die(json_encode(array("success" => false, "message" => "Error fetching programme information!")));
        die(json_encode(array("success" => true, "message" => $rslt)));
    }
    //
    elseif ($_GET["url"] == "adp-form") {
        if (!isset($_GET["adp_key"]) || empty($_GET["adp_key"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field")));
        }
        $rslt = $admin->fetchAdmissionPeriod($_GET["adp_key"]);
        if (!$rslt) die(json_encode(array("success" => false, "message" => "Error fetching admissions information!")));
        die(json_encode(array("success" => true, "message" => $rslt)));
    }
    //
    elseif ($_GET["url"] == "user-form") {
        if (!isset($_GET["user_key"]) || empty($_GET["user_key"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field")));
        }
        $rslt = $admin->fetchSystemUser($_GET["user_key"]);
        if (!$rslt) die(json_encode(array("success" => false, "message" => "Error fetching user account information!")));
        die(json_encode(array("success" => true, "message" => $rslt)));
    }

    // All POST request will be sent here
} elseif ($_SERVER['REQUEST_METHOD'] == "POST") {
    if ($_GET["url"] == "admin-login") {

        if (!isset($_SESSION["_adminLogToken"]) || empty($_SESSION["_adminLogToken"]))
            die(json_encode(array("success" => false, "message" => "Invalid request: 1!")));
        if (!isset($_POST["_vALToken"]) || empty($_POST["_vALToken"]))
            die(json_encode(array("success" => false, "message" => "Invalid request: 2!")));
        if ($_POST["_vALToken"] !== $_SESSION["_adminLogToken"]) {
            die(json_encode(array("success" => false, "message" => "Invalid request: 3!")));
        }

        $username = $expose->validateText($_POST["username"]);
        $password = $expose->validatePassword($_POST["password"]);

        $result = $admin->verifyAdminLogin($username, $password);

        if (!$result) {
            $_SESSION['adminLogSuccess'] = false;
            die(json_encode(array("success" => false, "message" => "Incorrect application username or password! ")));
        }

        $_SESSION['user'] = $result[0]["id"];
        $_SESSION['role'] = $result[0]["role"];
        $_SESSION['user_type'] = $result[0]["type"];
        $_SESSION["admin_period"] = $expose->getCurrentAdmissionPeriodID();

        if (strtoupper($result[0]['role']) == "VENDORS") {
            $_SESSION["vendor_id"] = $expose->getVendorPhoneByUserID($_SESSION["user"])[0]["id"];
        }

        $_SESSION['adminLogSuccess'] = true;
        die(json_encode(array("success" => true,  "message" => strtolower($result[0]["role"]))));
    }

    // set admission period
    elseif ($_GET["url"] == "set-admission-period") {
        if (!isset($_POST["data"])) die(json_encode(array("success" => false, "message" => "Invalid request!")));
        if (empty($_POST["data"])) die(json_encode(array("success" => false, "message" => "Missing input in request!")));
        $_SESSION["admin_period"] = (int) $_POST["data"];
        die(json_encode(array("success" => true,  "message" => "Admisssion period changed!")));
    }

    // Resend verification code
    elseif ($_GET["url"] == "resend-code") {
        if (!isset($_POST["resend_code"])) die(json_encode(array("success" => false, "message" => "Invalid request!")));
        if (empty($_POST["resend_code"])) die(json_encode(array("success" => false, "message" => "Missing input!")));

        $code_type = $expose->validateText($_POST["resend_code"]);
        switch ($code_type) {
            case 'sms':
                // For vendor resend otp code
                if (isset($_SESSION["_verifySMSToken"]) && !empty($_SESSION["_verifySMSToken"]) && isset($_POST["_vSMSToken"]) && !empty($_POST["_vSMSToken"]) && $_POST["_vSMSToken"] == $_SESSION["_verifySMSToken"]) {

                    $vendorPhone = $expose->getVendorPhoneByUserID($_SESSION["user"]);

                    if (!empty($vendorPhone)) {
                        $response = $expose->sendOTP($vendorPhone[0]["phone_number"]);

                        if (isset($response["otp_code"])) {
                            $_SESSION['sms_code'] = $response["otp_code"];
                            $_SESSION['verifySMSCode'] = true;
                            $data["success"] = true;
                            $data["message"] = "Verification code sent!";
                        } else {
                            $data["success"] = false;
                            $data["message"] = $response["statusDescription"];
                        }
                    } else {
                        $data["success"] = false;
                        $data["message"] = "No phone number entry found for this user!";
                    }
                }

                // for user/applicant/online resend otp code
                else if (isset($_SESSION["_step5Token"]) && !empty($_SESSION["_step5Token"]) && isset($_POST["_v5Token"]) && !empty($_POST["_v5Token"]) && $_POST["_v5Token"] == $_SESSION["_step5Token"]) {

                    $to = $_SESSION["step4"]["country_code"] . $_SESSION["step4"]["phone_number"];
                    $response = $expose->sendOTP($to);

                    if (isset($response["otp_code"])) {
                        $_SESSION['sms_code'] = $response["otp_code"];
                        $data["success"] = true;
                        $data["message"] = "Verification code resent!";
                    } else {
                        $data["success"] = false;
                        $data["message"] = $response["statusDescription"];
                    }
                } else {
                    die(json_encode(array("success" => false, "message" => "Invalid OTP SMS request!")));
                }
                break;
        }
        die(json_encode($data));
    }

    // Get details on form
    elseif ($_GET["url"] == "formInfo") {
        if (!isset($_POST["form_id"]) || empty($_POST["form_id"])) {
            die(json_encode(array("success" => false, "message" => "Error: Form has not been set properly in database!")));
        }

        $form_id = $expose->validateInput($_POST["form_id"]);
        $result = $expose->getFormPriceA($form_id);

        if (empty($result)) die(json_encode(array("success" => false, "message" => "Forms' price has not set in the database!")));
        die(json_encode(array("success" => true, "message" => $result)));
    }

    //Vendor endpoint
    elseif ($_GET["url"] == "sellAction") {
        if (isset($_SESSION["_vendor1Token"]) && !empty($_SESSION["_vendor1Token"]) && isset($_POST["_v1Token"]) && !empty($_POST["_v1Token"]) && $_POST["_v1Token"] == $_SESSION["_vendor1Token"]) {

            if (!isset($_POST["first_name"]) || empty($_POST["first_name"])) {
                die(json_encode(array("success" => false, "message" => "Customer first name is required!")));
            }
            if (!isset($_POST["last_name"]) || empty($_POST["last_name"])) {
                die(json_encode(array("success" => false, "message" => "Customer last name is required!")));
            }
            if (!isset($_POST["formSold"]) || empty($_POST["formSold"])) {
                die(json_encode(array("success" => false, "message" => "Choose a type of form to sell!")));
            }
            if (!isset($_POST["country"]) || empty($_POST["country"])) {
                die(json_encode(array("success" => false, "message" => "Phone number's country code is required!")));
            }
            if (!isset($_POST["phone_number"]) || empty($_POST["phone_number"])) {
                die(json_encode(array("success" => false, "message" => "Customer's phone number is required!")));
            }

            $first_name = $expose->validateText($_POST["first_name"]);
            $last_name = $expose->validateText($_POST["last_name"]);
            $phone_number = $expose->validatePhone($_POST["phone_number"]);
            $country = $expose->validateCountryCode($_POST["country"]);
            $form_id = $expose->validateNumber($_POST["formSold"]);
            //$form_type = $expose->validateNumber($_POST["form_type"]);
            $form_price = $_POST["form_price"];

            $charPos = strpos($country, ")");
            $country_name = substr($country, ($charPos + 2));
            $country_code = substr($country, 1, ($charPos - 1));

            $_SESSION["vendorData"] = array(
                "first_name" => $first_name,
                "last_name" => $last_name,
                "country_name" => $country_name,
                "country_code" => $country_code,
                "phone_number" => $phone_number,
                "email_address" => "",
                "form_id" => $form_id,
                //"form_type" => $form_type,
                "pay_method" => "CASH",
                "amount" => $form_price,
                "vendor_id" => $_SESSION["vendor_id"],
                "admin_period" => $_SESSION["admin_period"]
            );

            if (!isset($_SESSION["vendorData"]) || empty($_SESSION["vendorData"]))
                die(json_encode(array("success" => false, "message" => "Failed in preparing data payload submitted!")));

            if (!$expose->vendorExist($_SESSION["vendorData"]["vendor_id"]))
                die(json_encode(array("success" => false, "message" => "Process can only be performed by a vendor!")));

            die(json_encode($admin->processVendorPay($_SESSION["vendorData"])));
        } else {
            die(json_encode(array("success" => false, "message" => "Invalid request!")));
        }
    }

    //
    elseif ($_GET["url"] == "apps-data") {
        if (!isset($_POST["action"]) || !isset($_POST["form_t"])) die(json_encode(array("success" => false, "message" => "Invalid input!")));
        if (empty($_POST["action"]) || empty($_POST["form_t"])) die(json_encode(array("success" => false, "message" => "Missing request!")));

        $v_action = $expose->validateText($_POST["action"]);
        $v_form_t = $expose->validateNumber($_POST["form_t"]);
        $data = array('action' => $v_action, 'country' => 'All', 'type' => $v_form_t, 'program' => 'All');
        $result = $admin->fetchAppsSummaryData($_SESSION["admin_period"], $data);
        if (empty($result)) die(json_encode(array("success" => false, "message" => "Empty result!")));
        die(json_encode(array("success" => true, "message" => $result)));
    }
    //
    elseif ($_GET["url"] == "applicants") {

        if (!isset($_POST["action"]) || !isset($_POST["country"]) || !isset($_POST["type"]) || !isset($_POST["program"])) {
            die(json_encode(array("success" => false, "message" => "Missing input!")));
        }
        if (empty($_POST["action"]) || empty($_POST["country"]) || empty($_POST["type"]) || empty($_POST["program"])) {
            die(json_encode(array("success" => false, "message" => "Missing input!")));
        }

        $result = $admin->fetchAppsSummaryData($_SESSION["admin_period"], $_POST);
        if (!empty($result)) {
            $data["success"] = true;
            $data["message"] = $result;
        } else {
            $data["success"] = false;
            $data["message"] = "No result found!";
        }
        die(json_encode($data));
    }

    //
    else if ($_GET["url"] == "checkPrintedDocument") {
        if (!isset($_POST["app"]) || empty($_POST["app"])) die(json_encode(array("success" => false, "message" => "Missing input!")));
        if (!empty($admin->updateApplicationStatus($_POST["app"], 'printed', 1))) die(json_encode(array("success" => true)));
        die(json_encode(array("success" => false, "message" => "Failed to updated printed status!")));
    }

    //
    elseif ($_GET["url"] == "getAllAdmittedApplicants") {

        if (!isset($_POST["cert-type"]))
            die(json_encode(array("success" => false, "message" => "Invalid input field")));
        if (empty($_POST["cert-type"]))
            die(json_encode(array("success" => false, "message" => "Missing input field")));

        $result = $admin->getAllAdmittedApplicantsAllAll($_POST["cert-type"]);
        if (empty($result)) die(json_encode(array("success" => false, "message" => "No result found!")));
        die(json_encode(array("success" => true, "message" => $result)));
    }

    //
    elseif ($_GET["url"] == "getAllDeclinedApplicants") {

        if (!isset($_POST["cert-type"]))
            die(json_encode(array("success" => false, "message" => "Invalid input field")));
        if (empty($_POST["cert-type"]))
            die(json_encode(array("success" => false, "message" => "Missing input field")));

        $result = $admin->getAllDeclinedApplicantsAllAll($_POST["cert-type"]);
        if (empty($result)) die(json_encode(array("success" => false, "message" => "No result found!")));
        die(json_encode(array("success" => true, "message" => $result)));
    }

    //
    elseif ($_GET["url"] == "getUnadmittedApps") {

        if (!isset($_POST["cert-type"]) || !isset($_POST["prog-type"])) {
            die(json_encode(array("success" => false, "message" => "Invalid input field")));
        }
        if (empty($_POST["cert-type"]) || empty($_POST["prog-type"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field")));
        }

        $result = $admin->fetchAllUnadmittedApplicantsData($_POST["cert-type"], $_POST["prog-type"]);

        if (empty($result)) {
            die(json_encode(array("success" => false, "message" => "No result found!")));
        }
        die(json_encode(array("success" => true, "message" => $result)));
    }
    //
    elseif ($_GET["url"] == "admitAll") {
        if (!isset($_POST["cert-type"]) || !isset($_POST["prog-type"])) {
            die(json_encode(array("success" => false, "message" => "Invalid input field")));
        }
        if (empty($_POST["cert-type"]) || empty($_POST["prog-type"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field")));
        }

        $result = $admin->admitQualifiedStudents($_POST["cert-type"], $_POST["prog-type"]);

        if (empty($result)) {
            die(json_encode(array("success" => false, "message" => "No result found!")));
        }
        die(json_encode(array("success" => true, "message" => $result)));
    }
    //
    elseif ($_GET["url"] == "downloadBS") {
        if (!isset($_POST["cert-type"]) || empty($_POST["cert-type"]))
            die(json_encode(array("success" => false, "message" => "Please choose a certificate type!")));
        $url = "https://office.rmuictonline.com/download-bs.php?a=bs&c=" . $_POST["cert-type"];
        die(json_encode(array("success" => true, "message" => $url)));
    }
    //
    elseif ($_GET["url"] == "getBroadsheetData") {

        if (!isset($_POST["cert-type"]) || empty($_POST["cert-type"]))
            die(json_encode(array("success" => false, "message" => "Please choose a certificate type!")));

        //$result = $admin->fetchAllAdmittedApplicantsData($_POST["cert-type"]);
        die(json_encode($admin->fetchAllSubmittedApplicantsData($_POST["cert-type"])));
    }
    //
    elseif ($_GET["url"] == "downloadAwaiting") {
        $url = "../download-awaiting-ds.php?a=as&c=awaiting&ap=" . $_SESSION['admin_period'];
        die(json_encode(array("success" => true, "message" => $url)));
    }
    //
    elseif ($_GET["url"] == "extra-awaiting-data") {

        if (!isset($_POST["action"]) || empty($_POST["action"])) {
            die(json_encode(array("success" => false, "message" => "Invalid request (1)!")));
        }

        $result;

        switch ($_POST["action"]) {
                // download broadsheet dbs
            case 'dbs':
                $broadsheet = new DownloadExcelDataController($_POST['c']);
                $file = $broadsheet->generateFile();
                $result = $broadsheet->downloadFile($file);
                break;

                // upload awaiting datasheet uad
            case 'uad':

                if (!isset($_FILES["awaiting-ds"]) || empty($_FILES["awaiting-ds"])) {
                    die(json_encode(array("success" => false, "message" => "Invalid request!")));
                }

                if ($_FILES["awaiting-ds"]['error']) {
                    die(json_encode(array("success" => false, "message" => "Failed to upload file!")));
                }

                $startRow = $expose->validateNumber($_POST['startRow']);
                $endRow = $expose->validateNumber($_POST['endRow']);

                $excelData = new UploadExcelDataController($_FILES["awaiting-ds"], $_POST['startRow'], $_POST['endRow']);
                $result = $excelData->extractAwaitingApplicantsResults();
                break;
        }

        die(json_encode($result));
    }

    ///
    elseif ($_GET["url"] == "form-price") {
        if (!isset($_POST["form_type"]) || !isset($_POST["form_price"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field")));
        }
        if (empty($_POST["form_type"]) || empty($_POST["form_price"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field")));
        }
        if (empty($_POST["form_name"]) || empty($_POST["form_name"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field")));
        }

        $result = [];

        switch ($_POST["action"]) {
            case 'add':
                $rslt = $admin->addFormPrice($_POST["form_type"], $_POST["form_name"], $_POST["form_price"]);
                if (!$rslt) {
                    die(json_encode(array("success" => false, "message" => "Failed to add price!")));
                }
                $result = array("success" => true, "message" => "Successfully added form price!");
                break;

            case 'update':
                $rslt = $admin->updateFormPrice($_POST["form_id"], $_POST["form_type"], $_POST["form_name"], $_POST["form_price"]);
                if (!$rslt) {
                    die(json_encode(array("success" => false, "message" => "Failed to update price!")));
                }
                $result = array("success" => true, "message" => "Successfully updated form price!");
                break;

            default:
                die(json_encode(array("success" => false, "message" => "Invalid action!")));
                break;
        }

        die(json_encode($result));
    }

    //
    elseif ($_GET["url"] == "vendor-sub-branches-group") {
        if (!isset($_POST["vendor_key"]) || empty($_POST["vendor_key"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field")));
        }
        $rslt = $admin->fetchVendorSubBranchesGrp($_POST["vendor_key"]);
        if (!$rslt) die(json_encode(array("success" => false, "message" => "Error fetching vendor details!")));
        die(json_encode(array("success" => true, "message" => $rslt)));
    }

    //
    elseif ($_GET["url"] == "vendor-sub-branches") {
        if (!isset($_POST["vendor_branch"]) || empty($_POST["vendor_branch"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field")));
        }
        $rslt = $admin->fetchVendorSubBranches($_POST["vendor_branch"]);
        if (!$rslt) die(json_encode(array("success" => false, "message" => "Error fetching vendor details!")));
        die(json_encode(array("success" => true, "message" => $rslt)));
    }
    //
    elseif ($_GET["url"] == "vendor-form") {

        if (!isset($_POST["v-action"]) || empty($_POST["v-action"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field: Ghana Card")));
        }
        if (!isset($_POST["v-name"]) || empty($_POST["v-name"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field: Vendor Name")));
        }
        if (!isset($_POST["v-code"]) || empty($_POST["v-code"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field: Company code")));
        }
        if (!isset($_POST["v-email"]) || empty($_POST["v-email"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field: Email Address")));
        }
        if (!isset($_POST["v-phone"]) || empty($_POST["v-phone"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field: Phone Number")));
        }
        if (!isset($_POST["v-api-user"]) || empty($_POST["v-api-user"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field: API User status")));
        }

        $user_data = array(
            "first_name" => $_POST["v-name"], "last_name" => "MAIN", "user_name" => $_POST["v-email"], "user_role" => "Vendors",
            "user_type" => "user", "vendor_company" => $_POST["v-name"], "company_code" => $_POST["v-code"], "vendor_role" => "Ops Head",
            "vendor_phone" => $_POST["v-phone"], "vendor_branch" => "MAIN", "api_user" => ($_POST["v-api-user"] == "YES" ? 1 : 0)
        );

        $privileges = array("select" => 1, "insert" => 1, "update" => 0, "delete" => 0);

        $result;
        switch ($_POST["v-action"]) {
            case 'add':
                $rslt = $admin->addSystemUser($user_data, $privileges);
                if (!$rslt["success"]) die(json_encode($rslt));
                $result = array("success" => true, "message" => "Successfully added vendor account!");
                break;

            case 'update':
                $rslt = $admin->updateVendor($_POST["v-id"], $_POST["v-email"], $_POST["v-phone"]);
                if (!$rslt["success"]) die(json_encode($rslt));
                $result = array("success" => true, "message" => "Successfully updated vendor account information!");
                break;
        }

        if (isset($_FILES["other-branches"]) && !empty($_FILES["other-branches"])) {
            if ($_FILES["other-branches"]['error']) {
                $result = array("success" => false, "message" => "Successfully {$_POST["v-action"]}ed vendor's account information");
            } else {
                $result = $admin->uploadCompanyBranchesData($_POST["v-name"], $_FILES["other-branches"]);
            }
        }

        die(json_encode($result));
    }
    //
    elseif ($_GET["url"] == "prog-form") {
        if (!isset($_POST["prog-name"]) || empty($_POST["prog-name"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field: Name")));
        }
        if (!isset($_POST["prog-type"]) || empty($_POST["prog-type"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field: Type")));
        }
        if (!isset($_POST["prog-wkd"]) || empty($_POST["prog-wkd"])) {
            $prog_wkd = "0";
        } else {
            $prog_wkd = "1";
        }
        if (!isset($_POST["prog-grp"]) || empty($_POST["prog-grp"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field: Group")));
        }

        $result;
        switch ($_POST["prog-action"]) {
            case 'add':
                $rslt = $admin->addProgramme($_POST["prog-name"], $_POST["prog-type"], $prog_wkd, $_POST["prog-grp"]);
                if (!$rslt) {
                    die(json_encode(array("success" => false, "message" => "Failed to add vendor!")));
                }
                $result = array("success" => true, "message" => "Successfully added vendor!");
                break;

            case 'update':
                $rslt = $admin->updateProgramme($_POST["prog-id"], $_POST["prog-name"], $_POST["prog-type"], $prog_wkd, $_POST["prog-grp"]);
                if (!$rslt) {
                    die(json_encode(array("success" => false, "message" => "Failed to update vendor information!")));
                }
                $result = array("success" => true, "message" => "Successfully updated vendor information!");
                break;
        }

        die(json_encode($result));
    }
    //
    elseif ($_GET["url"] == "adp-form-verify" && $_POST["adp-action"] == 'add') {
        if (!isset($_POST["adp-start"]) || empty($_POST["adp-start"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field: Start Date")));
        }
        if (!isset($_POST["adp-end"]) || empty($_POST["adp-end"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field: End Date")));
        }
        if (!isset($_POST["adp-desc"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field: Description")));
        }

        $desc = '';
        if (isset($_POST["adp-desc"]) && !empty($_POST["adp-desc"])) $desc = $_POST["adp-desc"];

        if ($admin->fetchCurrentAdmissionPeriod()) {
            die(json_encode(array(
                "success" => false,
                "message" => "An admission period is currently open! Do you want to still continue?"
            )));
        }
        die(json_encode(array("success" => true, "message" => "add")));
    }

    //
    elseif ($_GET["url"] == "adp-form") {
        if (!isset($_POST["adp-start"]) || empty($_POST["adp-start"]))
            die(json_encode(array("success" => false, "message" => "Missing input field: Start Date")));
        if (!isset($_POST["adp-end"]) || empty($_POST["adp-end"]))
            die(json_encode(array("success" => false, "message" => "Missing input field: End Date")));
        if (!isset($_POST["adp-intake"]) || empty($_POST["adp-intake"]))
            die(json_encode(array("success" => false, "message" => "Missing input field: Description")));
        if (!isset($_POST["adp-desc"]))
            die(json_encode(array("success" => false, "message" => "Missing input field: Description")));

        if (isset($_POST["adp-desc"]) && empty($_POST["adp-desc"])) $desc = '';

        $result;
        switch ($_POST["adp-action"]) {
            case 'add':
                $result = $admin->addAdmissionPeriod($_POST["adp-start"], $_POST["adp-end"], $_POST["adp-desc"], $_POST["adp-intake"]);
                break;
            case 'update':
                $result = $admin->updateAdmissionPeriod($_POST["adp-id"], $_POST["adp-start"], $_POST["adp-desc"]);
                break;
        }
        die(json_encode($result));
    }
    //
    elseif ($_GET["url"] == "user-form") {
        if (!isset($_POST["user-fname"]) || empty($_POST["user-fname"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field: First name")));
        }
        if (!isset($_POST["user-lname"]) || empty($_POST["user-lname"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field: Last name")));
        }
        if (!isset($_POST["user-email"]) || empty($_POST["user-email"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field: Email")));
        }
        if (!isset($_POST["user-role"]) || empty($_POST["user-role"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field: Role")));
        }
        if (!isset($_POST["user-type"]) || empty($_POST["user-type"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field: User Type")));
        }

        if ($_POST["user-role"] == "Vendors") {
            if (!isset($_POST["vendor-tin"]) || empty($_POST["vendor-tin"])) {
                die(json_encode(array("success" => false, "message" => "Missing input field: Ghana Card")));
            }
            if (!isset($_POST["vendor-phone"]) || empty($_POST["vendor-phone"])) {
                die(json_encode(array("success" => false, "message" => "Missing input field: Phone Number")));
            }
            if (!isset($_POST["vendor-company"]) || empty($_POST["vendor-company"])) {
                die(json_encode(array("success" => false, "message" => "Missing input field: Address")));
            }
            if (!isset($_POST["vendor-address"]) || empty($_POST["vendor-address"])) {
                die(json_encode(array("success" => false, "message" => "Missing input field: Address")));
            }
        }

        $user_data = array(
            "first_name" => $_POST["user-fname"], "last_name" => $_POST["user-lname"],
            "user_name" => $_POST["user-email"], "user_role" => $_POST["user-role"],
            "user_type" => $_POST["user-type"], "vendor_company" => $_POST["vendor-company"],
            "vendor_tin" => $_POST["vendor-tin"], "vendor_phone" => $_POST["vendor-phone"],
            "vendor_address" => $_POST["vendor-address"]
        );

        $privileges = array("select" => 1, "insert" => 0, "update" => 0, "delete" => 0);
        if (isset($_POST["privileges"]) && !empty($_POST["privileges"])) {
            foreach ($_POST["privileges"] as $privilege) {
                if ($privilege == "insert") $privileges["insert"] = 1;
                if ($privilege == "update") $privileges["update"] = 1;
                if ($privilege == "delete") $privileges["delete"] = 1;
            }
        }

        $result;
        switch ($_POST["user-action"]) {
            case 'add':
                $result = $admin->addSystemUser($user_data, $privileges);
                break;

            case 'update':
                $rslt = $admin->updateSystemUser($_POST, $privileges);
                if (!$rslt) {
                    die(json_encode(array("success" => false, "message" => "Failed to update admission information!")));
                }
                $result = array("success" => true, "message" => "Successfully updated admission information!");
                break;
        }

        die(json_encode($result));
    }

    // For sales report on accounts dashboard
    elseif ($_GET["url"] == "salesReport") {
        if (!isset($_POST["admission-period"])) die(json_encode(array("success" => false, "message" => "Invalid input request for admission period!")));
        if (!isset($_POST["from-date"])) die(json_encode(array("success" => false, "message" => "Invalid input request for from date!")));
        if (!isset($_POST["to-date"])) die(json_encode(array("success" => false, "message" => "Invalid input request for to date!")));
        if (!isset($_POST["form-type"])) die(json_encode(array("success" => false, "message" => "Invalid input request for form type!")));
        if (!isset($_POST["purchase-status"])) die(json_encode(array("success" => false, "message" => "Invalid input request for purchase status!")));
        if (!isset($_POST["payment-method"])) die(json_encode(array("success" => false, "message" => "Invalid input request for payment method!")));

        if ((!empty($_POST["from-date"]) && empty($_POST["to-date"])) || (!empty($_POST["to-date"]) && empty($_POST["from-date"])))
            die(json_encode(array("success" => false, "message" => "Date range (From - To) must be set!")));

        $result = $admin->fetchAllFormPurchases($_SESSION["admin_period"], $_POST);
        if (empty($result)) die(json_encode(array("success" => false, "message" => "No result found for given parameters!")));
        die(json_encode(array("success" => true, "message" => $result)));
    }

    // For sales report on vendor's dashboard
    elseif ($_GET["url"] == "vendorSalesReport") {
        if (!isset($_POST["admission-period"])) die(json_encode(array("success" => false, "message" => "Invalid input request for admission period!")));
        if (!isset($_POST["from-date"])) die(json_encode(array("success" => false, "message" => "Invalid input request for from date!")));
        if (!isset($_POST["to-date"])) die(json_encode(array("success" => false, "message" => "Invalid input request for to date!")));
        if (!isset($_POST["form-type"])) die(json_encode(array("success" => false, "message" => "Invalid input request for form type!")));
        if (!isset($_POST["purchase-status"])) die(json_encode(array("success" => false, "message" => "Invalid input request for purchase status!")));

        if ((!empty($_POST["from-date"]) && empty($_POST["to-date"])) || (!empty($_POST["to-date"]) && empty($_POST["from-date"])))
            die(json_encode(array("success" => false, "message" => "Date range (From - To) must be set!")));

        $_POST["vendor-id"] = $_SESSION["vendor_id"];

        $result = $admin->fetchAllVendorFormPurchases($_SESSION["admin_period"], $_POST);
        if (empty($result)) die(json_encode(array("success" => false, "message" => "No result found for given parameters!")));
        die(json_encode(array("success" => true, "message" => $result)));
    }

    //
    elseif ($_GET["url"] == "purchaseInfo") {
        if (!isset($_POST["_data"]) || empty($_POST["_data"]))
            die(json_encode(array("success" => false, "message" => "Invalid request!")));
        $transID = $expose->validateNumber($_POST["_data"]);
        $result = $admin->fetchFormPurchaseDetailsByTranID($transID);
        if (empty($result)) die(json_encode(array("success" => false, "message" => "No result found!")));
        die(json_encode(array("success" => true, "message" => $result)));
    }

    // send purchase info
    elseif ($_GET["url"] == "gen-send-purchase-info") {
        if (!isset($_POST["genSendTransID"]) || empty($_POST["genSendTransID"]))
            die(json_encode(array("success" => false, "message" => "Invalid request!")));
        $transID = $expose->validateNumber($_POST["genSendTransID"]);
        die(json_encode($admin->sendPurchaseInfo($transID)));
    }

    // send purchase info
    elseif ($_GET["url"] == "send-purchase-info") {
        if (!isset($_POST["sendTransID"]) || empty($_POST["sendTransID"]))
            die(json_encode(array("success" => false, "message" => "Invalid request!")));
        $transID = $expose->validateNumber($_POST["sendTransID"]);
        die(json_encode($admin->sendPurchaseInfo($transID, false)));
    }

    // send purchase info
    elseif ($_GET["url"] == "verify-transaction-status") {
        if (!isset($_POST["verifyTransID"]) || empty($_POST["verifyTransID"]))
            die(json_encode(array("success" => false, "message" => "Invalid request:  transaction!")));
        if (!isset($_POST["payMethod"]) || empty($_POST["payMethod"]))
            die(json_encode(array("success" => false, "message" => "Invalid request: payment method!")));
        $transID = $expose->validateNumber($_POST["verifyTransID"]);
        die(json_encode($admin->verifyTransactionStatus($_POST["payMethod"], $transID, false)));
    }

    // send an sms to customer
    elseif ($_GET["url"] == "sms-customer") {
        if (!isset($_POST["recipient"]) || empty($_POST["recipient"]))
            die(json_encode(array("success" => false, "message" => "No recipient!")));
        if (!isset($_POST["message"]) || empty($_POST["message"]))
            die(json_encode(array("success" => false, "message" => "No message typed!")));
        if (strlen($_POST["message"]) > 160)
            die(json_encode(array("success" => false, "message" => "Message is too long. Maximum allowed is 160 characters!")));

        // Send SMS message
        $to = str_replace(array("+", "(", ")", " "), "", $_POST["recipient"]);
        $response = json_decode($expose->sendSMS($to, $_POST["message"]));

        // Set SMS response status
        if (!$response->status) die(json_encode(array("success" => true, "message" => "Message sent successfully!")));
        die(json_encode(array("success" => true, "message" => "Failed to send message!")));
    }

    // fetch group sales data
    elseif ($_GET["url"] == "group-sales-report") {
        if (!isset($_POST["from-date"])) die(json_encode(array("success" => false, "message" => "Invalid input request for from date!")));
        if (!isset($_POST["to-date"])) die(json_encode(array("success" => false, "message" => "Invalid input request for to date!")));
        if (!isset($_POST["report-by"])) die(json_encode(array("success" => false, "message" => "Invalid input request for filter by!")));

        if ((!empty($_POST["from-date"]) && empty($_POST["to-date"])) || (!empty($_POST["to-date"]) && empty($_POST["from-date"])))
            die(json_encode(array("success" => false, "message" => "Date range (From - To) not set!")));

        $_data = $expose->validateText($_POST["report-by"]);
        $result = $admin->fetchFormPurchasesGroupReport($_POST);
        if (empty($result)) die(json_encode(array("success" => false, "message" => "No result found for given parameters!")));
        die(json_encode(array("success" => true, "message" => $result)));
    }

    // fetch group sales data
    elseif ($_GET["url"] == "group-sales-report-list") {
        if (!isset($_POST["_dataI"]) || empty($_POST["_dataI"])) die(json_encode(array("success" => false, "message" => "Invalid input request!")));
        if (!isset($_POST["from-date"])) die(json_encode(array("success" => false, "message" => "Invalid input request for from date!")));
        if (!isset($_POST["to-date"])) die(json_encode(array("success" => false, "message" => "Invalid input request for to date!")));
        if (!isset($_POST["report-by"])) die(json_encode(array("success" => false, "message" => "Invalid input request for filter by!")));

        if ((!empty($_POST["from-date"]) && empty($_POST["to-date"])) || (!empty($_POST["to-date"]) && empty($_POST["from-date"])))
            die(json_encode(array("success" => false, "message" => "Date range (From - To) not set!")));

        $_dataI = $expose->validateNumber($_POST["_dataI"]);
        $result = $admin->fetchFormPurchasesGroupReportInfo($_POST);
        if (empty($result)) die(json_encode(array("success" => false, "message" => "No result found for given parameters!")));
        die(json_encode(array("success" => true, "message" => $result)));
    }

    // download PDF
    elseif ($_GET["url"] == "download-file") {
        $result = $admin->prepareDownloadQuery($_POST);
        if (!$result) die(json_encode(array("success" => false, "message" => "Fatal error: server generated error!")));
        die(json_encode(array("success" => true, "message" => "successfully!")));
    } else if ($_GET["url"] == "general-download") {
    }

    // backup database
    elseif ($_GET["url"] == "backup-data") {
        $dbs = ["rmu_admissions"];
        $user = "root";
        $pass = "";
        $host = "localhost";

        if (!file_exists("../Backups")) mkdir("../Backups");

        foreach ($dbs as $db) {
            if (!file_exists("../Backups/$db")) mkdir("../Backups/$db");
            $file_name = $db . "_" . date("F_d_Y") . "@" . date("g_ia") . uniqid("_", false);
            $folder = "../Backups/$db/$file_name" . ".sql";
            $d = exec("mysqldump --user={$user} --password={$pass} --host={$host} {$db} --result-file={$folder}", $output);
            die(json_encode(array("success" => true, "message" => $output)));
        }
    }

    // reset password
    elseif ($_GET["url"] == "reset-password") {
        if (!isset($_POST["currentPassword"]) || empty($_POST["currentPassword"]))
            die(json_encode(array("success" => false, "message" => "Current password field is required!")));
        if (!isset($_POST["newPassword"]) || empty($_POST["newPassword"]))
            die(json_encode(array("success" => false, "message" => "New password field is required!")));
        if (!isset($_POST["renewPassword"]) || empty($_POST["renewPassword"]))
            die(json_encode(array("success" => false, "message" => "Retype new password field is required!")));

        $currentPass = $expose->validatePassword($_POST["currentPassword"]);
        $newPass = $expose->validatePassword($_POST["newPassword"]);
        $renewPass = $expose->validatePassword($_POST["renewPassword"]);

        if ($newPass !== $renewPass) die(json_encode(array("success" => false, "message" => "New password entry mismatched!")));

        $userDetails = $admin->verifySysUserExistsByID($_SESSION["user"]);
        if (empty($userDetails)) die(json_encode(array("success" => false, "message" => "Failed to verify user account!")));

        $result = $admin->verifyAdminLogin($userDetails[0]["user_name"], $currentPass);
        if (!$result) die(json_encode(array("success" => false, "message" => "Incorrect current password!")));

        $changePassword = $admin->resetUserPassword($_SESSION["user"], $newPass);
        die(json_encode($changePassword));
    }

    // admit an applicant to a particular programme and generate admission letter
    elseif ($_GET["url"] == "admit-individual-applicant") {
        if (!isset($_POST["app-prog"]) || empty($_POST["app-prog"]))
            die(json_encode(array("success" => false, "message" => "Please choose a programme!")));
        if (!isset($_POST["app-login"]) || empty($_POST["app-login"]))
            die(json_encode(array("success" => false, "message" => "There no match for this applicant in database!")));

        die(json_encode($admin->admitIndividualApplicant($_POST["app-login"], $_POST["app-prog"])));
    }

    // decline applicant admission
    elseif ($_GET["url"] == "decline-individual-applicant") {
        if (!isset($_POST["app-login"]) || empty($_POST["app-login"]))
            die(json_encode(array("success" => false, "message" => "There no match for this applicant in database!")));
        die(json_encode($admin->declineIndividualApplicant($_POST["app-login"])));
    }

    // Send admission letter to applicant
    elseif ($_GET["url"] == "send-admission-files") {
        if (!isset($_POST["app-login"]) || empty($_POST["app-login"]))
            die(json_encode(array("success" => false, "message" => "There no match for this applicant in database!")));
        if (!isset($_FILES["send-files"]) || empty($_FILES["send-files"]))
            die(json_encode(array("success" => false, "message" => "Invalid request!")));
        if ($_FILES["send-files"]['error'])
            die(json_encode(array("success" => false, "message" => "Failed to upload file!")));
        die(json_encode($admin->sendAdmissionFiles($_POST["app-login"], $_FILES["send-files"])));
    }

    // Enroll applicant
    elseif ($_GET["url"] == "enroll-applicant") {
        if (!isset($_POST["app-login"]) || empty($_POST["app-login"]))
            die(json_encode(array("success" => false, "message" => "There no match for this applicant in database!")));
        if (!isset($_POST["app-prog"]) || empty($_POST["app-prog"]))
            die(json_encode(array("success" => false, "message" => "Please choose a programme!")));
        die(json_encode($admin->enrollApplicant($_POST["app-login"], $_POST["app-prog"])));
    }

    //
    elseif ($_GET["url"] == "unenroll-applicant") {
        if (!isset($_POST["app-login"]) || empty($_POST["app-login"]))
            die(json_encode(array("success" => false, "message" => "There no match for this applicant in database!")));
        if ($admin->updateApplicationStatus($_POST["app-login"], "enrolled", 0)) die(json_encode(array("success" => true)));
        die(json_encode(array("success" => false, "message" => "Failed to updated enrollment status!")));
    }

    ///
    elseif ($_GET["url"] == "export-excel") {
        $t = new DownloadAllExcelDataController($_POST["action"]);
        $file = $t->generateFile();
        $t->downloadFile($file);
        die(json_encode(array("success" => true)));
    }

    //
    else if ($_GET["url"] == "generateNewAPIKeys") {
        if (!isset($_POST["__generateAPIKeys"]) || empty($_POST["__generateAPIKeys"]))
            die(json_encode(array("success" => false, "message" => "Invalid request received!")));

        die(json_encode($admin->generateAPIKeys($_SESSION["vendor_id"])));
    }

    // All PUT request will be sent here
} else if ($_SERVER['REQUEST_METHOD'] == "PUT") {
    parse_str(file_get_contents("php://input"), $_PUT);

    if ($_GET["url"] == "adp-form") {
        if (!isset($_PUT["adp_key"]) || empty($_PUT["adp_key"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field")));
        }

        $rslt = $admin->openOrCloseAdmissionPeriod($_PUT["adp_key"], 0);
        if (!$rslt) die(json_encode(array("success" => false, "message" => "Failed to delete programme!")));
        die(json_encode(array("success" => true, "message" => "Successfully deleted programme!")));
    }

    // All DELETE request will be sent here
} else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
    parse_str(file_get_contents("php://input"), $_DELETE);

    if ($_GET["url"] == "form-price") {
        if (!isset($_DELETE["form_key"]) || empty($_DELETE["form_key"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field")));
        }

        $rslt = $admin->deleteFormPrice($_DELETE["form_key"]);
        if (!$rslt) die(json_encode(array("success" => false, "message" => "Failed to delete form price!")));
        die(json_encode(array("success" => true, "message" => "Successfully deleted form price!")));
    }

    if ($_GET["url"] == "vendor-form") {
        if (!isset($_DELETE["vendor_key"]) || empty($_DELETE["vendor_key"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field")));
        }

        $rslt = $admin->deleteVendor($_DELETE["vendor_key"]);
        if (!$rslt) die(json_encode(array("success" => false, "message" => "Failed to delete form price!")));
        die(json_encode(array("success" => true, "message" => "Successfully deleted form price!")));
    }

    if ($_GET["url"] == "prog-form") {
        if (!isset($_DELETE["prog_key"]) || empty($_DELETE["prog_key"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field")));
        }

        $rslt = $admin->deleteProgramme($_DELETE["prog_key"]);
        if (!$rslt)  die(json_encode(array("success" => false, "message" => "Failed to delete programme!")));
        die(json_encode(array("success" => true, "message" => "Successfully deleted programme!")));
    }

    if ($_GET["url"] == "user-form") {
        if (!isset($_DELETE["user_key"]) || empty($_DELETE["user_key"])) {
            die(json_encode(array("success" => false, "message" => "Missing input field")));
        }

        $rslt = $admin->deleteSystemUser($_DELETE["user_key"]);
        if (!$rslt) die(json_encode(array("success" => false, "message" => "Failed to delete user account!")));
        die(json_encode(array("success" => true, "message" => "Successfully deleted user account!")));
    }
} else {
    http_response_code(405);
}

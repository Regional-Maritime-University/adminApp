<?php

namespace Src\Controller;

use Src\System\DatabaseMethods;
use Src\Controller\ExposeDataController;

class VoucherPurchase
{
    private $expose;
    private $dm;

    public function __construct()
    {
        $this->expose = new ExposeDataController();
        $this->dm = new DatabaseMethods();
    }

    public function logActivity(int $user_id, $operation, $description)
    {
        $query = "INSERT INTO `activity_logs`(`user_id`, `operation`, `description`) VALUES (:u,:o,:d)";
        $params = array(":u" => $user_id, ":o" => $operation, ":d" => $description);
        $this->dm->inputData($query, $params);
    }

    private function genPin(int $length_pin = 9)
    {
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle($str_result), 0, $length_pin);
    }

    private function genAppNumber(int $type, int $year)
    {
        $user_code = $this->expose->genCode(5);
        $app_number = ($type * 10000000) + ($year * 100000) + $user_code;
        return $app_number;
    }

    private function doesCodeExists($code)
    {
        $sql = "SELECT `id` FROM `applicants_login` WHERE `app_number`=:p";
        if ($this->dm->getID($sql, array(':p' => sha1($code)))) {
            return 1;
        }
        return 0;
    }

    private function saveVendorPurchaseData(int $ti, int $vd, int $fi, int $ap, $pm, float $am, $fn, $ln, $em, $cn, $cc, $pn)
    {
        $sql = "INSERT INTO `purchase_detail` (`id`, `vendor`, `form_id`, `admission_period`, `payment_method`, `amount`, `first_name`, `last_name`, `email_address`, `country_name`, `country_code`, `phone_number`) 
                VALUES(:ti, :vd, :fi, :ap, :pm, :am, :fn, :ln, :em, :cn, :cc, :pn)";
        $params = array(
            ':ti' => $ti, ':vd' => $vd, ':fi' => $fi, ':pm' => $pm, ':am' => $am, ':ap' => $ap,
            ':fn' => $fn, ':ln' => $ln, ':em' => $em, ':cn' => $cn, ':cc' => $cc, ':pn' => $pn
        );
        if ($this->dm->inputData($sql, $params)) return $ti;
        return 0;
    }

    private function updateVendorPurchaseData(int $trans_id, int $app_number, $pin_number, $status)
    {
        $sql = "UPDATE `purchase_detail` SET `app_number`= :a,`pin_number`= :p, `status` = :s WHERE `id` = :t";
        return $this->dm->getData($sql, array(':a' => $app_number, ':p' => $pin_number, ':s' => $status, ':t' => $trans_id));
    }

    private function registerApplicantPersI($user_id)
    {
        $sql = "INSERT INTO `personal_information` (`app_login`) VALUES(:a)";
        $this->dm->inputData($sql, array(':a' => $user_id));
    }

    private function registerApplicantProgI($user_id)
    {
        $sql = "INSERT INTO `program_info` (`app_login`) VALUES(:a)";
        $this->dm->inputData($sql, array(':a' => $user_id));
    }

    private function registerApplicantPreUni($user_id)
    {
        $sql = "INSERT INTO `previous_uni_records` (`app_login`) VALUES(:a)";
        $this->dm->inputData($sql, array(':a' => $user_id));
    }

    private function setFormSectionsChecks($user_id)
    {
        $sql = "INSERT INTO `form_sections_chek` (`app_login`) VALUES(:a)";
        $this->dm->inputData($sql, array(':a' => $user_id));
    }

    private function setHeardAboutUs($user_id)
    {
        $sql = "INSERT INTO `heard_about_us` (`app_login`) VALUES(:a)";
        $this->dm->inputData($sql, array(':a' => $user_id));
    }

    private function getApplicantLoginID($app_number)
    {
        $sql = "SELECT `id` FROM `applicants_login` WHERE `app_number` = :a;";
        return $this->dm->getID($sql, array(':a' => sha1($app_number)));
    }

    private function saveLoginDetails($app_number, $pin, $who)
    {
        $hashed_pin = password_hash($pin, PASSWORD_DEFAULT);
        $sql = "INSERT INTO `applicants_login` (`app_number`, `pin`, `purchase_id`) VALUES(:a, :p, :b)";
        $params = array(':a' => sha1($app_number), ':p' => $hashed_pin, ':b' => $who);

        if ($this->dm->inputData($sql, $params)) {
            $user_id = $this->getApplicantLoginID($app_number);

            //register in Personal information table in db
            $this->registerApplicantPersI($user_id);

            //register in Programs information
            $this->registerApplicantProgI($user_id);

            //register in Previous university information
            $this->registerApplicantPreUni($user_id);

            //Set initial form checks
            $this->setFormSectionsChecks($user_id);

            //Set initial form checks
            $this->setHeardAboutUs($user_id);

            return 1;
        }
        return 0;
    }

    private function genLoginDetails(int $type, int $year)
    {
        $rslt = 1;
        while ($rslt) {
            $app_num = $this->genAppNumber($type, $year);
            $rslt = $this->doesCodeExists($app_num);
        }
        $pin = strtoupper($this->genPin());
        return array('app_number' => $app_num, 'pin_number' => $pin);
    }

    //Get and Set IDs for foreign keys

    public function getVendorIDByTransactionID(int $trans_id)
    {
        return $this->dm->getData("SELECT vendor FROM purchase_detail WHERE id = :i", array(":i" => $trans_id))[0]["vendor"];
    }

    public function SaveFormPurchaseData($data, $trans_id)
    {
        if (!empty($data) && !empty($trans_id)) {
            //return json_encode($data) . " T=" . $trans_id;
            $fn = $data['first_name'];
            $ln = $data['last_name'];
            $em = $data['email_address'];
            $cn = $data['country_name'];
            $cc = $data['country_code'];
            $pn = $data['phone_number'];
            $am = $data['amount'];
            $fi = $data['form_id'];
            $vd = $data['vendor_id'];

            if ($data['pay_method'] == 'MOM') $pay_method = "MOMO";
            else if ($data['pay_method'] == 'CRD') $pay_method = "CARD";
            else $pay_method = $data['pay_method'];
            $pm = $pay_method;

            $ap_id = $data['admin_period'];
            // For on premises purchases, generate app number and pin and send immediately
            $purchase_id = $this->saveVendorPurchaseData($trans_id, $vd, $fi, $ap_id, $pm, $am, $fn, $ln, $em, $cn, $cc, $pn);
            if ($purchase_id) {
                if ($pm == "CASH") {
                    return $this->genLoginsAndSend($purchase_id);
                } else {
                    return array("success" => true, "message" => "Save purchase data!");
                }
            } else {
                return array("success" => false, "message" => "Failed saving purchase data!");
            }
        } else {
            return array("success" => false, "message" => "Invalid data entries!");
        }
    }

    public function getTransactionStatusFromDB($trans_id)
    {
        $sql = "SELECT `id`, `status` FROM `purchase_detail` WHERE `id` = :t";
        return $this->dm->getData($sql, array(':t' => $trans_id));
    }

    public function updateTransactionStatusInDB($status, $trans_id)
    {
        $sql = "UPDATE `purchase_detail` SET `status` = :s WHERE `id` = :t";
        return $this->dm->getData($sql, array(':s' => $status, ':t' => $trans_id));
    }

    private function getAppPurchaseData(int $trans_id)
    {
        // get form_category, country code, phone number
        $sql = "SELECT f.`form_category`, pd.`country_code`, pd.`phone_number`, pd.`email_address` 
                FROM `purchase_detail` AS pd, forms AS f WHERE pd.`id` = :t AND f.`id` = pd.`form_id`";
        return $this->dm->getData($sql, array(':t' => $trans_id));
    }

    public function genLoginsAndSend(int $trans_id)
    {
        $data = $this->getAppPurchaseData($trans_id);

        if (empty($data)) return array("success" => false, "message" => "No data records for this transaction!");

        $app_type = 0;

        if ($data[0]["form_category"] >= 2) {
            $app_type = 1;
        } else if ($data[0]["form_category"] == 1) {
            $app_type = 2;
        }

        $app_year = $this->expose->getAdminYearCode();

        $login_details = $this->genLoginDetails($app_type, $app_year);

        if ($this->saveLoginDetails($login_details['app_number'], $login_details['pin_number'], $trans_id)) {

            $this->updateVendorPurchaseData($trans_id, $login_details['app_number'], $login_details['pin_number'], 'COMPLETED');
            $vendor_id = $this->getVendorIDByTransactionID($trans_id);

            $this->logActivity(
                $vendor_id,
                "INSERT",
                "Vendor {$vendor_id} sold form with transaction ID {$trans_id}"
            );
            return array("success" => true, "exttrid" => $trans_id);

            /*$message = 'Your RMU Online Application login details. ';
            $message .= 'APPLICATION NUMBER: RMU-' . $login_details['app_number'];
            $message .= '    PIN: ' . $login_details['pin_number'] . ".";
            $message .= ' Follow the link, https://admissions.rmuictonline.com to start application process.';
            $to = $data[0]["country_code"] . $data[0]["phone_number"];

            $response = json_decode($this->expose->sendSMS($to, $message));

            if (!$response->status) {
                if (!empty($data[0]["email_address"])) {
                    $this->expose->sendEmail($data[0]["email_address"], 'ONLINE APPLICATION PORTAL LOGIN INFORMATION', $message);
                }
                $browser_mg = '
                        <p class="mb-4" style="text-align: justify !important; width: 100%; margin:0; padding:0">
                            <b class="text-success">Form purchase successful!</b><br> 
                            An email and SMS with your <b>Application Number</b> and <b>PIN</b> 
                            to access application portal, has been sent to you!<br> 
                            Please confirm and proceed to the <a href="https://admissions.rmuictonline.com/apply/"> 
                            <b>online application portal</b></a> 
                            to complete your application process.
                        </p>';
                return array("success" => true, "message" => $browser_mg, "exttrid" => $trans_id);
            } else {
                return array("success" => false, "message" => "Failed sending login details via SMS!");
            }*/
        } else {
            return array("success" => false, "message" => "Failed saving login details!");
        }
    }
}

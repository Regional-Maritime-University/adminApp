<?php

namespace Src\Controller;

use Src\Gateway\CurlGatewayAccess;
use Src\System\DatabaseMethods;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ExposeDataController extends DatabaseMethods
{

    public function genCode($length = 6)
    {
        $digits = $length;
        return rand(pow(10, $digits - 1), pow(10, $digits) - 1);
    }

    public function genVendorPin(int $length_pin = 12)
    {
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle($str_result), 0, $length_pin);
    }

    public function validateEmail($input)
    {
        if (empty($input)) return array("success" => false, "message" => "Input required!");

        $user_email = htmlentities(htmlspecialchars($input));
        $sanitized_email = filter_var($user_email, FILTER_SANITIZE_EMAIL);

        if (!filter_var($sanitized_email, FILTER_VALIDATE_EMAIL)) return array("success" => false, "message" => "Invalid email address!");

        return array("success" => true, "message" => $user_email);
    }

    public function validateInput($input)
    {
        if (empty($input)) die(json_encode(array("success" => false, "message" => "Input required!")));
        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[A-Za-z0-9]/', $user_input);
        if ($validated_input) return $user_input;
        die(json_encode(array("success" => false, "message" => "Invalid input!")));
    }

    public function validateCountryCode($input)
    {
        if (empty($input)) die(json_encode(array("success" => false, "message" => "Input required!")));
        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[A-Za-z0-9()+]/', $user_input);
        if ($validated_input) return $user_input;
        die(json_encode(array("success" => false, "message" => "Invalid input!")));
    }

    public function validatePassword($input)
    {
        if (empty($input)) die(json_encode(array("success" => false, "message" => "Input required!")));
        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[A-Za-z0-9()+@#.-_=$&!`]/', $user_input);
        if ($validated_input) return $user_input;
        die(json_encode(array("success" => false, "message" => "Invalid input!")));
    }

    public function validatePhone($input)
    {
        if (empty($input)) die(json_encode(array("success" => false, "message" => "Input required!")));
        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[0-9]/', $user_input);
        if ($validated_input) return $user_input;
        die(json_encode(array("success" => false, "message" => "Invalid input!")));
    }

    public function validateNumber($input)
    {
        if ($input == "") die(json_encode(array("success" => false, "message" => "Input required!")));
        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[0-9]/', $user_input);
        if ($validated_input) return $user_input;
        die(json_encode(array("success" => false, "message" => "Invalid input!")));
    }

    public function validateText($input)
    {
        if (empty($input)) die(json_encode(array("success" => false, "message" => "Input required!")));
        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[A-Za-z]/', $user_input);
        if ($validated_input) return $user_input;
        die(json_encode(array("success" => false, "message" => "Invalid input!")));
    }

    public function validateDate($date)
    {
        if (strtotime($date) === false) return array("success" => false, "message" => "Invalid date!");

        list($year, $month, $day) = explode('-', $date);

        if (checkdate($month, $day, $year)) return array("success" => true, "message" => $date);
    }

    public function validateImage($files)
    {
        if (!isset($files['file']['error']) || !empty($files["pics"]["name"])) {
            $allowedFileType = ['image/jpeg', 'image/png', 'image/jpg'];
            for ($i = 0; $i < count($files["pics"]["name"]); $i++) {
                $check = getimagesize($files["pics"]["tmp_name"][$i]);
                if ($check !== false && in_array($files["pics"]["type"][$i], $allowedFileType)) {
                    return array("success" => true, "message" => $files);
                }
            }
        }
        return array("success" => false, "message" => "Invalid file uploaded!");
    }

    public function validateInputTextOnly($input)
    {
        if (empty($input)) {
            return array("success" => false, "message" => "required");
        }

        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[A-Za-z]/', $user_input);

        if ($validated_input) {
            return array("success" => true, "message" => $user_input);
        }

        return array("success" => false, "message" => "invalid");
    }

    public function validateInputTextNumber($input)
    {
        if (empty($input)) {
            return array("success" => false, "message" => "required");
        }

        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[A-Za-z0-9]/', $user_input);

        if ($validated_input) {
            return array("success" => true, "message" => $user_input);
        }

        return array("success" => false, "message" => "invalid");
    }

    public function validateYearData($input)
    {
        if (empty($input) || strtoupper($input) == "YEAR") {
            return array("success" => false, "message" => "required");
        }

        if ($input < 1990 || $input > 2022) {
            return array("success" => false, "message" => "invalid");
        }

        $user_input = htmlentities(htmlspecialchars($input));
        $validated_input = (bool) preg_match('/^[0-9]/', $user_input);

        if ($validated_input) {
            return array("success" => true, "message" => $user_input);
        }

        return array("success" => false, "message" => "invalid");
    }

    public function validateGrade($input)
    {
        if (empty($input) || strtoupper($input) == "GRADE") {
            return array("success" => false, "message" => "required");
        }

        if (strlen($input) < 1 || strlen($input) > 2) {
            return array("success" => false, "message" => "invalid");
        }

        $user_input = htmlentities(htmlspecialchars($input));
        return array("success" => true, "message" => $user_input);
    }

    public function getIPAddress()
    {
        //whether ip is from the share internet  
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        //whether ip is from the proxy  
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        //whether ip is from the remote address  
        else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public function getDeciveInfo()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    public function getFormPriceA(int $form_id)
    {
        $sql = "SELECT * FROM `forms` WHERE `id` = :fi";
        return $this->getData($sql, array(":fi" => $form_id));
    }

    public function getFormPriceDataByFormID(int $form_id)
    {
        $sql = "SELECT * FROM `forms` WHERE `id` = :fi";
        return $this->getData($sql, array(":fi" => $form_id));
    }

    public function getFormPrice(int $form_type, int $admin_period)
    {
        return $this->getData(
            "SELECT `amount` FROM `forms` WHERE `form_category` = :ft AND admin_period = :ap",
            array(":ft" => $form_type, ":ap" => $admin_period)
        );
    }

    public function getAdminYearCode()
    {
        $sql = "SELECT EXTRACT(YEAR FROM (SELECT `start_date` FROM admission_period WHERE `active` = 1)) AS 'year'";
        $year = (string) $this->getData($sql)[0]['year'];
        return (int) substr($year, 2, 2);
    }

    public function getFormTypes()
    {
        return $this->getData("SELECT * FROM `forms`");
    }

    public function getPaymentMethods()
    {
        return $this->getData("SELECT * FROM `payment_method`");
    }

    public function getPrograms($type)
    {
        $sql = "SELECT * FROM `programs` WHERE `type` = :t";
        $param = array(":t" => $type);
        return $this->getData($sql, $param);
    }

    public function getHalls()
    {
        return $this->getData("SELECT * FROM `halls`");
    }

    public function sendEmail($recipient_email, $subject, $message)
    {
        //PHPMailer Object
        $mail = new PHPMailer(true); //Argument true in constructor enables exceptions

        //From email address and name
        $mail->From = "rmuicton@rmuictonline.com";
        $mail->FromName = "rmuicton";

        //To address and name
        $mail->addAddress($recipient_email);

        //Send HTML or Plain Text email
        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->Body = $message;

        try {
            if ($mail->send()) return 1;
        } catch (Exception $e) {
            return "Mailer Error: " . $mail->ErrorInfo;
        }
        return 0;
    }

    public function sendHubtelSMS($url, $payload)
    {
        $client = getenv('HUBTEL_CLIENT');
        $secret = getenv('HUBTEL_SECRET');
        $secret_key = base64_encode($client . ":" . $secret);

        $httpHeader = array("Authorization: Basic " . $secret_key, "Content-Type: application/json");
        $gateAccess = new CurlGatewayAccess($url, $httpHeader, $payload);
        return $gateAccess->initiateProcess();
    }

    public function sendSMS($to, $message)
    {
        $url = "https://sms.hubtel.com/v1/messages/send";
        $payload = json_encode(array("From" => "RMU", "To" => $to, "Content" => $message));
        return $this->sendHubtelSMS($url, $payload);
    }

    public function sendOTP($to)
    {
        $otp_code = $this->genCode(4);
        $message = 'Your OTP verification code: ' . $otp_code;
        $response = json_decode($this->sendSMS($to, $message), true);
        if (!$response["status"]) $response["otp_code"] = $otp_code;
        return $response;
    }

    public function getVendorPhone($vendor_id)
    {
        $sql = "SELECT `country_code`, `phone_number` FROM `vendor_details` WHERE `id`=:i";
        return $this->getData($sql, array(':i' => $vendor_id));
    }

    public function getVendorPhoneByUserID($user_id)
    {
        $sql = "SELECT v.`id`, v.`phone_number` FROM `vendor_details` AS v, `sys_users` AS u 
                WHERE u.`id` = v.`user_id` AND u.`id`=:i";
        return $this->getData($sql, array(':i' => $user_id));
    }

    public function vendorExist($vendor_id)
    {
        $str = "SELECT `id` FROM `vendor_details` WHERE `id`=:i";
        return $this->getID($str, array(':i' => $vendor_id));
    }

    public function verifyVendorLogin($username, $password)
    {
        $sql = "SELECT `vendor`, `password` FROM `vendor_login` WHERE `user_name` = :u";
        $data = $this->getData($sql, array(':u' => sha1($username)));
        if (!empty($data)) {
            if (password_verify($password, $data[0]["password"])) {
                return array("success" => true, "message" => $data[0]["vendor"]);
            } else {
                return array("success" => false, "message" => "No match found!");
            }
        }
        return array("success" => false, "message" => "User does not exist!");
    }

    public function getApplicationInfo($transaction_id)
    {
        $sql = "SELECT p.`id`, p.`first_name`, p.`last_name`, p.`phone_number`, p.`app_number`, p.`pin_number`, 
                f.`name`, f.`amount`, v.`company`, v.`branch`, a.`info` 
        FROM `purchase_detail` AS p, `forms` AS f, `vendor_details` AS v, `admission_period` AS a 
        WHERE p.`form_id` = f.`id` AND p.vendor = v.`id` AND p.`admission_period` = a.`id` AND p.`id` = :i";
        return $this->getData($sql, array(':i' => $transaction_id));
    }

    public function getCurrentAdmissionPeriodID()
    {
        return $this->getID("SELECT `id` FROM `admission_period` WHERE `active` = 1");
    }
}

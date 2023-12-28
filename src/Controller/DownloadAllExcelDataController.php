<?php

namespace Src\Controller;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Src\Controller\AdminController;

class DownloadAllExcelDataController
{
    private $admin = null;

    private $spreadsheet = null;
    private $writer = null;
    private $sheet = null;
    private $dataSheet = [];
    private $fileName = null;
    private $sheetTitle = null;
    private $status = null;

    public function __construct($status)
    {
        $this->status = $status;
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->writer = new Xlsx($this->spreadsheet);
        $this->admin = new AdminController();
    }

    /**
     * Download broadsheet
     */

    public function prepareBSData($query)
    {
        return $this->admin->exportAdmissionData($this->status, $query);
    }

    public function getExtraData()
    {
        $query = "SELECT ab.`school_name`, ab.`country`, ab.`region`, ab.`city`, ab.`cert_type`, ab.`other_cert_type`, 
        ab.`index_number`, ab.`month_started`, ab.`year_started`, ab.`month_completed`, ab.`year_completed`, 
        ab.`course_of_study`, ab.`other_course_studied`, ab.`awaiting_result`
        FROM  
        `applicants_login` AS al, `academic_background` AS ab, `form_sections_chek` AS fsc
        WHERE al.id = ab.app_login AND al.id = fsc.app_login AND fsc.app_login";
    }

    public function setSheetTitle($title)
    {
        $split = explode("-", $title, 2);
        $this->sheet->setCellValue('A1', $split[1]);
    }

    public function makeSpreadsheetContent($title)
    {
        $this->setSheetTitle($title);

        $applicantRawDataQuery = "SELECT 
        al.`id`, pd.`form_id`, pin.`prefix`, pin.`first_name`, pin.`middle_name`, pin.`last_name`, 
        pin.`suffix`, pin.`gender`, pin.`dob`, pin.`marital_status`, pin.`nationality`, pin.`country_res`, 
        pin.`disability`, pin.`disability_descript`, pin.`photo`, pin.`country_birth`, pin.`spr_birth`, 
        pin.`city_birth`, pin.`english_native`, pin.`speaks_english`, pin.`other_language`, pin.`postal_addr`, 
        pin.`postal_town`, pin.`postal_spr`, pin.`postal_country`, pin.`phone_no1_code`, pin.`phone_no1`, 
        pin.`phone_no2_code`, pin.`phone_no2`, pin.`email_addr`, pin.`p_prefix`, pin.`p_first_name`, 
        pin.`p_last_name`, pin.`p_occupation`, pin.`p_phone_no_code`, pin.`p_phone_no`, pin.`p_email_addr`, 
        pi.`first_prog`, `second_prog`, pi.`application_term`, pi.`study_stream`, hau.`medium`, hau.`description` 
        FROM  
        `applicants_login` AS al, `personal_information` AS pin, `program_info` AS pi, 
        `heard_about_us` AS hau, `purchase_detail` AS pd, `forms` AS f, `form_sections_chek` AS fsc 
        WHERE 
        al.id = pin.app_login AND al.id = pi.app_login AND al.purchase_id = pd.id AND pd.form_id = f.id AND 
        al.id = hau.app_login AND al.id = fsc.app_login";
        $datasheet1 = $this->prepareBSData($applicantRawDataQuery);

        $row = 2;
        foreach ($datasheet1 as $data1) {
            $this->sheet->fromArray($data1, NULL, 'A' . $row);
            //$this->sheet->setCellValue('AK' . $row, "");

            /*$academicDataQuery = "SELECT 
                ab.`school_name`, ab.`country`, ab.`region`, ab.`city`, ab.`cert_type`, ab.`other_cert_type`, 
                ab.`index_number`, ab.`month_started`, ab.`year_started`, ab.`month_completed`, ab.`year_completed`, 
                ab.`course_of_study`, ab.`other_course_studied`, ab.`awaiting_result`
            FROM `applicants_login` AS al, `academic_background` AS ab 
            WHERE al.id = ab.app_login AND al.id = {$data1["id"]}";
            $datasheet2 = $this->prepareBSData($academicDataQuery);

            foreach ($datasheet2 as $data2) {
                $this->sheet->fromArray($data2, NULL, 'AL' . $row);
            }*/

            $row += 1;
        }
    }

    public function saveSpreadsheetFile($filename)
    {
        $file = $filename . '.xlsx';

        if (file_exists($file)) {
            unlink($file);
        }

        $this->writer->save($file);

        $this->spreadsheet->disconnectWorksheets();
        unset($this->spreadsheet);
    }

    public function createFileName($status)
    {
        $dateData = $this->admin->getAcademicPeriod();
        $this->fileName = strtoupper("List of " . ($status != "all" ? " $status " : " ") . "Applicants");
        $academicIntake = $dateData[0]["start_year"] . " - " . $dateData[0]["end_year"] . " " . $dateData[0]["info"];
        $this->sheetTitle = $this->fileName . "(" . strtoupper($academicIntake) . ")";
    }

    public function generateFile()
    {
        $this->createFileName($this->status);
        $this->makeSpreadsheetContent($this->sheetTitle);
        $this->saveSpreadsheetFile($this->fileName);
        return $this->fileName;
    }

    public function downloadFile($file)
    {
        $file_url = './' . $file . ".xlsx";
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: utf-8");
        header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\"");
        if (readfile($file_url)) return true;
        return array("success" => false, "message" => "Download failed!");
    }
}

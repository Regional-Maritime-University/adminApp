<?php

require_once('bootstrap.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Src\Controller\AdminController;

class Broadsheet
{
    private $spreadsheet = null;
    private $writer = null;
    private $admin = null;
    private $sheet = null;
    private $dataSheet = [];
    private $fileName = null;
    private $sheetTitle = null;
    private $cert_type = null;
    private $action = null;

    public function __construct($cert_type, $action = "bs")
    {
        $this->action = $action;
        $this->cert_type = $cert_type;
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->writer = new Xlsx($this->spreadsheet);
        $this->admin = new AdminController();
    }

    public function prepareBSData()
    {
        $admittedApps = $this->admin->getAllAdmitedApplicants($this->cert_type);
        if (empty($admittedApps)) return 0;

        foreach ($admittedApps as  $appData) {
            $applicant = [];
            $applicant["pers_details"] = $appData;
            $subjs = $this->admin->getAppCourseSubjects($appData["id"]);
            $applicant["exam_details"] = $subjs;
            array_push($this->dataSheet, $applicant);
        }
    }

    public function formatSpreadsheet($title)
    {
        $this->sheet->setCellValue('A1', $title);
        $this->sheet->mergeCells('A1:J1');

        $this->sheet->setCellValue('A2', "NAME");
        $this->sheet->mergeCells('A2:A3');

        $this->sheet->setCellValue('J2', "PROGRAMME");
        $this->sheet->mergeCells('J2:J3');

        $this->sheet->setCellValue('B2', "CORE SUBJECTS");
        $this->sheet->mergeCells('B2:E2');

        $this->sheet->setCellValue('F2', "ELECTIVE SUBJECTS");
        $this->sheet->mergeCells('F2:I2');

        $this->sheet->setCellValue('B3', "CORE MATHEMATICS");
        $this->sheet->setCellValue('C3', "ENGLISH LANGUAGE");
        $this->sheet->setCellValue('D3', "INTEGRATED SCIENCE");
        $this->sheet->setCellValue('E3', "SOCIAL STUDIES");

        $this->sheet->setCellValue('F3', "ELECTIVE 1");
        $this->sheet->setCellValue('G3', "ELECTIVE 2");
        $this->sheet->setCellValue('H3', "ELECTIVE 3");
        $this->sheet->setCellValue('I3', "ELECTIVE 4");

        $this->sheet->getColumnDimension('A')->setAutoSize(true);
        $this->sheet->getColumnDimension('B')->setAutoSize(true);
        $this->sheet->getColumnDimension('C')->setAutoSize(true);
        $this->sheet->getColumnDimension('D')->setAutoSize(true);
        $this->sheet->getColumnDimension('E')->setAutoSize(true);
        $this->sheet->getColumnDimension('F')->setAutoSize(true);
        $this->sheet->getColumnDimension('G')->setAutoSize(true);
        $this->sheet->getColumnDimension('H')->setAutoSize(true);
        $this->sheet->getColumnDimension('I')->setAutoSize(true);
        $this->sheet->getColumnDimension('J')->setAutoSize(true);

        $this->sheet->getStyle('A1:J3')->getAlignment()->setHorizontal('center');
        $this->sheet->getStyle('A2:A3')->getAlignment()->setVertical('center');
        $this->sheet->getStyle('J2:J3')->getAlignment()->setVertical('center');
    }

    private function makeSpreadsheetContent($datasheet)
    {
        $coreExcelColumns = ["B", "C", "D", "E"];
        $elecExcelColumns = ["F", "G", "H", "I"];

        //$format_top->setTextWrap(1);

        $row = 4;

        foreach ($datasheet as $data) {

            // set applicant fullname
            $fullname = $data["pers_details"]["first_name"] . " " . $data["pers_details"]["last_name"];
            if (!empty($data["pers_details"]["middle_name"])) {
                $fullname = $data["pers_details"]["first_name"] . " " . $data["pers_details"]["middle_name"] . " " . $data["pers_details"]["last_name"];
            }

            $coreNextInput = 0;
            $electiveNextInput = 0;

            //set applicant name value
            $appNameCell = "A" . $row;
            $this->sheet->setCellValue($appNameCell, $fullname);

            foreach ($data["exam_details"] as $subj) {
                // set all core subject value
                if ($subj["type"] == "core") {
                    $coreCell = $coreExcelColumns[$coreNextInput] . "" . $row;
                    $this->sheet->setCellValue($coreCell, $subj["grade"]);
                    $coreNextInput += 1;
                }

                // set all core subject value
                if ($subj["type"] == "elective") {
                    $elecCell = $elecExcelColumns[$electiveNextInput] . "" . $row;
                    $this->sheet->setCellValue($elecCell, $subj["grade"]);
                    $electiveNextInput += 1;
                }
            }

            //set program value
            $progNameCell = "J" . $row;
            $this->sheet->setCellValue($progNameCell, $data["pers_details"]["programme"]);

            $row += 1;
        }
    }

    private function saveSpreadsheetFile($filename)
    {
        $file = $filename . '.xlsx';

        if (file_exists($file)) {
            unlink($file);
        }

        $this->writer->save($file);

        $this->spreadsheet->disconnectWorksheets();
        unset($this->spreadsheet);
    }

    public function createFileName($cert_type)
    {
        $dateData = $this->admin->getAcademicPeriod();
        $this->fileName = strtoupper("List of Admitted" . ($cert_type != "all" ? " $cert_type " : " ") . "Applicants");
        $academicIntake = $dateData[0]["start_year"] . " - " . $dateData[0]["end_year"] . " " . $dateData[0]["info"];
        $this->sheetTitle = $this->fileName . "(" . strtoupper($academicIntake) . ")";
    }

    public function generateFile()
    {
        $this->prepareBSData();
        if (!empty($this->dataSheet)) {
            $this->createFileName($this->cert_type);
            $this->formatSpreadsheet($this->sheetTitle);
            $this->makeSpreadsheetContent($this->dataSheet);
            $this->saveSpreadsheetFile($this->fileName);
            return $this->fileName;
        }
    }

    public function downloadFile($file)
    {
        $file_url = './' . $file . ".xlsx";
        header('Content-Type:application/octet-stream');
        header("Content-Transfer-Encoding:utf-8");
        header("Content-disposition:attachment;filename=\"" . basename($file_url) . "\"");
        readfile($file_url);
    }
}

$broadsheet = new Broadsheet($_GET["a"], $_GET['c']);
$file = $broadsheet->generateFile();
$broadsheet->downloadFile($file);

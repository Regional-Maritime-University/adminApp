<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Src\Controller\AdminController;

class AwaitingDataController
{
    private $admin = null;

    private $fileObj = null;
    private $startRow = null;
    private $endRow = null;
    private $targetPath = null;

    /**
     * Uploading awaiting students data
     */

    private function __construct1($fileObj, $startRow, $endRow)
    {
        $this->fileObj = $fileObj;
        $this->startRow = $startRow;
        $this->endRow = $endRow;
        $this->admin = new AdminController();
    }

    public function extractAwaitingData()
    {

        $allowedFileType = [
            'application/vnd.ms-excel',
            'text/xls',
            'text/xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        if (!in_array($this->fileObj["type"], $allowedFileType)) {
            return array("success" => false, "message" => "Invalid file type. Please choose an excel file!");
        }

        if ($this->fileObj['error'] == UPLOAD_ERR_OK) {
            // Set the target directory
            $upload_dir = '../../uploads/';

            // Create a unique file name
            $name = time() . '-' . 'awaiting';

            // Create the full path to the file
            $this->targetPath = $upload_dir . $name;

            // Delete file if exsists
            if (file_exists($this->targetPath)) {
                unlink($this->targetPath);
            }

            // Move the file to the target directory
            if (move_uploaded_file($this->fileObj['tmp_name'], $this->targetPath)) {
                return array("success" => false, "message" => "File upload successful!");
                return $this->getExcelDataIntoDB($this->targetPath, $this->startRow, $this->endRow);
            }
        }
    }

    public function saveSubjectAndGrades($subjects = array(), $aca_id)
    {
        if (!empty($subjects)) {
            $sql = "INSERT INTO `high_school_results` (`type`, `subject`, `grade`, `acad_back_id`) VALUES (:t, :s, :g, :ai)";

            // add core subjects
            for ($i = 0; $i < count($subjects["core"]); $i++) {
                $params = array(":t" => "core", ":s" => $subjects["core"][$i]["subject"], ":g" => $subjects["core"][$i]["grade"], ":ai" =>  $aca_id);
                $this->admin->inputData($sql, $params);
            }

            // add elective subjects
            for ($i = 0; $i < count($subjects["elective"]); $i++) {
                $params = array(":t" => "elective", ":s" => $subjects["elective"][$i]["subject"], ":g" => $subjects["elective"][$i]["grade"], ":ai" =>  $aca_id);
                $this->admin->inputData($sql, $params);
            }

            return 1;
        }
        return 0;
    }

    public function getExcelDataIntoDB()
    {
        $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadSheet = $Reader->load($this->targetPath);
        $excelSheet = $spreadSheet->getActiveSheet();
        $spreadSheetArray = $excelSheet->toArray();

        if ($this->endRow == 0) $endRow = count($spreadSheetArray);
        if ($this->startRow > 1) $this->startRow -= 1;

        $count = 0;

        for ($i = $this->startRow; $i <= $endRow - 1; $i++) {
            $admisNum = $spreadSheetArray[$i][1];
            $indexNum = $spreadSheetArray[$i][2];
            $examMonth = $spreadSheetArray[$i][3];
            $examYear = $spreadSheetArray[$i][4];
            $givenDOB = $spreadSheetArray[$i][5];
            $dateTime = DateTime::createFromFormat('d/m/Y', $givenDOB);
            $newDOB = $dateTime->format('Y-m-d');

            $names = explode(" ", $spreadSheetArray[$i][6]);
            $mname = "";

            if (count($names) > 3) {
                $lname = $names[0];
                $fname = $names[1];
                $mname = $names[2] . " " . $names[3];
            } elseif (count($names) > 2) {
                $lname = $names[0];
                $fname = $names[1];
                $mname = $names[2];
            } elseif (count($names) > 1) {
                $fname = $names[0];
                $lname = $names[1];
            }

            // Get all the courses

            $subjects = array_slice($spreadSheetArray, 6, count($spreadSheetArray));
            $examResults = array();

            for ($i = 0; $i < count($subjects); $i += 2) {
                if ($subjects[$i] == "") continue;
                $examResults[$i] = array(
                    "subject" => $subjects[$i],
                    "grade" => $subjects[($i + 1)]
                );
            }

            return $admisNum;
        }
        //echo "<script>alert('Successfully transfered " . $count . " excel data into DB')</script>";
        //return 1;
    }
}

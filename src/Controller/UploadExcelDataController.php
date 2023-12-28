<?php

namespace Src\Controller;

use Src\System\DatabaseMethods;

class UploadExcelDataController
{
    private $dm = null;

    private $fileObj = array();
    private $startRow = null;
    private $endRow = null;
    private $targetPath = null;
    private $errorsEncountered = 0;
    private $successEncountered = 0;

    public function __construct($fileObj, $startRow, $endRow)
    {
        $this->fileObj = $fileObj;
        $this->startRow = (int) $startRow;
        $this->endRow = (int) $endRow;
        $this->dm = new DatabaseMethods();
    }

    public function saveDataFile()
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

            // Create a unique file name
            $name = time() . '-' . 'awaiting.xlsx';

            // Create the full path to the file
            $this->targetPath = UPLOAD_DIR . "/awaiting/" . $name;

            // Delete file if exsists
            if (file_exists($this->targetPath)) {
                unlink($this->targetPath);
            }

            // Move the file to the target directory
            if (!move_uploaded_file($this->fileObj['tmp_name'], $this->targetPath))
                return array("success" => false, "message" => "Failed to upload file!");
            return array("success" => true, "message" => "File upload successful!");
        }
        return array("success" => false, "message" => "Error: Invalid file object!");
    }

    public function extractExcelData()
    {
        $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadSheet = $Reader->load($this->targetPath);
        $excelSheet = $spreadSheet->getActiveSheet();
        $spreadSheetArray = $excelSheet->toArray();

        if ($this->endRow == 0) $this->endRow = count($spreadSheetArray);
        if ($this->startRow > 1) $this->startRow -= 1;

        $dataset = array();

        for ($i = $this->startRow; $i <= $this->endRow - 1; $i++) {
            //$admisNum = $spreadSheetArray[$i][0];
            $indexNum = $spreadSheetArray[$i][1];
            //$examMonth = $spreadSheetArray[$i][2];
            //$examYear = $spreadSheetArray[$i][3];

            // Get all the courses

            $endRowData = count($spreadSheetArray[$i]);
            $examResults = array();

            for ($j = 6; $j < $endRowData; $j += 2) {
                if ($spreadSheetArray[$i][$j] == "") break;

                if (preg_match("/^english lang$/i", $spreadSheetArray[$i][$j])) {
                    array_push($examResults, array(
                        "type" => "core",
                        "subject" => "ENGLISH LANGUAGE",
                        "grade" => $spreadSheetArray[$i][($j + 1)]
                    ));
                } elseif (preg_match("/(?i)mathematics.*core/", $spreadSheetArray[$i][$j])) {
                    array_push($examResults, array(
                        "type" => "core",
                        "subject" => "MATHEMATICS (CORE)",
                        "grade" => $spreadSheetArray[$i][($j + 1)]
                    ));
                } elseif (preg_match("/^social studies$/i", $spreadSheetArray[$i][$j])) {
                    array_push($examResults, array(
                        "type" => "core",
                        "subject" => "SOCIAL STUDIES",
                        "grade" => $spreadSheetArray[$i][($j + 1)]
                    ));
                } elseif (preg_match("/^integrated science$/i", $spreadSheetArray[$i][$j])) {
                    array_push($examResults, array(
                        "type" => "core",
                        "subject" => "INTEGRATED SCIENCE",
                        "grade" => $spreadSheetArray[$i][($j + 1)]
                    ));
                } else {
                    array_push($examResults, array(
                        "type" => "elective",
                        "subject" => $spreadSheetArray[$i][$j],
                        "grade" => $spreadSheetArray[$i][($j + 1)]
                    ));
                }
            }

            array_push($dataset, array("index_number" => $indexNum, "exam_results" => $examResults));
        }

        return $dataset;
    }

    public function saveSubjectAndGrades($indexNumber, $subjects = array())
    {
        if (empty($subjects) || empty($indexNumber)) {
            $this->errorsEncountered += 1;
            return array(
                "success" => false, "index number" => $indexNumber, "message" => "Empty value inputs!"
            );
        }

        // Get applicant application number/id using index number provide
        $query = "SELECT ab.`id` AS acaID, ap.`id` AS appID FROM applicants_login AS ap, academic_background AS ab
                    WHERE ap.id = ab.app_login AND ab.index_number = :i";
        $appAcaID = $this->dm->getData($query, array(":i" => $indexNumber));

        if (empty($appAcaID)) {
            $this->errorsEncountered += 1;
            return array(
                "success" => false, "index number" => $indexNumber, "message" => "Applicant data not found in DB!",
            );
        }

        // Delete any existing records if any
        $deleteQuery = "DELETE FROM `high_school_results` WHERE `acad_back_id` = :ai";
        $this->dm->inputData($deleteQuery, array(":ai" => $appAcaID[0]["acaID"]));

        // Insert exam records
        $insertQuery = "INSERT INTO `high_school_results` (`type`, `subject`, `grade`, `acad_back_id`) VALUES (:t, :s, :g, :ai)";
        foreach ($subjects as $sbj) {
            $params = array(":t" => $sbj["type"], ":s" => $sbj["subject"], ":g" => $sbj["grade"], ":ai" => $appAcaID[0]["acaID"]);
            $this->dm->inputData($insertQuery, $params);
        }

        // Update Acagemic backgorund, set awaiting to 0
        $query = "UPDATE academic_background SET `awaiting_result` = 0 WHERE `id` = :ai AND index_number = :im";
        $this->dm->inputData($query, array(":ai" => $appAcaID[0]["acaID"], ":im" => $indexNumber));

        // Update form_check, set declaration to 1
        $query = "UPDATE form_sections_chek SET `declaration` = 1 WHERE `app_login` = :al";
        $this->dm->inputData($query, array(":al" => $appAcaID[0]["appID"]));

        return array("success" => true, "index number" => $indexNumber, "message" => "Subjects added!");
    }

    public function extractAwaitingApplicantsResults()
    {
        // save file to uploads folder
        $file_upload_msg = $this->saveDataFile();
        if (!$file_upload_msg["success"]) return $file_upload_msg;

        //extraxt data into array
        $extracted_data = $this->extractExcelData();
        if (empty($extracted_data)) return array("success" => true, "message" => "Couldn't extract excel data to DB!");

        $error_list = [];
        $output = [];
        $count = 0;

        // add results for each applicant to db
        foreach ($extracted_data as $data) {
            $result = $this->saveSubjectAndGrades($data["index_number"], $data["exam_results"]);
            if (!$result["success"]) array_push($error_list, $result);
            if ($result["success"]) $this->successEncountered += 1;
            $count++;
        }

        array_push($output, array("total_count" => $count));
        array_push($output, array("success_count" => $this->successEncountered));
        array_push($output, array("errors_count" => $this->errorsEncountered));
        array_push($output, array("errors" => $error_list));

        return $output;
    }
}

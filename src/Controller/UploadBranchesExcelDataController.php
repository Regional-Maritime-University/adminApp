<?php

namespace Src\Controller;

use Src\System\DatabaseMethods;

class UploadBranchesExcelDataController
{
    private $dm = null;
    private $reader = null;

    private $fileObj = array();
    private $fileLocation = null;
    private $mainBranch = null;
    private $startRow = null;
    private $endRow = null;
    private $targetPath = null;
    private $errorsEncountered = 0;
    private $successEncountered = 0;

    public function __construct($fileLocation, $mainBranch, $fileObj, $startRow, $endRow)
    {
        $this->fileObj = $fileObj;
        $this->fileLocation = $fileLocation;
        $this->mainBranch = $mainBranch;
        $this->startRow = (int) $startRow;
        $this->endRow = (int) $endRow;
        $this->dm = new DatabaseMethods();
        $this->reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
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
            $this->targetPath = UPLOAD_DIR . "/$this->fileLocation/" . $name;

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
        // save file to uploads folder
        $file_upload_msg = $this->saveDataFile();
        if (!$file_upload_msg["success"]) return $file_upload_msg;

        $spreadSheet = $this->reader->load($this->targetPath);
        $excelSheet = $spreadSheet->getActiveSheet();
        $spreadSheetArray = $excelSheet->toArray();

        if ($this->endRow == 0) $this->endRow = count($spreadSheetArray);
        if ($this->startRow > 1) $this->startRow -= 1;

        $dataset = array();
        $successCount = 0;
        $errorCount = 0;
        $privileges = array("select" => 1, "insert" => 1, "update" => 0, "delete" => 0);

        for ($i = $this->startRow; $i <= $this->endRow - 1; $i++) {
            $v_branch = $spreadSheetArray[$i][0];
            $v_email = $spreadSheetArray[$i][1];
            $v_phone = $spreadSheetArray[$i][2];

            $user_data = array(
                "first_name" => $v_name, "last_name" => $v_branch, "user_name" => $v_email,
                "user_role" => "Vendors", "vendor_company" => $v_name,
                "vendor_phone" => $v_phone, "vendor_branch" => $v_branch
            );
            if ($this->addSystemUser($user_data, $privileges)) $successCount += 1;
            else $errorCount += 1;
        }

        return array("success" => true, "message" => "Successfully added MAIN branch account and {$successCount} branches with {$errorCount} unsuccessful!");
    }
}

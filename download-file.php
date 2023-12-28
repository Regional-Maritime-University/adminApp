<?php
$file = "";
if (isset($_GET["type"]) && $_GET["type"] == "branch") $file = "./uploads/branches/" . "Sample RMU Vendors Data" . ".xlsx";

if (!empty($file)) {
    header('Content-Type:application/octet-stream');
    header("Content-Transfer-Encoding:utf-8");
    header("Content-disposition:attachment;filename=\"" . basename($file) . "\"");
    readfile($file);
}

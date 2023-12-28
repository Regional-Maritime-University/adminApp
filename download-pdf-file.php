<?php
session_start();

if (!isset($_SESSION["adminLogSuccess"]) || $_SESSION["adminLogSuccess"] == false || !isset($_SESSION["user"]) || empty($_SESSION["user"])) {
    header("Location: index.php");
}

if (!isset($_GET["w"])) {
    if (isset($_SERVER['HTTP_REFERER'])) {
        // redirect the user back to the previous page
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

use Src\Controller\AdminController;

require_once "./bootstrap.php";

$admin = new AdminController();

$result = array();
$title_var = "";
if (isset($_GET["w"]) && $_GET["w"] == 'pdfFileDownload') $result = $admin->executeDownloadQueryStmt();
?>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
</head>

<body>
    <div>
        <h3 style="text-align: center;" class="m-4">Transactions Report</h3>
        <h6 style="display: flex; justify-content: space-between" class="m-4">
            <?php if (isset($_GET["p"]) && $_GET["p"] == "vendors-transactions") { ?>
                <span><b>Filter By:</b> <?= $_SESSION["downloadQueryStmt"]["data"]["report-by"] == "PayMethod" ? "Payment Menthod" : $_SESSION["downloadQueryStmt"]["data"]["report-by"] ?></span>
                <span><b>Vendor:</b> <?= $_SESSION["downloadQueryStmt"]["data"]["report-by"] == "PayMethod" ? "Payment Menthod" : $_SESSION["downloadQueryStmt"]["data"]["report-by"] ?></span>
                <span><b>Date:</b> <?= $_SESSION["downloadQueryStmt"]["data"]["from-date"] . " - " . $_SESSION["downloadQueryStmt"]["data"]["to-date"]  ?></span>
            <?php } else if (isset($_GET["p"]) && $_GET["p"] == "daily-transactions") { ?>
                <span><b>Admission Period:</b> <?= isset($_SESSION["downloadQueryStmt"]["data"]["admission-period"]) && !empty($_SESSION["downloadQueryStmt"]["data"]["admission-period"]) ? $admin->fetchAdmissionPeriod($_SESSION["downloadQueryStmt"]["data"]["admission-period"])[0]["info"] : "" ?></span>
                <span><b>Date:</b> <?= $_SESSION["downloadQueryStmt"]["data"]["from-date"] . " - " . $_SESSION["downloadQueryStmt"]["data"]["to-date"]  ?></span>
                <span><b>Form Type:</b> <?= isset($_SESSION["downloadQueryStmt"]["data"]["form-type"]) && !empty($_SESSION["downloadQueryStmt"]["data"]["form-type"]) ? $admin->getFormByFormID($_SESSION["downloadQueryStmt"]["data"]["form-type"])[0]["name"] : "" ?></span>
                <span><b>Purchase Status:</b> <?= $_SESSION["downloadQueryStmt"]["data"]["purchase-status"] ?></span>
                <span><b>Payment Method:</b> <?= $_SESSION["downloadQueryStmt"]["data"]["payment-method"] ?></span>
            <?php } ?>
        </h6>
        <table class="table table-borderless datatable table-striped table-hover" style="font-size: 12px;">
            <?php
            switch ($_GET["p"]) {
                case 'vendors-transactions':
                    switch ($_GET["t"]) {
                        case "main":
            ?>
                            <thead class="table-secondary">
                                <tr>
                                    <th scope="col">S/N</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Total Sold</th>
                                    <th scope="col">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $index = 1;
                                foreach ($result as $row) { ?>
                                    <tr>
                                        <td><?= $index ?></td>
                                        <td><?= $row["title"] ?></td>
                                        <td><?= $row["total_num_sold"] ?></td>
                                        <td><?= $row["total_amount_sold"] ?></td>
                                    </tr>
                                <?php
                                    $index++;
                                } ?>
                            </tbody>
                        <?php
                            break;
                        case "specific":
                        ?>
                            <thead class="table-secondary">
                                <tr>
                                    <th scope="col">S/N</th>
                                    <th scope="col">Buyer Name</th>
                                    <th scope="col">Country</th>
                                    <th scope="col">Phone Number</th>
                                    <th scope="col">Payment Mode</th>
                                    <th scope="col">Date/Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $index = 1;
                                foreach ($result as $row) { ?>
                                    <tr>
                                        <td><?= $index ?></td>
                                        <td><?= $row["first_name"] . " " . $row["last_name"] ?></td>
                                        <td><?= $row["country_name"] ?></td>
                                        <td><?= "(" . $row["country_code"] . ")" . $row["phone_number"] ?></td>
                                        <td><?= $row["payment_method"] ?></td>
                                        <td><?= $row["added_at"] ?></td>
                                    </tr>
                                <?php
                                    $index++;
                                } ?>
                            </tbody>
                        <?php
                            break;
                    }
                    break;

                case 'daily-transactions':
                    switch ($_GET["t"]) {
                        case "main":
                        ?>
                            <thead class="table-secondary">
                                <tr>
                                    <th scope="col">S/N</th>
                                    <th scope="col">Transaction ID</th>
                                    <th scope="col">Customer</th>
                                    <th scope="col">Phone Number</th>
                                    <?php if (!isset($_SESSION["downloadQueryStmt"]["data"]["admission-period"]) || empty($_SESSION["downloadQueryStmt"]["data"]["admission-period"])) { ?>
                                        <th scope="col">Admission Period</th>
                                    <?php } ?>
                                    <?php if (!isset($_SESSION["downloadQueryStmt"]["data"]["form-type"]) || empty($_SESSION["downloadQueryStmt"]["data"]["form-type"])) { ?>
                                        <th scope="col">Form Bought</th>
                                    <?php } ?>
                                    <?php if (!isset($_SESSION["downloadQueryStmt"]["data"]["purchase-status"]) || empty($_SESSION["downloadQueryStmt"]["data"]["purchase-status"])) { ?>
                                        <th scope="col">Status</th>
                                    <?php } ?>
                                    <?php if (!isset($_SESSION["downloadQueryStmt"]["data"]["payment-method"]) || empty($_SESSION["downloadQueryStmt"]["data"]["payment-method"])) { ?>
                                        <th scope="col">Payment Method</th>
                                    <?php } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $index = 1;
                                foreach ($result as $row) { ?>
                                    <tr>
                                        <td><?= $index ?></td>
                                        <td><?= $row["id"] ?></td>
                                        <td><?= $row["fullName"] ?></td>
                                        <td><?= $row["phoneNumber"] ?></td>
                                        <?php if (!isset($_SESSION["downloadQueryStmt"]["data"]["admission-period"]) || empty($_SESSION["downloadQueryStmt"]["data"]["admission-period"])) { ?>
                                            <td><?= $row["admissionPeriod"] ?></td>
                                        <?php } ?>
                                        <?php if (!isset($_SESSION["downloadQueryStmt"]["data"]["form-type"]) || empty($_SESSION["downloadQueryStmt"]["data"]["form-type"])) { ?>
                                            <td><?= $row["formType"] ?></td>
                                        <?php } ?>
                                        <?php if (!isset($_SESSION["downloadQueryStmt"]["data"]["purchase-status"]) || empty($_SESSION["downloadQueryStmt"]["data"]["purchase-status"])) { ?>
                                            <td><?= $row["status"] ?></td>
                                        <?php } ?>
                                        <?php if (!isset($_SESSION["downloadQueryStmt"]["data"]["payment-method"]) || empty($_SESSION["downloadQueryStmt"]["data"]["payment-method"])) { ?>
                                            <td><?= $row["paymentMethod"] ?></td>
                                        <?php } ?>
                                    </tr>
                                <?php
                                    $index++;
                                } ?>
                            </tbody>
            <?php
                            break;
                    }

                    break;
            } ?>
        </table>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            window.print();
            window.close();
        });
    </script>
</body>
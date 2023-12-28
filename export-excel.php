<?php

use Src\Controller\AdminController;

session_start();

//if (!isset($_SESSION["admin_user"])) header("Location: index.php");

if (!isset($_GET["w"])) {
    if (isset($_SERVER['HTTP_REFERER'])) {
        // redirect the user back to the previous page
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

require_once "./bootstrap.php";

$admin = new AdminController();

$result = array();
$title_var = "";

switch ($_GET["w"]) {
    case 'apps':
        $data = array('action' => $_GET["a"], 'country' => $_GET["c"], 'type' => $_GET["t"], 'program' => $_GET["p"]);
        $result = $admin->fetchAppsSummaryData($data);
        switch ($data["action"]) {
            case 'apps-submitted':
                $title_var = "Submitted";
                break;

            case 'apps-in-progress':
                $title_var = "in progress";
                break;

            case 'apps-admitted':
                $title_var = "admitted";
                break;

            case 'apps-unadmitted':
                $title_var = "unadmitted";
                break;

            case 'apps-awaiting':
                $title_var = "awaiting";
                break;
        }
        break;

    default:
        # code...
        break;
}
?>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
</head>

<div>
    <h2 style="text-align: center;" class="m-4">List of all <?= $title_var ?> Applications</h2>
    <table class="table table-borderless datatable table-striped table-hover" style="font-size: 12px;">
        <thead class="table-dark">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Country</th>
                <th scope="col">Application Type</th>
                <th scope="col">Programme (1<sup>st</sup> Choice)</th>
                <th scope="col">Programme (2<sup>nd</sup> Choice)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($result as $ft) { ?>
                <tr>
                    <th scope="row"><?= $ft['id'] ?></th>
                    <td style="font-size: 12px;"><?= $ft["fullname"] ?></td>
                    <td><?= $ft["nationality"] ?></td>
                    <td><?= $ft["app_type"] ?></td>
                    <td><?= $ft["first_prog"] ?></td>
                    <td><?= $ft["second_prog"] ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<script>
    window.print();
    window.close();
</script>
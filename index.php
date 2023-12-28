<?php
session_start();

if (!isset($_SESSION["_adminLogToken"])) {
    $rstrong = true;
    $_SESSION["_adminLogToken"] = hash('sha256', bin2hex(openssl_random_pseudo_bytes(64, $rstrong)));
}
$_SESSION["lastAccessed"] = time();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>RMU Office | Login Portal</title>

    <!-- Favicons -->
    <link href="assets/img/rmu-logo.png" rel="icon">
    <link href="assets/img/rmu-logo.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <!--<link href="https://fonts.gstatic.com" rel="preconnect">-->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .btn-group-xs>.btn,
        .btn-xs {
            padding: 1px 5px;
            font-size: 12px;
            line-height: 1.5;
            border-radius: 3px;
        }

        .fp-header {
            width: 100% !important;
            background-color: #003262 !important;
            height: 60px !important;
        }

        .fp-header>div {
            width: 100% !important;
            height: 100% !important;
        }

        .rmu-logo-letter {
            font-family: "Ubuntu", sans-serif;
            font-size: 40px !important;
            color: #fff;
        }

        .items {
            display: flex !important;
            flex-direction: row !important;
            align-items: center;
            height: inherit;
        }

        .items>img,
        .items>span {
            padding: 2px 7px !important;
        }
    </style>
    <script src="js/jquery-3.6.0.min.js"></script>
</head>

<body>
    <nav class="fp-header">
        <div class="container">
            <div class="items">
                <img src="assets/img/rmu-logo.png" style="width: 60px;">
                <span class="rmu-logo-letter">RMU</span>
            </div>
        </div>
    </nav>

    <main>
        <div class="container">

            <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

                            <div class="card mb-3">

                                <div class="card-body">

                                    <div class="pt-4 pb-2">
                                        <h5 class="card-title text-center pb-0 fs-4">Login to Your Account</h5>
                                        <p class="text-center small">Enter your username & password to login</p>
                                    </div>

                                    <form id="adminLoginForm" class="row g-3 needs-validation" novalidate>

                                        <div class="col-12">
                                            <label for="yourUsername" class="form-label">Username</label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text" id="inputGroupPrepend">@</span>
                                                <input type="text" name="username" class="form-control" id="yourUsername" required>
                                                <div class="invalid-feedback">Please enter your username.</div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label for="yourPassword" class="form-label">Password</label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text bi bi-lock" id="inputGroupPrepend"></span>
                                                <input type="password" name="password" class="form-control" id="yourPassword" required>
                                                <div class="invalid-feedback">Please enter your password!</div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <button class="btn btn-primary w-100" id="submitBtn" type="submit">Login</button>
                                        </div>
                                        <input type="hidden" name="_vALToken" value="<?= $_SESSION['_adminLogToken'] ?>">
                                    </form>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </section>

        </div>
    </main><!-- End #main -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/chart.js/chart.min.js"></script>
    <script src="assets/vendor/echarts/echarts.min.js"></script>
    <script src="assets/vendor/quill/quill.min.js"></script>
    <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>
    <script src="js/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {

            $("#adminLoginForm").on("submit", function(e) {
                e.preventDefault();

                if (!$("#yourUsername").val()) {
                    alert("Username required!");
                    return;
                }

                if (!$("#yourPassword").val()) {
                    alert("Password required!");
                    return;
                }


                $.ajax({
                    type: "POST",
                    url: "endpoint/admin-login",
                    data: new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            window.location.href = result.message + "/";
                        } else {
                            alert(result['message']);
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            });

            $(document).on({
                ajaxStart: function() {
                    $("#submitBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
                },
                ajaxStop: function() {
                    $("#submitBtn").prop("disabled", false).html('Login');
                }
            });

        });
    </script>

</body>

</html>
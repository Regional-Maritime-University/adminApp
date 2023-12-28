<?php
session_start();
//echo $_SERVER["HTTP_USER_AGENT"];
if (isset($_SESSION["adminLogSuccess"]) && $_SESSION["adminLogSuccess"] == true && isset($_SESSION["user"]) && !empty($_SESSION["user"])) {
} else {
    header("Location: index.php");
}

if (!isset($_SESSION['SMSLogin']) && isset($_SESSION['verifySMSCode']) && $_SESSION['verifySMSCode'] == true) {
    if (!isset($_SESSION["_verifySMSToken"])) {
        $rstrong = true;
        $_SESSION["_verifySMSToken"] = hash('sha256', bin2hex(openssl_random_pseudo_bytes(64, $rstrong)));
    }
}

if (isset($_GET['logout']) || strtolower($_SESSION["role"]) != "vendors") {
    session_destroy();
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    header('Location: ../index.php');
}

require_once('../bootstrap.php');

use Src\Controller\AdminController;

$admin = new AdminController();
require_once('../inc/page-data.php');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?= require_once("../inc/head.php") ?>
    <style>
        .hide {
            display: none;
        }

        .display {
            display: block;
        }

        #wrapper {
            display: flex;
            flex-direction: column;
            flex-wrap: wrap;
            justify-content: space-between;
            width: 100% !important;
            height: 100% !important;
        }

        .flex-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .flex-container>div {
            height: 100% !important;
            width: 100% !important;
        }

        .flex-column {
            display: flex !important;
            flex-direction: column !important;
        }

        .flex-row {
            display: flex !important;
            flex-direction: row !important;
        }

        .justify-center {
            justify-content: center !important;
        }

        .justify-space-between {
            justify-content: space-between !important;
        }

        .align-items-center {
            align-items: center !important;
        }

        .align-items-baseline {
            align-items: baseline !important;
        }

        .flex-card {
            display: flex !important;
            justify-content: center !important;
            flex-direction: row !important;
        }

        .form-card {
            height: 100% !important;
            max-width: 425px !important;
            padding: 15px 10px 20px 10px !important;
        }

        .flex-card>.form-card {
            height: 100% !important;
            width: 100% !important;
        }

        .purchase-card-header {
            padding: 0 !important;
            width: 100% !important;
            height: 40px !important;
        }

        .purchase-card-header>h1 {
            font-size: 22px !important;
            font-weight: 600 !important;
            color: #003262 !important;
            text-align: center;
            width: 100%;
        }

        .purchase-card-step-info {
            color: #003262;
            padding: 0px;
            font-size: 14px;
            font-weight: 400;
            width: 100%;
        }

        .purchase-card-footer {
            width: 100% !important;
        }
    </style>
</head>

<body>
    <?= require_once("../inc/header.php") ?>

    <?= require_once("../inc/sidebar.php") ?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Forms Sale</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active">Sell Forms</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section dashboard">

            <div id="flashMessage" class="alert text-center" role="alert"></div>
            <!-- Left side columns -->
            <div class="row" style="display:flex !important; flex-direction:row !important; justify-content: center !important; align-items: center">
                <div class="flex-card">
                    <div class="form-card card">
                        <div class="purchase-card-header">
                            <h1>Verify <span class="heading-title"></span> Phone Number</h1>
                        </div>

                        <hr style="color:#999">

                        <div class="purchase-card-body" style="margin: 0px 10%;">
                            <form id="verifyOTPCodeForm" method="post" enctype="multipart/form-data">
                                <p class="mb-4">Enter the verification code sent to <span class="heading-title"></span> your phone number.</p>
                                <div class="mb-4" style="display:flex !important; flex-direction:row !important; justify-content: space-around !important; align-items:center">
                                    <input class="form-control num me-2" type="text" maxlength="4" style="padding: 10px 10px;text-align:center" name="code" id="code" placeholder="XXXX" required>
                                    <button class="btn btn-primary" type="submit" id="verifyCodeBtn" style="padding: 10px 10px;">Verify</button>
                                </div>
                                <input class="form-control" type="hidden" name="_vSMSToken" id="_vSMSToken" value="<?= $_SESSION["_verifySMSToken"] ?>">
                            </form>
                            <div class="purchase-card-footer flex-row" style="align-items: flex-end;">
                                <span id="timer"></span>
                                <button id="resend-code" class="btn btn-outline-dark btn-xs hide">Resend code</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- End Left side columns -->
        </section>

    </main><!-- End #main -->

    <?= require_once("../inc/footer-section.php") ?>
    <script>
        $(document).ready(function() {
            //get variable(parameters) from url
            function getUrlVars() {
                var vars = {};
                var parts = window.location.href.replace(
                    /[?&]+([^=&]+)=([^&]*)/gi,
                    function(m, key, value) {
                        vars[key] = value;
                    }
                );
                return vars;
            }

            // Set the verification identity type
            if (getUrlVars()["verify"] == "vendor") {
                $(".heading-title").text("Vendor's");
            } else if (getUrlVars()["verify"] == "customer") {
                $(".heading-title").text("Customer's");
            }

            // identifies which buttons were clicked: used for loading
            var triggeredBy = 0;

            // set count interval for resending code
            var count = 1;
            var intervalId = setInterval(() => {
                $("#timer").html("Resend code <b>(" + count + " sec)</b>");
                count = count - 1;
                if (count <= 0) {
                    clearInterval(intervalId);
                    $(' #timer').hide();
                    $('#resend-code').removeClass("hide").addClass("display");
                    return;
                }
            }, 1000);

            $("#resend-code").click(function(e) {
                e.preventDefault();
                triggeredBy = 1;
                let data = {
                    resend_code: "sms",
                    _vSMSToken: $("#_vSMSToken").val()
                };
                $.ajax({
                    type: "POST",
                    url: "../endpoint/resend-code",
                    data: data,
                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            flashMessage("alert-success", result.message);
                            clearInterval(intervalId);
                            $("#timer").show();
                            $('#resend-code').removeClass("display").addClass("hide");
                            count = 1;
                            intervalId = setInterval(() => {
                                $("#timer").html("Resend code <b>(" + count + " sec)</b>");
                                count = count - 1;
                                if (count <= 0) {
                                    clearInterval(intervalId);
                                    $('#timer').hide();
                                    $('#resend-code').removeClass("hide").addClass("display").attr("disabled", false);
                                    return;
                                }
                            }, 1000); /**/
                        } else {
                            flashMessage("alert-danger", result.message);
                        }
                    },
                    error: function(error) {
                        flashMessage("alert-danger", error);
                    }
                });
            });

            $("#verifyOTPCodeForm").on("submit", function(e) {
                e.preventDefault();
                triggeredBy = 2;

                var url = "";
                if (getUrlVars()["verify"] == "vendor") {
                    url = "verifyVendor";
                } else if (getUrlVars()["verify"] == "customer") {
                    url = "verifyCustomer";
                } else {
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: "../endpoint/" + url,
                    data: new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(result) {
                        console.log(result);
                        if (result.success) {
                            if (url == "verifyVendor")
                                window.location.href = result.message;
                            else
                                window.location.href = "confirm.php?status=000&exttrid=" + result.exttrid;
                        } else {
                            flashMessage("alert-danger", result.message);
                        }
                    },
                    error: function(error) {
                        flashMessage("alert-danger", error);
                    }
                });
            });

            $(document).on({
                ajaxStart: function() {
                    if (triggeredBy == 1) $("#resend-code").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> sending...');
                    if (triggeredBy == 2) $("#verifyCodeBtn").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
                },
                ajaxStop: function() {
                    if (triggeredBy == 1) $("#resend-code").prop("disabled", false).html('Resend code');
                    if (triggeredBy == 2) $("#verifyCodeBtn").prop("disabled", false).html('Verify');
                }
            });

            $("#code").focus();

            $(".num").on("keyup", function() {
                if (this.value.length == 4) {
                    $(this).next(":input").focus().select(); //.val(''); and as well clesr
                }
            });

            $("input[type='text']").on("click", function() {
                $(this).select();
            });

            function flashMessage(bg_color, message) {
                const flashMessage = document.getElementById("flashMessage");

                flashMessage.classList.add(bg_color);
                flashMessage.innerHTML = message;

                setTimeout(() => {
                    flashMessage.style.visibility = "visible";
                    flashMessage.classList.add("show");
                }, 500);

                setTimeout(() => {
                    flashMessage.classList.remove("show");
                    setTimeout(() => {
                        flashMessage.style.visibility = "hidden";
                    }, 500);
                }, 5000);
            }
        });
    </script>

</body>

</html>
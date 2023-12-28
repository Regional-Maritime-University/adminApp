<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">
    <div class="d-flex align-items-center justify-content-between">
        <a href="index.php" class="logo d-flex align-items-center">
            <img src="../assets/img/rmu-logo.png" alt="">
            <span class="d-none d-lg-block">RMU / <?= isset($_SESSION["role"]) ? $_SESSION["role"] : "" ?></span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <?php if (isset($adminSetup) && $adminSetup == true) { ?>
        <div class="setDashboardForm-bar" style="display:flex; justify-content:center; width: 100%; margin: 0 25px;">
            <div style="display:flex; justify-content:center;">
                <form class="search-form d-flex align-items-center" method="POST" action="#" id="setDashboardForm" style="min-width: 200px;">
                    <label for="admission-period" class="form-label me-2">Admission Period</label>
                    <select name="admission-period" id="admission-period" class="form-select me-2" style="width: 360px;">
                        <option value="" hidden>Choose</option>
                        <?php
                        $result = $admin->fetchAllAdmissionPeriod();
                        foreach ($result as $value) {
                        ?>
                            <option value="<?= $value["id"] ?>" <?= ($value["id"] == $_SESSION["admin_period"]) ? "selected" : "" ?>><?= $value["info"] . " - " . ($value["active"] ? "<span class='text-success'><b>OPENED</b></span>" : "<span class='text-danger'><b>CLOSED</b></span>") ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </form>
            </div>
        </div>
    <?php } ?>

    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">

            <li class="nav-item dropdown pe-3">

                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <img src="../assets/img/icons8-circled-user-male-skin-type-5-96.png" alt="Profile" class="rounded-circle">
                    <span class="d-none d-md-block dropdown-toggle ps-2"><?= isset($_SESSION["user"]) ? $admin->fetchUserName($_SESSION["user"])[0]["userName"] : "" ?></span>
                </a><!-- End Profile Iamge Icon -->

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6><?= isset($_SESSION["user"]) ? $admin->fetchFullName($_SESSION["user"])[0]["fullName"] : "" ?></h6>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="../user-profile.php">
                            <i class="bi bi-person"></i>
                            <span>My Profile</span>
                        </a>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="?logout=true">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Sign Out</span>
                        </a>
                    </li>

                </ul><!-- End Profile Dropdown Items -->
            </li><!-- End Profile Nav -->

        </ul>
    </nav><!-- End Icons Navigation -->

</header><!-- End Header -->
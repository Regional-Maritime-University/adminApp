<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <a class="nav-link " href="../<?= isset($_SESSION["role"]) ? strtolower($_SESSION["role"]) : "" ?>/">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li><!-- End Dashboard Nav -->

        <?php
        if (isset($_SESSION["role"]) && strtolower($_SESSION["role"]) == "admissions" && isset($_SESSION["role"]) && strtolower($_SESSION["user_type"]) == "admin") {
        ?>
            <li class="nav-item">
                <a class="nav-link collapsed" href="backup.php">
                    <i class="bi bi-database-fill-down"></i>
                    <span>Backup Database</span>
                </a>
            </li><!-- End Application Page Nav -->
        <?php } ?>

        <?php
        if (isset($_SESSION["role"]) && (strtolower($_SESSION["role"]) == "admissions" || strtolower($_SESSION["role"]) == "developers" || strtolower($_SESSION["role"]) == "accounts") && isset($_SESSION["role"]) && strtolower($_SESSION["user_type"]) == "admin") {
        ?>
            <li class="nav-item">
                <a class="nav-link collapsed" href="../<?= strtolower($_SESSION["role"]) ?>/user-account.php">
                    <i class="bi bi-shield-shaded"></i>
                    <span>User Accounts</span>
                </a>
            </li><!-- End User Account Page Nav -->
        <?php } ?>

    </ul>
</aside><!-- End Sidebar-->
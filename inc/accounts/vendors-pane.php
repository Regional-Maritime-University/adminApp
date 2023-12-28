<!--Vendors Pane-->
<div class="tab-pane fade show active" id="vendors-tab-pane" role="tabpanel" aria-labelledby="vendors-tab" tabindex="0">
    <div class="mt-4">

        <div class="row">

            <div class="alert alert-default" style="padding-left: 10px !important; padding-right: 10 !important">
                <span>
                    <button id="addNewVendorBtn" class="btn btn-success" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Add new vendor account">Add vendors</button>
                </span>
            </div>

            <div class="col-lg-5">
                <h2>Main Branch</h2>
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col" style="width: 250px;">Company</th>
                            <th scope="col">Phone Number</th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $vendors = $admin->fetchAllVendorsMainBranch();
                        if (!empty($vendors)) {
                            $i = 1;
                            foreach ($vendors as $vendor) {
                        ?>
                                <tr>
                                    <th scope="row"><?= $i ?></th>
                                    <td><?= $vendor["company"] ?></td>
                                    <td><?= $vendor["phone_number"] ?></td>
                                    <td id="<?= $vendor["company"] ?>" class="view-vendor"><span style="cursor:pointer;" class="bi bi-eye text-success" title="View <?= $vendor["company"] ?> Other branches"></span></td>
                                    <td id="<?= $vendor["id"] ?>" data-branchType="main" class="edit-vendor"><span style="cursor:pointer;" class="bi bi-pencil-square text-primary" title="Edit <?= $vendor["company"] ?>"></span></td>
                                    <td id="<?= $vendor["id"] ?>" class="delete-vendor"><span style="cursor:pointer;" class="bi bi-trash text-danger" title="Delete <?= $vendor["company"] ?>"></span></td>
                                </tr>
                        <?php
                                $i++;
                            }
                        }
                        ?>
                        <tr></tr>
                    </tbody>
                </table>
            </div>

            <div class="col-lg-1">
            </div>

            <div id="other-branches" class="col-lg-5">
                <h2 id="sub-branches-h">Sub Branches</h2>
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col" style="width: 250px;">Branch</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody class="sub-branches-tb">
                    </tbody>
                </table>
            </div>

            <!--Add document form modal-->
            <div class="modal fade" id="addOrUpdateVendorModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-2" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class=" modal-header">
                            <h5 class="modal-title" id="staticBackdropLabel">Upload <span class="doc-type">Certificate</span></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addOrUpdateVendorForm" method="post" enctype="multipart/form-data">
                                <div class="row mb-4 mt-4">
                                    <div class="col-8">
                                        <label for="v-name">Company Name</label>
                                        <input type="text" class="transform-text form-control form-control-sm" name="v-name" id="v-name" placeholder="Name">
                                    </div>
                                    <div class="col-4">
                                        <label for="v-code">Company Code</label>
                                        <input type="text" class="transform-text form-control form-control-sm" name="v-code" id="v-code" placeholder="Code" maxlength="3" pattern="[A-Za-z]{3}" title="Company code must be 3 characters A - Z">
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <h4 id="main-branch-h4"><u>Main Branch Account (Admin)</u> </h4>
                                    <div class="col">
                                        <label for="v-email">Email</label>
                                        <input type="text" class="form-control form-control-sm" name="v-email" id="v-email" placeholder="Email">
                                    </div>
                                    <div class="col">
                                        <label for="v-phone">Phone No.</label>
                                        <input type="text" class="form-control form-control-sm" name="v-phone" id="v-phone" placeholder="02441234567">
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col">
                                        <label for="v-email" style="margin-right: 15px;">API User? </label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input v-api-user-check" type="radio" name="v-api-user" id="v-api-user-yes" value="YES">
                                            <label class="form-check-label" for="v-api-user-yes"> YES </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input v-api-user-check" type="radio" name="v-api-user" id="v-api-user-no" value="NO" checked>
                                            <label class="form-check-label" for="v-api-user-no"> NO </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4" id="other-branches-file-upload">
                                    <h4><u>Other Branches Account</u> </h4>
                                    <div class="col">
                                        <input type="file" name="other-branches" id="other-branches" accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                                        <div class="alert alert-warning mt-4" role="alert">
                                            <h4 class="alert-heading">Instruction for File Upload!</h4>
                                            <p>Please follow the instructions below to successfully upload branches</p>
                                            <hr>
                                            <ul>
                                                <li>The allowed file formats are <b>.xlsx</b> and <b>.xls</b></li>
                                                <li><a href="../download-file.php?type=branch">Click Here</a> to download sample.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <button type="submit" class="btn btn-primary btn-sm" id="v-action-btn">Save</button>
                                </div>
                                <input type="hidden" name="v-action" id="v-action" value="add">
                                <input type="hidden" name="v-id" id="v-id" value="">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!--End of Modal-->

            <!-- Vendor list modal-->
            <div class="modal fade" id="vendorsBranchList" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-2" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class=" modal-header">
                            <h5 class="modal-title" id="vendorsBranchListLabel"><span class="branch-name">Branch</span> Branch</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Role</th>
                                        <th scope="col">Phone Number</th>
                                        <th scope="col"></th>
                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody class="branches-list-tb"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!--End of Modal-->

        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        $("#addNewVendorBtn").on("click", () => {
            $("#addOrUpdateVendorForm").trigger("reset");
            $("#v-name").prop("readonly", false).attr("style", "");
            $("#addOrUpdateVendorModal").modal("toggle");
            $("#other-branches-file-upload").show();
        })

        function resetVendorForm() {
            $("#v-id").val("");
            $("#v-name").val("");
            $("#v-tin").val("");
            $("#v-email").val("");
            $("#v-phone").val("");
            $("#v-address").val("");
        }

        $(".v-api-user-check").click(function() {
            if ($('#v-api-user-yes').is(':checked')) $("#other-branches-file-upload").slideUp();
            else if ($('#v-api-user-no').is(':checked')) $("#other-branches-file-upload").slideDown();
        });

        $("#addOrUpdateVendorForm").on("submit", function(e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: "../endpoint/vendor-form",
                data: new FormData(this),
                contentType: false,
                cache: false,
                processData: false,
                success: function(result) {
                    console.log(result);
                    if (result.message == "logout") {
                        window.location.href = "?logout=true";
                        return;
                    }
                    alert(result.message);
                    window.location.reload();
                },
                error: function(error) {
                    console.log(error);
                }
            });
            resetVendorForm();
        });

        $(document).on("click", ".view-vendor", function(e) {
            var tr = $(this).closest('tr');
            tr.addClass("table-active");

            let data = {
                vendor_key: $(this).attr("id")
            }

            $.ajax({
                type: "POST",
                url: "../endpoint/vendor-sub-branches-group",
                data: data,
                success: function(result) {
                    console.log(result);
                    if (result.success) {
                        $("#sub-branches-h").text(result.message[0]["company"] + " Branches");
                        $(".sub-branches-tb").html("");
                        $.each(result.message, function(index, data) {
                            $(".sub-branches-tb").append(
                                '<tr>' +
                                '<th scope="row">' + (index + 1) + '</th>' +
                                '<td>' + data["branch"] + '</td>' +
                                '<td data-branch="' + data["branch"] + '" class="view-vendor-list">' +
                                '<span style="cursor:pointer;" class="bi bi-eye text-success" title="View ' + data["branch"] + ' vendors account"> View</span>' +
                                '</td>' +
                                '</tr>'
                            );
                        });
                    } else {
                        if (result.message == "logout") {
                            window.location.href = "?logout=true";
                            return;
                        }
                        alert(result.message)
                    }

                },
                error: function(error) {
                    console.log(error);
                }
            });
        });

        $(document).on("click", ".view-vendor-list", function() {
            let branch_ds = this.dataset.branch;
            let data = {
                vendor_branch: branch_ds
            }

            console.log(branch_ds);

            $.ajax({
                type: "POST",
                url: "../endpoint/vendor-sub-branches",
                data: data,
                success: function(result) {
                    console.log(result);
                    if (result.success) {
                        $("#sub-branches-h").text(result.message[0]["company"] + " Branches");
                        $(".branches-list-tb").html("");
                        $.each(result.message, function(index, data) {
                            $(".branches-list-tb").append(
                                '<tr>' +
                                '<th scope="row">' + (index + 1) + '</th>' +
                                '<td>' + data["role"] + '</td>' +
                                '<td>' + data["phone_number"] + '</td>' +
                                '<td id="' + data["id"] + '" data-branchType="sub" class="edit-vendor">' +
                                '<span style="cursor:pointer;" class="bi bi-pencil-square text-primary" title="Edit ' + data["phone_number"] + '"> </span>' +
                                '</td>' +
                                '<td id="' + data["id"] + '" class="delete-vendor">' +
                                '<span style="cursor:pointer;" class="bi bi-trash text-danger" title="Delete ' + data["phone_number "] + '" ></span>' +
                                '</td>' +
                                '</tr>'
                            );
                        });

                        $("#vendorsBranchList").modal("toggle");
                    } else {
                        if (result.message == "logout") {
                            window.location.href = "?logout=true";
                            return;
                        }
                        alert(result.message)
                    }

                },
                error: function(error) {
                    console.log(error);
                }
            });
        });

        $(document).on("click", ".view-branch", function(e) {
            var tr = $(this).closest('tr');
            tr.addClass("table-active");

            let data = {
                vendor_key: $(this).attr("id")
            }

            $.ajax({
                type: "POST",
                url: "../endpoint/vendor-sub-branches",
                data: data,
                success: function(result) {
                    console.log(result);
                    if (result.success) {
                        $("#sub-branches-h").text(result.message[0]["company"] + " Branches");
                        $(".sub-branches-tb").html("");
                        $.each(result.message, function(index, data) {
                            $(".sub-branches-tb").append(
                                '<tr>' +
                                '<th scope="row">' + (index + 1) + '</th>' +
                                '<td>' + data["branch"] + '</td>' +
                                '<td>' + data["phone_number"] + '</td>' +
                                '<td id="' + data["id"] + '" data-branchType="sub" class="edit-vendor">' +
                                '<span style="cursor:pointer;" class="bi bi-pencil-square text-primary" title="Edit ' + data["branch"] + '"> </span>' +
                                '</td>' +
                                '<td id="' + data["id"] + '" class="delete-vendor">' +
                                '<span style="cursor:pointer;" class="bi bi-trash text-danger" title="Delete ' + data["branch "] + '" ></span>' +
                                '</td>' +
                                '</tr>'
                            );
                        });
                    } else {
                        if (result.message == "logout") {
                            window.location.href = "?logout=true";
                            return;
                        }
                        alert(result.message)
                    }

                },
                error: function(error) {
                    console.log(error);
                }
            });
        });

        $(document).on("click", ".edit-vendor", function(e) {
            let ds = this.dataset.branchtype;
            let data = {
                vendor_key: $(this).attr("id")
            }

            $.ajax({
                type: "GET",
                url: "../endpoint/vendor-form",
                data: data,
                success: function(result) {
                    console.log(result);
                    if (result.success) {
                        $("#v-action").attr("value", "update");
                        $(".header-title").text("Edit Vendor Account");
                        $("#v-action-btn").text("Save");
                        $("#v-id").val(result.message[0].id);
                        $("#v-name").val(result.message[0].company).prop("readonly", true).attr("style", "background-color: #f1f1f1; color: #000;");
                        $("#v-code").val(result.message[0].company_code).prop("readonly", true).attr("style", "background-color: #f1f1f1; color: #000;");
                        $("#v-email").val(result.message[0].user_name);
                        $("#v-phone").val(result.message[0].phone_number);

                        if (result.message[0].api_user) $("#v-api-user-yes").prop("checked", true);
                        else $("#v-api-user-no").prop("checked", true);

                        if ($('#v-api-user-yes').is(':checked')) {
                            $("#other-branches-file-upload").slideUp();
                            $('#v-api-user-no').prop("disabled", true);
                        } else if ($('#v-api-user-no').is(':checked')) {
                            $("#other-branches-file-upload").slideDown();
                            $('#v-api-user-yes').prop("disabled", true);
                        }

                        if (ds == "main") {
                            $("#main-branch-h4").show();
                            $("#other-branches-file-upload").show();
                        }

                        if (ds == "sub") {
                            $("#main-branch-h4").hide();
                            $("#other-branches-file-upload").hide();
                        };

                        $("#addOrUpdateVendorModal").modal("toggle");
                    } else {
                        if (result.message == "logout") {
                            window.location.href = "?logout=true";
                            return;
                        }
                        alert(result.message)
                    };

                },
                error: function(error) {
                    console.log(error);
                }
            });
        });

        $(document).on("click", ".delete-vendor", function(e) {
            let conf = confirm("Are you sure you want to delete this vednor's account?");
            if (!conf) return;

            var data = {
                vendor_key: $(this).attr("id")
            }

            $.ajax({
                type: "DELETE",
                url: "../endpoint/vendor-form",
                data: data,
                success: function(result) {
                    console.log(result);
                    if (result.message == "logout") {
                        window.location.href = "?logout=true";
                        return;
                    }
                    alert(result.message);
                    window.location.reload();
                },
                error: function(error) {
                    console.log(error);
                }
            });
            resetVendorForm();
        });
    });
</script>
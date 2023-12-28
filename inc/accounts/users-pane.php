<!--Vendors Pane-->
<div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-7">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col" style="width: 250px;">Name</th>
                            <th scope="col" style="width: 250px;">Username</th>
                            <th scope="col">Role</th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sys_users = $admin->fetchAllNotVendorSystemUsers();
                        if (!empty($sys_users)) {
                            $i = 1;
                            foreach ($sys_users as $user) {
                        ?>
                                <tr>
                                    <th scope="row"><?= $i ?></th>
                                    <td><?= $user["first_name"] . " " . $user["last_name"] ?></td>
                                    <td><?= $user["user_name"] ?></td>
                                    <td><?= $user["role"] ?></td>
                                    <td>
                                        <span id="<?= $user["id"] ?>" style="cursor:pointer;" class="bi bi-pencil-square text-primary edit-user" title="Edit"></span>
                                    </td>
                                    <td>
                                        <span id="<?= $user["id"] ?>" style="cursor:pointer;" class="bi bi-trash text-danger delete-user" title="Delete"></span>
                                    </td>
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
            <div class="col-lg-5">
                <form id="addOrUpdateUserForm" method="post" enctype="multipart/form-data">
                    <div class="card">
                        <h5 class="card-header">Add New User</h5>
                        <div class="card-body">
                            <fieldset class="mb-4">
                                <legend>Personal Detail</legend>
                                <div class="row" style="display: flex; flex-direction:row; justify-content: space-between">
                                    <div class="col mb-2">
                                        <label for="user-fname">First Name</label>
                                        <input type="text" class="form-control form-control-sm" name="user-fname" id="user-fname" placeholder="First Name" required>
                                    </div>
                                    <div class="col mb-2">
                                        <label for="user-lname">Last Name</label>
                                        <input type="text" class="form-control form-control-sm" name="user-lname" id="user-lname" placeholder="Last Name" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col mb-2">
                                        <label for="user-email">Email Address</label>
                                        <input type="email" class="form-control form-control-sm" name="user-email" id="user-email" placeholder="Email" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col mb-2">
                                        <label for="user-role">Role</label>
                                        <select style="width: 100%;" name="user-role" id="user-role" class="form-select form-select-sm" required>
                                            <option value="" hidden>Choose...</option>
                                            <?php if (isset($_SESSION["role"]) && strtolower($_SESSION["role"]) == "accounts") { ?>
                                                <option value="Accounts" selected>Accounts</option>
                                            <?php } else if (isset($_SESSION["role"]) && strtolower($_SESSION["role"]) == "admissions") { ?>
                                                <option value="Admissions" selected>Admissions</option>
                                            <?php } else if (isset($_SESSION["role"]) && strtolower($_SESSION["role"]) == "developers") { ?>
                                                <option value="Accounts">Accounts</option>
                                                <option value="Admissions">Admissions</option>
                                                <option value="Developers">Developers</option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col mb-2">
                                        <label for="user-type">User Type</label>
                                        <select style="width: 100%;" name="user-type" id="user-type" class="form-select form-select-sm" required>
                                            <option value="" hidden>Choose...</option>
                                            <option value="admin">Admin</option>
                                            <option value="user">User</option>
                                        </select>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="mb-4" style="border-top: 1px solid #aaa; padding: 10px 0px">
                                <div style="display: flex; flex-direction:row; justify-content: space-around">

                                    <p style="font-weight: bolder;">Privileges: </p>
                                    <div class="mb-3" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="User can view data">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="select" name="privileges[]" id="select" checked disabled>
                                            <label class="form-check-label" for="select">
                                                View
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="User can add data">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="insert" name="privileges[]" id="insert">
                                            <label class="form-check-label" for="insert">
                                                Add
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="User can edit data">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="update" name="privileges[]" id="update">
                                            <label class="form-check-label" for="update">
                                                Edit
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="User can remove data">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="delete" name="privileges[]" id="delete">
                                            <label class="form-check-label" for="delete">
                                                Remove
                                            </label>
                                        </div>
                                    </div>
                                </div>

                            </fieldset>

                            <div>
                                <button type="submit" style="width: 100px" class="btn btn-primary btn-sm" id="user-action-btn">Add</button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="user-action" id="user-action" value="add">
                    <input type="hidden" name="user-id" id="user-id" value="">
                </form>

                <!-- Add form type modal form-->
                <div class="modal fade" id="addFormType" tabindex="-1" aria-labelledby="addFormTypeLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="staticBackdropLabel">Form Type</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="formTypeForm" action="#" method="post" class="">
                                    <div class="card">
                                        <h5 class="card-header">Add Form</h5>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <label for="form-name">Action</label>
                                                <div style="display:flex; flex-direction:row; justify-content:baseline; align-items:baseline;">
                                                    <select name="form-type" id="form-type" class="form-select form-select-sm">
                                                        <option value="add">Add</option>
                                                        <option value="Update">Update</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="form-price">Form Name</label>
                                                <input type="text" class="form-control form-control-sm" name="form-price" id="form-price" placeholder="0.00">
                                            </div>
                                            <div>
                                                <button type="submit" class="btn btn-primary btn-sm">Add</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        function resetUserForm() {
            $("#user-id").val("");
            $("#user-fname").val("");
            $("#user-lname").val("");
            $("#user-email").val("");
            $("#user-role").val("");
            $("#user-role option:selected").attr("selected", false);
        }

        $("#user-role").on("change", function() {
            if ($(this).val() == "Vendors") {
                $("#vendor_info").toggle("slow");
            } else {
                $("#vendor_info").hide("slow");
            }
        });

        $("#addOrUpdateUserForm").on("submit", function(e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: "../endpoint/user-form",
                data: new FormData(this),
                contentType: false,
                cache: false,
                processData: false,
                success: function(result) {
                    console.log(result);
                    if (result.success) {
                        alert(result.message);
                        window.location.reload();
                    } else {
                        if (result.message == "logout") {
                            window.location.href = "?logout=true";
                            return;
                        }
                        alert(result.message);
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });
            resetUserForm();
        });

        $(".edit-user").click(function(e) {
            let data = {
                user_key: $(this).attr("id")
            }

            $.ajax({
                type: "GET",
                url: "../endpoint/user-form",
                data: data,
                success: function(result) {
                    console.log(result);
                    if (result.success) {
                        $("#user-action").attr("value", "update");
                        $(".card-header").text("Update User");
                        $("#user-action-btn").text("Update");
                        $("#user-id").val(result.message[0].id);
                        $("#user-fname").val(result.message[0].first_name);
                        $("#user-lname").val(result.message[0].last_name);
                        $("#user-email").val(result.message[0].user_name);
                        $("#user-role option:selected").attr("selected", false);
                        $("#user-role" + " option[value='" + result.message[0].role + "']").attr('selected', true);
                        $("#user-type option:selected").attr("selected", false);
                        $("#user-type" + " option[value='" + result.message[0].type + "']").attr('selected', true);
                        $("#select").attr('checked', parseInt(result.message[0].select) ? true : false);
                        $("#insert").attr('checked', parseInt(result.message[0].insert) ? true : false);
                        $("#update").attr('checked', parseInt(result.message[0].update) ? true : false);
                        $("#delete").attr('checked', parseInt(result.message[0].delete) ? true : false);

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

        $(".delete-user").click(function(e) {
            let conf = confirm("Are you sure you want to delete this user's account?");
            if (!conf) return;

            var data = {
                user_key: $(this).attr("id")
            }

            $.ajax({
                type: "DELETE",
                url: "../endpoint/user-form",
                data: data,
                success: function(result) {
                    console.log(result);
                    if (result.success) {
                        alert(result.message);
                        window.location.reload();
                    } else {
                        if (result.message == "logout") {
                            window.location.href = "?logout=true";
                            return;
                        }
                        alert(result.message);
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });
            resetUserForm();
        });
    });
</script>
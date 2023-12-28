<!--Programmes Pane-->
<div class="tab-pane fadeshow active" id="programmes-tab-pane" role="tabpanel" aria-labelledby="programmes-tab" tabindex="0">
    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-8">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col" style="width: 250px;">Program Name</th>
                            <th scope="col">Type</th>
                            <th scope="col">Weekend</th>
                            <th scope="col">Group</th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $programmes = $admin->fetchAllPrograms();
                        if (!empty($programmes)) {
                            $i = 1;
                            foreach ($programmes as $prog) {
                        ?>
                                <tr>
                                    <th scope="row"><?= $i ?></th>
                                    <td><?= $prog["name"] ?></td>
                                    <td><?= $prog["type"] ?></td>
                                    <td><?= $prog["weekend"] ? '<span class="bi bi-check-lg text-success"></span>' : '<span class="bi bi-x-lg text-danger"></span>' ?></td>
                                    <td><?= $prog["group"] ?></td>
                                    <td id="<?= $prog["id"] ?>" class="edit-prog"><span style="cursor:pointer;" class="bi bi-pencil-square text-primary" title="Edit <?= $prog["name"] ?>"></span></td>
                                    <td id="<?= $prog["id"] ?>" class="delete-prog"><span style="cursor:pointer;" class="bi bi-trash text-danger" title="Delete <?= $prog["name"] ?>"></span></td>
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
            <div class="col-lg-4">
                <form id="addOrUpdateProgrammeForm" method="post" enctype="multipart/form-data">
                    <div class="card">
                        <h5 class="card-header">Add New Programme</h5>
                        <div class="card-body">
                            <div class="mb-2">
                                <label for="prog-type">Type</label>
                                <div style="display:flex; flex-direction:row; justify-content:baseline; align-items:baseline;">
                                    <select name="prog-type" id="prog-type" class="form-select form-select-sm">
                                        <option value="0">Choose...</option>
                                        <?php
                                        $data = $admin->fetchAvailableformTypes();
                                        foreach ($data as $ft) {
                                        ?>
                                            <option value="<?= $ft['id'] ?>"><?= $ft['name'] ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="prog-name">Name</label>
                                <input type="text" style="text-transform: uppercase;" class="form-control form-control-sm" name="prog-name" id="prog-name" placeholder="Programme Name">
                            </div>
                            <div class="mb-3 form-check" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Check if this programme available to weekend students.">
                                <input class="form-check-input" type="checkbox" name="prog-wkd" id="prog-wkd" value="off">
                                <label class="form-check-label" for="prog-wkd">
                                    Weekend available?
                                </label>
                            </div>
                            <div class="mb-3" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Field is need for appropriate students admission processing.">
                                <label for="form-name">Group</label>
                                <select name="prog-grp" id="prog-grp" class="form-select form-select-sm">
                                    <option value="0">Choose...</option>
                                    <option value="M">Masters (Postgraduate related programme)</option>
                                    <option value="A">A (Engineering and science related programme)</option>
                                    <option value="B">B (General programme)</option>
                                </select>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary btn-sm" id="prog-action-btn">Add</button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="prog-action" id="prog-action" value="add">
                    <input type="hidden" name="prog-id" id="prog-id" value="">
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
                                <button type="button" class="btn btn-primary">Understood</button>
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
        function resetProgForm() {
            $("#prog-id").val("");
            $("#prog-type option:selected").attr("selected", false);
            $("#prog-name").val("");
            $("#prog-wkd").attr("checked", false);
            $("#prog-grp option:selected").attr("selected", false);
        }

        $("#addOrUpdateProgrammeForm").on("submit", function(e) {
            e.preventDefault();

            $.ajax({
                type: "POST",
                url: "../endpoint/prog-form",
                data: new FormData(this),
                contentType: false,
                cache: false,
                processData: false,
                success: function(result) {
                    console.log(result);
                    if (result.success) {
                        alert(result.message);
                        resetProgForm();
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
        });

        $(".edit-prog").click(function(e) {
            let data = {
                prog_key: $(this).attr("id")
            }

            $.ajax({
                type: "GET",
                url: "../endpoint/prog-form",
                data: data,
                success: function(result) {
                    console.log(result);
                    if (result.success) {
                        $("#prog-action").attr("value", "update");
                        $(".card-header").text("Update Programme");
                        $("#prog-action-btn").text("Update");
                        $("#prog-id").val(result.message[0].id);
                        $("#prog-type option:selected").attr("selected", false);
                        $("#prog-type" + " option[value='" + result.message[0].type + "']").attr('selected', true);
                        $("#prog-name").val(result.message[0].name);
                        $("#prog-wkd").attr("checked", !!result.message[0].weekend);
                        $("#prog-grp option:selected").attr("selected", false);
                        $("#prog-grp" + " option[value='" + result.message[0].group + "']").attr('selected', true);
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

        $(".delete-prog").click(function(e) {
            alert($(this).attr("id"))
            var data = {
                prog_key: $(this).attr("id")
            }

            $.ajax({
                type: "DELETE",
                url: "../endpoint/prog-form",
                data: data,
                success: function(result) {
                    console.log(result);
                    if (result.success) {
                        alert(result.message);
                        resetProgForm();
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
        });
    });
</script>
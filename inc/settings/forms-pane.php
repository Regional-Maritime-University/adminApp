<!--Forms Pane-->
<div class="tab-pane fade show active" id="forms-tab-pane" role="tabpanel" aria-labelledby="forms-tab" tabindex="0">
    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-1">
            </div>
            <div class="col-lg-5">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Name</th>
                            <th scope="col">Price ($)</th>
                            <th scope="col"></th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $forms = $admin->fetchAllFormPriceDetails();
                        if (!empty($forms)) {
                            $i = 1;
                            foreach ($forms as $form) {
                        ?>
                                <tr>
                                    <th scope="row"><?= $i ?></th>
                                    <td><?= $form["form_name"] ?></td>
                                    <td><?= $form["amount"] ?></td>
                                    <td id="<?= $form["id"] ?>" class="edit-form"><span style="cursor:pointer;" class="bi bi-pencil-square text-primary" title="Edit <?= $form["form_name"] ?>"></span></td>
                                    <td id="<?= $form["id"] ?>" class="delete-form"><span style="cursor:pointer;" class="bi bi-trash text-danger" title="Delete <?= $form["form_name"] ?>"></span></td>
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
            <div class="col-lg-2">
            </div>
            <div class="col-lg-3">
                <form id="addUpdateFormPriceForm" action="#" method="post" class="">
                    <div class="card">
                        <h5 class="card-header">Add Form Price</h5>
                        <div class="card-body">
                            <div class="mb-2">
                                <label for="form-type">Form Category</label>
                                <div style="display:flex; flex-direction:row; justify-content:baseline; align-items:baseline;">
                                    <select name="form-type" id="form-type" class="form-select form-select-sm">
                                        <option value="0">Select</option>
                                        <?php
                                        $data = $admin->getFormCategories();
                                        foreach ($data as $ft) {
                                        ?>
                                            <option value="<?= $ft['id'] ?>"><?= $ft['name'] ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                    <span class="bi bi-plus-circle-fill text-success" style="margin-inline-start: 5px;" data-bs-toggle="modal" data-bs-target="#addFormType"></span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="form-name">Form Name</label>
                                <input type="text" class="form-control form-control-sm" name="form-name" id="form-name" placeholder="Form Title/Name">
                            </div>
                            <div class="mb-3">
                                <label for="form-price">Price</label>
                                <input type="text" class="form-control form-control-sm" name="form-price" id="form-price" placeholder="0.00">
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary btn-sm" id="fp-action-btn">Add</button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="p-action" id="p-action" value="add">
                    <input type="hidden" name="p-id" id="p-id" value="">
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
                                <form id="addUpdateFormTypeForm" action="#" method="post" class="">
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
            <div class="col-lg-1">
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        function resetPriceForm() {
            $("#form-type option:selected").attr("selected", false);
            $("#form-price").attr("value", "");
        }

        $("#addUpdateFormPriceForm").submit(function(e) {
            e.preventDefault();

            var data = {
                action: $("#p-action").val(),
                form_type: $("#form-type").val(),
                form_name: $("#form-name").val(),
                form_price: $("#form-price").val(),
                form_id: $("#p-action").val() == "update" ? $("#p-id").val() : 0
            }

            $.ajax({
                type: "POST",
                url: "../endpoint/form-price",
                data: data,
                success: function(result) {
                    console.log(result);
                    if (result.message == "logout") {
                        window.location.href = "?logout=true";
                        return;
                    }
                    alert(result.message);
                    if (result.success) {
                        window.location.reload();
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });

            resetPriceForm();
        });

        $(".edit-form").click(function(e) {
            let data = {
                form_key: $(this).attr("id")
            }

            $.ajax({
                type: "GET",
                url: "../endpoint/form-price",
                data: data,
                success: function(result) {
                    console.log(result);
                    if (result.success) {
                        $("#p-action").attr("value", "update");
                        $(".card-header").text("Update Form Price");
                        $("#fp-action-btn").text("Update");
                        $("#p-id").attr("value", result.message[0].fp_id);
                        $("#form-name").val(result.message[0].fp_name);
                        $("#form-price").val(result.message[0].amount);
                        $("#form-type option:selected").attr("selected", false);
                        $("#form-type" + " option[value='" + result.message[0].ft_id + "']").attr('selected', true);
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

        $(".delete-form").click(function(e) {
            if (!confirm("Are you sure you want to delete this form?")) return;

            var data = {
                form_key: $(this).attr("id")
            }

            $.ajax({
                type: "DELETE",
                url: "../endpoint/form-price",
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
        });
    });
</script>
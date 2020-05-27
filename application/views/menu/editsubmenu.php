<div class="container">
    <div class="row">
        <div class="col-md-8">

            <div class="card">
                <div class="card-header">
                    Form Edit Submenu
                </div>
                <div class="card-body">

                    <form action="<?= base_url('menu/submenu'); ?>" method="post">
                        <div class="modal-body">

                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?= $user['url']; ?>">
                            </div>

                            <div class="form-group">
                                <label for="menu_id">Menu_id</label>
                                <select class="form-control" id="menu_id" name="menu_id">
                                    <option>Select Menu</option>
                                    <?php foreach ($menu as $m) : ?>
                                        <option value="<?= $m['id']; ?>"><?= $m['menu']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group mt-3">
                                <label for="url">Url</label>
                                <input type="text" class="form-control" id="url" name="url" placeholder="url...">
                            </div>

                            <div class="form-group">
                                <label for="icon">Icon </label>
                                <input type="text" class="form-control" id="icon" name="icon" placeholder="icon...">
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" checked>
                                    <label class="form-check-label" for="is_active">
                                        Active?
                                    </label>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add</button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
<div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">

        <div class="col-md-6">

            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">

                        <div class="col-md">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">Forgot your password?</h1>
                                </div>

                                <?= $this->session->flashdata('message'); ?>


                                <form action="<?= base_url('auth/forgotpassword'); ?>" method="post" class="user">
                                    <div class="form-group">
                                        <input type="text" class="form-control form-control-user" name="email" id="email" placeholder="Enter Email Address..." value="<?= set_value('email'); ?>">
                                        <?= form_error('email', '<small  class="text-danger pl-3">', '</small>') ?>
                                    </div>

                                    <div class="form-group">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-user btn-block">
                                        resset
                                    </button>
                                </form>
                                <hr>
                                <div class="text-center">
                                    <a class="small" href="<?= base_url('auth/registration'); ?>">Create an Account!</a>
                                </div>
                                <div class="text-center">
                                    <a class="small" href="<?= base_url('auth'); ?>">Back to login</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
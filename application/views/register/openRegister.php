<section class="main-content-wrapper">
    <?php
    if ($this->session->flashdata('exception_3')) {

        echo '<section class="alert-wrapper"><div class="alert alert-danger alert-dismissible"> 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <div class="alert-body"><p><i class="m-right fa fa-check"></i>';
        echo escape_output($this->session->flashdata('exception_3'));unset($_SESSION['exception_3']);
        echo '</p></div></div></section>';
    }
    ?>

    <section class="content-header">
        <h3 class="top-left-header">
            <?php echo lang('open_register'); ?>
        </h3>
    </section>


    <div class="box-wrapper">
            <!-- general form elements -->
            <div class="table-box">
                <!-- form start -->
                <?php echo form_open(base_url('Register/addBalance')); ?>
                <input type="hidden" name="opening_balance" id="opening_balance" class="opening_balance_hidden" value="0">
                <div>
                    <div class="row">
                        <?php foreach (getAllPaymentMethods(5) as $value):?>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><?php echo escape_output($value->name); ?></label>
                                <input type="hidden" value="<?php echo escape_output($value->name); ?>" name="payment_names[]">
                                <input type="hidden" value="<?php echo escape_output($value->id); ?>" name="payment_ids[]">
                                <input tabindex="1" onfocus="select();" type="text" name="payments[]" class="form-control cal_row"
                                    placeholder="<?php echo lang('opening_balance'); ?>"
                                    value="0">
                            </div>
                            <?php if (form_error('opening_balance')) { ?>
                            <div class="callout callout-danger my-2">
                                <?php echo form_error('opening_balance'); ?>
                            </div>
                            <?php } ?>
                        </div>
                        <?php endforeach;?>
                    </div>
                </div>
                <!-- /.box-body -->
                <p>&nbsp;</p>
                <a><?php echo lang('total_opening_balance'); ?>: <span class="total_opening_balance">0.00</span></a>
                <p>&nbsp;</p>
                <div class="box-footer px-0">
                    <button type="submit" name="submit" value="submit"
                        class="btn bg-blue-btn"><?php echo lang('submit'); ?></button>

                </div>
                <?php echo form_close(); ?>
            </div>
    </div>
    <script type="text/javascript" src="<?php echo base_url('frequent_changing/js/opening_register.js'); ?>"></script>
</section>
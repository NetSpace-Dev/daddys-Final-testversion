<link rel="stylesheet" href="<?php echo base_url() ?>frequent_changing/css/custom_check_box.css">

<section class="main-content-wrapper">

    <section class="content-header">
        <h3 class="top-left-header">
            <?php echo lang('AddPrinter'); ?>
        </h3>  
    </section>

    <div class="box-wrapper">
        
            <div class="table-box">  
            
                <?php 
                $attributes = array('id' => 'add_Printer');
                echo form_open_multipart(base_url('printer/addEditPrinter/'), $attributes); ?>
                <div>
                    <div class="row">
                        <div class="col-sm-12 mb-2 col-md-4 col-lg-4">
                            <div class="form-group">
                                <label><?php echo lang('title'); ?> <small>(<?php echo lang('to_identify_printer_easily'); ?>)</small> <span class="required_star">*</span></label>
                                <input tabindex="1" autocomplete="off" type="text" name="title" class="form-control" placeholder="<?php echo lang('title'); ?>" value="<?php echo set_value('title'); ?>">
                            </div>
                            <?php if (form_error('title')) { ?>
                                <div class="callout callout-danger my-2">
                                    <span class="error_paragraph"><?php echo form_error('title'); ?></span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-sm-12 mb-2 col-md-4 col-lg-4">
                            <div class="form-group">
                                <label><?php echo lang('Printer'); ?> <?php echo lang('type'); ?> <span class="required_star">*</span></label>
                                <select class="form-control select2" id="type" name="type">
                                    <option <?php echo set_select('type',"network") ?> value="network"><?php echo lang('network'); ?></option>
                                    <option <?php echo set_select('type',"windows") ?> value="windows"><?php echo lang('windows'); ?></option>
                                </select>
                            </div>
                            <?php if (form_error('type')) { ?>
                                <div class="callout callout-danger my-2">
                                    <span class="error_paragraph"><?php echo form_error('type'); ?></span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-sm-12 mb-2 col-md-4 col-lg-4">
                            <div class="form-group">
                                <label><?php echo lang('characters_per_line'); ?> <span class="required_star">*</span> <div class="tooltip_custom">
                                        <i data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo lang('printer_per_line_tooltip'); ?>" data-feather="help-circle"></i>
                                    </div></label>
                                <input tabindex="1" autocomplete="off" type="number" name="characters_per_line" class="form-control" placeholder="<?php echo lang('characters_per_line'); ?>" value="<?php echo set_value('characters_per_line'); ?>">
                            </div>
                            <?php if (form_error('characters_per_line')) { ?>
                                <div class="callout callout-danger my-2">
                                    <span class="error_paragraph"><?php echo form_error('characters_per_line'); ?></span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-sm-12 mb-2 col-md-4 col-lg-4 network_div1">
                            <div class="form-group">
                                <label><?php echo lang('printer_ip_address'); ?> <span class="required_star">*</span><div class="tooltip_custom">
                                        <i data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo lang('printer_ip_tooltip'); ?>" data-feather="help-circle"></i>
                                    </div></label>
                                <input tabindex="1" autocomplete="off" type="text" name="printer_ip_address" class="form-control" placeholder="<?php echo lang('printer_ip_address'); ?>" value="<?php echo set_value('printer_ip_address'); ?>">
                            </div>
                            <?php if (form_error('printer_ip_address')) { ?>
                                <div class="callout callout-danger my-2">
                                    <span class="error_paragraph"><?php echo form_error('printer_ip_address'); ?></span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-sm-12 mb-2 col-md-4 col-lg-4 network_div1">
                            <div class="form-group">
                                <label><?php echo lang('printer_port'); ?> <span class="required_star">*</span><div class="tooltip_custom">
                                        <i data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo lang('printer_port_tooltip'); ?>" data-feather="help-circle"></i>
                                    </div></label>
                                <input tabindex="1" autocomplete="off" type="text" name="printer_port" class="form-control" placeholder="<?php echo lang('printer_port'); ?>" value="<?php echo set_value('printer_port'); ?>">
                            </div>
                            <?php if (form_error('printer_port')) { ?>
                                <div class="callout callout-danger my-2">
                                    <span class="error_paragraph"><?php echo form_error('printer_port'); ?></span>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="col-sm-12 mb-2 col-md-4 col-lg-4 receipt_printer_div txt_11">
                            <div class="form-group">
                                <label><?php echo lang('path'); ?> <span class="required_star">*</span> </label> <a class="pull-right no_underline_pos btn bg-blue-btn custom_bt_xs" href="<?php echo base_url()?>images/shareable_path.png" target="_blank"><?php echo lang('printer_path_tooltip'); ?></a>
                                <input tabindex="1" autocomplete="off" type="text" name="path" class="form-control" placeholder="<?php echo lang('path'); ?>" value="<?php echo set_value('path'); ?>">
                            </div>
                            <?php if (form_error('path')) { ?>
                                <div class="callout callout-danger my-2">
                                    <span class="error_paragraph"><?php echo form_error('path'); ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-sm-12 col-md-2  mb-2">                
                            <button type="submit" name="submit" value="submit" class="w-100 btn bg-blue-btn">
                                <?php echo lang('submit'); ?>
                            </button>
                        </div>
                        <div class="col-sm-12 col-md-2 mb-2">
                            <a class="btn bg-blue-btn w-100" href="<?php echo base_url() ?>printer/printers">
                                <?php  echo lang('back'); ?>
                            </a>                    
                        </div>
                    </div>
                <?php echo form_close(); ?>
            </div>
    </div>
    
</section>
<script type="text/javascript" src="<?php echo base_url('frequent_changing/js/printer.js'); ?>"></script>
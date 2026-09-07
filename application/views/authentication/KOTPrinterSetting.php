<?php
$base_color = '';
?>
<script type="text/javascript" src="<?php echo base_url('frequent_changing/js/setting.js'); ?>"></script>



<!-- Main content -->
<section class="main-content-wrapper">
    <?php
    if ($this->session->flashdata('exception')) {

        echo '<section class="alert-wrapper"><div class="alert alert-success alert-dismissible fade show"> 
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <div class="alert-body"><p><i class="m-right fa fa-check"></i>';
        echo escape_output($this->session->flashdata('exception'));unset($_SESSION['exception']);
        echo '</p></div></div></section>';
    }
    ?>


    <section class="content-header">
        <h3 class="top-left-header">
            <?php echo lang('kot_printer_setting'); ?>
        </h3>
    </section>
    <div class="box-wrapper">
        <div class="row">

            <!-- left column -->
            <div class="col-md-12">
                <div class="table-box">
                    <!-- /.box-header -->
                    <!-- form start -->
                    <?php
                    $attributes = array('id' => 'restaurant_setting_form');
                    echo form_open_multipart(base_url('setting/KOTPrinterSetting/' . $encrypted_id),$attributes); ?>
                    <div class="box-body">
                        <div class="row">
                            <div class="mb-3 col-sm-12 col-md-4 col-lg-4">
                                <div class="form-group">
                                    <label><?php echo lang('printing_choice'); ?> <span
                                                class="required_star">*</span></label>
                                    <select class="form-control printing select2" id="printing_kot" name="printing_kot">
                                        <option
                                            <?php echo isset($outlet_information->printing_kot) && $outlet_information->printing_kot == "web_browser"?"selected":'' ?>
                                            <?php echo set_select('printing_kot',"web_browser") ?> value="web_browser">
                                            <?php echo lang('web_browser'); ?></option>
                                        <option
                                            <?php echo isset($outlet_information->printing_kot) && $outlet_information->printing_kot == "direct_print"?"selected":'' ?>
                                            <?php echo set_select('printing_kot',"direct_print") ?> value="direct_print">
                                            <?php echo lang('direct_print'); ?> (<?php echo lang('script_install_in_localhost'); ?>)</option>
                                        <option
                                            <?php echo isset($outlet_information->printing_kot) && $outlet_information->printing_kot == "live_server_print"?"selected":'' ?>
                                            <?php echo set_select('printing_kot',"live_server_print") ?>
                                            value="live_server_print"><?php echo lang('direct_print'); ?> (<?php echo lang('script_install_in_web_server'); ?>)</option>
                                    </select>
                                </div>
                                <?php if (form_error('printer_kot')) { ?>
                                    <div class="alert alert-error txt-uh-21">
                                        <?php echo form_error('printer_kot'); ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="mb-3 col-sm-12 col-md-4 col-lg-4 display_none receipt_printer_div_kot">
                                <div class="form-group">
                                    <label><?php echo lang('receipt_printer'); ?> <span
                                                class="required_star">*</span></label>
                                    <select class="form-control select2" id="receipt_printer_kot"
                                            name="receipt_printer_kot">
                                        <option value=""><?php echo lang('select'); ?></option>
                                        <?php
                                        foreach ($printers as $value):
                                            ?>
                                            <option
                                                <?php echo  isset($outlet_information->receipt_printer_kot) && $outlet_information->receipt_printer_kot == $value->id?"selected":'' ?>
                                                <?php echo set_select('receipt_printer_kot',$value->id) ?>
                                                    value="<?php echo escape_output($value->id) ?>">
                                                <?php echo escape_output($value->title) ?>
                                                <?php echo escape_output($value->path?"(".$value->path.")":'') ?></option>
                                            <?php
                                        endforeach;
                                        ?>
                                    </select>
                                </div>
                                <?php if (form_error('receipt_printer_kot')) { ?>
                                    <div class="alert alert-error txt-uh-21">
                                        <?php echo form_error('receipt_printer_kot'); ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="mb-3 col-sm-12 col-md-4 col-lg-4 txt_11 print_server_url_div_kot">
                                <div class="form-group">
                                    <label><?php echo lang('print_server_url'); ?> <span
                                            class="required_star">*</span></label>
                                    <a class="pull-right no_underline_pos btn bg-blue-btn custom_bt_xs" href="<?php echo base_url()?>images/ethernet_wifi.png" target="_blank"><?php echo lang('HowtogetIPv4Address'); ?></a>
                                    <button type="button" class="pull-right btn btn-xs btn-success btn-detect-ip-btn" data-target-input="print_server_url_kot" style="font-size: 11px; padding: 2px 8px; line-height: 1.5; background-color: #28a745; border-color: #28a745; color: #fff; margin-right: 5px; float: right;">Auto Detect IP</button>
                                    <input tabindex="1" autocomplete="off" type="text" id="print_server_url_kot"
                                           name="print_server_url_kot" class="form-control"
                                           placeholder="<?php echo lang('print_server_url_exm'); ?>"
                                           value="<?= escape_output($outlet_information->print_server_url_kot); ?>">
                                </div>
                                <?php if (form_error('print_server_url_kot')) { ?>
                                    <div class="callout callout-danger my-2">
                                        <?php echo form_error('print_server_url_kot'); ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="mb-3 col-sm-12 col-md-4 col-lg-4 print_format_div_kot">
                                <div class="form-group radio_button_problem">
                                    <label><?php echo lang('print_format'); ?> <span class="required_star">*</span></label>
                                    <div class="radio">
                                        <label>
                                            <input tabindex="5" autocomplete="off" type="radio" name="print_format_kot"
                                                   id="print_format_thermal" value="No Print" <?php
                                            if ($outlet_information->print_format_kot == "No Print") {
                                                echo "checked";
                                            };
                                            ?>><?php echo lang('No_Print'); ?> </label>

                                        <label>

                                            <input tabindex="5" autocomplete="off" type="radio" name="print_format_kot"
                                                   id="print_format_thermal" value="56mm" <?php
                                            if ($outlet_information->print_format_kot == "56mm") {
                                                echo "checked";
                                            };
                                            ?>><?php echo lang('56mm'); ?> </label>
                                        <label>



                                            <input tabindex="5" autocomplete="off" type="radio" name="print_format_kot"
                                                   id="print_format_a4" value="80mm" <?php
                                            if ($outlet_information->print_format_kot == "80mm") {
                                                echo "checked";
                                            };
                                            ?>><?php echo lang('80mm'); ?>
                                        </label>
                                    </div>
                                </div>
                                <?php if (form_error('print_format_kot')) { ?>
                                    <div class="callout callout-danger my-2">
                                        <?php echo form_error('print_format_kot'); ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="clearfix"></div>

                            <div class="col-sm-3">
                                <button type="submit" name="submit" value="submit"
                                        class="btn bg-blue-btn w-100"><?php echo lang('submit'); ?></button>
                            </div>
                        </div>
                    </div>
                    <!-- /.box-body -->


                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>

</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const detectButtons = document.querySelectorAll('.btn-detect-ip-btn');
    detectButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = btn.getAttribute('data-target-input');
            const inputEl = document.getElementById(targetId);
            
            btn.textContent = 'Detecting...';
            btn.disabled = true;
            
            // Try localhost print server get_ip.php
            fetch('http://127.0.0.1/print_server/get_ip.php')
                .then(res => res.json())
                .then(data => {
                    if (data && data.status === 'success' && data.ip) {
                        inputEl.value = data.ip;
                        btn.textContent = 'Auto Detect IP';
                        btn.disabled = false;
                        alert('IP Address successfully detected: ' + data.ip);
                    } else {
                        throw new Error('Invalid response');
                    }
                })
                .catch(err => {
                    console.log('Failed fetching from 127.0.0.1, trying localhost...', err);
                    return fetch('http://localhost/print_server/get_ip.php')
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.status === 'success' && data.ip) {
                                inputEl.value = data.ip;
                                btn.textContent = 'Auto Detect IP';
                                btn.disabled = false;
                                alert('IP Address successfully detected: ' + data.ip);
                            } else {
                                throw new Error('Invalid response');
                            }
                        });
                })
                .catch(err => {
                    btn.textContent = 'Auto Detect IP';
                    btn.disabled = false;
                    alert('Could not detect local IP. Make sure print_server is running on your local server.');
                });
        });
        
        // Auto detect on load if field is empty
        const targetId = btn.getAttribute('data-target-input');
        const inputEl = document.getElementById(targetId);
        if (inputEl && !inputEl.value.trim()) {
            fetch('http://127.0.0.1/print_server/get_ip.php')
                .then(res => res.json())
                .then(data => {
                    if (data && data.status === 'success' && data.ip) {
                        inputEl.value = data.ip;
                    }
                })
                .catch(err => {
                    fetch('http://localhost/print_server/get_ip.php')
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.status === 'success' && data.ip) {
                                inputEl.value = data.ip;
                            }
                        })
                        .catch(e => console.log('Silent auto-detect failed:', e));
                });
        }
    });
});
</script>
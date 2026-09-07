<!DOCTYPE html>
<?php
// Functions for auto-detecting IP and shared printers
function getLocalIP() {
    if (function_exists('gethostname') && function_exists('gethostbynamel')) {
        $ips = gethostbynamel(gethostname());
        if ($ips) {
            foreach ($ips as $i) {
                if ($i !== '127.0.0.1' && filter_var($i, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    return $i;
                }
            }
        }
    }
    if (function_exists('gethostname')) {
        $i = gethostbyname(gethostname());
        if ($i !== '127.0.0.1' && filter_var($i, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $i;
        }
    }
    if (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '127.0.0.1' && $_SERVER['SERVER_ADDR'] !== '::1') {
        return $_SERVER['SERVER_ADDR'];
    }
    if (isset($_SERVER['HTTP_HOST'])) {
        $host = parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST);
        if (!$host) {
            $host = $_SERVER['HTTP_HOST'];
        }
        $host = explode(':', $host)[0];
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && $host !== '127.0.0.1') {
            return $host;
        }
    }
    return '127.0.0.1'; // fallback
}

function getSharedPrinters() {
    $printers = [];
    
    // Attempt PowerShell first (most reliable and structured)
    try {
        $cmd = 'powershell -NoProfile -Command "@(Get-Printer | Where-Object Shared -eq $true | Select-Object Name, ShareName) | ConvertTo-Json"';
        $output = shell_exec($cmd);
        if ($output) {
            $decoded = json_decode($output, true);
            if (is_array($decoded)) {
                if (isset($decoded['Name'])) {
                    $decoded = [$decoded];
                }
                foreach ($decoded as $printer) {
                    if (isset($printer['Name']) && isset($printer['ShareName'])) {
                        $printers[] = [
                            'name' => trim($printer['Name']),
                            'share_name' => trim($printer['ShareName'])
                        ];
                    }
                }
            }
        }
    } catch (Exception $e) {
        // Ignore
    }
    
    // Fallback: wmic if powershell fails or returned nothing
    if (empty($printers)) {
        try {
            $output = shell_exec('wmic printer where Shared=TRUE get Name, ShareName /value');
            if ($output) {
                $lines = explode("\n", $output);
                $currentPrinter = [];
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    if (strpos($line, '=') !== false) {
                        list($key, $value) = explode('=', $line, 2);
                        $currentPrinter[trim($key)] = trim($value);
                        if (count($currentPrinter) == 2) {
                            if (isset($currentPrinter['Name']) && isset($currentPrinter['ShareName'])) {
                                $printers[] = [
                                    'name' => $currentPrinter['Name'],
                                    'share_name' => $currentPrinter['ShareName']
                                ];
                            }
                            $currentPrinter = [];
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // Ignore
        }
    }
    
    return $printers;
}

$root=$_SERVER["HTTP_HOST"];
$root.= str_replace(basename($_SERVER["SCRIPT_NAME"]), "", $_SERVER["SCRIPT_NAME"]);
$base_url = $root;

$local_ip = getLocalIP();
$shared_printers = getSharedPrinters();
?>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Print Server Setting</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <script src="assets/bower_components/jquery/dist/jquery.min.js"></script>
    <link rel="shortcut icon" href="logo/favicon.ico" type="image/x-icon">
    <link rel="icon" href="logo/favicon.ico" type="image/x-icon">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="assets/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <!-- Theme style -->
    <link rel="stylesheet" href="assets/dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="assets/dist/css/common.css">
    <link rel="stylesheet" href="assets/dist/css/custom/login.css">
    <style>
        .bg-blue-btn, .bg-red-btn {
            color: white;
            text-decoration: none;
            padding: 8.5px 21px !important;
            border-radius: 0.358rem !important;
            font-weight: 500;
            font-size: 14px;
            text-transform: capitalize;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        .bg-blue-btn {
            background-color: #7367f0;
        }
        .bg-blue-btn:hover {
            color: white;
        }
        .div_network,
        .div_shared_printers,
        .div_ip_share_text_manual {
            display: none;
        }
    </style>
</head>

<body class="loginPage">
<div class="row">
    <input type="hidden" id="base_url" value="<?php echo $base_url?>">
    <h1 style="text-align: center">Print Server Setting</h1>

        <form>
          <div class="row">
              <div class="form-group">
                  <input tabindex="2" type="text" id="ethernet_ip"  name="ethernet_ip" class="form-control" placeholder="IPv4 Address" value="<?php echo htmlspecialchars($local_ip); ?>">
                  <small><i style="color:red;padding-left: 12px;"> Eg: IPv4 Address: <b>192.168.0.105</b> (<a target="_blank" href="logo/ethernet_wifi.png">How to get IPv4 Address?</a> )</i></small>
              </div>
              <div class="form-group">
                  <select class="form-control" name="connection_type" id="connection_type">
                      <option value="https://">https</option>
                      <option value="http://">http</option>
                  </select>
                  <small><i style="color:red;padding-left: 12px;">If your server URL is like https://yourwebsite.com/ then select https</i></small><br>
                  <small><i style="color:red;padding-left: 12px;">If your server URL is like http://yourwebsite.com/ then select http</i></small><br>
              </div>


              <div class="form-group">
                  <select class="form-control" name="type" id="type">
                      <option value="">Printer Type</option>
                      <option value="network">Network Printer</option>
                      <option value="windows">USB Printer</option>
                  </select>
              </div>

              <div class="form-group div_shared_printers">
                  <select class="form-control" id="shared_printer_select" name="shared_printer_select">
                      <option value="">Select a shared printer...</option>
                      <?php foreach ($shared_printers as $printer): ?>
                          <option value="<?php echo htmlspecialchars($printer['share_name']); ?>">
                              <?php echo htmlspecialchars($printer['name'] . ' (' . $printer['share_name'] . ')'); ?>
                          </option>
                      <?php endforeach; ?>
                      <option value="__manual__">-- Enter Share Name Manually --</option>
                  </select>
                  <small><i style="color:red;padding-left: 12px;">Detected shared printers on this computer. (<a target="_blank" href="logo/shareable_path.png">Need help sharing?</a>)</i></small>
              </div>

              <div class="form-group div_ip_share_text_manual">
                  <input tabindex="2" type="text" id="ip_share_text"  name="ip_share_text" class="form-control" placeholder="Printer IP Address / Share Name" value="">
                  <small class="help_network_only"><i style="color:red;padding-left: 12px;">
                          Printer IP Address e.g: <b>192.168.1.87</b></i></small>
                  <small class="help_manual_usb_only"><i style="color:red;padding-left: 12px;">
                          Share Name e.g: <b>Door Soft Printer</b> (<a target="_blank" href="logo/shareable_path.png">How to get Share Name?</a> )</i></small>
              </div>
              <div class="form-group div_network">
                  <input tabindex="3" type="text" id="port_address"  name="port_address" class="form-control" placeholder="Printer Port Address" value="">
                  <small>
                      <i style="color:red;padding-left: 12px;">
                          In maximum case the Printer Port Address is 9100 but in case it is different <br>&nbsp;&nbsp;&nbsp;please do a test print from your printer after turning it on, you will get the <br>&nbsp;&nbsp;Printer Port Address in that test print paper.
                      </i></small>
              </div>
              <small style="text-align: center"><i style="color:red;padding-left: 12px;"> Before click <b>Test Print</b>, please check- (<a target="_blank" href="logo/click_here_for_test_print.png">Click Here</a> )</i></small>
              <a href="#" class="btn bg-blue-btn set_url">Test Print</a>
           </div>
        </form>

    <div class="clearfix"></div>
</div>
<!-- Bootstrap 3.3.7 -->
<script src="assets/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script>
    $(function () {
        "use strict";
        function set_show_hide() {
            let type = $("#type").val();
            if (type == "network") {
                $(".div_network").show();
                $(".div_shared_printers").hide();
                $(".div_ip_share_text_manual").show();
                $(".help_network_only").show();
                $(".help_manual_usb_only").hide();
            } else if (type == "windows") {
                $(".div_network").hide();
                $(".div_shared_printers").show();
                
                // Show manual entry if manual is selected or if no printers were found
                let selected_printer = $("#shared_printer_select").val();
                if (selected_printer == "__manual__" || $("#shared_printer_select option").length <= 2) {
                    $(".div_ip_share_text_manual").show();
                    $(".help_network_only").hide();
                    $(".help_manual_usb_only").show();
                } else {
                    $(".div_ip_share_text_manual").hide();
                }
            } else {
                $(".div_network").hide();
                $(".div_shared_printers").hide();
                $(".div_ip_share_text_manual").hide();
            }
        }
        $(document).on('change', '#type', function(e){
            set_show_hide();
        });
        $(document).on('change', '#shared_printer_select', function(e){
            set_show_hide();
        });
        $(document).on('click', '.set_url', function(e){
           let ethernet_ip = $("#ethernet_ip").val();
           let connection_type = $("#connection_type").val();
           let type = $("#type").val();
           let port_address = $("#port_address").val();
           let base_url = $("#base_url").val();
           
           let ip_share_text = '';
           if (type === 'windows') {
               let selected_printer = $("#shared_printer_select").val();
               if (selected_printer && selected_printer !== '__manual__') {
                   ip_share_text = selected_printer;
               } else {
                   ip_share_text = $("#ip_share_text").val();
               }
           } else {
               ip_share_text = $("#ip_share_text").val();
           }

            $("#ethernet_ip").css("border","1px solid #d2d6de");
            $("#shared_printer_select").css("border","1px solid #d2d6de");
            $("#ip_share_text").css("border","1px solid #d2d6de");
            $("#port_address").css("border","1px solid #d2d6de");
            $("#base_url").css("border","1px solid #d2d6de");

            if(ethernet_ip==''){
                $("#ethernet_ip").css("border","3px solid red");
                $("#ethernet_ip").focus();
                return false;
            }else if(type==''){
                $("#type").css("border","3px solid red");
                $("#type").focus();
                return false;
            } else if(type == 'windows' && ($("#shared_printer_select").val() == '' || ($("#shared_printer_select").val() == '__manual__' && $("#ip_share_text").val() == ''))){
                if ($("#shared_printer_select").val() == '') {
                    $("#shared_printer_select").css("border","3px solid red");
                    $("#shared_printer_select").focus();
                } else {
                    $("#ip_share_text").css("border","3px solid red");
                    $("#ip_share_text").focus();
                }
                return false;
            } else if(type == 'network' && ip_share_text == ''){
                $("#ip_share_text").css("border","3px solid red");
                $("#ip_share_text").focus();
                return false;
            }else if (port_address=='' && type=="network"){
                $("#port_address").css("border","3px solid red");
                $("#port_address").focus();
                return false;
            }else{
                let url_redirect = connection_type + ethernet_ip + "/print_server/print.php?printer_type_value=" + encodeURIComponent(ip_share_text) + "&&port=" + encodeURIComponent(port_address) + "&&type=" + encodeURIComponent(type);
                window.open(url_redirect, '_blank');
            }

        });
        set_show_hide();
    });
</script>
</body>

</html>
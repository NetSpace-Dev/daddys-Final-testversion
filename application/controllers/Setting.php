<?php
/*
  ###########################################################
  # PRODUCT NAME: 	iRestora PLUS - Next Gen Restaurant POS | NULLED by raz0r
  ###########################################################
  # AUTHER:		Doorsoft
  ###########################################################
  # EMAIL:		info@doorsoft.co
  ###########################################################
  # COPYRIGHTS:		RESERVED BY Door Soft
  ###########################################################
  # WEBSITE:		http://www.doorsoft.co
  ###########################################################
  # This is Setting Controller
  ###########################################################
 */

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'third_party/endroid_qrcode/autoload.php';

use Endroid\QrCode\ErrorCorrectionLevel;
//use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
//use Endroid\QrCode\Response\QrCodeResponse;


class Setting extends Cl_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Authentication_model');
        $this->load->model('Common_model');
        $this->load->model('Outlet_model');
        $this->load->library('form_validation');
        $this->Common_model->setDefaultTimezone();

        if (!$this->session->has_userdata('user_id')) {
            redirect('Authentication/index');
        }
        //start check access function
        $segment_2 = $this->uri->segment(2);
        $segment_3 = $this->uri->segment(3);
        $controller = "6";
        $function = "";

        if($segment_2=="index" || $segment_2==""){
            $function = "update";
        }else if($segment_2=="tax"){
            $controller = "52";
            $function = "update_tax";
        }else if($segment_2=="selfOrder" || $segment_2=="onlineOrder" || $segment_2=="selfOrderQrcode" || $segment_2=="onlineOrderURL" || $segment_2=="downloadQRcode"){
            $controller = "64";
            $function = "update";
        }else if($segment_2=="invoicePrinterSetting"){
               $controller = "40";
            $function = "update";
        }else if($segment_2=="resetTransactionalData" || $segment_2=="resettransactionaldata"){
               $controller = "350";
            $function = "reset";
        }else if($segment_2=="billPrinterSetting"){
               $controller = "43";
            $function = "update";
        }else if($segment_2=="KOTPrinterSetting"){
               $controller = "46";
            $function = "update";
        }else if($segment_2=="customerDisplaySetting" || $segment_2=="customer_display_upload" || $segment_2=="customer_display_delete"){
               $controller = "6";
            $function = "update";
        }else{
           $this->session->set_flashdata('exception_er', lang('menu_not_permit_access'));
            redirect('Authentication/userProfile');
        }

        if(!checkAccess($controller,$function)){
            $this->session->set_flashdata('exception_er', lang('menu_not_permit_access'));
            redirect('Authentication/userProfile');
        }
        //end check access function
    }

     /**
     * setting info
     * @access public
     * @return void
     * @param int
     */
    public function index($id = '') {
        $encrypted_id = $id;
        $company_id = $id = $outlet_id = $this->session->userdata('company_id');
        $language_manifesto = $this->session->userdata('language_manifesto');
        $id = $this->custom->encrypt_decrypt($id, 'decrypt');

        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $this->form_validation->set_rules('business_name', lang('business_name'), 'required|max_length[50]');
            $this->form_validation->set_rules('short_name', lang('short_name'), 'required|max_length[2]');
            $this->form_validation->set_rules('invoice_logo', lang('invoice_logo'), 'callback_validate_invoice_logo|max_length[500]');
            $this->form_validation->set_rules('date_format', lang('date_format'), 'required|max_length[250]');
            $this->form_validation->set_rules('zone_name', lang('Time_Zone'), 'required|max_length[250]');
            $this->form_validation->set_rules('currency', lang('currency'), 'required|max_length[250]');
            $this->form_validation->set_rules('invoice_footer', lang('invoice_footer'), 'max_length[250]');
            if(str_rot13($language_manifesto)!="eriutoeri"):
                $this->form_validation->set_rules('default_waiter', lang('Default_Waiter'), 'max_length[11]');
            endif;
            $this->form_validation->set_rules('default_customer', lang('Default_Customer'), 'required|max_length[11]');
            $this->form_validation->set_rules('default_payment', lang('Default_Payment_Method'), 'required|max_length[11]');

            $is_loyalty_enable = htmlspecialcharscustom($this->input->post($this->security->xss_clean('is_loyalty_enable')));
            if($is_loyalty_enable=="enable"){
                $this->form_validation->set_rules('minimum_point_to_redeem', lang('minimum_point_to_redeem'), 'required|max_length[11]');
                $this->form_validation->set_rules('loyalty_rate', lang('loyalty_rate'), 'required|max_length[11]');
            }

            if ($this->form_validation->run() == TRUE) {
                $outlet_info = array();
                $outlet_info['business_name'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('business_name')));
                $outlet_info['short_name'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('short_name')));
                if ($_FILES['invoice_logo']['name'] != "") {
                    $outlet_info['invoice_logo'] = $this->session->userdata('invoice_logo');
                    $this->session->unset_userdata('invoice_logo');
                }else{
                    $outlet_info['invoice_logo'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('invoice_logo_p')));
                }
                $outlet_info['website'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('website')));
                $outlet_info['date_format'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('date_format')));
                $outlet_info['zone_name'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('zone_name')));
                $outlet_info['currency'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('currency')));
                $outlet_info['currency_position'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('currency_position')));
                $outlet_info['precision'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('precision')));
                if(isServiceAccessOnly('sGmsJaFJE')):
                    $outlet_info['saas_landing_page'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('saas_landing_page')));
                endif;
                if(str_rot13($language_manifesto)!="eriutoeri"):
                    $outlet_info['default_waiter'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('default_waiter')));
                endif;

                $outlet_info['default_customer'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('default_customer')));
                $outlet_info['default_payment'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('default_payment')));
                $outlet_info['invoice_footer'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('invoice_footer')));

                $outlet_info['service_amount'] = htmlspecialcharscustom($this->input->post('service_amount'));
                $outlet_info['delivery_amount'] = htmlspecialcharscustom($this->input->post('delivery_amount'));
                $outlet_info['decimals_separator'] = htmlspecialcharscustom($this->input->post('decimals_separator'));
                $outlet_info['thousands_separator'] = htmlspecialcharscustom($this->input->post('thousands_separator'));
                $outlet_info['default_order_type_delivery_p'] = htmlspecialcharscustom($this->input->post('default_order_type_delivery_p'));
                $outlet_info['when_clicking_on_item_in_pos'] = htmlspecialcharscustom($this->input->post('when_clicking_on_item_in_pos'));
                $outlet_info['is_rounding_enable'] = htmlspecialcharscustom($this->input->post('is_rounding_enable'));
                $outlet_info['default_order_type'] = htmlspecialcharscustom($this->input->post('default_order_type'));
                $outlet_info['is_loyalty_enable'] = htmlspecialcharscustom($this->input->post('is_loyalty_enable'));
                $outlet_info['pre_or_post_payment'] = htmlspecialcharscustom($this->input->post('pre_or_post_payment'));
                $outlet_info['minimum_point_to_redeem'] = htmlspecialcharscustom($this->input->post('minimum_point_to_redeem'));
                $outlet_info['loyalty_rate'] = htmlspecialcharscustom($this->input->post('loyalty_rate'));
                $outlet_info['split_bill'] = htmlspecialcharscustom($this->input->post('split_bill'));
                $outlet_info['place_order_tooltip'] = htmlspecialcharscustom($this->input->post('place_order_tooltip'));
                $outlet_info['food_menu_tooltip'] = htmlspecialcharscustom($this->input->post('food_menu_tooltip'));
                $outlet_info['sms_send_auto'] = htmlspecialcharscustom($this->input->post('sms_send_auto'));


                if(!isServiceAccessOnlyLogin('sGmsJaFJE')):
                    $outlet_info['export_daily_sale'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('export_daily_sale')));
                    endif;

                if ($id == "") {
                    $outlet_info['starting_date'] = date("Y-m-d");
                    $outlet_info['user_id'] = $this->session->userdata('user_id');
                    $outlet_info['company_id'] = $this->session->userdata('company_id');
                    $outlet_info['outlet_code'] = $this->Outlet_model->generateOutletCode();
                }
                if ($id == "") {
                    $this->Common_model->insertInformation($outlet_info, "tbl_companies");
                    $this->session->set_flashdata('exception', lang('insertion_success'));
                } else {
                    $this->Common_model->updateInformation($outlet_info, $id, "tbl_companies");
                    $this->session->set_flashdata('exception', lang('update_success'));
                }
                $this->session->set_userdata($outlet_info);
                redirect('setting');
            } else {
                $data = array();
                $data['encrypted_id'] = $encrypted_id;
                $data['outlet_information'] = $this->Common_model->getDataById($id, "tbl_companies");
                $data['zone_names'] = $this->Common_model->getAllForDropdown("tbl_time_zone");
                $data['customers'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_customers");
                $data['printers'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_printers");
                if(str_rot13($language_manifesto)!="eriutoeri"):
                    $data['waiters'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_users");
                endif;
                $data['paymentMethods'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_payment_methods");
                $data['main_content'] = $this->load->view('authentication/setting', $data, TRUE);
                $this->load->view('userHome', $data);
            }
        } else {
            $data = array();
            $data['encrypted_id'] = $encrypted_id;
            $data['outlet_information'] = $this->Common_model->getDataById($id, "tbl_companies");
            $data['zone_names'] = $this->Common_model->getAllForDropdown("tbl_time_zone");
            $data['customers'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_customers");
            $data['printers'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_printers");
            if(str_rot13($language_manifesto)!="eriutoeri"):
                $data['waiters'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_users");
            endif;
            $data['deliveryPartners'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_delivery_partners");
            $data['paymentMethods'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_payment_methods");
            $data['main_content'] = $this->load->view('authentication/setting', $data, TRUE);
            $this->load->view('userHome', $data);
        }

    }
    public function invoicePrinterSetting($id = '') {
        $encrypted_id = $id;
        $company_id = $id = $outlet_id = $this->session->userdata('company_id');
        $language_manifesto = $this->session->userdata('language_manifesto');
        $id = $this->custom->encrypt_decrypt($id, 'decrypt');

        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $outlet_info = array();
            $outlet_info['print_format_invoice'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('print_format_invoice')));
            $outlet_info['printing_invoice'] = htmlspecialcharscustom($this->input->post('printing_invoice'));
            $outlet_info['receipt_printer_invoice'] = htmlspecialcharscustom($this->input->post('receipt_printer_invoice'));
            $outlet_info['print_server_url_invoice'] = htmlspecialcharscustom($this->input->post('print_server_url_invoice'));
            $outlet_info['open_cash_drawer_when_printing_invoice'] = htmlspecialcharscustom($this->input->post('open_cash_drawer_when_printing_invoice'));
            if ($id == "") {
                $this->Common_model->insertInformation($outlet_info, "tbl_companies");
                $this->session->set_flashdata('exception', lang('insertion_success'));
            } else {
                $this->Common_model->updateInformation($outlet_info, $id, "tbl_companies");
                $this->session->set_flashdata('exception', lang('update_success'));
            }
            $this->session->set_userdata($outlet_info);
            redirect('setting/invoicePrinterSetting');
        } else {
            $data = array();
            $data['encrypted_id'] = $encrypted_id;
            $data['outlet_information'] = $this->Common_model->getDataById($id, "tbl_companies");
            $data['zone_names'] = $this->Common_model->getAllForDropdown("tbl_time_zone");
            $data['customers'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_customers");
            $data['printers'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_printers");
            if(str_rot13($language_manifesto)!="eriutoeri"):
                $data['waiters'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_users");
            endif;
            $data['paymentMethods'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_payment_methods");
            $data['main_content'] = $this->load->view('authentication/invoice_printer_setting', $data, TRUE);
            $this->load->view('userHome', $data);
        }

    }
    public function billPrinterSetting($id = '') {
        $encrypted_id = $id;
        $company_id = $id = $outlet_id = $this->session->userdata('company_id');
        $language_manifesto = $this->session->userdata('language_manifesto');
        $id = $this->custom->encrypt_decrypt($id, 'decrypt');

        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $outlet_info = array();
            $outlet_info['print_format_bill'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('print_format_bill')));
            $outlet_info['printing_bill'] = htmlspecialcharscustom($this->input->post('printing_bill'));
            $outlet_info['receipt_printer_bill'] = htmlspecialcharscustom($this->input->post('receipt_printer_bill'));
            $outlet_info['print_server_url_bill'] = htmlspecialcharscustom($this->input->post('print_server_url_bill'));
            if ($id == "") {
                $this->Common_model->insertInformation($outlet_info, "tbl_companies");
                $this->session->set_flashdata('exception', lang('insertion_success'));
            } else {
                $this->Common_model->updateInformation($outlet_info, $id, "tbl_companies");
                $this->session->set_flashdata('exception', lang('update_success'));
            }
            $this->session->set_userdata($outlet_info);
            redirect('setting/billPrinterSetting');
        } else {
            $data = array();
            $data['encrypted_id'] = $encrypted_id;
            $data['outlet_information'] = $this->Common_model->getDataById($id, "tbl_companies");
            $data['zone_names'] = $this->Common_model->getAllForDropdown("tbl_time_zone");
            $data['customers'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_customers");
            $data['printers'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_printers");
            if(str_rot13($language_manifesto)!="eriutoeri"):
                $data['waiters'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_users");
            endif;
            $data['paymentMethods'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_payment_methods");
            $data['main_content'] = $this->load->view('authentication/bill_printer_setting', $data, TRUE);
            $this->load->view('userHome', $data);
        }

    }
    public function KOTPrinterSetting($id = '') {
        $encrypted_id = $id;
        $company_id = $id = $outlet_id = $this->session->userdata('company_id');
        $language_manifesto = $this->session->userdata('language_manifesto');
        $id = $this->custom->encrypt_decrypt($id, 'decrypt');

        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $outlet_info = array();
            $outlet_info['print_format_kot'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('print_format_kot')));
            $outlet_info['printing_kot'] = htmlspecialcharscustom($this->input->post('printing_kot'));
            $outlet_info['receipt_printer_kot'] = htmlspecialcharscustom($this->input->post('receipt_printer_kot'));
            $outlet_info['print_server_url_kot'] = htmlspecialcharscustom($this->input->post('print_server_url_kot'));

            if ($id == "") {
                $this->Common_model->insertInformation($outlet_info, "tbl_companies");
                $this->session->set_flashdata('exception', lang('insertion_success'));
            } else {
                $this->Common_model->updateInformation($outlet_info, $id, "tbl_companies");
                $this->session->set_flashdata('exception', lang('update_success'));
            }
            $this->session->set_userdata($outlet_info);
            redirect('setting/KOTPrinterSetting');
        } else {
            $data = array();
            $data['encrypted_id'] = $encrypted_id;
            $data['outlet_information'] = $this->Common_model->getDataById($id, "tbl_companies");
            $data['zone_names'] = $this->Common_model->getAllForDropdown("tbl_time_zone");
            $data['customers'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_customers");
            $data['printers'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_printers");
            if(str_rot13($language_manifesto)!="eriutoeri"):
                $data['waiters'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_users");
            endif;
            $data['paymentMethods'] = $this->Common_model->getAllByCompanyId($company_id, "tbl_payment_methods");
            $data['main_content'] = $this->load->view('authentication/KOTPrinterSetting', $data, TRUE);
            $this->load->view('userHome', $data);
        }

    }
     /**
     * validate invoice logo
     * @access public
     * @return string
     * @param boolean
     */
    public function validate_invoice_logo() {

        if ($_FILES['invoice_logo']['name'] != "") {
            $config['upload_path'] = './images';
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size'] = '1000';
            $config['encrypt_name'] = TRUE;
            $config['detect_mime'] = TRUE;
            $this->load->library('upload', $config);
            if ($this->upload->do_upload("invoice_logo")) {
                $upload_info = $this->upload->data();
                $file_name = $upload_info['file_name'];
                $config['image_library'] = 'gd2';
                $config['source_image'] = './images/' . $file_name;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = 100;
                $config['height'] = 100;
                $this->load->library('image_lib', $config);
                $this->image_lib->resize();
                $this->session->set_userdata('invoice_logo', $file_name);

            } else {
                $this->form_validation->set_message('validate_invoice_logo', $this->upload->display_errors());
                return FALSE;
            }
        }
        $login_session['active_menu_tmp'] = '';
        $this->session->set_userdata($login_session);
    }
     /**
     * smtp Email Setting
     * @access public
     * @return void
     * @param int
     */
    public function smtpEmailSetting($id = '') {
        if (htmlspecialcharscustom($this->input->post('submit'))) {
                $this->form_validation->set_rules('host_name', "Host Name", "required|max_length[300]");
                $this->form_validation->set_rules('port_address', "Port Address", "required|max_length[300]");
                $this->form_validation->set_rules('user_name', "Username", "required|max_length[300]");
                $this->form_validation->set_rules('password', "Password", "required|max_length[300]");

            if ($this->form_validation->run() == TRUE) {

                $data['host_name'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('host_name')));
                $data['port_address'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('port_address')));
                $data['user_name'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('user_name')));
                $data['password'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('password')));
                $data_json['smtp_enable_status'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('enable_status')));
                $data_json['smtp_details'] = json_encode($data);

                $this->Common_model->updateInformation($data_json, $id, "tbl_companies");
                $this->session->set_flashdata('exception', lang('update_success'));
                redirect('setting/smtpEmailSetting');
            }else{
                $data = array();
                $data['company'] = getCompanyInfo();
                $data['main_content'] = $this->load->view('authentication/smtpEmailSetting', $data, TRUE);
                $this->load->view('userHome', $data);
            }

        } else {
            $data = array();
            $data['company'] = getCompanyInfo();
            $data['main_content'] = $this->load->view('authentication/smtpEmailSetting', $data, TRUE);
            $this->load->view('userHome', $data);
        }
    }
    /**
     * resetTransactional Data
     * @access public
     * @return void
     * @param no
     */
    public function resetTransactionalData() {
        //truncate all transactional data
        $this->db->query("TRUNCATE tbl_sales");
        $this->db->query("TRUNCATE tbl_sales_details");
        $this->db->query("TRUNCATE tbl_sales_details_modifiers");
        $this->db->query("TRUNCATE tbl_sale_consumptions");
        $this->db->query("TRUNCATE tbl_sale_consumptions_of_menus");
        $this->db->query("TRUNCATE tbl_sale_consumptions_of_modifiers_of_menus");
        $this->db->query("TRUNCATE tbl_purchase");
        $this->db->query("TRUNCATE tbl_purchase_ingredients");
        $this->db->query("TRUNCATE tbl_holds");
        $this->db->query("TRUNCATE tbl_holds_details");
        $this->db->query("TRUNCATE tbl_holds_details_modifiers");
        $this->db->query("TRUNCATE tbl_holds_table");
        $this->db->query("TRUNCATE tbl_expenses");
        $this->db->query("TRUNCATE tbl_expense_items");
        $this->db->query("TRUNCATE tbl_supplier_payments");
        $this->db->query("TRUNCATE tbl_customer_due_receives");
        $this->session->set_flashdata('exception', lang('truncate_update_success'));
        redirect('setting/index');
    }
     /**
     * tax
     * @access public
     * @return void
     * @param int
     */
    public function tax($id = '') {
        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $this->form_validation->set_rules('collect_tax', 'Collect Tax', 'required|max_length[10]');
            if ($this->input->post('collect_tax') == "Yes") {
                $this->form_validation->set_rules('tax_title', 'Tax Title', 'required|max_length[50]');
                $this->form_validation->set_rules('tax_registration_no', 'Tax Registration No', 'required|max_length[50]');
                $this->form_validation->set_rules('tax_is_gst', 'Tax is GST', 'required|max_length[50]');
                $this->form_validation->set_rules('taxes[]', 'Taxes', 'required|max_length[10]');
            }

            if ($this->form_validation->run() == TRUE) {
                $outlet_info = array();
                $outlet_info['collect_tax'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('collect_tax')));
                if ($this->input->post('collect_tax') == "Yes") {
                    $outlet_info['tax_title'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('collect_tax')));
                    $outlet_info['tax_registration_no'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('collect_tax')));
                    $outlet_info['tax_is_gst'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('collect_tax')));
                }
                $outlet_info['tax_type'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('tax_type')));
                $outlet_info['tax_title'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('tax_title')));
                $outlet_info['tax_registration_no'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('tax_registration_no')));
                $outlet_info['tax_is_gst'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('tax_is_gst')));
                $this->session->set_userdata($outlet_info);

                $this->Common_model->updateInformation($outlet_info, $id, "tbl_companies");

                if(!empty($_POST['taxes'])){
                    $this->saveOutletTaxes($_POST['taxes'], $id, 'tbl_outlet_taxes');
                }
                $this->session->set_flashdata('exception', 'Information has been updated successfully!');

                redirect('setting/tax');
            } else {
                $data = array();
                $data['company'] = getCompanyInfo();
                $data['main_content'] = $this->load->view('authentication/tax', $data, TRUE);
                $this->load->view('userHome', $data);
            }

        } else {
            $data = array();
            $data['company'] = getCompanyInfo();
            $data['main_content'] = $this->load->view('authentication/tax', $data, TRUE);
            $this->load->view('userHome', $data);
        }
    }
    public function selfOrder($id = '') {
        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $this->form_validation->set_rules('sos_enable_self_order', lang('sos_enable_self_order'), 'required|max_length[10]');
            if ($this->form_validation->run() == TRUE) {
                $outlet_info = array();
                $outlet_info['sos_enable_self_order'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('sos_enable_self_order')));
                $this->Common_model->updateInformation($outlet_info, $id, "tbl_companies");
                $this->session->set_flashdata('exception', 'Information has been updated successfully!');

                redirect('setting/selfOrder');
            } else {
                $data = array();
                $data['company'] = getCompanyInfo();
                $data['main_content'] = $this->load->view('authentication/selfOrderSetting', $data, TRUE);
                $this->load->view('userHome', $data);
            }

        } else {
            $data = array();
            $data['company'] = getCompanyInfo();
            $data['main_content'] = $this->load->view('authentication/selfOrderSetting', $data, TRUE);
            $this->load->view('userHome', $data);
        }
    }
    public function selfOrderQrcode($id = '') {
        $outlet_id_post = htmlspecialcharscustom($this->input->post($this->security->xss_clean('outlet_id')));
        $outlet_id = isset($outlet_id_post) && $outlet_id_post?$outlet_id_post:$this->session->userdata('outlet_id');
        if (htmlspecialcharscustom($this->input->post('submit'))) {
            if(isLMni()):
                $this->form_validation->set_rules('outlet_id', lang('outlet'), 'required|max_length[10]');
            endif;

            if ($this->form_validation->run() == TRUE) {
                $data = array();
                if($outlet_id){
                    $tables = $this->Common_model->getAllByCustomId($outlet_id,"outlet_id","tbl_tables",$order='');
                    $company_id = $this->session->userdata("company_id");
                    foreach ($tables as $value){
                        $url_qrcode = base_url().'self-order/'.$value->id.'/'.$outlet_id.'/'.$company_id.'/Yes/';
                        $save_path = "qr_code/";
                        $file_name = $save_path."table-qrcode-".$value->id.".png";
                        $footer_text = "Table: ".$value->name;
                        $this->create_qr($url_qrcode,$file_name,$footer_text);
                    }
                    $data['tables'] = $tables;
                }
                $data['main_content'] = $this->load->view('authentication/generate_qrcode', $data, TRUE);
                $this->load->view('userHome', $data);

            } else {

                $data = array();
                $data['company'] = getCompanyInfo();
                $data['main_content'] = $this->load->view('authentication/generate_qrcode', $data, TRUE);
                $this->load->view('userHome', $data);
            }

        } else {
            $data = array();
            if($outlet_id){
                $tables = $this->Common_model->getAllByCustomId($outlet_id,"outlet_id","tbl_tables",$order='');
                $company_id = $this->session->userdata("company_id");
                foreach ($tables as $value){
                    $url_qrcode = base_url().'self-order/'.$value->id.'/'.$outlet_id.'/'.$company_id.'/Yes/';
                    $save_path = "qr_code/";
                    $file_name = $save_path."table-qrcode-".$value->id.".png";
                    $footer_text = "Table: ".$value->name;
                    $this->create_qr($url_qrcode,$file_name,$footer_text);
                }
                $data['tables'] = $tables;
            }
            $data['company'] = getCompanyInfo();
            $data['main_content'] = $this->load->view('authentication/generate_qrcode', $data, TRUE);
            $this->load->view('userHome', $data);

        }
    }
    public function onlineOrder($id = '') {
        if (htmlspecialcharscustom($this->input->post('submit'))) {
            $this->form_validation->set_rules('sos_enable_online_order', lang('sos_enable_online_order'), 'required|max_length[10]');
            if ($this->form_validation->run() == TRUE) {
                $outlet_info = array();
                $outlet_info['sos_enable_online_order'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('sos_enable_online_order')));
                $this->Common_model->updateInformation($outlet_info, $id, "tbl_companies");
                $this->session->set_flashdata('exception', 'Information has been updated successfully!');

                redirect('setting/onlineOrder');
            } else {
                $data = array();
                $data['company'] = getCompanyInfo();
                $data['main_content'] = $this->load->view('authentication/onlineOrderSetting', $data, TRUE);
                $this->load->view('userHome', $data);
            }

        } else {
            $data = array();
            $data['company'] = getCompanyInfo();
            $data['main_content'] = $this->load->view('authentication/onlineOrderSetting', $data, TRUE);
            $this->load->view('userHome', $data);
        }
    }
    public function onlineOrderURL($id = '') {
        $outlet_id_post = htmlspecialcharscustom($this->input->post($this->security->xss_clean('outlet_id')));
        $outlet_id = isset($outlet_id_post) && $outlet_id_post?$outlet_id_post:$this->session->userdata('outlet_id');
        if (htmlspecialcharscustom($this->input->post('submit'))) {
            if(isLMni()):
                $this->form_validation->set_rules('outlet_id', lang('outlet'), 'required|max_length[10]');
            endif;

            if ($this->form_validation->run() == TRUE) {
                $data = array();
                if($outlet_id){
                    $tables = $this->Common_model->getAllByCustomId($outlet_id,"outlet_id","tbl_tables",$order='');
                    $company_id = $this->session->userdata("company_id");
                    foreach ($tables as $value){
                        $url_qrcode = base_url().'self-order/'.$value->id.'/'.$outlet_id.'/'.$company_id.'/Yes/';
                        $save_path = "qr_code/";
                        $file_name = $save_path."table-qrcode-".$value->id.".png";
                        $footer_text = "Table: ".$value->name;
                        $this->create_qr($url_qrcode,$file_name,$footer_text);
                    }
                    $data['tables'] = $tables;
                }
                $data['main_content'] = $this->load->view('authentication/generate_url', $data, TRUE);
                $this->load->view('userHome', $data);

            } else {

                $data = array();
                $data['company'] = getCompanyInfo();
                $data['main_content'] = $this->load->view('authentication/generate_url', $data, TRUE);
                $this->load->view('userHome', $data);
            }

        } else {
            $data = array();
            if($outlet_id){
                $tables = $this->Common_model->getAllByCustomId($outlet_id,"outlet_id","tbl_tables",$order='');
                $company_id = $this->session->userdata("company_id");
                foreach ($tables as $value){
                    $url_qrcode = base_url().'self-order/'.$value->id.'/'.$outlet_id.'/'.$company_id.'/Yes/';
                    $save_path = "qr_code/";
                    $file_name = $save_path."table-qrcode-".$value->id.".png";
                    $footer_text = "Table: ".$value->name;
                    $this->create_qr($url_qrcode,$file_name,$footer_text);
                }
                $data['tables'] = $tables;
            }
            $data['company'] = getCompanyInfo();
            $data['main_content'] = $this->load->view('authentication/generate_url', $data, TRUE);
            $this->load->view('userHome', $data);

        }
    }
    public function downloadQRcode($file = "") {
        // load ci download helder
        $this->load->helper('download');
        $data = file_get_contents("qr_code/" . $file); // Read the file's
        $name = $file;
        force_download($name, $data);
    }
    function create_qr($datanya,$filename,$footer_text){
        // Create a basic QR code
        $data = $datanya;
        $qrCode = new QrCode($data);
        $qrCode->setSize(230);

        // Set advanced options
        $qrCode->setWriterByName('png');
        $qrCode->setMargin(15);
        $qrCode->setEncoding('UTF-8');
        $qrCode->setLabel($footer_text,'18');
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
        $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
        $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
        $qrCode->setValidateResult(false);
        $qrCode->writeFile($filename);
        return $filename;
    }
     /**
     * save Outlet Taxes
     * @access public
     * @return void
     * @param string
     * @param int
     * @param string
     */
    public function saveOutletTaxes($outlet_taxes, $company_id, $table_name)
    {
        $main_arr = array();
        $tax_string ='';
        foreach($outlet_taxes as $key=>$single_tax){
            $oti = array();
            if(isset($_POST['p_tax_id'][$key]) && $_POST['p_tax_id'][$key]){
                $oti['id'] = $_POST['p_tax_id'][$key];
            }else{
                $oti['id'] = 1;
            }
            $oti['tax'] = $single_tax;
            $oti['tax_rate'] = $_POST['tax_rate'][$key];
            $main_arr[] = $oti;
            $tax_string.=$single_tax.":";
        }
        $data['tax_setting'] = json_encode($main_arr);
        $data['tax_string'] = $tax_string;
        $this->Common_model->updateInformation($data, $company_id, "tbl_companies");
    }
     /**
     * whats app Setting
     * @access public
     * @return void
     * @param int
     */
    public function whatsappSetting($id = '') {
        if (htmlspecialcharscustom($this->input->post('submit'))) {
                $this->form_validation->set_rules('whatsapp_share_number', "Whatsapp Share Number", "required|max_length[300]");
            if ($this->form_validation->run() == TRUE) {
                $data['whatsapp_share_number'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('whatsapp_share_number')));
                $this->Common_model->updateInformation($data, $id, "tbl_companies");
                $this->session->set_flashdata('exception', lang('update_success'));
                redirect('setting/whatsappSetting');
            }else{
                $data = array();
                $data['company'] = getCompanyInfo();
                $data['main_content'] = $this->load->view('authentication/whatsappSetting', $data, TRUE);
                $this->load->view('userHome', $data);
            }

        } else {
            $data = array();
            $data['company'] = getCompanyInfo();
            $data['main_content'] = $this->load->view('authentication/whatsappSetting', $data, TRUE);
            $this->load->view('userHome', $data);
        }
    }
     /**
     * sms Setting
     * @access public
     * @return void
     * @param int
     */
    public function smsSetting($id = '') {
        if (htmlspecialcharscustom($this->input->post('submit'))) {

            $enable_status  =htmlspecialcharscustom($this->input->post($this->security->xss_clean('enable_status')));
            $this->form_validation->set_rules('enable_status', "Enable Status", "max_length[50]");
            if($enable_status==1){
                $this->form_validation->set_rules('f_1_1', "User Name", "required|max_length[300]");
                $this->form_validation->set_rules('f_1_2', "Password", "required|max_length[300]");
            }else if($enable_status==2){
                $this->form_validation->set_rules('f_2_1', "User Name", "required|max_length[300]");
                $this->form_validation->set_rules('f_2_2', "Password", "required|max_length[300]");
            }else if($enable_status==3){
                $this->form_validation->set_rules('f_3_1', "User Name", "required|max_length[300]");
                $this->form_validation->set_rules('f_3_2', "Password", "required|max_length[300]");
                $this->form_validation->set_rules('f_3_3', "API Key", "required");
            }else if($enable_status==4){
                $this->form_validation->set_rules('f_4_1', "SID", "required|max_length[300]");
                $this->form_validation->set_rules('f_4_2', "Token", "required");
                $this->form_validation->set_rules('f_4_3', "Twilio Number", "required|max_length[300]");
            }else if($enable_status==5){
                $this->form_validation->set_rules('f_5_1', "API Key", "required|max_length[300]");
                $this->form_validation->set_rules('f_5_2', "API Secret Key", "required|max_length[300]");
            }

            if ($this->form_validation->run() == TRUE) {
                $data = array();
                $data['sms_enable_status'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('enable_status')));
                $data_json['f_1_1'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('f_1_1')));
                $data_json['f_1_2'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('f_1_2')));
                $data_json['f_2_1'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('f_2_1')));
                $data_json['f_2_2'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('f_2_2')));
                $data_json['f_3_1'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('f_3_1')));
                $data_json['f_3_2'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('f_3_2')));
                $data_json['f_3_3'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('f_3_3')));
                $data_json['f_4_1'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('f_4_1')));
                $data_json['f_4_2'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('f_4_2')));
                $data_json['f_4_3'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('f_4_3')));
                $data_json['f_5_1'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('f_5_1')));
                $data_json['f_5_2'] =htmlspecialcharscustom($this->input->post($this->security->xss_clean('f_5_2')));
                $data['sms_details'] = json_encode($data_json);
                $this->Common_model->updateInformation($data, $id, "tbl_companies");
                redirect('setting/smsSetting');
            }else{
                $data = array();
                $data['company'] = getCompanyInfo();
                $company_id = $this->session->userdata('company_id');
                $data['users'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_users");
                $data['main_content'] = $this->load->view('authentication/smsSetting', $data, TRUE);
                $this->load->view('userHome', $data);
            }

        } else {
            $data = array();
            $data['company'] = getCompanyInfo();
            $company_id = $this->session->userdata('company_id');
            $data['users'] = $this->Common_model->getAllByCompanyIdForDropdown($company_id, "tbl_users");
        $data['main_content'] = $this->load->view('authentication/smsSetting', $data, TRUE);
            $this->load->view('userHome', $data);
        }
    }

    public function customerDisplaySetting($id = '') {
        $company_id = $this->session->userdata('company_id');
        $slideshow_dir = FCPATH . 'images/slideshow';
        $config_file = $slideshow_dir . '/config.json';
        $slides = [];
        
        if (file_exists($config_file)) {
            $slides = json_decode(file_get_contents($config_file), true);
            if (!is_array($slides)) {
                $slides = [];
            }
        } else {
            // Migrate: scan directory and generate config.json
            if (is_dir($slideshow_dir)) {
                $files = scandir($slideshow_dir);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') continue;
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm'])) {
                        $slides[] = [
                            'name' => $file,
                            'url' => base_url('images/slideshow/' . $file),
                            'type' => in_array($ext, ['mp4', 'webm']) ? 'video' : 'image',
                            'size' => is_file($slideshow_dir . '/' . $file) ? round(filesize($slideshow_dir . '/' . $file) / 1024, 2) : 0,
                            'is_external' => false
                        ];
                    }
                }
                // Save it
                if (!empty($slides)) {
                    if (!is_dir($slideshow_dir)) {
                        mkdir($slideshow_dir, 0777, true);
                    }
                    file_put_contents($config_file, json_encode($slides, JSON_PRETTY_PRINT));
                }
            }
        }

        $data = array();
        $data['slides'] = $slides;
        $data['encrypted_id'] = $this->custom->encrypt_decrypt($company_id, 'encrypt');
        $data['main_content'] = $this->load->view('authentication/customer_display_setting', $data, TRUE);
        $this->load->view('userHome', $data);
    }

    public function customer_display_upload() {
        $slideshow_dir = './images/slideshow';
        if (!is_dir($slideshow_dir)) {
            mkdir($slideshow_dir, 0777, true);
        }
        $config_file = $slideshow_dir . '/config.json';
        
        // Load existing config
        $slides = [];
        if (file_exists($config_file)) {
            $slides = json_decode(file_get_contents($config_file), true);
            if (!is_array($slides)) {
                $slides = [];
            }
        }
        
        $media_source_type = $this->input->post('media_source_type');
        
        if ($media_source_type === 'external') {
            $external_url = $this->input->post('external_url');
            $external_type = $this->input->post('external_type');
            
            if ($external_url) {
                // Generate a name from url or custom
                $name = 'External_' . time() . '_' . substr(md5($external_url), 0, 8);
                
                $slides[] = [
                    'name' => $name,
                    'url' => $external_url,
                    'type' => $external_type,
                    'size' => 0,
                    'is_external' => true
                ];
                
                file_put_contents($config_file, json_encode($slides, JSON_PRETTY_PRINT));
                $this->session->set_flashdata('exception', 'External link added successfully!');
            } else {
                $this->session->set_flashdata('exception_er', 'Please enter a valid URL.');
            }
        } else {
            // Upload Local File logic
            if ($_FILES['slideshow_file']['name'] != "") {
                $config['upload_path'] = $slideshow_dir;
                $config['allowed_types'] = 'jpg|jpeg|png|gif|webp|mp4|webm';
                $config['max_size'] = '15360'; // Max 15MB
                $config['encrypt_name'] = TRUE;
                $this->load->library('upload', $config);
                
                if ($this->upload->do_upload("slideshow_file")) {
                    $upload_data = $this->upload->data();
                    $file_name = $upload_data['file_name'];
                    $file_ext = strtolower($upload_data['file_ext']);
                    $file_path = $upload_data['full_path'];
                    
                    $is_video = in_array($file_ext, ['.mp4', '.webm']);
                    $file_size = round($upload_data['file_size'], 2);
                    
                    if (!$is_video && in_array($file_ext, ['.jpg', '.jpeg', '.png', '.gif'])) {
                        // Convert to WebP for modern compression & storage savings
                        $webp_file_name = pathinfo($file_name, PATHINFO_FILENAME) . '.webp';
                        $webp_file_path = $upload_data['file_path'] . $webp_file_name;
                        
                        $img = null;
                        if ($file_ext === '.jpg' || $file_ext === '.jpeg') {
                            $img = @imagecreatefromjpeg($file_path);
                        } elseif ($file_ext === '.png') {
                            $img = @imagecreatefrompng($file_path);
                            if ($img) {
                                imagepalettetotruecolor($img);
                                imagealphablending($img, true);
                                imagesavealpha($img, true);
                            }
                        } elseif ($file_ext === '.gif') {
                            $img = @imagecreatefromgif($file_path);
                        }
                        
                        if ($img) {
                            if (@imagewebp($img, $webp_file_path, 80)) {
                                imagedestroy($img);
                                @unlink($file_path); // Delete the original uploaded file
                                
                                $file_name = $webp_file_name;
                                $file_size = round(filesize($webp_file_path) / 1024, 2);
                            } else {
                                imagedestroy($img);
                            }
                        }
                    }
                    
                    $slides[] = [
                        'name' => $file_name,
                        'url' => base_url('images/slideshow/' . $file_name),
                        'type' => $is_video ? 'video' : 'image',
                        'size' => $file_size,
                        'is_external' => false
                    ];
                    
                    file_put_contents($config_file, json_encode($slides, JSON_PRETTY_PRINT));
                    $this->session->set_flashdata('exception', 'File uploaded and optimized successfully!');
                } else {
                    $this->session->set_flashdata('exception_er', $this->upload->display_errors('', ''));
                }
            } else {
                $this->session->set_flashdata('exception_er', 'Please select a file to upload.');
            }
        }
        
        redirect('setting/customerDisplaySetting');
    }

    public function customer_display_delete($filename = '') {
        if ($filename) {
            $filename = urldecode($filename);
            $slideshow_dir = FCPATH . 'images/slideshow';
            $config_file = $slideshow_dir . '/config.json';
            
            if (file_exists($config_file)) {
                $slides = json_decode(file_get_contents($config_file), true);
                if (is_array($slides)) {
                    $new_slides = [];
                    $deleted = false;
                    foreach ($slides as $slide) {
                        if ($slide['name'] === $filename) {
                            // If it's a local file, delete it
                            if (!isset($slide['is_external']) || !$slide['is_external']) {
                                $file_path = $slideshow_dir . '/' . $slide['name'];
                                if (is_file($file_path)) {
                                    unlink($file_path);
                                }
                            }
                            $deleted = true;
                        } else {
                            $new_slides[] = $slide;
                        }
                    }
                    if ($deleted) {
                        file_put_contents($config_file, json_encode($new_slides, JSON_PRETTY_PRINT));
                        $this->session->set_flashdata('exception', 'Slide deleted successfully!');
                    } else {
                        $this->session->set_flashdata('exception_er', 'Slide not found.');
                    }
                }
            } else {
                // Legacy unlink code if config.json is missing
                $file_path = FCPATH . 'images/slideshow/' . basename($filename);
                if (is_file($file_path)) {
                    unlink($file_path);
                    $this->session->set_flashdata('exception', 'File deleted successfully!');
                } else {
                    $this->session->set_flashdata('exception_er', 'File not found or already deleted.');
                }
            }
        }
        redirect('setting/customerDisplaySetting');
    }
}

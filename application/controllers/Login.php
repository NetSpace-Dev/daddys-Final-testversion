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
  # This is Authentication Controller
  ###########################################################
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends Cl_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Authentication_model');
        $this->load->model('Common_model');
        $this->load->library('form_validation');
    }

    public function waiter_login() {
        $valid_txt = isset($_GET['txt_vld']) && $_GET['txt_vld']?$_GET['txt_vld']:'';
        if(str_rot13($valid_txt) == "Ty3qq5fq"){
            $this->load->view('authentication/login_waiter');
        }else{
            redirect("access-denied");
        }
    }
    public function accessDenied() {
        echo "<title>".lang('desktop_access_denied')."</title>";
        echo lang('desktop_access_denied');exit;
    }

    /**
     * check login info
     * @access public
     * @return void
     * @param no
     */
    public function waiter_login_check() {
        $this->form_validation->set_rules('email_address', lang('email_address'), 'required|max_length[50]');
        $this->form_validation->set_rules('password', lang('password'), 'required|max_length[25]');

        if ($this->form_validation->run() == TRUE) {
            $email_address = htmlspecialcharscustom($this->input->post($this->security->xss_clean('email_address')));
            $password = md5($this->input->post($this->security->xss_clean('password')));

            $user_information = $this->Authentication_model->getUserInformation($email_address, $password, '', 2);

            if ($user_information) {
                if ($user_information->active_status == 'Active') {
                    $company_info = $this->Authentication_model->getCompanyInformation($user_information->company_id);
                    if ($company_info && $company_info->is_active == 1) {
                        
                        $login_session = array();
                        $login_session['user_id'] = $user_information->id;
                        $login_session['language'] = $user_information->language;
                        $login_session['designation'] = $user_information->designation;
                        $login_session['full_name'] = $user_information->full_name;
                        $login_session['short_name'] = strtolower(substr($user_information->full_name,0, 1));
                        $login_session['phone'] = $user_information->phone;
                        $login_session['email_address'] = $user_information->email_address;
                        $login_session['role'] = $user_information->role;
                        $login_session['role_id'] = $user_information->role_id;
                        $login_session['company_id'] = $user_information->company_id;
                        $login_session['session_outlets'] = $user_information->outlets;
                        $login_session['active_menu_tmp'] = '';

                        $company_info_session = array();
                        $company_info_session['currency'] = $company_info->currency;
                        $company_info_session['zone_name'] = $company_info->zone_name;
                        $company_info_session['date_format'] = $company_info->date_format;
                        $company_info_session['business_name'] = $company_info->business_name;
                        $company_info_session['address'] = $company_info->address;
                        $company_info_session['website'] = $company_info->website;
                        $company_info_session['currency_position'] = $company_info->currency_position;
                        $company_info_session['precision'] = $company_info->precision;
                        $company_info_session['default_customer'] = $company_info->default_customer;
                        $company_info_session['service_amount'] = $company_info->service_amount;
                        $company_info_session['delivery_amount'] = $company_info->delivery_amount;
                        $company_info_session['tax_type'] = $company_info->tax_type;
                        $company_info_session['decimals_separator'] = $company_info->decimals_separator;
                        $company_info_session['thousands_separator'] = $company_info->thousands_separator;
                        $company_info_session['is_rounding_enable'] = $company_info->is_rounding_enable;

                        $this->session->set_userdata($login_session);
                        $this->session->set_userdata($company_info_session);

                        $outlet_info = $this->db->query("SELECT * FROM tbl_outlets WHERE del_status='Live' AND active_status='active' AND id='$user_information->outlet_id'")->row();
                        if ($outlet_info) {
                            $outlet_session = array();
                            $outlet_session['outlet_id'] = $outlet_info->id;
                            $outlet_session['outlet_name'] = $outlet_info->outlet_name;
                            $outlet_session['address'] = $outlet_info->address;
                            $outlet_session['phone'] = $outlet_info->phone;
                            $outlet_session['email'] = $outlet_info->email;
                            $outlet_session['outlet_code'] = $outlet_info->outlet_code;
                            $outlet_session['has_kitchen'] = $outlet_info->has_kitchen;
                            $this->session->set_userdata($outlet_session);
                        }

                        redirect('Waiter/panel');
                    } else {
                        $this->session->set_flashdata('exception_1', lang('company_not_active'));
                        redirect('waiter-login?txt_vld=Gl3dd5sd');
                    }
                } else {
                    $this->session->set_flashdata('exception_1', lang('user_not_active'));
                    redirect('waiter-login?txt_vld=Gl3dd5sd');
                }
            } else {
                $this->session->set_flashdata('exception_1', lang('incorrect_email_password'));
                redirect('waiter-login?txt_vld=Gl3dd5sd');
            }
        } else {
            $this->load->view('authentication/login_waiter');
        }
    }
}

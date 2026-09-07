<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Print_api extends Cl_Controller {

    public function __construct() {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, Authorization, X-Requested-With");
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit();
        }
        parent::__construct();
        $this->load->model('Common_model');
    }

    /**
     * Fetch pending print jobs for local print agent
     * URL: https://mydomain.com/Print_api/get_pending_prints?outlet_id=1
     */
    public function get_pending_prints() {
        $outlet_id = $this->input->get('outlet_id');
        
        $this->db->select('*');
        $this->db->from('tbl_print_queue');
        $this->db->where('status', 'pending');
        if (!empty($outlet_id)) {
            $this->db->where('outlet_id', $outlet_id);
        }
        $this->db->order_by('id', 'ASC');
        $this->db->limit(10);
        $jobs = $this->db->get()->result();

        // Auto-cleanup printed jobs older than 7 days to keep DB table small
        if (rand(1, 10) == 1) {
            $this->db->query("DELETE FROM tbl_print_queue WHERE status = 'printed' AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");
        }

        echo json_encode(array(
            'status' => 'success',
            'jobs' => $jobs
        ));
    }

    /**
     * Mark print job as printed
     * URL: https://mydomain.com/Print_api/mark_as_printed
     */
    public function mark_as_printed() {
        $job_id = $this->input->post('job_id');
        if ($job_id) {
            $this->db->where('id', $job_id);
            $this->db->update('tbl_print_queue', array('status' => 'printed'));
            echo json_encode(array('status' => 'success'));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Missing job_id'));
        }
    }

    /**
     * Add print job to queue via AJAX or API
     * URL: https://mydomain.com/Print_api/add_to_queue_ajax
     */
    public function add_to_queue_ajax() {
        $outlet_id = $this->input->post('outlet_id') ? $this->input->post('outlet_id') : 1;
        $print_type = $this->input->post('print_type');
        $content_data = $this->input->post('content_data');

        if ($print_type && $content_data) {
            $data = array(
                'outlet_id' => $outlet_id,
                'print_type' => $print_type,
                'content_data' => is_string($content_data) ? $content_data : json_encode($content_data),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            );
            $this->db->insert('tbl_print_queue', $data);
            $insert_id = $this->db->insert_id();
            echo json_encode(array('status' => 'success', 'job_id' => $insert_id));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Invalid parameters'));
        }
    }
}

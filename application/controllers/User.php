<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_logged_in();
    }
    public function index()
    {
        $data['title'] = 'My profile';
        $data['user'] = $this->db->get_where('user', ['email' =>
        $this->session->userdata('email')])->row_array();

        $this->load->view('template/admin_header', $data);
        $this->load->view('template/sidebar', $data);
        $this->load->view('template/topbar', $data);
        $this->load->view('user/index', $data);
        $this->load->view('template/admin_footer');
    }

    public function edit()
    {
        $data['title'] = 'Edit Profile';
        $data['user'] = $this->db->get_where('user', ['email' =>
        $this->session->userdata('email')])->row_array();
        $data['menu'] = $this->db->get('user_menu')->result_array();

        $this->form_validation->set_rules('name', 'Full name', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('template/admin_header', $data);
            $this->load->view('template/sidebar', $data);
            $this->load->view('template/topbar', $data);
            $this->load->view('menu/edit', $data);
            $this->load->view('template/admin_footer');
        } else {
            $name = $this->input->post('name');
            $email = $this->input->post('email');
            //upload gambar
            //cek gambar yang akan di uploatd
            $upload_image = $_FILES['image']['name'];

            if ($upload_image) { //jika ada gambar yang di uppload
                //isi config dari user guid codeigniter
                $config['allowed_types'] = 'gif|jpg|png';
                $config['max_size']     = '5048';
                $config['upload_path'] = './assets/img/';

                $this->load->library('upload', $config);

                if ($this->upload->do_upload('image')) { //jika daa gambar yang di upload

                    //ambil data image lama dari database ,jika old image tidak sama dengan (namanya ) default.jpg
                    //hapus(jika gambar mengunakan unlink) di cari di folder lalu isi dengan gambar lama
                    $old_image = $data['user']['image'];
                    if ($old_image != 'default.jpg') {
                        unlink(FCPATH . 'assets/img/' . $old_image);
                    }

                    //variable gambar lama di sisi dengan ambil dari data yang telah di upload 
                    //set ke data base image di isi dengan new image 
                    $new_image = $this->upload->data('file_name');
                    $this->db->set('image', $new_image);
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">' . $this->upload->display_errors() . '</div>');
                    redirect('user');
                }
                //ahir upload gambar
            }

            $this->db->set('name', $name);
            $this->db->where('email', $email);
            $this->db->update('user');
            $this->session->set_flashdata('flash', '<div class="alert alert-success" role="alert">Has been updated !</div>');
            redirect('user');
        }
    }

    public function changepassword()
    {
        $data['title'] = 'Change Password';
        $data['user'] = $this->db->get_where('user', ['email' =>
        $this->session->userdata('email')])->row_array();

        $this->form_validation->set_rules('current_password', 'Current password', 'required|trim');
        $this->form_validation->set_rules('new_password1', 'New Password', 'required|trim|min_length[3]|matches[new_password2]');
        $this->form_validation->set_rules('new_password2', 'Confirm New Password', 'required|trim|min_length[3]', 'matches[new_password1]');

        if ($this->form_validation->run() == false) {

            $this->load->view('template/admin_header', $data);
            $this->load->view('template/sidebar', $data);
            $this->load->view('template/topbar', $data);
            $this->load->view('user/changepassword', $data);
            $this->load->view('template/admin_footer');
        } else {
            //masukan passowrd lama dan baru ke dalam variabel
            $current_password = $this->input->post('current_password');
            $new_password = $this->input->post('new_password1');
            //jika user masukan password lama tidak sama dengan password lama di database
            if (!password_verify($current_password, $data['user']['password'])) {
                $this->session->set_flashdata('flash', '<div class="alert alert-success" role="alert">Wrong current password !</div>');
                redirect('user/changepassword');
            } else {
                if ($new_password == $current_password) {
                    $this->session->set_flashdata('flash', '<div class="alert alert-success" role="alert">New password cannot be same as currrent password!</div>');
                    redirect('user/changepassword');
                } else {
                    //jika benar password di hash
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    //query ke data base
                    $this->db->set('password', $password_hash);
                    $this->db->where('email', $this->session->userdata['email']);
                    $this->db->update('user');
                }
            }
        }
    }
}

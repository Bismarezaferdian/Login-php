<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
    }
    public function index()
    {
        //jika sudah login , jangan kembali ke login 
        if ($this->session->userdata('email')) {
            redirect('user');
        }
        $data['title'] = 'Login page';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');
        if ($this->form_validation->run() == FALSE) {
            $this->load->view('template/header', $data);
            $this->load->view('auth/login');
            $this->load->view('template/footer');
        } else {
            $this->_login();
        }
    }

    private function _login()
    {
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        $user = $this->db->get_where('user', ['email' => $email])->row_array();

        //jika user ada
        if ($user) {
            //jika user active
            if ($user['is_active'] == 1) {
                //cek password
                if (password_verify($password, $user['password'])) {
                    //jika password benar
                    $data = [
                        'email' => $user['email'],
                        'role_id' => $user['role_id']
                    ];
                    $this->session->set_userdata($data);
                    if ($user['role_id'] == 1) {
                        redirect('admin');
                    } else {

                        redirect('user');
                    }
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                    Wrong password ! </div>');
                    redirect('auth');
                }
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                This email has not been activated ! </div>');
                redirect('auth');
            }
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
            Email is not registered ! </div>');
            redirect('auth');
        }
    }

    public function registration()
    {
        //jika sudah login ,jangan balik ke registration
        if ($this->session->userdata('email')) {
            redirect('user');
        }

        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]', [
            'is_unique' => 'This email has already registered!'
        ]);
        $this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[3]|matches[password2]', [
            'matches' => 'password dont match!', //perintah juka tidak maches masukan apa
            'min_length' => 'password too short', //printah jika password tertalu pendek masukan apa 
        ]);
        $this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password1]');


        if ($this->form_validation->run() == FALSE) {
            $data['title'] = 'Registration page';
            $this->load->view('template/header', $data);
            $this->load->view('auth/registration');
            $this->load->view('template/footer');
        } else {
            $email = htmlspecialchars($this->input->post('email', true));
            $data = [
                'name' => htmlspecialchars($this->input->post('name', true)),
                'email' => $email,
                'image' => 'default.jpg',
                'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
                'role_id' => 2,
                'is_active' => 0,
                'date_created' => time()
            ];

            //siapkakn bilangan token
            $token = base64_encode(random_bytes(32));
            $user_token = [
                'email' => $email,
                'token' => $token,
                'date_created' => time()

            ];


            //insert too database
            $this->db->insert('user', $data);
            $this->db->insert('user_token', $user_token);

            //kirim email
            $this->_sendEmail($token, 'verify');

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
             Congratulation! your account has been created. Please verify email </div>');
            redirect('auth');
        }
    }

    private function _sendEmail($token, $type)
    {
        //konfigurasi untuk ngirim email
        $config = [
            'protocol'  => 'smtp',
            'smtp_host' => 'ssl://smtp.googlemail.com',
            'smtp_user' => 'ariferdi5695@gmail.com',
            'smtp_pass' => 'Bismarez0506',
            'smtp_port' => 465,
            'mailtype'  => 'html',
            'charset'   => 'utf-8',
            'newline'   => "\r\n"
        ];

        $this->email->initialize($config);


        $this->email->from('ariferdi5695@gmail.com', 'bisma');
        $this->email->to($this->input->post('email'));

        if ($type == 'verify') {

            $this->email->subject('Activation ');
            $this->email->message('click this link for verify your account : <a href="' . base_url() . 'auth/verify?email=' . $this->input->post('email') . '&token=' . urlencode($token) . '">Activate</a>');
        } else if ($type == 'forgot') {
            $this->email->subject('reset Password');
            $this->email->message('click this link for reset your password : <a href="' . base_url() . 'auth/resetpassword?email=' . $this->input->post('email') . '&token=' . urlencode($token) . '">Reset Password</a>');
        }


        if ($this->email->send()) {
            return true;
        } else {
            $this->email->print_debugger();
            die;
        }
    }


    public function verify()
    {
        //ambil email dan token dari 
        $email = $this->input->get('email');
        $token = $this->input->get('token');

        $user = $this->db->get_where('user', ['email' => $email])->row_array();

        if ($user) {

            //ambil dulu user token dari database
            $user_token = $this->db->get_where('user_token', ['token' => $token])->row_array();
            if ($user_token) {
                if (time() - $user_token['date_created'] < (60 * 60 * 24)) {
                    $this->db->set('is_active', 1);
                    $this->db->where('email', $email);
                    $this->db->update('user');
                    //jika berhasil delete user token
                    $this->db->delete('user_token', ['email' => $email]);
                    //kasih flash data 
                    $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">' . $email . ' Haas been activate! Please login </div>');
                    redirect('auth');
                } else {
                    $this->db->delete('user', ['email' => $email]);
                    $this->db->delete('user_token', ['email' => $email]);
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert"> account activation failed ! token expired . </div>');
                    redirect('auth');
                }
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert"> account activation failed ! wrong token. </div>');
                redirect('auth');
            }
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert"> account activation failed ! wrong email. </div>');
            redirect('auth');
        }
    }

    public function logout()
    {
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('password');

        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
        You have been logged out ! </div>');
        redirect('auth');
    }

    public function blocked()
    {
        $this->load->view('auth/blocked');
    }

    public function forgotPassword()
    {
        $data['title'] = 'Forgot Password';

        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');

        if ($this->form_validation->run() == false) {
            $this->load->view('template/header', $data);
            $this->load->view('auth/forgot-password');
            $this->load->view('template/footer');
        } else {
            //ambil email dari form input
            $email = htmlspecialchars($this->input->post('email'));
            //cek email ada tidak di database 
            $user = $this->db->get_where('user', ['email' => $email, 'is_active' => 1])->row_array();
            //jika ada email/user
            if ($user) {
                //kita bikin bilangan token
                $token = base64_encode(random_bytes(32));
                //siap kan data untuk di masukan kedalam database 
                $user_token = [
                    'email' => $email,
                    'token' => $token,
                    'date_created' => time()
                ];
                //insert kedalam table user_token
                $this->db->insert('user_token', $user_token);
                //kirim email beserta token dan type forgot/verify
                $this->_sendEmail($token, 'forgot');

                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert"> please cek email for your reset password ! </div>');
                redirect('auth/forgotpassword');
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert"> your email not registered or activated ! </div>');
                redirect('auth/forgotpassword');
            }
        }
    }
}

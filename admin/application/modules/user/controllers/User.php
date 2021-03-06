<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Illuminate\Database\Capsule\Manager as Capsule;
use Nurmanhabib\WilayahIndonesia\Sources\DatabaseSource;

class User extends Admin {

    public function __construct()
    {
        parent::__construct();
        
        $this->load->model('Mod_user', 'model');
        $this->load->library('WilayahIndonesia', null, 'wilayah');

        $hostname = getenv('AUTH_DB_HOST') ?: 'localhost';
        $username = getenv('AUTH_DB_USERNAME') ?: 'root';
        $password = getenv('AUTH_DB_PASSWORD') ?: '';
        $database = getenv('AUTH_DB_DATABASE') ?: 'portal_learning';

        $source = new DatabaseSource($hostname, $username, $password, $database);
        $this->wilayah->setSource($source);
    }

    public function index()
    {
        $data['list_user'] = $this->model->getAll();

        $this->template->build('view_user', $data);
    }

    public function wilayah()
    {
        echo $this->wilayah->ajax();
    }

    public function create()
    {
        //$this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[6]|max_length[10]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]|matches[password_confirmation]');
        $this->form_validation->set_rules('password_confirmation', 'Password Confirmation', 'required|matches[password]');

        $data['role_lists'] = $this->model->getRoleLists();

        if ($this->form_validation->run() == FALSE) {
            $this->template->inject_partial('script', $this->wilayah->script(site_url('user/wilayah')));
            $this->template->build('form_create', $data);
        } else {
            $username   = set_value('email');
            $password   = set_value('password');
            $email      = set_value('email');
            $role       = set_value('role');

            $profile    = array(
                'first_name'        => set_value('first_name'),
                'last_name'         => set_value('last_name'),
                'gender'            => set_value('gender'),
                'tempat_lahir'      => set_value('tempat_lahir'),
                'tanggal_lahir'      => set_value('tanggal_lahir'),
                'address'           => set_value('address'),
                'desa_id'           => set_value('desa'),
                'avatar'            => set_value('avatar'),
            );

            $register = $this->model->register($username, $password, $email, $role, $profile);

            if (isset($_FILES['avatar']['tmp_name'])) {
                $this->model->setAvatar($register->user_id, $_FILES['avatar']);
            }

            if ($register == FALSE) {
                set_message_error($this->ion_auth->errors());

                $this->template->build('form_create', $data);
            } else {
                redirect('user', 'refresh');
            }
        }
    }

    public function updateProfile($id)
    {
        $source = $this->wilayah->getSource();
        $auth   = new Library\Auth\Auth;

        $user               = $auth->getById($id);
        $user->wilayah      = $source->getParentByDesa($user->profile->desa_id);
        
        $data['user']       = $user;
        $data['profile']    = $user->profile;
        $data['role_lists'] = $this->model->getRoleLists();

        $this->template->inject_partial('script', $this->wilayah->script(site_url('user/wilayah')));
        $this->template->build('form_update',$data);
    }

    public function saveUpdateProfile($id)
    {
        $user       = array(
            'email' => $this->input->post('email')
        );
        
        $profile    = array(
            'first_name'         => set_value('first_name'),
            'last_name'          => set_value('last_name'),
            'gender'             => set_value('gender'),
            'tempat_lahir'       => set_value('tempat_lahir'),
            'tanggal_lahir'      => set_value('tanggal_lahir'),
            'address'            => set_value('address'),
            'desa_id'            => set_value('desa'),
            'avatar'             => set_value('avatar'),
        );
        
        $res = $this->model->update($id, $user, $profile);

        if (isset($_FILES['avatar']) && $_FILES['avatar']['tmp_name']) {
            $this->model->setAvatar($id, $_FILES['avatar']);
        }

        if ($res==TRUE) {
            set_message_success('User berhasil diperbarui.');

            redirect('user/updateProfile/'.$id);
        } else {
            set_message_error('User gagal diperbarui.');

            redirect('user/updateProfile/'.$id);
        }
    }

    public function changepassword($user_id)
    {
        $this->form_validation->set_rules('password', 'New Password', 'required|min_length[6]');
        $this->form_validation->set_rules('password_confirmation', 'New Password Confirmation', 'required|min_length[6]|matches[password]');
        $this->form_validation->set_rules('password_old', 'Old Password', 'required');

        if ($this->form_validation->run() == FALSE) {
            $data['user'] = auth()->getById($user_id);

            $this->template->build('formChangePass', $data);
        } else {
            $password       = set_value('password');
            $password_old   = set_value('password_old');
            $changed        = $this->model->changePassword($user_id, $password, $password_old);

            if ($changed) {
                set_message_success('Password berhasil diperbarui.');

                redirect('user/updateProfile/'.$user_id, 'refresh');
            } else {
                set_message_error('Password lama tidak sesuai.');

                redirect('user/changepassword/'.$user_id, 'refresh');
            }
        }
    }

    public function delete($user_id)
    {
        $data = $this->model->delete($user_id);

        redirect('user', $data);
    }

}

/* End of file User.php */
/* Location: ./application/modules/user/controllers/User.php */

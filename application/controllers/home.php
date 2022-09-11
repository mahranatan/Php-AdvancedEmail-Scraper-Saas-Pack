<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
* @category controller
* class home
*/
class home extends CI_Controller
{

    /**
    * load constructor
    * @access public
    * @return void
    */
    public function __construct()
    {
        parent::__construct();
        set_time_limit(0);
        $this->load->helpers('my_helper');

        $seg = $this->uri->segment(2);
        if ($seg!="installation" && $seg!= "installation_action") {
            if (file_exists(APPPATH.'install.txt')) {
                redirect('home/installation', 'location');
            }
        }

        if (!file_exists(APPPATH.'install.txt')) {
            $this->load->database();
            $this->load->model('basic');
            $this->_time_zone_set();
            $this->upload_path = realpath(APPPATH . '../upload');
        }
        
    }

    /**
    * method to install software
    * @access public
    * @return void
    */
    public function installation()
    {
        if (!file_exists(APPPATH.'install.txt')) {
            redirect('home/login', 'location');
        }
        $data = array("body" => "page/install", "page_title" => "Install Package");
        $this->_front_viewcontroller($data);
    }

    /**
    * method to installation action
    * @access public
    * @return void
    */
    public function installation_action()
    {
        if (!file_exists(APPPATH.'install.txt')) {
            redirect('home/login', 'location');
        }

        if ($_POST) {
            // validation
            $this->form_validation->set_rules('host_name',               '<b>Host Name</b>',                   'trim|required|xss_clean');
            $this->form_validation->set_rules('database_name',           '<b>Database Name</b>',               'trim|required|xss_clean');
            $this->form_validation->set_rules('database_username',       '<b>Database Username</b>',           'trim|required|xss_clean');
            $this->form_validation->set_rules('database_password',       '<b>Database Password</b>',           'trim|xss_clean');
            $this->form_validation->set_rules('app_username',            '<b>Admin Panel Login Email</b>',     'trim|required|valid_email|xss_clean');
            $this->form_validation->set_rules('app_password',            '<b>Admin Panel Login Password</b>',  'trim|required|xss_clean');
            $this->form_validation->set_rules('institute_name',          '<b>Company Name</b>',                'trim|xss_clean');
            $this->form_validation->set_rules('institute_address',       '<b>Company Address</b>',             'trim|xss_clean');
            $this->form_validation->set_rules('institute_mobile',        '<b>Company Phone / Mobile</b>',      'trim|xss_clean');

            // go to config form page if validation wrong
            if ($this->form_validation->run() == false) {
                return $this->installation();
            } else {
                $host_name = addslashes(strip_tags($this->input->post('host_name', true)));
                $database_name = addslashes(strip_tags($this->input->post('database_name', true)));
                $database_username = addslashes(strip_tags($this->input->post('database_username', true)));
                $database_password = addslashes(strip_tags($this->input->post('database_password', true)));
                $app_username = addslashes(strip_tags($this->input->post('app_username', true)));
                $app_password = addslashes(strip_tags($this->input->post('app_password', true)));
                $institute_name = addslashes(strip_tags($this->input->post('institute_name', true)));
                $institute_address = addslashes(strip_tags($this->input->post('institute_address', true)));
                $institute_mobile = addslashes(strip_tags($this->input->post('institute_mobile', true)));

                if (!@mysql_connect($host_name, $database_username, $database_password)) {
                    $this->session->set_userdata('mysql_error', "Could not conenect to MySQL.");
                    return $this->installation();
                }
                if (!@mysql_select_db($database_name)) {
                    $this->session->set_userdata('mysql_error', "Database not found.");
                    return $this->installation();
                }
                mysql_close();

                 // writing application/config/my_config
                  $app_my_config_data = "<?php ";
                $app_my_config_data.= "\n\$config['default_page_url'] = '".$this->config->item('default_page_url')."';\n";
                $app_my_config_data.= "\$config['product_name'] = '".$this->config->item('product_name')."';\n";
                $app_my_config_data.= "\$config['product_short_name'] = '".$this->config->item('product_short_name')."' ;\n";
                $app_my_config_data.= "\$config['product_version'] = '".$this->config->item('product_version')." ';\n\n";
                $app_my_config_data.= "\$config['institute_address1'] = '$institute_name';\n";
                $app_my_config_data.= "\$config['institute_address2'] = '$institute_address';\n";
                $app_my_config_data.= "\$config['institute_email'] = '$app_username';\n";
                $app_my_config_data.= "\$config['institute_mobile'] = '$institute_mobile';\n";
                $app_my_config_data.= "\$config['developed_by'] = '".$this->config->item('developed_by')."';\n";
                $app_my_config_data.= "\$config['developed_by_href'] = '".$this->config->item('developed_by_href')."';\n";
                $app_my_config_data.= "\$config['developed_by_title'] = '".$this->config->item('developed_by_title')."';\n";
                $app_my_config_data.= "\$config['developed_by_prefix'] = '".$this->config->item('developed_by_prefix')."' ;\n";
                $app_my_config_data.= "\$config['support_email'] = '".$this->config->item('support_email')."' ;\n";
                $app_my_config_data.= "\$config['support_mobile'] = '".$this->config->item('support_mobile')."' ;\n";
                $app_my_config_data.= "\$config['time_zone'] = '' ;\n";
                $app_my_config_data.= "\$config['sess_use_database'] = TRUE;\n";
                $app_my_config_data.= "\$config['sess_table_name'] = 'ci_sessions';\n";
                file_put_contents(APPPATH.'config/my_config.php', $app_my_config_data, LOCK_EX);
                  //writting  application/config/my_config

                  //writting application/config/database
                  $database_data = "";
                $database_data.= "<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');\n
                    \$active_group = 'default';
                    \$active_record = true;
                    \$db['default']['hostname'] = '$host_name';
                    \$db['default']['username'] = '$database_username';
                    \$db['default']['password'] = '$database_password';
                    \$db['default']['database'] = '$database_name';
                    \$db['default']['dbdriver'] = 'mysql';
                    \$db['default']['dbprefix'] = '';
                    \$db['default']['pconnect'] = TRUE;
                    \$db['default']['db_debug'] = TRUE;
                    \$db['default']['cache_on'] = FALSE;
                    \$db['default']['cachedir'] = '';
                    \$db['default']['char_set'] = 'utf8';
                    \$db['default']['dbcollat'] = 'utf8_general_ci';
                    \$db['default']['swap_pre'] = '';
                    \$db['default']['autoinit'] = TRUE;
                    \$db['default']['stricton'] = FALSE;";
                file_put_contents(APPPATH.'config/database.php', $database_data, LOCK_EX);
                  //writting application/config/database

                  // loding database library, because we need to run queries below and configs are already written

                $this->load->database();
                $this->load->model('basic');
                  // loding database library, because we need to run queries below and configs are already written

                  // dumping sql
                $dump_file_name = 'initial_db.sql';
                $dump_sql_path = 'assets/backup_db/'.$dump_file_name;
                $this->basic->import_dump($dump_sql_path);
                  // dumping sql

                  //generating hash password for admin and updaing database
                $app_password = md5($app_password);
                $this->basic->update_data($table = "users", $where = array("user_type" => "Admin"), $update_data = array("mobile" => $institute_mobile, "email" => $app_username, "password" => $app_password, "name" => $institute_name, "status" => "1", "deleted" => "0", "address" => $institute_address));
                  //generating hash password for admin and updaing database

                  //deleting the install.txt file,because installation is complete
                  if (file_exists(APPPATH.'install.txt')) {
                      unlink(APPPATH.'install.txt');
                  }
                  //deleting the install.txt file,because installation is complete
                  redirect('home/login');
            }
        }
    }


    /**
    * method to index page
    * @access public
    * @return void
    */
    public function index()
    {
        $this->login_page();
    }

    
    /**
    * method to set time zone
    * @access public
    * @return void
    */
    public function _time_zone_set()
    {
       $time_zone = $this->config->item('time_zone');
        if ($time_zone== '') {
            $time_zone="Europe/Dublin";
        }
        date_default_timezone_set($time_zone);
    }


    /**
    * method to show time zone list
    * @access public
    * @return array
    */    
    public function _time_zone_list()
    {
        $all_time_zone=array(
            'Kwajalein'                    => 'GMT -12.00 Kwajalein',
            'Pacific/Midway'                => 'GMT -11.00 Pacific/Midway',
            'Pacific/Honolulu'                => 'GMT -10.00 Pacific/Honolulu',
            'America/Anchorage'            => 'GMT -9.00  America/Anchorage',
            'America/Los_Angeles'            => 'GMT -8.00  America/Los_Angeles',
            'America/Denver'                => 'GMT -7.00  America/Denver',
            'America/Tegucigalpa'            => 'GMT -6.00  America/Tegucigalpa',
            'America/New_York'                => 'GMT -5.00  America/New_York',
            'America/Caracas'                => 'GMT -4.30  America/Caracas',
            'America/Halifax'                => 'GMT -4.00  America/Halifax',
            'America/St_Johns'                => 'GMT -3.30  America/St_Johns',
            'America/Argentina/Buenos_Aires'=> 'GMT +-3.00 America/Argentina/Buenos_Aires',
            'America/Sao_Paulo'            =>' GMT -3.00  America/Sao_Paulo',
            'Atlantic/South_Georgia'        => 'GMT +-2.00 Atlantic/South_Georgia',
            'Atlantic/Azores'                => 'GMT -1.00  Atlantic/Azores',
            'Europe/Dublin'                => 'GMT 	   Europe/Dublin',
            'Europe/Belgrade'                => 'GMT +1.00  Europe/Belgrade',
            'Europe/Minsk'                    => 'GMT +2.00  Europe/Minsk',
            'Asia/Kuwait'                    => 'GMT +3.00  Asia/Kuwait',
            'Asia/Tehran'                    => 'GMT +3.30  Asia/Tehran',
            'Asia/Muscat'                    => 'GMT +4.00  Asia/Muscat',
            'Asia/Yekaterinburg'            => 'GMT +5.00  Asia/Yekaterinburg',
            'Asia/Kolkata'                    => 'GMT +5.30  Asia/Kolkata',
            'Asia/Katmandu'                => 'GMT +5.45  Asia/Katmandu',
            'Asia/Dhaka'                    => 'GMT +6.00  Asia/Dhaka',
            'Asia/Rangoon'                    => 'GMT +6.30  Asia/Rangoon',
            'Asia/Krasnoyarsk'                => 'GMT +7.00  Asia/Krasnoyarsk',
            'Asia/Brunei'                    => 'GMT +8.00  Asia/Brunei',
            'Asia/Seoul'                    => 'GMT +9.00  Asia/Seoul',
            'Australia/Darwin'                => 'GMT +9.30  Australia/Darwin',
            'Australia/Canberra'            => 'GMT +10.00 Australia/Canberra',
            'Asia/Magadan'                    => 'GMT +11.00 Asia/Magadan',
            'Pacific/Fiji'                    => 'GMT +12.00 Pacific/Fiji',
            'Pacific/Tongatapu'            => 'GMT +13.00 Pacific/Tongatapu'
        );

        return $all_time_zone;
    }

    /**
    * method to disable cache
    * @access public
    * @return void
    */
    public function _disable_cache()
    {
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    /**
    * method to
    * @access public
    * @return void
    */     
    public function access_forbidden()
    {
        $this->load->view('page/access_forbidden');
    }

    /**
    * method to load front viewcontroller
    * @access public
    * @return void
    */
    public function _front_viewcontroller($data=array())
    {
        // $this->_disable_cache();
        if (!isset($data['body'])) {
            $data['body']=$this->config->item('default_page_url');
        }
    
        if (!isset($data['page_title'])) {
            $data['page_title']="";
        }

        $this->load->view('front/theme_front', $data);
    }

    
    public function _viewcontroller($data=array())
    {
        if (!isset($data['body'])) {
            $data['body']=$this->config->item('default_page_url');
        }
    
        if (!isset($data['page_title'])) {
            $data['page_title']="Admin Panel";
        }

        if (!isset($data['crud'])) {
            $data['crud']=0;
        }
        // fetch all pending student queries to show in admin notification area
        //$data['student_query_notifications']=$this->_admin_notifications();
        $this->load->view('admin/theme/theme', $data);
    }


    public function _member_viewcontroller($data=array())
    {
        if (!isset($data['body'])) {
            $data['body']=$this->config->item('default_page_url');
        }
    
        if (!isset($data['page_title'])) {
            $data['page_title']="Member Panel";
        }

        if (!isset($data['crud'])) {
            $data['crud']=0;
        }
            
        $this->load->view('member/theme_member/theme', $data);
    }


    public function _site_viewcontroller($data=array())
    {
        if (!isset($data['body'])) {
            $data['body']="site/homepage";
        }
        if (!isset($data['page_title'])) {
            $data['page_title']="";
        }
        $this->load->view('site/theme_front', $data);
    }


    public function contact()
    {
        $data['body']='site/contact';
        $data['page_title']="Contact Us";
        $this->_site_viewcontroller($data);
    }

    public function email_contact()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            redirect('home/access_forbidden', 'location');
        }
        
        if ($_POST) {
            $this->form_validation->set_rules('email',                    '<b>Email</b>',            'trim|required|valid_email');
            $this->form_validation->set_rules('subject',                '<b>Subject</b>',            'trim|required');
            $this->form_validation->set_rules('message',                '<b>Message</b>',            'trim|required');

                                    
            if ($this->form_validation->run() == false) {
                return $this->contact();
            } else {
                $email=$this->input->post('email', true);
                $subject=$this->input->post('subject', true);
                $message=$this->input->post('message', true);
                $from = $email;
                $this->_mail_sender($from, $to=$this->config->item("institute_email"), $subject, $message, $mask=$from, $html=0, $smtp=1);
                $this->session->set_flashdata('mail_sent', 1);
                redirect('home/contact', 'location');
            }
        }
    }

    /**
    * method to load login page
    * @access public
    * @return void
    */
    public function login_page()
    {
        if (file_exists(APPPATH.'install.txt')) {
            redirect('home/installation', 'location');
        }

        if ($this->session->userdata('logged_in') == 1 && $this->session->userdata('user_type') == 'Admin') {
            redirect('admin/index', 'location');
        }
        if ($this->session->userdata('logged_in') == 1 && $this->session->userdata('user_type') == 'Member') {
            redirect('admin/member_panel', 'location');
        }
                
        $this->load->view('page/login');
    }
    
    public function login() //loads home view page after login (this )
    {
        if (file_exists(APPPATH.'install.txt')) {
            redirect('home/installation', 'location');
        }

        $this->form_validation->set_rules('username', '<b>Email</b>', 'trim|required|xss_clean|valid_email');
        $this->form_validation->set_rules('password', '<b>Password</b>', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $this->load->view('page/login');
        } else {
            $username = $this->input->post('username', true);
            $password = md5($this->input->post('password', true));

            $table = 'users';
            $where['where'] = array('email' => $username, 'password' => $password, "deleted" => "0", "status" => "1");

            $info = $this->basic->get_data($table, $where, $select = '', $join = '', $limit = '', $start = '', $order_by = '', $group_by = '', $num_rows = 1);

            $count = $info['extra_index']['num_rows'];
            
            if ($count == 0) {
                $this->session->set_flashdata('login_msg', 'Invalid email or password');
                redirect(uri_string());
            } else {
                $username = $info[0]['name'];
                $user_type = $info[0]['user_type'];
                $user_id = $info[0]['id'];

                $this->session->set_userdata('logged_in', 1);
                $this->session->set_userdata('username', $username);
                $this->session->set_userdata('user_type', $user_type);
                $this->session->set_userdata('user_id', $user_id);
                $this->session->set_userdata('download_id', time());

                if ($this->session->userdata('logged_in') == 1 && $this->session->userdata('user_type') == 'Admin') {
                    redirect('admin/index', 'location');
                }
                if ($this->session->userdata('logged_in') == 1 && $this->session->userdata('user_type') == 'Member') {
                    redirect('admin/member_panel', 'location');
                }
            }
        }
    }


    public function sign_up()
    {
        // $this->load->view('page/sign_up');
        $data['body'] = 'page/sign_up';
        $data['page_title']="Sign Up";
        $this->_front_viewcontroller($data);
    }

    public function sign_up_action()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            redirect('home/access_forbidden', 'location');
        }
        if($_POST) {
            $this->form_validation->set_rules('name', '<b>Name</b>', 'trim|required|xss_clean');
            $this->form_validation->set_rules('address', '<b>Address</b>', 'trim|required|xss_clean');
            $this->form_validation->set_rules('email', '<b>Email</b>', 'trim|required|xss_clean|valid_email|is_unique[users.email]');
            $this->form_validation->set_rules('mobile', '<b>Mobile</b>', 'trim|required|xss_clean');
            $this->form_validation->set_rules('password', '<b>Password</b>', 'trim|required|xss_clean');
            $this->form_validation->set_rules('confirm_password', '<b>Confirm password</b>', 'trim|required|xss_clean|matches[password]');

            if($this->form_validation->run() == FALSE){
                $this->sign_up();
            } else {
                $name = $this->input->post('name', TRUE);
                $address = $this->input->post('address', TRUE);
                $email = $this->input->post('email', TRUE);
                $mobile = $this->input->post('mobile', TRUE);
                $password = $this->input->post('password', TRUE);

                // $this->db->trans_start();

                $code = $this->_random_number_generator();
                $data = array(
                    'name' => $name,
                    'email' => $email,
                    'mobile' => $mobile,
                    'address' => $address,
                    'password' => md5($password),
                    'user_type' => 'Member',
                    'status' => '0',
                    'activation_code' => $code
                    );

                if ($this->basic->insert_data('users', $data)) {
                    //email to user
                    $url = site_url()."home/account_activation";
                    $message = "<p>To active your account please perform the following Steps:</p>
                                <ol>
                                    <li>Go to this url:".$url."</li>
                                    <li>Enter this code:".$code."</li>
                                    <li>Activate Account</li>
                                <ol>";


                    $from = $this->config->item('institute_email');
                    $to = $email;
                    $subject = $this->config->item('product_name')." | Account Activation";
                    $mask = $subject;
                    $html = 1;
                    $this->_mail_sender($from, $to, $subject, $message, $mask, $html);

                    $this->session->set_userdata('reg_success',1);
                    return $this->sign_up();

                }

            }

        }
    }

    public function account_activation()
    {
        $data['body']='page/account_activation';
        $data['page_title']="Account Activation";
        $this->_front_viewcontroller($data);
    }

    public function account_activation_action()
    {
        if ($_POST) {
            $code=trim($this->input->post('code', true));
            $email=$this->input->post('email', true);

            $table='users';
            $where['where']=array('activation_code'=>$code,'email'=>$email);
            $select=array('id');

            $result=$this->basic->get_data($table, $where, $select);

            if (empty($result)) {
                echo 0;
            } else {
                foreach ($result as $row) {
                    $user_id=$row['id'];
                }

                $this->basic->update_data('users', array('id'=>$user_id), array('status'=>'1'));
                echo 2;
                
            }
        }
    }


    /**
    * method to load logout page
    * @access public
    * @return void
    */
    public function logout()
    {
        $this->session->sess_destroy();
        redirect('home/login_page', 'location');
    }

    /**
    * method to generate random number
    * @access public
    * @param int
    * @return int
    */
    public function _random_number_generator($length=6)
    {
        $rand = substr(uniqid(mt_rand(), true), 0, $length);
        return $rand;
    }

    /**
    * method to generate religion
    * @access public
    * @return array
    */
    public function religion_generator()
    {
        $religion=array(
            'Islam'=>'Islam',
            'Hinduism'=>'Hinduism',
            'Christanity'=>'Christanity',
            'Buddhist'=>'Buddhist',
            'Others'=>'Others'
        );
        
        return $religion;
    }

    /**
    * method to load forgor password view page
    * @access public
    * @return void
    */
    public function forgot_password()
    {
        $data['body']='page/forgot_password';
        $data['page_title']="Forget Password";
        $this->_front_viewcontroller($data);
    }

    /**
    * method to generate code
    * @access public
    * @return void
    */
    public function code_genaration()
    {
        $email = trim($this->input->post('email'));
        $result = $this->basic->get_data('users', array('where' => array('email' => $email)), array('count(*) as num'));

        if ($result[0]['num'] == 1) {
            //entry to forget_password table
            $expiration = date("Y-m-d H:i:s", strtotime('+1 day', time()));
            $code = $this->_random_number_generator();
            $url = site_url().'home/password_recovery';

            echo $code,$url;

            $table = 'forget_password';
            $info = array(
                'confirmation_code' => $code,
                'email' => $email,
                'expiration' => $expiration
                );

            if ($this->basic->insert_data($table, $info)) {
                //email to user
                $message = "<p>To reset your password please perform the following Steps:</p>
                            <ol>
                                <li>Go to this url:".$url."</li>
                                <li>Enter this code:".$code."</li>
                                <li>Reset password</li>
                            <ol>
                            <h4>The code and the url will expire after 24 hours.</h4>";


                $from = $this->config->item('institute_email');
                $to = $email;
                $subject = $this->config->item('product_name')." | Reset Password";
                $mask = $subject;
                $html = 1;
                $this->_mail_sender($from, $to, $subject, $message, $mask, $html);
            }
        } else {
            echo 0;
        }
    }

    /**
    * method to password recovery
    * @access public
    * @return void
    */
    public function password_recovery()
    {
        $data['body']='page/password_recovery';
        $data['page_title']="Forget Recovery";
        $this->_front_viewcontroller($data);
    }

    /**
    * method to check recovery
    * @access public
    * @return void
    */
    public function recovery_check()
    {
        if ($_POST) {
            $code=trim($this->input->post('code', true));
            $newp=md5($this->input->post('newp', true));
            $conf=md5($this->input->post('conf', true));

            $table='forget_password';
            $where['where']=array('confirmation_code'=>$code,'success'=>0);
            $select=array('email','expiration');

            $result=$this->basic->get_data($table, $where, $select);

            if (empty($result)) {
                echo 0;
            } else {
                foreach ($result as $row) {
                    $email=$row['email'];
                    $expiration=$row['expiration'];
                }

                $now=time();
                $exp=strtotime($expiration);

                if ($now>$exp) {
                    echo 1;
                } else {
                    $student_info_where['where'] = array('email'=>$email);
                    $student_info_select = array('id');
                    $student_info_id = $this->basic->get_data('users', $student_info_where, $student_info_select);
                    $this->basic->update_data('users', array('id'=>$student_info_id[0]['id']), array('password'=>$newp));
                    $this->basic->update_data('forget_password', array('confirmation_code'=>$code), array('success'=>1));
                    echo 2;
                }
            }
        }
    }


    /**
    * method to config mail sender
    * @access public
    * @return boolean
    */
    function _mail_sender($from = '', $to = '', $subject = '', $message = '', $mask = "", $html = 0, $smtp = 1)
    {
        if ($from!= '' && $to!= '' && $subject!='' && $message!= '') {
            if (!is_array($to)) {
                $to=array($to);
            }

            if ($smtp == '1') {
                $where2 = array("where" => array('status' => '1'));
                $email_config_details = $this->basic->get_data("email_config", $where2, $select = '', $join = '', $limit = '', $start = '',
                                                        $group_by = '', $num_rows = 0);

                if (count($email_config_details) == 0) {
                    $this->load->library('email');
                } else {
                    foreach ($email_config_details as $send_info) {
                        $send_email = trim($send_info['email_address']);
                        $smtp_host = trim($send_info['smtp_host']);
                        $smtp_port = trim($send_info['smtp_port']);
                        $smtp_user = trim($send_info['smtp_user']);
                        $smtp_password = trim($send_info['smtp_password']);
                    }

            /*****Email Sending Code ******/
                $config = array(
                  'protocol' => 'smtp',
                  'smtp_host' => "{$smtp_host}",
                  'smtp_port' => "{$smtp_port}",
                  'smtp_user' => "{$smtp_user}", // change it to yours
                  'smtp_pass' => "{$smtp_password}", // change it to yours
                  'mailtype' => 'html',
                  'charset' => 'utf-8',
                  'newline' =>  '\r\n',
                  'smtp_timeout' => '30'
                 );

                    $this->load->library('email', $config);
                }
            } /*** End of If Smtp== 1 **/

            if (isset($send_email) && $send_email!= "") {
                $from = $send_email;
            }
            $this->email->from($from, $mask);
            $this->email->to($to);
            $this->email->subject($subject);
            $this->email->message($message);
            if ($html == 1) {
                $this->email->set_mailtype('html');
            }

            if ($this->email->send()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    /**
    * load constructor
    * @access public
    * @return boolean
    */
    public function _sms_sender($message='', $mobile='')
    {
        if ($message!='' && $mobile!='') {
            $api_user = 'xeroneit987';
            $api_pwd = '01722977459';

            $this->sms_manager->set_credentioal($api_user, $api_pwd);
            if (strlen($mobile==11)) {
                $mobile='88'.$mobile;
            }

            if ($this->sms_manager->send_sms($message, $mobile)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
    * method to get email provider
    * @access public
    * @return array
    */
    public function get_email_providers()
    {
        $table='email_provider';
        $results=$this->basic->get_data($table);
        $email_provider=array();
        foreach ($results as $row) {
            $email_provider[$row['id']]=$row['provider_name'];
        }
        return $email_provider;
    }

    /**
    * method to get social networks
    * @access public
    * @return array
    */
    public function get_social_networks()
    {
        $table='social_network';
        $results=$this->basic->get_data($table);
        $social_network=array();
        foreach ($results as $row) {
            $social_network[$row['social_network_name']]=$row['social_network_name'];
        }
        return $social_network;
    }

    /**
    * method to get search engines
    * @access public
    * @return array
    */
    public function get_searche_engines()
    {
        $table='searh_engine';
        $results=$this->basic->get_data($table);
        $searh_engine=array();
        foreach ($results as $row) {
            $searh_engine[$row['search_engine_name']]=$row['search_engine_name'];
        }
        return $searh_engine;
    }

    /**
    * method to get districts
    * @access public
    * @return array
    */
    public function get_districts()
    {
        $table='district';
        $where['where'] = array('status' => 1);
        $results=$this->basic->get_data($table, $where);
        $districts=array();
        foreach ($results as $row) {
            $districts[$row['id']]=$row['district_name'];
        }
        return $districts;
    }

    /**
    * method to generate blood group
    * @access public
    * @return array
    */
    public function blood_group_generator()
    {
        $blood_groups=array(
        ''=>'Blood Group',
        'A+'=>'A+',
        'A-'=>'A-',
        'B+'=>'B+',
        'B-'=>'B-',
        'O+'=>'O+',
        'O-'=>'O-',
        'AB+'=>'AB+',
        'AB-'=>'AB-'
        );
        return $blood_groups;
    }


    /**
    * method to select district
    * @access public
    * @param int
    * @param string    
    * @return void
    */
    public function thana_select_as_district($district_id, $name_and_id="thana_id")
    {
        $table='thana';
        $where_simple=array('district_id'=>$district_id);
        $where=array('where'=>$where_simple);
        $results=$this->basic->get_data($table, $where, $select='', $join='', $limit='', $start='', $order_by='thana_name asc');

        $str='';
        $str.='<select id="'.$name_and_id.'" class="form-control" name="'.$name_and_id.'">';
        $str.='<option value="">Select Thana</option>';
        for ($i=0;$i<count($results);$i++) {
            $str.='<option value="'.$results[$i]['id'].'">'.$results[$i]['thana_name'].'</option>';
        }
        $str.='</select>';
        echo $str;
    }

    /**
    * method to download page loader
    * @access public
    * @return void
    */
    public function download_page_loader()
    {
        $this->load->view('page/download');
    }
}

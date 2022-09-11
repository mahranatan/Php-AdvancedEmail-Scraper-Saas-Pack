<?php require_once("home.php"); // including home controller

class Website extends Home
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
    }

    public function index()
    {
        $config_data=array();
        $data=array();
        $price=0;
        $config_data=$this->basic->get_data("payment_config","","monthly_fee");
        if(array_key_exists(0,$config_data)) $price=$config_data[0]['monthly_fee'];
        $data['price']=$price;
        $this->load->view('aes_website/index',$data);
    }

}

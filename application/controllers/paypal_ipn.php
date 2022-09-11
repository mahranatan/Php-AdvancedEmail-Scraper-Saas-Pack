<?php

class Paypal_ipn extends CI_Controller
{

    public function __construct()
    {
		 parent::__construct();
		$this->load->library('paypal_class');
		$this->load->model('basic');
        set_time_limit(0);
    }
	
		public function ipn_notify(){
	
		$payment_info=$this->paypal_class->run_ipn();
		
		$verify_status=$payment_info['verify_status'];
		$first_name=$payment_info['data']['first_name'];
		$last_name=$payment_info['data']['last_name'];
		$buyer_email=$payment_info['data']['payer_email'];
		$receiver_email=$payment_info['data']['receiver_email'];
		$country=$payment_info['data']['address_country'];
		$payment_date=$payment_info['data']['payment_date'];
		$transaction_id=$payment_info['data']['txn_id'];
		$payment_type=$payment_info['data']['payment_type'];
		$payment_amount=$payment_info['data']['mc_gross'];
		$user_id=$payment_info['data']['custom'];
		
		$simple_where['where'] = array('user_id'=>$user_id);
        $select = array('cycle_start_date','cycle_expired_date');
		
        $prev_payment_info = $this->basic->get_data('transaction_history',$simple_where,$select,$join='',$limit='1',$start=0,$order_by='ID DESC',$group_by='');
		
		$prev_cycle_expired_date="";
		
		foreach($prev_payment_info as $info){
			$prev_cycle_expired_date=$info['cycle_expired_date'];
		}
		
		if($prev_cycle_expired_date==""){
			 $cycle_start_date=date('Y-m-d');
			 $cycle_expired_date=date("Y-m-d",strtotime('+30 day',strtotime($cycle_start_date)));
		}
		
		else if (strtotime($prev_cycle_expired_date) < strtotime(date('Y-m-d'))){
			$cycle_start_date=date('Y-m-d');
			$cycle_expired_date=date("Y-m-d",strtotime('+30 day',strtotime($cycle_start_date)));
		}
		
		else if (strtotime($prev_cycle_expired_date) > strtotime(date('Y-m-d'))){
			$cycle_start_date=date("Y-m-d",strtotime('+1 day',strtotime($prev_cycle_expired_date)));
			$cycle_expired_date=date("Y-m-d",strtotime('+30 day',strtotime($cycle_start_date)));
		}
		
		
		/** insert the transaction into database ***/
		
	   $config_data=array();
       $data=array();
       $price=0;
       $config_data=$this->basic->get_data("payment_config","","monthly_fee");
       if(array_key_exists(0,$config_data))
	   	 $price=$config_data[0]['monthly_fee'];
       
	   
		if($verify_status!="VERIFIED" || $payment_amount<$price){
			exit();
		}
		
		 $insert_data=array(
                "verify_status" 	=>$verify_status,
                "first_name"		=>$first_name,
				"last_name"			=>$last_name,
				"paypal_email"		=>$buyer_email,
				"receiver_email" 	=>$receiver_email,
				"country"			=>$country,
				"payment_date" 		=>$payment_date,
				"payment_type"		=>$payment_type,
				"transaction_id"	=>$transaction_id,
				"user_id"			=>$user_id,
				"cycle_start_date"	=>$cycle_start_date,
				"cycle_expired_date" =>$cycle_expired_date,
				"paid_amount"	=>$payment_amount
            );
			
			
        $this->basic->insert_data('transaction_history', $insert_data);
		
		/** Update user table **/
		$table='users';
		$where=array('id'=>$user_id);
		$data=array('expired_date'=>$cycle_expired_date);
		$this->basic->update_data($table,$where,$data);
	}
	
	

}


<?php require_once("home.php"); // including home controller

class scrape extends Home
{

    public $user_id;
    public $download_id;
    
    /**
    * load constructor
    * @access public
    * @return void
    */
    public function __construct()
    {
        parent::__construct();
        if ($this->session->userdata('logged_in') != 1) {
            redirect('home/login_page', 'location');
        }
        $this->user_id=$this->session->userdata('user_id');
        $this->download_id=$this->session->userdata('download_id');
        set_time_limit(0);
    }
    
    /**
    * method to scrap website
    * @access public
    * @param string
    * @param string
    * @return void
    */
    public function scrape_website($website='', $proxy='')
    {
        set_time_limit(0);
        
        $website=rawurldecode($website);
        $proxy=rawurldecode($proxy);
		$website=str_replace("____","/",$website);
		
        $config['url']=$website;
		
        $this->load->library('scraper', $config);
        
        /*** Insert domain name into database ***/
        
        $db_insert_website=$this->security->xss_clean($website);
        $insert_data=array(
                "user_id" =>$this->user_id,
                "domain_name"=>$db_insert_website,
                "last_scraped_time"=>date("Y-m-d H:i:s")
            );
        $this->basic->insert_data('domain', $insert_data);
        
        $this->scraper->domain_id=$this->db->insert_id();
		
		/**** Get decode email string from fuzzy_string_replace *****/
		$where_simple=array("user_id"=>$this->user_id,"deleted"=>"0");
        $where  = array('where'=>$where_simple);
        $table = "fuzzy_string_replace";
        $string_replace_info = $this->basic->get_data($table, $where);
		
		
        $this->scraper->start_scrapping($proxy,$string_replace_info);
    }
    
    
    /**
    * method to scrape url
    * @access public
    * @return void
    */
    public function scrape_url()
    {
        $this->load->library('scraper');
        $urls=$this->input->post('urls', true);
        
        $urls=str_replace("\n", ",", $urls);
        $url_array=explode(",", $urls);
		
        
		/**** Get decode email string from fuzzy_string_replace *****/
		$where_simple=array("user_id"=>$this->user_id,"deleted"=>"0");
        $where  = array('where'=>$where_simple);
        $table = "fuzzy_string_replace";
        $string_replace_info = $this->basic->get_data($table, $where);
		
		
        $email_writer=fopen("download/url/email_{$this->user_id}_{$this->download_id}.csv", "w");
        $total_email=0;
        
        foreach ($url_array as $url) {
            $found_email=$this->scraper->get_email_from_url($url,$proxy='',$string_replace_info);
            
            
            /**insert url into table url***/
            $time=date("Y-m-d H:i:s");
            $insert_data=array(
                                'user_id'                =>$this->user_id,
                                'url_name'                =>$url,
                                'last_scraped_time'        => $time
                                );
            
            $this->basic->insert_data('url', $insert_data);
            $url_id=$this->db->insert_id();
            
            /***Write Email***/
            foreach ($found_email as $f_email) {
                if ($f_email) {
                    $write_email=array();
                    $write_email[]=$f_email;
                    fputcsv($email_writer, $write_email);
                    
                    $db_insert_f_email=$this->db->escape($f_email);
                    /*** insert email into database table_name email ***/
                    $q="Insert into email(user_id,url_id,found_email)
										 values('$this->user_id','$url_id',$db_insert_f_email)";
                    $this->db->query($q);
                    $total_email++;
                }
            }
        }
        
        echo "<h4>Total Found Email: {$total_email} </h4>&nbsp&nbsp";
        $email_download_link= base_url()."download/url/email_{$this->user_id}_{$this->download_id}.csv";
        if ($total_email != 0) {
            echo '<a href="'.$email_download_link.'" target="_blank" class="btn btn-warning"><i class="fa fa-cloud-download"></i> <b>Download EMail</b></a>';
        }
    }
    
    /**
    * method to scrape search engine
    * @access public
    * @param string
    * @param string
    * @param string
    * @param string
    * @param string
    * @return void
    */
    public function scrape_searchengine($keyword='', $search_engine='', $social_network='', $proxy='', $email_provider='')
    {
        $this->load->library('scraper');
            
        $keyword=rawurldecode($keyword);
        $search_engine=rawurldecode($search_engine);
        $social_network=rawurldecode($social_network);
        $proxy=rawurldecode($proxy);
		
		
		$social_network=str_replace("____","/",$social_network);
		$email_provider=str_replace("____","/",$email_provider);
		
        if ($proxy=='no') {
            $proxy="";
        }
              
        if(is_numeric($email_provider))
        {
            $where['where'] = array('id'=>$email_provider);
            $info = $this->basic->get_data('email_provider', $where, $select=array('provider_name'));
            $email_providers = array();
            foreach ($info as $value) {
                $email_providers[] = $value['provider_name'];
            }
        }
        else $email_providers[0]=$email_provider;
            
        if (!empty($proxy) || isset($proxy)) {
            $proxy_array=str_replace("\n", ",", $proxy);
            $proxy_array=explode(",", $proxy_array);
            $proxy_array=array_filter($proxy_array);
            $proxy_array=array_values($proxy_array);
        }
            
            
        $keyword=$this->scraper->make_keyword($keyword, $social_network, $email_providers);
            
        $delay=array('5','10','20','30');
            
            /****insert into search_engine_search table **/
            
            $insert_data=array(
                "user_id" =>$this->user_id,
                "search_keyword"=>$keyword,
                "search_in"    =>$social_network,
                "search_engine_name"=>$search_engine,
                "last_scraped_time"=>date("Y-m-d H:i:s")
            );
            
        $this->basic->insert_data('search_engine_search', $insert_data);
        $search_keyword_id=$this->db->insert_id();
            
        $email_writer=fopen("download/search_engine/email_{$this->user_id}_{$this->download_id}.csv", "w");
        $total_email=0;
            
            
        
        for ($i=0;$i<10;$i++) {
            if ($proxy!='') {
                $using_proxy=random_value_from_array($proxy_array, $default=null);
            } else {
                $using_proxy="";
            }
                
                
            if ($search_engine=='Google') {
                $emails=$this->scraper->googleSearch($keyword, $page_number=$i, $using_proxy);
            } elseif ($search_engine=='Bing') {
                $emails=$this->scraper->bingSearch($keyword, $page_number=$i, $using_proxy);
            }
                
            $emails_display=implode(", ", $emails);
                 
            echo "<script type=\"text/javascript\">parent.document.getElementById( 'email_list').innerHTML += '<li>$emails_display</li>';</script>";
                 
            foreach ($emails as $f_email) {
                if ($f_email) {
                    $write_email=array();
                    $write_email[]=$f_email;
                    fputcsv($email_writer, $write_email);
                        
                    $db_insert_f_email=$this->db->escape($f_email);
                        /*** insert email into database table_name email ***/
                        $q="Insert into email(user_id,search_engine_url_id,found_email)
											 values('$this->user_id','$search_keyword_id',$db_insert_f_email)";
                    $this->db->query($q);
                    $total_email++;
                }
            }
            flush() ;
			
                 
                 
            $sleep_time=random_value_from_array($delay, $default=null);
            sleep($sleep_time);
        }
            
            
        echo "<script>";
        $email_download_link= base_url()."download/search_engine/email_{$this->user_id}_{$this->download_id}.csv";
        echo "parent.document.getElementById('email_download_div').innerHTML='<a href=\"{$email_download_link}\" target=\"_blank\" class=\"btn btn-lg btn-warning\"><i class=\"fa fa-cloud-download\"></i> <b>Download Email</b></a>';";
        echo "parent.document.getElementById('success_msg').innerHTML='<h3 class=\"text-center\" style=\"color:olive;\">Scraping Completed</h3>'";
        echo "</script>";
    }
    
    
    
    /**
    * method to whois search
    * @access public
    * @return void
    */
    public function whois_search()
    {
        $this->load->library('scraper');
        $urls=$this->input->post('urls', true);
        
        $urls=str_replace("\n", ",", $urls);
        $url_array=explode(",", $urls);
        
        $domain_writer=fopen("download/whois/domain_{$this->user_id}_{$this->download_id}.csv", "w");
        $total_email=0;
        
        /**Write header in csv file***/
            $write_domain[]="Domain";
        $write_domain[]="Is Registered";
        $write_domain[]="Tech Email";
        $write_domain[]="Admin Email";
        $write_domain[]="Name Servers";
        $write_domain[]="Created At";
        $write_domain[]="Changed At";
        $write_domain[]="Sponsor";
        $write_domain[]="Expires At";
        fputcsv($domain_writer, $write_domain);
        
        foreach ($url_array as $domain) {
		
			/***Remove all www. http:// and https:// ****/
			
			$domain=str_replace("www.","",$domain);
			$domain=str_replace("http://","",$domain);
			$domain=str_replace("https://","",$domain);
			
            $domain_info=$this->scraper->whois_email($domain);
            $write_domain=array();
            $write_domain[]=$domain;
            $write_domain[]=$domain_info['is_registered'];
            $write_domain[]=$domain_info['tech_email'];
            $write_domain[]=$domain_info['admin_email'];
            $write_domain[]=$domain_info['name_servers'];
            $write_domain[]=$domain_info['created_at'];
            $write_domain[]=$domain_info['changed_at'];
            $write_domain[]=$domain_info['sponsor'];
            $write_domain[]=$domain_info['expire_at'];
            fputcsv($domain_writer, $write_domain);
            
            /** Insert into database ***/
            
            $time=date("Y-m-d H:i:s");
            $insert_data=array(
                                'user_id'        => $this->user_id,
                                'domain_name'    => $domain,
                                'tech_email'    => $domain_info['tech_email'],
                                'admin_email'    => $domain_info['admin_email'],
                                'is_registered'    =>$domain_info['is_registered'],
                                'namve_servers' =>$domain_info['name_servers'],
                                'created_at'    =>$domain_info['created_at'],
                                'sponsor'        =>$domain_info['sponsor'],
                                'changed_at'    =>$domain_info['changed_at'],
                                'expire_at'        =>$domain_info['expire_at'],
								'scraped_time'   =>$time
                                );
            
            $this->basic->insert_data('whois_search', $insert_data);
        }
    }

    /**
    * method to show page status search
    * @access public
    * @return void
    */
    public function page_status_search()
    {
        $this->load->library('scraper');
        $urls=$this->input->post('urls', true);
        
        $urls=str_replace("\n", ",", $urls);
        $url_array=explode(",", $urls);
        
        $domain_writer=fopen("download/page_status/page_staus_{$this->user_id}_{$this->download_id}.csv", "w");
        $total_email=0;
        
        /**Write header in csv file***/
            $write_domain[]="URL";
        $write_domain[]="HTTP Code";
        $write_domain[]="Status";
        $write_domain[]="Total Time (sec)";
        $write_domain[]="Name Lookup Time (sec)";
        $write_domain[]="Connect Time (sec)";
        $write_domain[]="Download Speed Time";
        $write_domain[]="Check Status Date";
        fputcsv($domain_writer, $write_domain);
        
        $http_codes = array( 100 => 'Continue', 101 => 'Switching Protocols', 102 => 'Processing', 200 => 'OK',
         201 => 'Created', 202 => 'Accepted', 203 => 'Non-Authoritative Information', 204 => 'No Content',
         205 => 'Reset Content', 206 => 'Partial Content', 207 => 'Multi-Status', 300 => 'Multiple Choices',
         301 => 'Moved Permanently', 302 => 'Found', 303 => 'See Other', 304 => 'Not Modified', 305 => 'Use Proxy',
         306 => 'Switch Proxy', 307 => 'Temporary Redirect', 400 => 'Bad Request', 401 => 'Unauthorized',
         402 => 'Payment Required', 403 => 'Forbidden', 404 => 'Not Found', 405 => 'Method Not Allowed',
         406 => 'Not Acceptable', 407 => 'Proxy Authentication Required', 408 => 'Request Timeout', 409 => 'Conflict',410 => 'Gone', 411 => 'Length Required', 412 => 'Precondition Failed', 413 => 'Request Entity Too Large',414 => 'Request-URI Too Long', 415 => 'Unsupported Media Type', 416 => 'Requested Range Not Satisfiable',417 => 'Expectation Failed', 418 => 'I\'m a teapot', 422 => 'Unprocessable Entity', 423 => 'Locked',
         424 => 'Failed Dependency', 425 => 'Unordered Collection', 426 => 'Upgrade Required', 449 => 'Retry With',
         450 => 'Blocked by Windows Parental Controls', 500 => 'Internal Server Error', 501 => 'Not Implemented',
         502 => 'Bad Gateway', 503 => 'Service Unavailable', 504 => 'Gateway Timeout',
         505 => 'HTTP Version Not Supported', 506 => 'Variant Also Negotiates', 507 => 'Insufficient Storage',
         509 => 'Bandwidth Limit Exceeded', 510 => 'Not Extended',
         0 => 'Not Registered' );
        foreach ($url_array as $domain) {
            $time=date("Y-m-d H:i:s");
            $domain_info=$this->scraper->page_status_check($domain);
            $write_domain=array();
            $write_domain[]=$domain;
            $write_domain[]=$domain_info['http_code'];
            $write_domain[]=$http_codes[$domain_info['http_code']];
            $write_domain[]=$domain_info['total_time'];
            $write_domain[]=$domain_info['namelookup_time'];
            $write_domain[]=$domain_info['connect_time'];
            $write_domain[]=$domain_info['speed_download'];
            $write_domain[]=$time;
            fputcsv($domain_writer, $write_domain);
            
            /** Insert into database ***/
            
            
            $insert_data=array(
                                'url'        => $domain,
                                'http_code'    => $domain_info['http_code'],
                                'status'    => $http_codes[$domain_info['http_code']],
                                'total_time'    => $domain_info['total_time'],
                                'namelookup_time'    =>$domain_info['namelookup_time'],
                                'connect_time' =>$domain_info['connect_time'],
                                'speed_download'    =>$domain_info['speed_download'],
                                'check_date'        => $time
                                );
            
            $this->basic->insert_data('page_status', $insert_data);
        }
    }
    
    /**
    * method to email validator
    * @access public
    * @return void
    */
    public function email_validator()
    {
        $this->load->library('scraper');
        $emails=$this->input->post('emails', true);
        $emails=str_replace("\n", ",", $emails);
        $emails_array=explode(",", $emails);
        $total_emal=count($emails_array);
        
        $email_validator_writer=fopen("download/email_validator/email_validator_{$this->user_id}_{$this->download_id}.csv", "w");
        $total_valid_email=0;
        
        /*** Write header in the csv file ***/
        
            $write_validation[]="Email";
        $write_validation[]="Is Valid Pattern";
        $write_validation[]="Is MX Record Exist";
        fputcsv($email_validator_writer, $write_validation);
            
        $valid_email="";
            
        
        foreach ($emails_array as $email) {
            $result = $this->scraper->email_validate($email);
            $is_valid  = ($result['is_valid']) ? 'Yes':'No';
            $is_exists = ($result['is_exists']) ? "Yes" : "No";
            
            $write_validation=array();
            $write_validation[]=$email;
            $write_validation[]=$is_valid;
            $write_validation[]=$is_exists;
            fputcsv($email_validator_writer, $write_validation);
            
            /**if two validation passed then 1+1= 2**/
            if ($result['is_valid']+$result['is_exists'] == 2) {
                $valid_email.=$email."\n";
                $total_valid_email++;
            }
        }
        
        /*** Write all valid email address in text file **/
        
        $valid_email_file_writer = fopen("download/email_validator/email_validator_{$this->user_id}_{$this->download_id}.txt", "w");
        fwrite($valid_email_file_writer, $valid_email);
        fclose($valid_email_file_writer);
        
        /**Display total valid email between total email***/
        
        echo "Total {$total_valid_email} valid email found of {$total_emal}";
    }
    
    /**
    * method to make email unique
    * @access public
    * @return void
    */
    public function email_unique_maker()
    {
        $emails=$this->input->post('emails', true);
        $emails=str_replace("\n", ",", $emails);
        $emails_array=explode(",", $emails);
        
        $total_email=count($emails_array);
        
        $emails_array=array_unique($emails_array);
        
        $total_unique_email=count($emails_array);
        
        $unique_email_str=implode("\n", $emails_array);
        
        /*** Write all Unique email address in text file **/
        
        $unique_email_file_writer = fopen("download/unique_email/unique_email_{$this->user_id}_{$this->download_id}.txt", "w");
        fwrite($unique_email_file_writer, $unique_email_str);
        fclose($unique_email_file_writer);
        
        echo "Total {$total_unique_email} unique email found of {$total_email} email";
    }
}

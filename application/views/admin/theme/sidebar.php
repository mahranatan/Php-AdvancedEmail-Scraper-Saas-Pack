<aside class="main-sidebar">
  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">
    <!-- Sidebar user panel -->  

    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu">
      <li class="header">MAIN NAVIGATION</li>
      <li><a href="<?php echo site_url()."admin/dashboard"; ?>"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
      
<!-- <li><a href="'.site_url().'admin_config/configuration"><i class="fa fa-cogs"></i><span>General Settings</span></a></li>; -->
<!-- <li><a href="'.site_url().'admin/user_management"><i class="fa fa-user"></i><span>User Management</span></a></li>; -->
      
      <?php if ($this->session->userdata('logged_in') == 1 && $this->session->userdata('user_type') == 'Admin') : ?>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-gears"></i> <span><b>Settings</b></span>
            <i class="fa fa-angle-left pull-right"></i>
          </a>
      
          <ul class="treeview-menu">
            <li><a href="<?php echo site_url(); ?>admin_config/configuration"><i class="fa fa-cogs"></i><span>General Settings</span></a></li>  
            <li><a href="<?php echo site_url()."admin_config_email/index"; ?>"><i class="fa fa-gears"></i> Email Settings</a></li>    
          </ul>
        </li> <!-- end my sms -->

        <li><a href="<?php echo site_url(); ?>admin/user_management"><i class="fa fa-users"></i><span>User Management</span></a></li>
        <li><a href="<?php echo site_url(); ?>admin/notify_members"><i class="fa fa-bell-o"></i><span>Send Notification</span></a></li>
      
        <li class="treeview">
          <a href="#">
            <i class="fa fa-paypal"></i> <span><b>Payment</b></span>
            <i class="fa fa-angle-left pull-right"></i>
          </a>
		  
          <ul class="treeview-menu">
            <li> <a href="<?php echo site_url()."payment/payment_dashboard_admin"; ?>"> <i class="fa fa-dashboard"></i> Dashboard</a></li>   
            <li><a href="<?php echo site_url()."payment/payment_setting_admin"; ?>"><i class="fa fa-gears"></i> Payment Settings</a></li>    
            <li><a href="<?php echo site_url()."payment/admin_payment_history"; ?>"><i class="fa fa-history"></i> Payment History</a></li>     
          </ul>
        </li> <!-- end my sms --> 
      <?php endif; ?>
	  
	  
	   <li><a href="<?php echo site_url()."admin/decode_email_string"; ?>"><i class="fa fa-minus-square"></i> <span>Decode Email String</span></a></li>  
	   

      <?php if ($this->session->userdata('logged_in') == 1 && $this->session->userdata('user_type') == 'Member') : ?>
        <li><a href="<?php echo site_url()."payment/member_payment_history"; ?>"><i class="fa fa-paypal"></i> <span>Payment</span></a></li> 
      <?php endif; ?> 
     
      <li><a href="<?php echo site_url()."admin/website_search"; ?>"><i class="fa fa-magnet"></i> <span>Crawl Website</span></a></li>     
      <li><a href="<?php echo site_url()."admin/url_search"; ?>"><i class="fa fa-crosshairs"></i> <span>Crawl URL</span></a></li>      
      <li><a href="<?php echo site_url()."admin/searchengine_search"; ?>"><i class="fa  fa-binoculars"></i> <span>Search in Search Engine</span></a></li>      
      <li><a href="<?php echo site_url()."admin_advance/text_file_search"; ?>"><i class="fa fa-search"></i> <span>Search in Text/XML/JSON File</span></a></li>      
      <li><a href="<?php echo site_url()."admin_advance/doc_file_search"; ?>"><i class="fa fa-file-word-o"></i> <span>Search in Doc/Docx/PDF File</span></a></li>      
      <li><a href="<?php echo site_url()."admin_advance/whois_search"; ?>"><i class="fa fa-street-view"></i> <span>Whois Search</span></a></li>      
      <li><a href="<?php echo site_url()."admin_advance/email_validator"; ?>"><i class="fa fa-check"></i> <span>Email Validation Check</span></a></li>      
      <li><a href="<?php echo site_url()."admin_advance/unique_email_maker"; ?>"><i class="fa fa-minus-square"></i> <span>Duplicate Email Filter</span></a></li>      
      <li><a href="<?php echo site_url()."admin_advance/page_status_checker"; ?>"><i class="fa fa-check-square-o"></i> <span>Page Status Check</span></a></li>      
      <!-- <li><a href="<?php echo site_url()."admin/all_email"; ?>"><i class="fa fa-envelope"></i> <span>All Email</span></a></li>       -->
      
    </ul>
  </section>
  <!-- /.sidebar -->
</aside>
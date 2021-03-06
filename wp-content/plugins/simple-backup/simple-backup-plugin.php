<?php


class Simple_Backup_Plugin{

	//plugin version number
	private $version = "2.6.6";
	
	private $debug = false;


	//holds simple security settings page class
	private $settings_page;
	
	//holds ftp tools object
	private $ftp_tools;


	
	//options are: edit, upload, link-manager, pages, comments, themes, plugins, users, tools, options-general
	private $page_icon = "options-general"; 	
	
	//settings page title, to be displayed in menu and page headline
	private $plugin_title = "Simple Backup";
	
	//page name
	private $plugin_name = "simple-backup";
	
	//will be used as option name to save all options
	private $setting_name = "simple-backup-settings";
	

	
	//holds plugin options
	private $opt = array();




	//initialize the plugin class
	public function __construct() {
		
		$this->opt = get_option($this->setting_name);
		
		//check pluign settings and display alert to configure and save plugin settings
		add_action( 'admin_init', array(&$this, 'check_plugin_settings') );
		
		//initialize plugin settings
        add_action( 'admin_init', array(&$this, 'settings_page_init') );
		
		//create menu in wp admin menu
        add_action( 'admin_menu', array(&$this, 'admin_menu') );
		
		//add help menu to settings page
		add_filter( 'contextual_help', array(&$this,'admin_help'), 10, 3);	
		
		// add plugin "Settings" action on plugin list
		add_action('plugin_action_links_' . plugin_basename(SB_LOADER), array(&$this, 'add_plugin_actions'));
		
		// add links for plugin help, donations,...
		add_filter('plugin_row_meta', array(&$this, 'add_plugin_links'), 10, 2);
		
		
		
		$backup_manager = new Simple_Backup_Manager($this->opt);
		$this->backup_manager = $backup_manager;
		
		add_action( 'admin_head', array($backup_manager, 'screen_options') );
		add_action( 'admin_menu', array($backup_manager, 'simple_backup_admin_menu') );
		
		
		if(isset($this->opt['backup_settings']['enable_ftp_backup_system']) && "true" == $this->opt['backup_settings']['enable_ftp_backup_system']){
			$this->ftp_tools = new Simple_Backup_FTP_Tools();
		}
	
	}



	//setup the plugin settings page
	public function settings_page_init() {

		$this->settings_page  = new Simple_Backup_Settings_Page( $this->setting_name );
		
		$this->settings_page->extra_tabs = array(array('id'=>'backup', 'title'=>'Backup Manager', 'link' => admin_url()."tools.php?page=backup_manager"));
		
        //set the settings
        $this->settings_page->set_sections( $this->get_settings_sections() );
        $this->settings_page->set_fields( $this->get_settings_fields() );
		$this->settings_page->set_sidebar( $this->get_settings_sidebar() );

		$this->build_optional_tabs();
		
        //initialize settings
        $this->settings_page->init();
    }



	public function check_plugin_settings(){
		if( isset($_GET['page']) ){
			if ($_GET['page'] == "backup_manager" || $_GET['page'] == "simple-backup-settings" ){
				
				//check for plugin settings, make sure they are saved
				if(false === get_option($this->setting_name)){
					$link = admin_url()."options-general.php?page=simple-backup-settings&tab=backup_settings";
					$message = '<div class="error"><p>Welcome!<br>This plugin needs to be configured before you can make a backup.';
					$message .= '<br>Please Configure and Save the <a href="%1$s">Plugin Settings</a> before you continue!!</p></div>';
					echo sprintf($message, $link);
				}
				
				
				global $wp_version;
				$req_wp_version = "3.3";
				//check for proper WP version.
				if (version_compare($wp_version, $req_wp_version, '<')) {
					$message = '<div class="error"><p>Warning!<br>This plugin requires WordPress version '.$req_wp_version.' or later.';
					$message .= '<br>Please <a href="'.admin_url().'update-core.php">Update WordPress</a> before you continue!</p></div>';
					echo $message;
				}
				
				
			}
		}
	}




   /**
     * Returns all of the settings sections
     *
     * @return array settings sections
     */
    public function get_settings_sections() {
	
		$settings_sections = array(
			array(
				'id' => 'backup_settings',
				'title' => __( 'Backup Settings', $this->plugin_name )
			),
			array(
				'id' => 'wp_optimizer_settings',
				'title' => __( 'WordPress Optimization', $this->plugin_name )
			),
			array(
				'id' => 'db_optimizer_settings',
				'title' => __( 'Database Optimization', $this->plugin_name )
			)
		);
		
		
		if(isset($this->opt['backup_settings']['enable_ftp_backup_system']) && "true" == $this->opt['backup_settings']['enable_ftp_backup_system']){
			$settings_sections[] = array(
				'id' => 'ftp_server_settings',
				'title' => __( 'FTP Server Settings', $this->plugin_name )
			);
		}

								
        return $settings_sections;
    }


    /**
     * Returns all of the settings fields
     *
     * @return array settings fields
     */
    public function get_settings_fields() {
		$settings_fields = array(
			'backup_settings' => array(
				array(
                    'name' => 'enable_file_backup',
                    'label' => __( 'File Backup', $this->plugin_name ),
                    'desc' => 'Enable WordPress File Backup',
                    'type' => 'radio',
					//'default' => 'true',
                    'options' => array(
                        'true' => 'Enabled',
                        'false' => 'Disabled'
                    )
                ),
				array(
                    'name' => 'file_compression',
                    'label' => __( 'File Backup Format', $this->plugin_name ),
                    'desc' => 'Files Backup Format and Compression',
                    'type' => 'select',
					//'default' => '.tar.gz',
                    'options' => array(
                        '.tar' => '.tar',
                        '.tar.gz' => '.tar.gz',
                        '.tar.bz2' => '.tar.bz2',
						'.zip' => '.zip',
                    )
                ),
				array(
                    'name' => 'enable_db_backup',
                    'label' => __( 'Database Backup', $this->plugin_name ),
                    'desc' => 'Enable WordPress Database Backup',
                    'type' => 'radio',
					//'default' => 'true',
                    'options' => array(
                        'true' => 'Enabled',
                        'false' => 'Disabled'
                    )
                ),
				
				array(
                    'name' => 'db_compression',
                    'label' => __( 'Database Backup Format', $this->plugin_name ),
                    'desc' => 'Database Backup Format and Compression',
                    'type' => 'select',
					//'default' => '.sql',
                    'options' => array(
                        '.sql' => '.sql',
                        '.sql.zip' => '.sql.zip',
                        '.sql.gz' => '.sql.gz',
						'.sql.bz2' => '.sql.bz2',
                    )
                ),
				array(
                    'name' => 'enable_ftp_backup_system',
                    'label' => __( 'FTP Storage', $this->plugin_name ),
                    'desc' => 'Enable FTP Storage for Backup Files',
                    'type' => 'radio',
					//'default' => 'true',
                    'options' => array(
                        'true' => 'Enabled',
                        'false' => 'Disabled'
                    )
                )
			),
			'wp_optimizer_settings' => array(
				array(
                    'name' => 'delete_spam_comments',
                    'label' => __( 'Delete Spam Comments', $this->plugin_name ),
                    'desc' => 'Delete Spam Comments Before Backup (Recommended)',
                    'type' => 'radio',
					//'default' => 'true',
                    'options' => array(
                        'true' => 'Enabled',
                        'false' => 'Disabled'
                    )
                ),
				array(
                    'name' => 'delete_unapproved_comments',
                    'label' => __( 'Delete Un-Approved Comments', $this->plugin_name ),
                    'desc' => 'Delete Un-Approved Comments Before Backup (Advanced)',
                    'type' => 'radio',
					//'default' => 'true',
                    'options' => array(
                        'true' => 'Enabled',
                        'false' => 'Disabled'
                    )
                ),
				array(
                    'name' => 'delete_revisions',
                    'label' => __( 'Delete Revisions', $this->plugin_name ),
                    'desc' => 'Delete Revisions Before Backup (Advanced)',
                    'type' => 'radio',
					//'default' => 'true',
                    'options' => array(
                        'true' => 'Enabled',
                        'false' => 'Disabled'
                    )
                ),
				array(
                    'name' => 'delete_auto_drafts',
                    'label' => __( 'Delete Auto Drafts', $this->plugin_name ),
                    'desc' => 'Delete Auto Drafts Before Backup (Advanced)',
                    'type' => 'radio',
					//'default' => 'true',
                    'options' => array(
                        'true' => 'Enabled',
                        'false' => 'Disabled'
                    )
                ),
				array(
                    'name' => 'delete_transient_options',
                    'label' => __( 'Delete Transient Options', $this->plugin_name ),
                    'desc' => 'Delete Transient Options Before Backup (Advanced)',
                    'type' => 'radio',
					//'default' => 'true',
                    'options' => array(
                        'true' => 'Enabled',
                        'false' => 'Disabled'
                    )
                )
			),
			'db_optimizer_settings' => array(
				array(
                    'name' => 'optimize_database',
                    'label' => __( 'Optimize Database', $this->plugin_name ),
                    'desc' => 'Optimize WordPress MySQL Database Before Backup (Recommended)',
                    'type' => 'radio',
					//'default' => 'true',
                    'options' => array(
                        'true' => 'Enabled',
                        'false' => 'Disabled'
                    )
                ),
				array(
                    'name' => 'check_database',
                    'label' => __( 'Check Database', $this->plugin_name ),
                    'desc' => 'Check WordPress MySQL Database Before Backup (Recommended)',
                    'type' => 'radio',
					//'default' => 'true',
                    'options' => array(
                        'true' => 'Enabled',
                        'false' => 'Disabled'
                    )
                ),
				array(
                    'name' => 'repair_database',
                    'label' => __( 'Repair Database', $this->plugin_name ),
                    'desc' => 'Repair WordPress MySQL Database Before Backup (Advanced)',
                    'type' => 'radio',
					//'default' => 'true',
                    'options' => array(
                        'true' => 'Enabled',
                        'false' => 'Disabled'
                    )
                )
			),
			'ftp_server_settings' => array(
				array(
                    'name' => 'ftp_server_hostname',
                    'label' => __( 'Server Name', $this->plugin_name ),
                    'desc' => 'FTP Server Name or IP Address',
                    'type' => 'text'
                ),
				array(
                    'name' => 'ftp_server_port',
                    'label' => __( 'Server Port', $this->plugin_name ),
                    'desc' => 'FTP Server Port Number',
                    'type' => 'text',
					'default' => '21'
                ),
				array(
                    'name' => 'ftp_server_username',
                    'label' => __( 'Server Username', $this->plugin_name ),
                    'desc' => 'FTP Server Username',
                    'type' => 'text'
                ),
				array(
                    'name' => 'ftp_server_password',
                    'label' => __( 'Server Password', $this->plugin_name ),
                    'desc' => 'FTP Server Password',
                    'type' => 'text'
                ),
				array(
                    'name' => 'ftp_server_directory',
                    'label' => __( 'Server Directory', $this->plugin_name ),
                    'desc' => 'Directory to Store Backups on FTP Server',
                    'type' => 'text',
					'default' => 'simple-backup'
                )
			)
		);
		
        return $settings_fields;
    }



	

	//plugin settings page template
	public function plugin_settings_page(){
	
		echo "<style> 
		.form-table{ clear:left; } 
		.nav-tab-wrapper{ margin-bottom:0px; }
		</style>";
		
		echo $this->display_social_media(); 
		
        echo '<div class="wrap" >';
		
			echo '<div id="icon-'.$this->page_icon.'" class="icon32"><br /></div>';
			
			echo "<h2>".$this->plugin_title." Plugin Settings</h2>";
			
			//$this->show_backup_manager_link();
			$this->show_do_backup_button();
			
			$this->settings_page->show_tab_nav();
			
			echo '<div id="poststuff" class="metabox-holder has-right-sidebar">';
			
				echo '<div class="inner-sidebar">';
					echo '<div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position:relative;">';
					
						$this->settings_page->show_sidebar();
					
					echo '</div>';
				echo '</div>';
			
				echo '<div class="has-sidebar" >';			
					echo '<div id="post-body-content" class="has-sidebar-content">';
						
						$this->settings_page->show_settings_forms();
						
						//$this->show_do_backup_button();
						
						$this->show_ftp_tools();
						
					echo '</div>';
				echo '</div>';
				
			echo '</div>';
			
        echo '</div>';
		
    }
	
	
	
	private function show_ftp_tools(){
	
	
		if(isset($_GET['tab']) && ($_GET['tab'] == 'ftp_server_settings')){
		
			echo "<form method='post'  style='display:inline;'>";
				echo '<div style=" margin-left:10px; margin-top:-10px;">';
					echo "<input type='hidden' name='test-ftp-server' value='test-ftp-server'>";
					echo "<input type='submit' class='button-secondary' value='Test FTP  Settings'>";
					
				echo "</div>";
			echo "</form>";
				
		}
		
		
		
		if( isset($_GET['tab']) && ($_GET['tab'] == 'ftp_server_settings') && isset($_POST['test-ftp-server']) && ($_POST['test-ftp-server'] == "test-ftp-server") ){
		
			$this->ftp_tools->connection_test();
			
		}
		
	}
	
	
	/**
	
	function ftp_recursive_file_listing($ftp_connection, $path = ".") { 
		static $allFiles = array(); 
		$contents = ftp_nlist($ftp_connection, $path); 
	
		foreach($contents as $currentFile) { 
			// assuming its a folder if there's no dot in the name 
			if (strpos($currentFile, '.') === false) { 
				$this->ftp_recursive_file_listing($ftp_connection, $currentFile); 
			} 
			//$allFiles[$path][] = substr($currentFile, strlen($path) + 1); 
			$allFiles[$path][] = $currentFile; 
		} 
		return $allFiles; 
	} 
	**/


	private function show_do_backup_button(){
	
		//if(!isset($_GET['tab']) || ($_GET['tab'] != 'ftp_server_settings')){
		
			echo "<form method='post' action='".admin_url()."tools.php?page=backup_manager' style='display:inline;'>";
				echo '<div style=" margin-left:10px; margin-top:10px;">';
					echo "<input type='hidden' name='simple-backup' value='simple-backup'>";
					echo "<input type='submit' class='button-primary' value='Create WordPress Backup'>";
					
				echo "</div>";
			echo "</form>";
				
		//}
	}

	private function show_backup_manager_link(){
		echo "<p float='left'><a  href='".get_option('siteurl')."/wp-admin/tools.php?page=backup_manager' >View Backup Manager</a></p>";
	}



   	public function admin_menu() {
		
        $this->page_menu = add_options_page( $this->plugin_title, $this->plugin_title, 'manage_options',  $this->setting_name, array($this, 'plugin_settings_page') );
    }


	public function admin_help($contextual_help, $screen_id, $screen){
	
		global $simple_backup_file_manager_page;
		
		if ( $screen_id == $this->page_menu || $screen_id == $simple_backup_file_manager_page ) {
				
			$support_the_dev = $this->display_support_us();
			$screen->add_help_tab(array(
				'id' => 'developer-support',
				'title' => "Support the Developer",
				'content' => "<h2>Support the Developer</h2><p>".$support_the_dev."</p>"
			));
			
			
			$video_code = "<style>
		.videoWrapper {
			position: relative;
			padding-bottom: 56.25%; /* 16:9 */
			padding-top: 25px;
			height: 0;
		}
		.videoWrapper iframe {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
		}
		</style>";
		
			$video_code .= '<div class="videoWrapper"><iframe width="640" height="360" src="http://www.youtube.com/embed/W2YoEneu8H0?rel=0&vq=hd720" frameborder="0" allowfullscreen></iframe></div>';

			$screen->add_help_tab(array(
				'id' => 'tutorial-video',
				'title' => "Tutorial Video",
				'content' => "<h2>{$this->plugin_title} Tutorial Video</h2><p>$video_code</p>"
			));
			
			$screen->add_help_tab(array(
				'id' => 'plugin-support',
				'title' => "Plugin Support",
				'content' => "<h2>{$this->plugin_title} Support</h2><p>For {$this->plugin_title} Plugin Support please visit <a href='http://mywebsiteadvisor.com/support/' target='_blank'>MyWebsiteAdvisor.com</a></p>"
			));
			
			

			$screen->set_help_sidebar("<p>Please Visit us online for more Free WordPress Plugins!</p><p><a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/' target='_blank'>MyWebsiteAdvisor.com</a></p><br>");
			
		}
			
		

	}
	
	
	
	private function do_diagnostic_sidebar(){
	
		ob_start();
		
			echo "<p>Plugin Version: $this->version</p>";
				
			echo "<p>Server OS: ".PHP_OS." (" . strlen(decbin(~0)) . " bit)</p>";
			
			echo "<p>Required PHP Version: 5.2+<br>";
			echo "Current PHP Version: " . phpversion() . "</p>";

			
			if( ini_get('safe_mode') ){
				echo "<p><font color='red'>PHP Safe Mode is enabled!<br><b>Disable Safe Mode in php.ini!</b></font></p>";
			}else{
				echo "<p>PHP Safe Mode: is disabled!</p>";
			}
			
			if(strpos(ini_get('disable_functions'), 'exec')  !== false){
				echo "<p><font color='red'>Disabled Functions: ".ini_get('disable_functions')."<br><b>Please enable 'exec' function in php.ini!</b></font></p>";
			}
			
			if( strpos(ini_get('disable_functions'), 'passthru') !== false){
				echo "<p><font color='red'>Disabled Functions: ".ini_get('disable_functions')."<br><b>Please enable 'passthru' function in php.ini!</b></font></p>";
			}
			
			
			if(function_exists('exec')){
			
				echo "<p>";
				
				if(exec('type tar')){
					echo "Command 'tar' is enabled!</br>";
				}else{
					echo "Command 'tar' was not found!</br>";
				}
				
				if(exec('type gzip')){
					echo "Command 'gzip' is enabled!</br>";
				}else{
					echo "Command 'gzip' was not found!</br>";
				}
				
				if(exec('type bzip2')){
					echo "Command 'bzip2' is enabled!</br>";
				}else{
					echo "Command 'bzip2' was not found!</br>";
				}
				
				if(exec('type zip')){
					echo "Command 'zip' is enabled!</br>";
				}else{
					echo "Command 'zip' was not found!</br>";
				}
				
				if(exec('type mysqldump')){
					echo "Command 'mysqldump' is enabled!</br>";
				}else{
					echo "Command 'mysqldump' was not found!</br>";
				}
			
				echo "</p>";
				
			}
			
						
			echo "<p>Memory Use: " . number_format(memory_get_usage()/1024/1024, 1) . " / " . ini_get('memory_limit') . "</p>";
			
			echo "<p>Peak Memory Use: " . number_format(memory_get_peak_usage()/1024/1024, 1) . " / " . ini_get('memory_limit') . "</p>";
			
			if(function_exists('sys_getloadavg')){
				$lav = sys_getloadavg();
				echo "<p>Server Load Average: ".$lav[0].", ".$lav[1].", ".$lav[2]."</p>";
			}
			
			
			$bytes = disk_free_space("."); 
			echo "<p>Disk Space Available: " . size_format( $bytes, 2 ) . " (Approx.)</p>";
			
			$bytes = exec("du -s " . ABSPATH . " --exclude '".ABSPATH."simple-backup/*'"); 
			$size = explode("	", $bytes);
			echo "<p>WordPress File Size: " . size_format(($size[0] * 1024)) . " (Approx.)</p>";
				
	
			global $wpdb;
			$status_rows = $wpdb->get_results( "SHOW TABLE STATUS" );
			$size = 0;
			foreach($status_rows as $row){
				$size += $row->Data_length;  
			}
			
			echo "<p>WordPress DB Size: " . size_format($size) . " (Approx.)</p>";
	
		return ob_get_clean();
				
	}
	
	
	
	
	
	
	private function get_settings_sidebar(){
	
		$plugin_resources = "<p><a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/simple-backup/' target='_blank'>Plugin Homepage</a></p>
			<p><a href='http://mywebsiteadvisor.com/learning/video-tutorials/simple-backup-tutorial/'  target='_blank'>Plugin Tutorial</a></p>
			<p><a href='http://mywebsiteadvisor.com/contact-us/'  target='_blank'>Plugin Support</a></p>
			<p><a href='http://mywebsiteadvisor.com/contact-us/'  target='_blank'>Contact Us</a></p>
			<p><a href='http://wordpress.org/support/view/plugin-reviews/simple-security?rate=5#postform'  target='_blank'>Rate and Review This Plugin</a></p>";
	
		$more_plugins = "<p><a href='http://mywebsiteadvisor.com/tools/premium-wordpress-plugins/'  target='_blank'>Premium WordPress Plugins!</a></p>
			<p><a href='http://profiles.wordpress.org/MyWebsiteAdvisor/'  target='_blank'>Free Plugins on Wordpress.org!</a></p>
			<p><a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/'  target='_blank'>Free Plugins on MyWebsiteAdvisor.com!</a></p>";
	
		$follow_us = "<p><a href='http://facebook.com/MyWebsiteAdvisor/'  target='_blank'>Follow us on Facebook!</a></p>
			<p><a href='http://twitter.com/MWebsiteAdvisor/'  target='_blank'>Follow us on Twitter!</a></p>
			<p><a href='http://www.youtube.com/mywebsiteadvisor'  target='_blank'>Watch us on YouTube!</a></p>
			<p><a href='http://MyWebsiteAdvisor.com/'  target='_blank'>Visit our Website!</a></p>";
	
		$upgrade = "<p>
			<a href='http://mywebsiteadvisor.com/products-page/premium-wordpress-plugin/simple-backup-ultra/'  target='_blank'>Upgrade to Simple Backup Ultra!</a><br />
			<br />
			<b>Features:</b><br />
			-Automatic Backup Function<br />
			-Email Backup Notification<br />
			-Daily, Weekly or Monthly Schedule<br />
			-Much More!</br>
			</p>";
	
		$sidebar_info = array(
			array(
				'id' => 'diagnostic',
				'title' => 'Plugin Diagnostic Check',
				'content' => $this->do_diagnostic_sidebar()		
			),
			array(
				'id' => 'resources',
				'title' => 'Plugin Resources',
				'content' => $plugin_resources	
			),
			array(
				'id' => 'upgrade',
				'title' => 'Plugin Upgrades',
				'content' => $upgrade	
			),
			array(
				'id' => 'more_plugins',
				'title' => 'More Plugins',
				'content' => $more_plugins	
			),
			array(
				'id' => 'follow_us',
				'title' => 'Follow MyWebsiteAdvisor',
				'content' => $follow_us	
			)
		);
		
		return $sidebar_info;

	}






		//build optional tabs, using debug tools class worker methods as callbacks
	private function build_optional_tabs(){
		if(true === $this->debug){
			//general debug settings
			$plugin_debug = array(
				'id' => 'plugin_debug',
				'title' => __( 'Plugin Settings Debug', $this->plugin_name ),
				'callback' => array(&$this, 'show_plugin_settings')
			);
	
			//$enabled = isset($this->opt['debug_settings']['enable_display_plugin_settings']) ? $this->opt['debug_settings']['enable_display_plugin_settings'] : 'false';
			//if( $enabled === 'true' ){ 	
			$this->settings_page->add_section( $plugin_debug );
			//}
		}
		
		$plugin_tutorial = array(
			'id' => 'plugin_tutorial',
			'title' => __( 'Plugin Tutorial Video', $this->plugin_name ),
			'callback' => array(&$this, 'show_plugin_tutorual')
		);
		$this->settings_page->add_section( $plugin_tutorial );
	}
	

 

	// displays the plugin options array
	public function show_plugin_settings(){
				
		echo "<pre>";
			print_r($this->opt);
		echo "</pre>";
			
	}



	public function show_plugin_tutorual(){
	
		echo "<style>
		.videoWrapper {
			position: relative;
			padding-bottom: 56.25%; /* 16:9 */
			padding-top: 25px;
			height: 0;
		}
		.videoWrapper iframe {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
		}
		</style>";

		$video_id = "W2YoEneu8H0";
		echo sprintf( '<div class="videoWrapper"><iframe width="640" height="360" src="http://www.youtube.com/embed/%1$s?rel=0&vq=hd720" frameborder="0" allowfullscreen ></iframe></div>', $video_id);
		
	
	}



	/**
	 * Add "Settings" action on installed plugin list
	 */
	public function add_plugin_actions($links) {
		array_unshift($links, '<a href="options-general.php?page=' . $this->setting_name . '">' . __('Settings') . '</a>');
		
		return $links;
	}
	
	/**
	 * Add links on installed plugin list
	 */
	public function add_plugin_links($links, $file) {
		if($file == plugin_basename(SB_LOADER)) {
			$upgrade_url = 'http://mywebsiteadvisor.com/products-page/premium-wordpress-plugin/simple-backup-ultra/';
			$links[] = '<a href="'.$upgrade_url.'" target="_blank" title="Click Here to Upgrade this Plugin!">Upgrade Plugin</a>';
			
			$tutorial_url = 'http://mywebsiteadvisor.com/learning/video-tutorials/simple-backup-tutorial/';
			$links[] = '<a href="'.$tutorial_url.'" target="_blank" title="Click Here to View the Plugin Video Tutorial!">Tutorial Video</a>';
			
			$rate_url = 'http://wordpress.org/support/view/plugin-reviews/' . basename(dirname(__FILE__)) . '?rate=5#postform';
			$links[] = '<a href="'.$rate_url.'" target="_blank" title="Click Here to Rate and Review this Plugin on WordPress.org">Rate This Plugin</a>';
		}
		
		return $links;
	}
	
	
	public function display_support_us(){
				
		$string = '<p><b>Thank You for using the '.$this->plugin_title.' Plugin for WordPress!</b></p>';
		$string .= "<p>Please take a moment to <b>Support the Developer</b> by doing some of the following items:</p>";
		
		$rate_url = 'http://wordpress.org/support/view/plugin-reviews/' . basename(dirname(__FILE__)) . '?rate=5#postform';
		$string .= "<li><a href='$rate_url' target='_blank' title='Click Here to Rate and Review this Plugin on WordPress.org'>Click Here</a> to Rate and Review this Plugin on WordPress.org!</li>";
		
		$string .= "<li><a href='http://facebook.com/MyWebsiteAdvisor' target='_blank' title='Click Here to Follow us on Facebook'>Click Here</a> to Follow MyWebsiteAdvisor on Facebook!</li>";
		$string .= "<li><a href='http://twitter.com/MWebsiteAdvisor' target='_blank' title='Click Here to Follow us on Twitter'>Click Here</a> to Follow MyWebsiteAdvisor on Twitter!</li>";
		$string .= "<li><a href='http://mywebsiteadvisor.com/tools/premium-wordpress-plugins/' target='_blank' title='Click Here to Purchase one of our Premium WordPress Plugins'>Click Here</a> to Purchase Premium WordPress Plugins!</li>";
	
		return $string;
	}
	
	
	
	
	
	public function display_social_media(){
	
		$social = '<style>
	
		.fb_edge_widget_with_comment {
			position: absolute;
			top: 0px;
			right: 200px;
		}
		
		</style>
		
		<div  style="height:20px; vertical-align:top; width:45%; float:right; text-align:right; margin-top:5px; padding-right:16px; position:relative;">
		
			<div id="fb-root"></div>
			<script>(function(d, s, id) {
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) return;
			  js = d.createElement(s); js.id = id;
			  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=253053091425708";
			  fjs.parentNode.insertBefore(js, fjs);
			}(document, "script", "facebook-jssdk"));</script>
			
			<div class="fb-like" data-href="http://www.facebook.com/MyWebsiteAdvisor" data-send="true" data-layout="button_count" data-width="450" data-show-faces="false"></div>
			
			
			<a href="https://twitter.com/MWebsiteAdvisor" class="twitter-follow-button" data-show-count="false"  >Follow @MWebsiteAdvisor</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		
		
		</div>';
		
		return $social;

	}	

	
}

?>

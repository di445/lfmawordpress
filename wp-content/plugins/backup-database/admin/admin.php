<?php

class backup_db_admin {

	var $options;

	function __construct() {
	
		/* Plugin slug and version */
		$this->slug = 'backup_db';
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$this->plugin_data = get_plugin_data(backup_db_path . 'index.php', false, false);
		
		
		/* Priority actions */
		add_action('admin_menu', array(&$this, 'add_menu'), 9);
		add_action('admin_enqueue_scripts', array(&$this, 'add_styles'), 9);
		
		add_action('admin_init', array(&$this, 'admin_init'), 9);
		
	}
	


function backup_db(){

	
   global $backup_db,$wpdb;
	$all_tables = $wpdb->get_col('SHOW TABLES');
	$othertable=$_POST['other_tables'];
	$wp_backup_default_tables = array_intersect($all_tables, $backup_db->core_table_names);

	 $siteName=preg_replace('/[^A-Za-z0-9\_]/', '_', get_bloginfo('name'));
        $WPDBFileName=$siteName.'_'.Date("Y_m_d").'_'.Time("H:M:S").rand(9, 9999).'_database';       
        $SQLfilename=$WPDBFileName.'.sql';
	$filename=$WPDBFileName.'.zip';	
	$path_info = wp_upload_dir();
	wp_mkdir_p($path_info['basedir'].'/backup-database');
         if(isset($_POST['other_tables']))
	$tables =array_merge($wp_backup_default_tables,$othertable);
	else
	$tables=$wp_backup_default_tables;

//	error_log(print_r($tables,true));
	//get all of the tables
	//$tables = $wpdb->get_col('SHOW TABLES');
	
	//cycle through

		
	foreach($tables as $table)
	{
		$result = mysql_query('SELECT * FROM '.$table);
		$num_fields = mysql_num_fields($result);
		
		$return.= 'DROP TABLE '.$table.';';
		$row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
		$return.= "\n\n".$row2[1].";\n\n";
		
		for ($i = 0; $i < $num_fields; $i++) 
		{
			while($row = mysql_fetch_row($result))
			{
				$return.= 'INSERT INTO '.$table.' VALUES(';
				for($j=0; $j < $num_fields; $j++) 
				{
					$row[$j] = addslashes($row[$j]);
					
					if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
					if ($j < ($num_fields-1)) { $return.= ','; }
				}
				$return.= ");\n";
			}
		}
		$return.="\n\n\n";
	}
	
	//save file
	$handle = fopen($path_info['basedir'] .'/backup-database/'.$SQLfilename,'w+');
	fwrite($handle,$return);
	fclose($handle);
	
		$upload_path = array(
		'filename' => ($filename),
		'dir' => ($path_info['basedir'].'/backup-database/'.$SQLfilename),
		'url' => ($path_info['baseurl'].'/backup-database/'.$SQLfilename),
		'size' => 0
	);
		
	 if ( class_exists( 'ZipArchive' ) )
		{
			
			$zip = new ZipArchive;
			$zip->open($path_info['basedir'].'/backup-database/'.$WPDBFileName.".zip", ZipArchive::CREATE);
                        $zip->addFile($path_info['basedir'].'/backup-database/'.$SQLfilename,$SQLfilename);                        
			$zip->close();
                    //    @unlink($path_info['basedir']."/backup-database/".$SQLfilename.".sql");
		
		}
		
                
  
		echo '<div class="updated fade"><p><strong>'.sprintf(__('Download Database Backup file <a href="%s">here</a>.','userpro'),$path_info['baseurl'] .'/backup-database/'.$SQLfilename).'</strong></p></div>';

	do_action("backupdb_cloud_store");
	
}


	function admin_init() {
		
		$this->tabs = array(
			'settings' => __('Create Backup','backup_db'),
			'dbinfo' => __('DB Info','backup_db'),
			'tableinfo' => __('Table Info','backup_db'),				
			'help' => __('Help','backup_db'),
			
			
						
		);
		$this->default_tab = 'settings';
		
		$this->options = get_option('backup_db');
		if (!get_option('backup_db')) {
			update_option('backup_db', backup_db_default_options() );
		}
		
	}
	
	
	

	function add_styles(){
	
		wp_register_style('backup_db_admin', backup_db_url.'admin/css/admin.css');
		wp_enqueue_style('backup_db_admin');
		
		
		
		
	}
	
	function add_menu() {
	
		
		add_management_page( __('Backup Database','backup_db'),  __('Backup Database','backup_db'), 'manage_options', $this->slug, array(&$this, 'admin_page'), '');
		
		
	
	}

	function admin_tabs( $current = null ) {
			$tabs = $this->tabs;
			$links = array();
			if ( isset ( $_GET['tab'] ) ) {
				$current = $_GET['tab'];
			} else {
				$current = $this->default_tab;
			}
			foreach( $tabs as $tab => $name ) :
				if ( $tab == $current ) :
					$links[] = "<a class='nav-tab nav-tab-active' href='?page=".$this->slug."&tab=$tab'>$name</a>";
				else :
					$links[] = "<a class='nav-tab' href='?page=".$this->slug."&tab=$tab'>$name</a>";
				endif;
			endforeach;
			foreach ( $links as $link )
				echo $link;
	}

	function get_tab_content() {
		$screen = get_current_screen();
		if( strstr($screen->id, $this->slug ) ) {
			if ( isset ( $_GET['tab'] ) ) {
				$tab = $_GET['tab'];
			} else {
				$tab = $this->default_tab;
			}
			require_once backup_db_path.'admin/panels/'.$tab.'.php';
		}
	}
	
	
	
	function save() {
	
		foreach($_POST as $key => $value) {
	
			if ($key != 'submit') {
				if (!is_array($_POST[$key])) {
				
					$this->options[$key] = stripslashes( esc_attr($_POST[$key]) );
				} else {
					
				
					$this->options[$key] = $_POST[$key];
					
				}
			}
		}
		
		update_option('backup_db', $this->options);
		
	
	}

	
	
	
	
	function admin_page() {
	
		
		
		if (isset($_POST['submit'])) {
			
			$this->backup_db();
		}
		
		
		
		
	?>
	
		<div class="wrap <?php echo $this->slug; ?>-admin">
		
			<h2 class="nav-tab-wrapper"><?php $this->admin_tabs(); ?></h2>

			<div class="<?php echo $this->slug; ?>-admin-contain">
				
				<?php $this->get_tab_content(); ?>
				
				<div class="clear"></div>
				
			</div>
			
		</div>

	<?php }

}
$backup_db_admin = new backup_db_admin();
?>

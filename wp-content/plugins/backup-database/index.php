<?php
/*
Plugin Name:Backup Database
Plugin URI:http://www.wpproking.com
Description:A Very Light Weight WordPress Database Backup Plugin
Version: 1.2
Author:WPProKing 
*/
define('backup_db_path',plugin_dir_path(__FILE__ ));
define('backup_db_url',plugin_dir_url(__FILE__ ));



if ( !class_exists( "backup_db" ) ) {
	class backup_db {
	
		var $core_table_names = array();
       function __construct() {
       	global $table_prefix, $wpdb;
       	
	$this->install();
		$possible_names = array(
			'categories',
			'commentmeta',
			'comments',
			'link2cat',
			'linkcategories',
			'links',
			'options',
			'post2cat',
			'postmeta',
			'posts',
			'terms',
			'term_taxonomy',
			'term_relationships',
			'users',
			'usermeta',
		);

		foreach( $possible_names as $name ) {
			if ( isset( $wpdb->{$name} ) ) {
				$this->core_table_names[] = $wpdb->{$name};
			}
		}




	}
		function install() {
			
		if (is_admin()){
		foreach (glob(backup_db_path . 'admin/*.php') as $filename) { include $filename; }
		}
		foreach (glob(backup_db_path . 'functions/*.php') as $filename) { require_once $filename; }

			
		}

		

	}
}

$backup_db = new backup_db();





?>

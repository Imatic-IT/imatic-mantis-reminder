<?php
# Make sure this script doesn't run via the webserver  
if( php_sapi_name() != 'cli' ) {  
	echo "It is not allowed to run this script through the webserver.\n";  
	exit( 1 );  
}  
# This page sends an E-mail to the REPORTER if an issue is awaiting feedback
#

require_once( dirname( __FILE__, 4 ) . DIRECTORY_SEPARATOR . 'core.php' );
$t_login	= config_get( 'plugin_Reminder_reminder_login' );
$ok=auth_attempt_script_login( $t_login );
$t_core_path = config_get( 'core_path' );
require_once( $t_core_path.'email_api.php' );
$t_project		= config_get('plugin_Reminder_reminder_feedback_project');
$status			= config_get('plugin_Reminder_reminder_feedback_status');
$t_rem_body1	= config_get( 'plugin_Reminder_reminder_group_body1' );
$t_rem_body2	= config_get( 'plugin_Reminder_reminder_group_body2' );
$query = "select id,reporter_id,handler_id,project_id from {bug} bugs where status in (".implode(",", $status).") ";


$t_rem_include	= config_get('plugin_Reminder_reminder_include');
$t_rem_projects	= "(";
$t_rem_projects	.= config_get('plugin_Reminder_reminder_project_id');
$t_rem_projects	.= ")";
if (ON==$t_rem_include){
	if (!empty( config_get( 'plugin_Reminder_reminder_project_id' ) )) {
		$query .= " and bugs.project_id IN ". $t_rem_projects;
	}
}else{
	if (!empty( config_get( 'plugin_Reminder_reminder_project_id' ) )) {
		$query .= " and bugs.project_id NOT IN ".$t_rem_projects;
	}
}
$query .= " order by reporter_id";
$results = db_query( $query );
if ($results){
	$start = true ;
	$list= "";
	// first group and store feedback reminder per issue
	while ($row1 = db_fetch_array($results)) {
		$id 	   	= $row1['id'];
		$handler	= $row1['handler_id'];
		$project  = $row1['project_id'];
		$reporter = $row1['reporter_id'];
		
    //echo "id: ".$id." handler id: ".$handler." project id: ".$project."\n";
		
    if ($start){
			//$handler2 = $handler ;
			$reporter2 = $reporter ;
			$start = false ;
		}
		//if ($handler== $handler2){
		if ($reporter== $reporter2){
			$list .=" \n\n"; 
			//$list .= string_get_bug_view_url_with_fqdn( $id, $handler2 );
			$list .= string_get_bug_view_url_with_fqdn( $id, $reporter2 );			
		} else {
			// now send the grouped email
			$body  = $t_rem_body1. " \n\n";
			$body .= $list. " \n\n";
			$body .= $t_rem_body2;
			//$result = email_group_reminder( $handler2, $body);
			$result = email_group_reminder( $reporter2, $body);
			
			//$handler2 = $handler ;
			$reporter2 = $reporter;
			
			//$list= string_get_bug_view_url_with_fqdn( $id, $handler2 );
			$list= string_get_bug_view_url_with_fqdn( $id, $reporter2 );
		}
		$list .=" \n";
	}
	// handle last grouped email
	if ($results){
		$body  = $t_rem_body1. " \n\n";
		$body .= $list. " \n\n";
		$body .= $t_rem_body2;
		//why the handler? Feedback reminder email should go to the Reporter!
    //$result = email_group_reminder( $handler2, $body);
    $result = email_group_reminder( $reporter2, $body);
	}
} 
if (php_sapi_name() !== 'cli'){
	echo config_get( 'plugin_Reminder_reminder_finished' );
}

# Send Grouped reminder
function email_group_reminder( $p_user_id, $issues ) {
	$t_username = user_get_field( $p_user_id, 'username' );
	$t_email = user_get_email( $p_user_id );
	$t_message = $issues ;
	$t_subject	= config_get( 'plugin_Reminder_reminder_subject' );
	if( !is_blank( $t_email ) ) {
		email_store( $t_email, $t_subject, $t_message );
		if( OFF == config_get( 'email_send_using_cronjob' ) ) {
				email_send_all();
		}
	}
}

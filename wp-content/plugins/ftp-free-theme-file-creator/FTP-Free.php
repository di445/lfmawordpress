<?php
/*
Plugin Name: FTP-Free Theme File Creator
Plugin URI: http://dandyplow.com/ftp-free
Description: WP-Admin lets us upload and create new posts, pages, media, and more, so why do we have to log into our FTP or hosting provider's file manager every time we need to create a new theme template file?  This simple plugin adds a simple, non-invasive form under existing theme files in the theme editor.  Type in a name for the template file, click the submit button, and you're set!
Version: 2.0
Author: P.G. McCullough
Author URI: http://p.mccullo.ug
License: GPL2
*/

/*  Copyright 2014 P.G. McCullough  (email : patrick@dandyplow.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if(is_admin()) {
function ftpf_create_template_file(){
if(($_POST['filetodelete']) && (file_exists(get_template_directory().'/'.$_POST['filetodelete']))) {
unlink(get_template_directory().'/'.$_POST['filetodelete']);
$newlocale = site_url().'/wp-admin/theme-editor.php?file='.$cleanfilename.'&theme='.$_GET['theme'].'&ftpf_delete=1'; ?>
<script>
window.location.replace('<?php echo $newlocale; ?>');
</script>
<?php }
if($_GET['add_template_file'] == 1) {
$cleanfilename = str_replace(".php", "FXDOTPHPFX", $_POST['cleanname']);
$cleanfilename = str_replace("-", "FXDASHFX", $cleanfilename);
$cleanfilename = preg_replace("/[^A-Za-z0-9 ]/", '', $cleanfilename);
$cleanfilename = str_replace("FXDOTPHPFX", ".php", $cleanfilename);
$cleanfilename = str_replace("FXDASHFX", "-", $cleanfilename);
$filenamenumber = 1;
$splitfilename = explode(".",$cleanfilename);
while(file_exists(get_template_directory().'/'.$cleanfilename)) {
$cleanfilename = $splitfilename[0]."-".$filenamenumber.".".$splitfilename[1];
$filenamenumber++;
}
file_put_contents(get_template_directory().'/'.$cleanfilename,"<?php
/*
Template Name: ".$_POST['cleantitle']."
*/ ?>");
$newlocale = site_url().'/wp-admin/theme-editor.php?file='.$cleanfilename.'&theme='.$_GET['theme'].'&ftpf_create=1'; ?>
<script>
window.location.replace('<?php echo $newlocale; ?>');
</script>
<?php }
}

add_action('admin_head', 'ftpf_create_template_file');

add_action('admin_footer', 'ftpf_add_template_file');

function ftpf_add_template_file() { 
wp_enqueue_script('jquery');
if (strpos($_SERVER['REQUEST_URI'],'theme-editor.php') !== false) {
echo '
<script>
function ftpf_deleteFile(fileName) {
jQuery("body").append("<div id=\"ftpf_confirm_popup\" style=\"width: 100%; height: 100%; background: url(\''.plugins_url().'/FTP-Free/images/trans_bg.png\'); position: fixed; top: 0px; left: 0px; z-index: 100\"><div style=\"position: fixed; width: 360px; height: 130px; background: #fff; box-shadow: 0 0 3px 3px; border-radius: 10px; left: 50%; top: 50%; margin-top: -100px; margin-left: -200px; padding: 20px; padding-top: 50px; text-align: center;\"><form method=\"post\"><input type=\"hidden\" value=\""+fileName+"\" name=\"filetodelete\">Are you sure you want to delete \""+fileName+"\"?<br><table style=\"width: 70%; margin: auto; margin-top: 25px;\"><tr><td><input type=\"submit\" value=\"Delete\" style=\"background: #cc0000\" class=\"button button-primary\"></td><td></td><td><div onclick=\"jQuery(\'#ftpf_confirm_popup\').remove()\" class=\"button button-primary\">Cancel</td></tr></table></div></div>");
}
jQuery("#templateside").append("<h3>Add Template File</h3><br /><form method=\"post\" action=\"?add_template_file=1&theme='.$_GET['theme'].'\"><input name=\"cleantitle\" type=\"text\" oninput=\"if((this.value)==\'\') {jQuery(\'#newfilename\').html(\'(*.php)\');} else {jQuery(\'#newfilename\').html(\'(\'+this.value.toLowerCase().replace(/ /g, \'-\')+\'.php)\'); jQuery(\'#hiddencleanname\').val(jQuery(\'#newfilename\').html())}\"><span style=\'margin-left: 12px;\' class=\'nonessential\' id=\'newfilename\'>(*.php)</span><br /><br /><input type=\"hidden\" name=\"cleanname\" id=\"hiddencleanname\"><input type=\"submit\" class=\"button button-primary\" value=\"Create File\"></form>");

var i = 1;
jQuery("#templateside li").each(function(i) {
   jQuery(this).append("<div onclick=\'ftpf_deleteFile(this.id)\' style=\'float: right; margin-top: -40px; background: #cc0000\' class=\'button button-primary\' id=\'deletedFile"+i+"\'>X</div>");
});

var p = 1;
jQuery("#templateside").find("a").each(function(p) {
var testRE = jQuery(this).attr("href").match("file=(.*)&");
jQuery("#deletedFile"+p).attr("id",testRE[1]);
});

</script>';
}
if($_GET['ftpf_create']==1) { ?>
<script>
jQuery('<div id="message" class="updated"><p>File created successfully.</p></div>').insertBefore('.wrap');
</script>
<?php };
if($_GET['ftpf_delete']==1) { ?>
<script>
jQuery('<div id="message" class="updated"><p>File deleted successfully.</p></div>').insertBefore('.wrap');
</script>
<?php };
}}; ?>
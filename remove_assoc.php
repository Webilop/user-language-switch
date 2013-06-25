<?php
$relation_id = $_GET['pid'];
$lang = $_GET['lang'];
$post_id = $_GET['post'];
$meta = $_GET['meta'];
include '../../../wp-config.php';
if(isset($relation_id)){
   delete_post_meta( $relation_id, 'uls_translation_'.$lang );
   delete_post_meta( $post_id, $meta );
   $con = mysql_connect('DB_HOST', 'DB_USER', 'DB_PASSWORD');
   if (!$con){
      die('Could not connect: ' . mysql_error());
   }
   mysql_select_db('DB_NAME');
   $sql1 = 'DELETE FROM wp_postmeta
        WHERE post_id='.$relation_id.' AND meta_key=uls_translation_'.$lang;
   $sql2 = 'DELETE FROM wp_postmeta
        WHERE post_id='.$post_id.' AND meta_key='.$meta;
   mysql_query( $sql1, $con );
   mysql_query( $sql2, $con );
}

?>

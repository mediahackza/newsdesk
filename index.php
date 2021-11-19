<?php
/*
Plugin Name: Newsdesk Manager
Plugin URI: https://mediahack.co.za/newsdesk-manager
Description: Tools for managing a publishing system
Author: Alastair Otter
Author URI: https://mediahack.co.za
Version: 0.1.1
*/

function newsdesk_admin_style() {
    wp_register_style( 'newsdesk-admin.css', plugin_dir_url( __FILE__ ) . 'assets/css/newsdesk-admin.css', false, '1.0.0' );
    wp_enqueue_style( 'newsdesk-admin.css' );
}

add_action( 'admin_enqueue_scripts', 'newsdesk_admin_style' ); 


add_action('admin_menu', 'newsdesk_setup_menu');
 
function newsdesk_setup_menu(){
    add_menu_page( 'Newsdesk Manager', 'Newsdesk', 'manage_options', 'nd-manager', 'addNewsdesk' );
}
 

// add post published action 
function newsdesk_post_published( $new_status, $old_status, $post) { 
    if ( $new_status == 'publish' && $old_status != 'publish' ) {
        $id = $post->id; 
        $title = $post->post_title;
        $excerpt = $post->post_excerpt; 
        $authorid = get_post_field( 'post_author', $id );
        $author = get_the_author_meta('display_name', $authorid);
        $type = get_post_type($id);
        $link = get_permalink($id);
        $recipient = "alastair.otter@gijn.org";
        $message = "A new " . $type . " has been published by " . $author ;
        $message .= "\n\nYou can view the post here: " . $link . "\n\n";
        if(isset($excerpt)) { 
            $message .= $excerpt;
        }
        wp_mail($recipient, "[New Post] " . $title, $message);
    }
}
add_action('transition_post_status', 'newsdesk_post_published', 10, 3 );


// Add admin page
function addNewsdesk(){
    
?>
<div class="nd-plugin-title">
    <h1>Newsdesk Manager</h1>
</div>

<?php 
$type = "any";
if(isset($_GET['nd-type'])) { 
    $type = $_GET['nd-type'];
}

$args = array(
    'post_type'    => $type,
    'posts_per_page' => 20,
    'orderby' => 'date',
    'suppress_filters' => true,
    'post_status' => array('publish')
);
$pages = new WP_Query ( $args );

$row = "<table class='newsdesk-table'>";
$row .= "<thead class='nd-table-head'><tr>";
$row .= "<th class='nd-title-bar' colspan='2'>Most Recent Posts & Pages</th>";
$row .= "<th class='nd-title-bar' width='40%'><span class='nd-filter'>Filter: "; 
if($type == "any") { $row .= "<a href='?page=nd-manager&nd-type=any' class='nd-filter-selected'>all</a> | "; } 
else { $row .= "<a href='?page=nd-manager&nd-type=any'>all</a> | "; }
if($type == 'page') { $row .= "<a href='?page=nd-manager&nd-type=page' class='nd-filter-selected'>pages</a> | "; }
else { $row .= "<a href='?page=nd-manager&nd-type=page'>pages</a> | "; }
if($type == 'post') { $row .= "<a href='?page=nd-manager&nd-type=post' class='nd-filter-selected'>posts</a></span></th>"; }
else { $row .= "<a href='?page=nd-manager&nd-type=post'>posts</a></span></th>"; }

$row .= "</tr></thead>";

if ( $pages->have_posts() ) {
 
    while ( $pages->have_posts() ) {
 
        $pages->the_post();
        
 
        $row .= "<tr class='nd-row-spacer'><td colspan='3'></td></tr>";
        $row .= "<tr class='nd-title-row'>";
        $row .= "<td class='newsdesk-td newsdesk-title' colspan='3'><a href='" . get_the_permalink() . "' target='_blank' class='nd-title-link'>" . get_the_title() . "</a>
        <div class='nd-editing-options'><a href='post.php?post=" . get_the_id() . "&action=edit' target='_blank' class='nd-title-link'>Edit</a> | <a href='" . get_the_permalink() . "' target='_blank' class='nd-title-link'>View</a></div>
        </td><tr>";
        $row .= "<tr class='nd-body-row'>";
        $row .= "<td class='newsdesk-td '>"; 
        if(get_post_type() == 'post') { $row .= "<span class='nd-post'>Post</span>"; } else { $row .= "<span class='nd-page'>Page</span>"; }        
        $row .= "</td>";
        $row .= "<td class='newsdesk-td'><span class='nd-author'>Posted By</span> &nbsp; " . get_the_author() . "</td>";
        $row .= "<td class='newsdesk-td'>"; 
        if(get_post_status() == "publish") { $row .= "<span class='nd-published'>Published</span>"; } else { $row .= "<span class='nd-status-other'>Not published</span>"; }
        $row .= " &nbsp; " . get_the_date('F j, Y H:m ');
        $row .= "</td>";
        $row .= "</tr>";
        
 
    }
 
}

$row .= "</table>";
wp_reset_postdata();

echo $row;
 
}

?>
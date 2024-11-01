<?php
/*
Plugin Name:WP Our Team
Plugin URL: http://themeidol.com
Description: A full responsive simple and easy plugin to manage team member with shortcode in page or post.You can display team members picture,social links,and Job Title
Version: 1.1
Author: themeidol
Author URI: http://themeidol.com
Contributors: themeidol
License:

  Copyright (C) 2016 themeidol

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


class WPOurteam {
    private static $instance;
    const VERSION = '1.1';

    private static function has_instance() {
        return isset( self::$instance ) && null != self::$instance;
    }

    public static function get_instance() {
        if ( ! self::has_instance() ) {
            self::$instance = new WPOurteam;
        }
        return self::$instance;
    }

    public static function setup() {
        self::get_instance();
    }

    protected function __construct() {
        if ( ! self::has_instance() ) {
            $this->init();
        }
    }
    public function init() {
        add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
        add_action('init', array( $this, 'register_custom_posttype_team'));
        /*** Function to add thumbnails ***/           
        add_image_size( 'admin-thumb',80, 60, true );
        add_image_size( 'team-thumb', 180, 180, true );
        add_action( 'admin_init', array( $this, 'team_init') );
        add_action( 'save_post', array( $this,'team_meta_box_save' ));
        /**********************************************************
        * to edit default post listing
        * 
        **********************************************************/
        add_filter( 'manage_edit-team_columns', array( $this,'edit_wpteam_columns' )) ;
        add_shortcode("WPour_team", array( $this,"team_shortcode"));
        add_action( 'manage_team_posts_custom_column', array( $this,'manage_team_columns'), 10, 2 );
        //Rich Editing
        add_filter( 'mce_external_plugins', array( $this,'add_plugin' ));
        add_filter( 'mce_buttons', array( $this,'register_button' ));

    }
    function register_button( $buttons ) {
     array_push( $buttons, "|", "ourteam" );
     return $buttons;
    }

    function add_plugin( $plugin_array ) {
     $plugin_array['ourteam'] = plugin_dir_url(__FILE__) . '/assets/js/our-team.js';
     return $plugin_array;
    }



    function register_plugin_styles() {
      global $wp_styles;
      wp_register_style('easy-team', plugin_dir_url(__FILE__) . '/assets/css/team.css');
      wp_enqueue_style('easy-team');
      wp_enqueue_script( 'easy-front-team', plugins_url( 'assets/js/easy-team.js', __FILE__ ), array('jquery'), self::VERSION, 'all' );
      wp_enqueue_style( 'font-awesome-styles', plugins_url( 'assets/css/font-awesome.min.css', __FILE__ ), array(), self::VERSION, 'all' );

    }
    public function team_init() {

        add_meta_box("member-information", "Member Information", array( $this,"team_meta_options"), "team", "normal", "high");      
    }

    function team_meta_options( $post ) {

        $values = get_post_custom( $post->ID );

        $job_title  = isset( $values['job_title'] ) ? esc_attr( $values['job_title'][0] ) : '';

        $facebook   = isset( $values['facebook'] ) ? esc_attr( $values['facebook'][0] ) : '';

        $twitter   = isset( $values['twitter'] ) ? esc_attr( $values['twitter'][0] ) : '';

        $linkedin   = isset( $values['linkedin'] ) ? esc_attr( $values['linkedin'][0] ) : '';

        $pinterest   = isset( $values['pinterest'] ) ? esc_attr( $values['pinterest'][0] ) : '';

        

        wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' );

        ?>
        <table width="100%" border="0" class="options" cellspacing="5" cellpadding="5">
            <tr>
                <td width="1%">
                    <label for="job_title"><?php _e('Job Title', 'Easy Team'); ?></label>
                </td>
                <td width="10%">
                    <input type="text" id="job_title" name="job_title" value="<?php echo $job_title; ?>" placeholder="<?php _e('Enter job title', 'Easy Team'); ?>" style="width:90%; padding: 5px 10px; line-height: 20px;"/>
                </td>          
            </tr>  
           <tr>
                <td width="1%">
                    <label for="facebook"><?php _e('Facebook', 'Easy Team'); ?></label>
                </td>
                <td width="10%">
                    <input type="text" id="facebook" name="facebook" value="<?php echo $facebook; ?>" placeholder="<?php _e('Enter facebook url', 'Easy Team'); ?>" style="width:90%; padding: 5px 10px; line-height: 20px;"/>
                </td>          
            </tr>  
            <tr>
                <td width="1%">
                    <label for="twitter"><?php _e('Twitter', 'Easy Team'); ?></label>
                </td>
                <td width="10%">
                    <input type="text" id="twitter" name="twitter" value="<?php echo $twitter; ?>" placeholder="<?php _e('Enter twitter url', 'Easy Team'); ?>" style="width:90%; padding: 5px 10px; line-height: 20px;"/>
                </td>          
            </tr>  
            <tr>
                <td width="1%">
                    <label for="linkedin"><?php _e('LinkedIn', 'Easy Team'); ?></label>
                </td>
                <td width="10%">
                    <input type="text" id="linkedin" name="linkedin" value="<?php echo $linkedin; ?>" placeholder="<?php _e('Enter linkedin url', 'Easy Team'); ?>" style="width:90%; padding: 5px 10px; line-height: 20px;"/>
                </td>          
            </tr> 
            <tr>
                <td width="1%">
                    <label for="pinterest"><?php _e('Pinterest', 'Easy Team'); ?></label>
                </td>
                <td width="10%">
                    <input type="text" id="pinterest" name="pinterest" value="<?php echo $pinterest; ?>" placeholder="<?php _e('Enter pinterest url', 'Easy Team'); ?>" style="width:90%; padding: 5px 10px; line-height: 20px;"/>
                </td>          
            </tr>    
        </table>   
        <?php   
    }

    public function team_meta_box_save( $post_id )
    {
        global $post;  

        $custom_meta_fields = array( 'job_title', 'facebook', 'twitter', 'linkedin', 'pinterest');

        // Bail if we're doing an auto save
        if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        
        // if our nonce isn't there, or we can't verify it, bail
        if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'my_meta_box_nonce' ) ) return;
        
        // if our current user can't edit this post, bail
        if( !current_user_can( 'edit_posts' ) ) return;
        
        // now we can actually save the data
        $allowed = array( 
                    'a' => array( 
                            'href' => array(),
                            'title' => array()
                        ),
                    'br' => array(),
                   'em' => array(),
                   'strong' => array(),
                   'p' => array(),
                   'span' => array(),
                   'div' => array(),
                );    
     
        foreach( $custom_meta_fields as $custom_meta_field ){

            if( isset( $_POST[$custom_meta_field] ) )           

                update_post_meta($post->ID, $custom_meta_field, wp_kses( $_POST[$custom_meta_field], $allowed) );      
        }
            
       
    }
    public function register_custom_posttype_team() {

    $labels = array(
        'name'                  => __('Team'),
        'singular_name'         => __('Team'),
        'add_new'               => __('Add Member'),
        'add_new_item'          => __('Add New'),
        'edit_item'             => __('Edit Member'),
        'new_item'              => __('New Member'),
        'view_item'             => __('View Team Member'),
        'search_items'          => __('Search Members'),
        'not_found'             => __('Members not found'),
        'not_found_in_trash'    => __('Members not found in Trash'),
        'parent_item_colon'     => '',
        'menu_name'             => __('Team')
    );
    $args = array(
        'label'                 => __( 'team' ),
        'description'           => __( 'Post type to manage Team for Easy Team' ),
        'labels'                => $labels,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'show_in_nav_menus'     => true,
        'show_in_admin_bar'     => true,
        'capability_type'       => 'post',
        'hierarchical'          => true,        
        'supports'              => array( 'title', 'thumbnail' ),      
        'menu_icon'             => plugin_dir_url(__FILE__). '/assets/images/icon-team.png',
        'taxonomies'            => array('team-groups')
    );

    register_post_type('team', $args);
   

        
    }
    function edit_wpteam_columns( $columns ) {

    $columns = array(

                'cb' => '<input type="checkbox" />',

                'title' => __( 'Name','Easy Team' ),

               
                'jobtitle' => __( 'Job Title','Easy Team' ),

                'image' => __( 'Image','Easy Team' ),

                'date' => __( 'Date','Easy Team' )
            );

        return $columns;

    }



    function manage_team_columns( $column, $post_id ) {

        global $post;

        switch( $column ) {

             
            case 'jobtitle' :

                _e( get_post_meta($post_id,'job_title',true) );

            break;

            case 'image' :

                the_post_thumbnail('admin-thumb');

            break;            

            default :

                break;

        }

    }

    public function team_shortcode($atts, $content = null) {

      extract(shortcode_atts(array(
        "limit" => '' 

      ), $atts));

      ob_start();  
    // Define limit
    if ($limit) {
      $posts_per_page = $limit;
    } else {
      $posts_per_page = '-1';
    }


  // Create the Query
    $post_type = 'team';
    $orderby = 'post_date';
    $order = 'DESC';
    
    
      $query = new WP_Query(array(
      'post_type' => $post_type,
      'posts_per_page' => $posts_per_page,
      'orderby' => $orderby,
      'order' => $order,
      'no_found_rows' => 1      
    )
    );
    

    //Get post type count
    $post_count = $query->post_count;
 

  // Displays Custom post info

  if ($post_count > 0):
  ?>
  <div id="easy-team" class="easy-members">
  <ul class="our-team">
  <?php
  // Loop
  while ($query->have_posts()):$query->the_post();?>
    <li>
    <?php $team_id = get_the_ID(); ?>
    <div class="member-details">
     <div class="mem-des">
      <?php
      
        if (function_exists('has_post_thumbnail') && has_post_thumbnail()) {
            ?><div class="mem-image"><?php the_post_thumbnail('team-thumb'); ?></div><?php
          }
          else
          {
            ?>
            <div class="mem-image"><img src="<?php echo plugins_url('/assets/images/profile-pic.png',__FILE__);?>"></div>
            <?php
          }
      ?>
      <div class="hover-info">  
      <?php 
          $facebook = esc_url(get_post_meta($team_id,'facebook',true));
          $twitter = esc_url(get_post_meta($team_id,'twitter',true));
          $linkedin = esc_url(get_post_meta($team_id,'linkedin',true));
          $pinterest = esc_url(get_post_meta($team_id,'pinterest',true));
      ?> 

      <?php if($facebook){ ?>
            <a href="<?php echo $facebook;?>" target="_blank" title="" class="team-facebook"><i class="fa fa-facebook"></i></a>
      <?php } ?>  

      <?php if($twitter){ ?>
            <a href="<?php echo $twitter;?>" target="_blank" title="" class="team-twitter"><i class="fa fa-twitter"></i></a>
      <?php } ?> 

      <?php if($linkedin){ ?>
            <a href="<?php echo $linkedin;?>" target="_blank" title="" class="team-linkedin"><i class="fa fa-linkedin"></i></a>
      <?php } ?> 

      <?php if($pinterest){ ?>
            <a href="<?php echo $pinterest;?>" target="_blank" title="" class="team-pinterest"><i class="fa fa-pinterest"></i></a>
      <?php } ?> 
       
         
      </div>
          
      </div>
    </div>  
    <div class="member-info">
     <span class="mem-name"><?php the_title();?></span>
     <span class="mem-pos"><?php echo get_post_meta($team_id,'job_title',true);?></span> 
     <?php $team_content = get_the_content();
     if($team_content){ ?>
        <p class="mem-content"><?php echo $team_content; ?></p>
        <?php 
      } ?>
    </div>
  </li>


  <?php endwhile;?>
    </ul>
  </div>
  <?php 
  else:
  ?>
    <span class'no-member'>Team Members not found.</span>
  <?php endif;

  // Reset query to prevent conflicts
  wp_reset_query();

  ?>

  <?php

  return ob_get_clean();

  }

}
WPOurteam::setup();

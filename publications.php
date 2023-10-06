<?php
/*
Plugin Name: GHI page publications cards
Description: GHI page publications cards
Version: 1.0
Author: Mustafa slim
Author URI: https://www.linkedin.com/in/mostafa-slim-5ba483127
*/


if (!is_page('publication')) {
  wp_enqueue_script('ajax-script', plugin_dir_url(__FILE__) . 'my-script.js', array('jquery'), false, true);

  // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
  wp_localize_script(
    'ajax-script',
    'ajax_object',
    array('ajax_url' => admin_url('admin-ajax.php'))
  );
  // Only applies to dashboard panel
}


add_action('wp_ajax_my_action', 'my_action');
add_action('wp_ajax_nopriv_my_action', 'my_action');

// Same handler function...
function my_action()
{
  $category = $_POST['category'];
  $type = $_POST['type'];
  $order = $_POST['order'];
  $page=$_POST['page'];
  $args = array(
    'post_type' => 'publication',
    'posts_per_page' => 10, // Show all posts
    'paged' =>$page,
  );

  if(!empty($order)){
    $args['meta_key'] = 'date_published';
    $args['order_by'] ='meta_value';
    $args['order'] = $order;
  }
  // check if filters is not empty 
  if (!empty($category) || !empty($type)) {
    $relation = 'AND';
    if (empty($category) || empty($type))
      $relation = 'OR';
    $args['meta_query'] = array(
      'relation' => $relation, // You can use 'OR' if needed
      array(
        'key' => 'category',
        'value' => $category,
        'compare' => '='
      ),
      array(
        'key' => 'type',
        'value' => $type,
        'compare' => '='
      )
    );
  }
  wp_send_json(publication_cards($args));
  wp_die();
}

function cards_shortcode()
{

  $fields = get_post_type_object('publication');
  $group_fields_id = acf_get_field_groups(array('post_type' => 'publication'))[0]['key'];
  $group_fields = acf_get_fields($group_fields_id);
  $counter = 0;

  /* start filter section */
  echo '<div class="flex flex-col sm:flex-row justify-center gap-[8px] ">';
  foreach ($group_fields as $field) {
    $field_name = $field['name'];
    if ($field_name == 'category' || $field_name == 'type') {
      $counter++;
      echo   '<select id="select_' . $counter . '" name="' . $field_name . '_filter">
    <option value="">Select ' . $field_name . '</option>';
      foreach ($field['choices'] as $key => $choise) {
        echo '<option value="' . $choise . '">' . $choise . '</option>';
      }
      echo '</select>';
    }
  }
  echo '<select id="select_3">
        <option value="">Sort Results By</option>
        <option value="DESC">Newest</option>
        <option value="ASC">Latest</option>
        </select>';
  echo '</div>';
  /* end filter section */

  /* end filter section */

  $args = array(
    'post_type' => 'publication',
    'posts_per_page' => 10, // Show all posts
    'paged' =>get_query_var('paged') ? get_query_var('paged') : 1, //
  );
  wp_enqueue_style('GhiCardsStyles', '/wp-content/plugins/ghi-publications/style.css');
  echo '<section id="publication-section" class="mt-5">';
  $html= publication_cards($args);
  echo $html['html'];
  echo '</section>';
  // import jQuery script
  echo '<span id="loader" class="loader"></span>';
  if($html['max_page_number']>1){
  echo '<div>';
  echo '<button class="block mt-3 mx-auto text-[11px] bg-[transparent] p-[4px_16px] font-[500] mt-[14px] rounded-[4px] uppercase border-[1px] border-solid border-[#860334] text-[#860334] transition-all duration-500 hover:bg-[#860334] hover:text-white" id="load-more-button">Load More</button>';
  echo '</div>';}
  echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
}

function publication_cards($args)
{
  $custom_query = new WP_Query($args);
  $response['max_page_number']=$custom_query->max_num_pages;
  $response['html']="";
  $first_post=true;
  if ($custom_query->have_posts()) : $response['html'].='
    <div class="container mx-auto">
    <div id="card_container" class="flex justify-center flex-wrap gap-4">';
    while ($custom_query->have_posts()) : $custom_query->the_post();

      $response['html'].=' <div class="relative transition-all duration-500 flex flex-col justify-center bg-[#FFF] p-[17px] border border-[#eee] w-[900px] max-w-[100%] text-[12px] shadow-[#00000019_0px_20px_25px_-5px,#0000_0px_10px_10px_-5px] transition-all hover:scale-[1.02] hover:shadow-2xl rounded-[6px] cursor-pointer">';
           if($first_post )
           {
            $text='Latest</div>'; 
            $response['html'].='<div class="absolute -top-[15px] right-[5px] bg-[#860334] text-white z-30 p-[5px_10px] rounded-[4px]">';
             if(isset($args['order']) && $args['order'] == 'ASC')
               $text='Oldest</div>';
            $response['html'].=$text;
            $first_post=false;     
           }
            $response['html'].='<div class="flex flex-col sm:flex-row gap-[24px] md:gap-5 items-center">
              <div class="w-[200px] h-[200px] max-w-[100%] rounded-md overflow-hidden">
                <img
                  class="bg-cover w-full h-full"
                  src="'.get_home_url().'/wp-content/uploads'.(explode(",", get_post_meta(get_the_ID(),'dfiFeatured',true)[0])[1]).'"
                />
              </div>
              <div class="flex flex-1 flex-col items-center md:items-start">
                <h3  class="font-bold text-[12px] text-[black] uppercase leading-[1.3] mb-0 max-w-[377px]">
               '.get_post_meta(get_the_ID(), 'title', true).'
                </h3>
                <span class="block text-xs text-[#888] italic font-[500] m-[6px_0px]">Full Course</span>
               <div class="flex flex-col">
                 <div
                      class="flex items-center  last:mb-0 gap-2"
                    >
                      <span class="text-[#860334]"><i class="fa-solid fa-code text-[14px]"></i></span>
                      <span class="text-xs">'  .  get_post_meta(get_the_ID(), 'type', true)  . '</span>
                 </div>
                 <div
                      class="flex items-center  last:mb-0 gap-2"
                    >
                      <span class="text-[#860334]"><i class="fa-regular fa-calendar text-[14px]"></i></span>
                      <span class="text-xs">'  . date("F j, Y", strtotime(get_post_meta(get_the_ID(), 'date_published', true))) . '</span>
                 </div>
                 <div
                      class="flex items-center  last:mb-0 gap-2"
                    >
                      <span class="text-[#860334]"><i class="fa-solid fa-user text-[13px]"></i></span>
                      <span class="text-xs">'.get_post_meta(get_the_ID(), 'author', true) .'</span>
                 </div>
                 <div
                      class="flex items-center  last:mb-0 gap-2"
                    >
                      <span class="text-[#860334]"><i class="fa-solid fa-globe text-[14px]"></i></span>
                      <span class="text-xs">'   . get_post_meta(get_the_ID(), 'language', true) . '</span>
                 </div>
               </div>
              </div>
            </div>
          </div>';

    endwhile;
    $response['html'].='</div>
      </div>';
  endif;
  return $response;
}



add_shortcode('publications-cards', 'cards_shortcode');

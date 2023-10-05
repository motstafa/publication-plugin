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
    array('ajax_url' => admin_url('admin-ajax.php'),)
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
  $order_by = $_POST['order'];
  $page=$_POST['page'];
  $args = array(
    'post_type' => 'publication',
    'posts_per_page' => 1, // Show all posts
    'paged' => $page,
    'meta_key' => 'date_published',
    'orderby' => 'meta_value',
    'order' => $order_by //
  );

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
  echo publication_cards($args);
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
        <option value"">Sort Results By</option>
        <option value"DESC">Newest</option>
        <option value"ASC">Latest</option>
        </select>';
  echo '</div>';
  /* end filter section */

  /* end filter section */

  $args = array(
    'post_type' => 'publication',
    'posts_per_page' => 1, // Show all posts
    'paged' =>get_query_var('paged'), // Show
  );

  wp_enqueue_style('GhiCardsStyles', '/wp-content/plugins/ghi-publications/style.css');
  echo '<section id="publication-section" class="mt-5">';
  publication_cards($args);
  echo '</section>';
  // import jQuery script
  echo '<span id="loader" class="loader"></span>';
  echo '<div>';
  echo '<button class="block mt-3 mx-auto text-[11px] bg-[transparent] p-[4px_16px] font-[500] mt-[14px] rounded-[4px] uppercase border-[1px] border-solid border-[#860334] text-[#860334] transition-all duration-500 hover:bg-[#860334] hover:text-white" id="load-more-button">Load More</button>';
  echo '</div>';
  echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
}



function publication_cards($args)
{
  $custom_query = new WP_Query($args);
  if ($custom_query->have_posts()) : echo
    '<div class="container mx-auto">
    <div id="card_container" class="flex justify-center flex-wrap gap-4">';
    while ($custom_query->have_posts()) : $custom_query->the_post();
      // Display your post content here
      // echo '<div class="card flex flex-col p-1 bg-[#FFF] border border-[#eee] w-[300px] min-h-[300px] text-[12px] shadow-[#00000019_0px_20px_25px_-5px,#0000_0px_10px_10px_-5px]">
      //         <div class="lines"></div>

      //         <div class="content">
      //           <div class="flex-1 flex flex-col bg-[#FFF]">
      //             <div class="flex flex-col">
      //               <span class="font-bold">' . get_the_title() . '</span>

      //               <div class="flex items-center  mb-2 last:mb-0 gap-2">
      //               <span class="text-xs"><i class="fa-solid fa-code"></i></span>
      //               <span class="text-xs pb-2 text-[#888]">
      //               ' . get_post_meta(get_the_ID(), 'type', true) . '
      //               </span>
      //               </div>
      //               <div
      //               class="flex items-center  mb-2 last:mb-0 gap-2"
      //             >
      //               <span class="text-xs"><i class="fa-solid fa-globe"></i></span>
      //               <span class="text-[#860334]">' . get_post_meta(get_the_ID(), 'language', true) . '</span>
      //             </div>
      //             </div> 
      //             <div class="flex-1 text-[#000] border-t border-b py-3">
      //                 <div class="flex items-center  mb-2 last:mb-0 gap-2">
      //                    <span class="text-xs"><i class="fa-solid fa-user"></i></span>
      //                    <span class="text-[#860334]">' . get_post_meta(get_the_ID(), 'author', true) . '</span>
      //                 </div>
      //             </div>
      //           </div>
      //           <div class="flex-1 text-[#000] border-t border-b py-3">
      //            <span class="text-xs"><i class="fa-regular fa-calendar"></i></span> 
      //             <span class="text-[#860334]">' . date("F j, Y", strtotime(get_post_meta(get_the_ID(), 'date_published', true))) . '</span>
      //           </div>
      //         </div>
      //       </div>';


      // echo ' <div class="card relative flex flex-col justify-center p-[28px] bg-[#FFF] border border-[#eee] w-[300px] min-h-[450px] text-[12px] shadow-[#00000019_0px_20px_25px_-5px,#0000_0px_10px_10px_-5px] transition-all hover:scale-[1.02] hover:shadow-2xl rounded-[7px]">
      //       <div class="absolute -top-[15px] right-[5px] bg-[#860334] text-white z-30 p-[5px_10px] rounded-[4px]">
      //         New
      //       </div>
      //       <div class="lines"></div>
      //       <div class="content flex flex-col h-[100%]">
      //         <div class="mt-[10px]">
      //           <img
      //             class="h-[158px] w-full bg-cover rounded-[3px]"
      //             alt=""
      //             src="https://media-ghi.ghi.aub.edu.lb/wp-content/uploads/2022/10/28115331/publications.jpg"
      //           />
      //         </div>
      //         <div class="flex flex-col bg-[#FFF]">
      //           <div class="flex flex-col my-1">
      //             <span class="font-bold text-[16px] mt-[12px] text-[black]">' . get_the_title() . '</span>
      //             <span class="text-xs pb-2 text-[#888]">
      //               Free online drawing application for all ages
      //             </span>
      //           </div>
      //           <div class="flex-1 text-[#000] border-t border-b py-3">
      //            <div
      //                 class="flex items-center  mb-2 last:mb-0 gap-2"
      //               >
      //                 <span class="text-[#860334]"><i class="fa-solid fa-code text-[14px]"></i></span>
      //                 <span class="text-xs">'  .  get_post_meta(get_the_ID(), 'type', true)  . '</span>
      //            </div>
      //            <div
      //                 class="flex items-center  mb-2 last:mb-0 gap-2"
      //               >
      //                 <span class="text-[#860334]"><i class="fa-regular fa-calendar text-[14px]"></i></span>
      //                 <span class="text-xs">'  . date("F j, Y", strtotime(get_post_meta(get_the_ID(), 'date_published', true))) . '</span>
      //            </div>
      //            <div
      //                 class="flex items-center  mb-2 last:mb-0 gap-2"
      //               >
      //                 <span class="text-[#860334]"><i class="fa-solid fa-user text-[13px]"></i></span>
      //                 <span class="text-xs">'  . get_post_meta(get_the_ID(), 'author', true) . '</span>
      //            </div>
      //            <div
      //                 class="flex items-center  mb-2 last:mb-0 gap-2"
      //               >
      //                 <span class="text-[#860334]"><i class="fa-solid fa-globe text-[14px]"></i></span>
      //                 <span class="text-xs">'   . get_post_meta(get_the_ID(), 'language', true) . '</span>
      //            </div>
      //           </div>
      //         </div>
      //         <div class="h-[30px] flex items-center text-[10px]">
      //           <span>active 15 days ago</span>
      //         </div>
      //         <button class="bg-[#092140] text-[white] text-[9px] border=[transparent] font-semibold tracking-[0.5px] uppercase padding-[5px_13px] rounded-[6px]">
      //           Read more
      //         </button>
      //       </div>
      //     </div>';

      echo ' <div class="relative transition-all duration-500 flex flex-col justify-center bg-[#FFF] p-[17px] border border-[#eee] w-[900px] max-w-[100%] text-[12px] shadow-[#00000019_0px_20px_25px_-5px,#0000_0px_10px_10px_-5px] transition-all hover:scale-[1.02] hover:shadow-2xl rounded-[6px] cursor-pointer">
            <div class="absolute -top-[15px] right-[5px] bg-[#860334] text-white z-30 p-[5px_10px] rounded-[4px]">
              Latest
            </div>
            <div class="flex flex-col sm:flex-row gap-[24px] md:gap-5 items-center">
              <div class="w-[200px] h-[200px] max-w-[100%] rounded-md overflow-hidden">
                <img
                  class="bg-cover w-full h-full"
                  src="https://media-ghi.ghi.aub.edu.lb/wp-content/uploads/2023/02/20151440/BLOG-BANNER-2-1024x576.png"
                />
              </div>
              <div class="flex flex-1 flex-col items-center md:items-start">
                <h3  class="font-bold text-[12px] text-[black] uppercase leading-[1.3] mb-0 max-w-[377px]">
              The state of cancer research in fragile and conflict-affected settings in the Middle East and North Africa Region: A bibliometric analysis
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
                      <span class="text-xs">Horgan D, Mia R, Erhabor T, Hamdi Y, Dandara C, Lal JA, Domgue JF, Ewumi O, Nyawira T, Meyer S, Kondji D, Francisco NM, Ikeda S, Chuah C, De Guzman R, Paul A, Reddy Nallamalla K, Park W-Y, Tripathi V, Tripathi R, Johns A, Singh MP, Phipps ME, Dube F, Whittaker K, Mukherji D, Rasheed HMA, Kozaric M, Pinto JA, Doral Stefani S, Augustovski F, Aponte Rueda ME, Fujita Alarcon R, Barrera-Saldana HA.</span>
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
    echo '</div>
      </div>';
  endif;
}



add_shortcode('publications-cards', 'cards_shortcode');

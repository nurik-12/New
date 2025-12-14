<?php
add_action('wp_enqueue_scripts', function(){
  wp_enqueue_script('jquery');
});

add_action('wp_ajax_load_advert', 'load_advert_callback');
add_action('wp_ajax_nopriv_load_advert', 'load_advert_callback');

function load_advert_callback(){
  $id = intval($_POST['id']);
  $post = get_post($id);
  if(!$post) wp_die();
  echo '<h2>'.$post->post_title.'</h2>';
  echo '<div>'.apply_filters('the_content', $post->post_content).'</div>';
  wp_die();
}

add_action('wp_ajax_raise_ad', 'raise_ad_callback');
add_action('wp_ajax_nopriv_raise_ad', 'raise_ad_callback');

function raise_ad_callback(){
  if(!is_user_logged_in()) wp_die('Login required');
  $id = intval($_POST['id']);
  update_post_meta($id, 'is_raised', time());
  echo 'ok';
  wp_die();
}

add_shortcode('adverts_popup', function(){
  $q = new WP_Query(['post_type'=>'advert','posts_per_page'=>20]);
  ob_start(); ?>
  <div class="ads-grid">
  <?php while($q->have_posts()): $q->the_post(); ?>
    <div class="ad-card">
      <h3><?php the_title(); ?></h3>
      <button class="open-ad" data-id="<?php the_ID(); ?>">Смотреть</button>
      <button class="raise-ad" data-id="<?php the_ID(); ?>">Поднять</button>
    </div>
  <?php endwhile; wp_reset_postdata(); ?>
  </div>

  <div id="ad-modal">
    <div class="ad-box">
      <span id="ad-close">×</span>
      <div id="ad-content">Загрузка...</div>
    </div>
  </div>

  <script>
  jQuery(function($){
    $('.open-ad').click(function(){
      let id=$(this).data('id');
      $('#ad-modal').fadeIn();
      $.post('<?php echo admin_url("admin-ajax.php"); ?>',{action:'load_advert',id:id},function(res){
        $('#ad-content').html(res);
      });
    });
    $('.raise-ad').click(function(){
      let id=$(this).data('id');
      $.post('<?php echo admin_url("admin-ajax.php"); ?>',{action:'raise_ad',id:id},function(){
        alert('Поднято');
      });
    });
    $('#ad-close,#ad-modal').click(function(e){
      if(e.target.id==='ad-modal'||e.target.id==='ad-close') $('#ad-modal').fadeOut();
    });
  });
  </script>
<?php return ob_get_clean();});

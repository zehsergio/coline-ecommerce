<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/** @var array $image_ids */
/** @var WC_Product $product */

$show_side = true;
if ( isset($settings['show_side_previews']) ) {
  $show_side = ($settings['show_side_previews'] === 'yes');
}
?>
<?php
$h_d = isset($settings['max_height_desktop']) ? intval($settings['max_height_desktop']) : 560;
$h_m = isset($settings['max_height_mobile']) ? intval($settings['max_height_mobile']) : 420;
$style_vars = '--rbpdp-h-d: ' . $h_d . 'px; --rbpdp-h-m: ' . $h_m . 'px;';
?>
<div class="rbpdp" data-widget-id="<?php echo esc_attr($widget_id); ?>" style="<?php echo esc_attr($style_vars); ?>">
  <?php if ( $show_side ) : ?>
  <div class="rbpdp-gallery rbpdp-gallery--previous" aria-hidden="true">
    <?php foreach ( $image_ids as $img_id ) : ?>
      <div class="rbp-slide" data-media-id="<?php echo esc_attr($img_id); ?>">
        <?php echo wp_get_attachment_image( $img_id, 'large', false, ['class'=>'rbp-img','alt'=>get_post_meta($img_id,'_wp_attachment_image_alt',true)] ); ?>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="rbpdp-gallery rbpdp-gallery--main">
    <?php foreach ( $image_ids as $img_id ) : ?>
      <div class="rbp-slide" data-media-id="<?php echo esc_attr($img_id); ?>">
        <?php echo wp_get_attachment_image( $img_id, 'full', false, ['class'=>'rbp-img','alt'=>get_post_meta($img_id,'_wp_attachment_image_alt',true)] ); ?>
      </div>
    <?php endforeach; ?>
    <div class="rbp-controls">
      <button type="button" class="rbp-btn rbp-btn-prev" aria-label="<?php esc_attr_e('Previous','rb-flickity-pdp'); ?>">←</button>
      <button type="button" class="rbp-btn rbp-btn-next" aria-label="<?php esc_attr_e('Next','rb-flickity-pdp'); ?>">→</button>
    </div>
  </div>

  <?php if ( $show_side ) : ?>
  <div class="rbpdp-gallery rbpdp-gallery--next" aria-hidden="true">
    <?php foreach ( $image_ids as $img_id ) : ?>
      <div class="rbp-slide" data-media-id="<?php echo esc_attr($img_id); ?>">
        <?php echo wp_get_attachment_image( $img_id, 'large', false, ['class'=>'rbp-img','alt'=>get_post_meta($img_id,'_wp_attachment_image_alt',true)] ); ?>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<div class="table product-info quickview-info hidden-xs" style="height:<?php echo $img_h; ?>px; width:980px;">
     
     <div class="table-cell left" style="padding-bottom:0;">
     
     <?php if ($thumb || $images) { ?>
     <div class="image-area" id="gallery" style="position:relative;">
            
        <?php if ($thumb) { ?>
        <div class="main-image qv_image carousel" style="width:<?php echo $img_w; ?>px;">
        <img src="<?php echo $thumb; ?>" title="<?php echo $heading_title; ?>" alt="<?php echo $heading_title; ?>" />
        
        <?php if ($images) { ?>
        <?php foreach ($images as $image) { ?>
        <img src="<?php echo $image['thumb']; ?>" title="<?php echo $heading_title; ?>" alt="<?php echo $heading_title; ?>" />
        <?php } ?>
        <?php } ?>
        
        </div>
        <?php } ?>
       
            
     </div> <!-- .table-cell.left ends -->
     <?php } ?>
      
     </div> <!-- .image-area ends -->

    <div class="table-cell w100 right">
	<div class="inner">
    
    <div class="product-h1">
    <h1><?php echo $heading_title; ?></h1>
    </div>
    
    <?php if ($review_status && ($review_qty > 0)) { ?>
    <div class="rating">
    <span class="rating_stars rating r<?php echo $rating; ?>">
    <i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>
    </span>
    </div>
    <span class="review_link">(<a class="hover_uline to_reviews" href="<?php echo $product_href; ?>"><?php echo $reviews; ?></a>)</span>
	<?php } ?>

    <?php if ($price) { ?>
      <ul class="list-unstyled price">
        <?php if (!$special) { ?>
        <li><?php echo $price; ?></li>
        <?php } else { ?>
        <li><span class="price-old"><?php echo $price; ?></span><?php echo $special; ?></li>
        <?php } ?>
      </ul>
        
        <?php if ($discounts) { ?>
        <p class="discount">
        <?php foreach ($discounts as $discount) { ?>
        <span><?php echo $discount['quantity']; ?><?php echo $text_discount; ?><i class="price"><?php echo $discount['price']; ?></i></span>
        <?php } ?>
        </p>
        <?php } ?>
      
      <?php } ?> <!-- if price ends -->
      
      
      <?php if ($meta_description_status && $meta_description) { ?>
      <p class="meta_description"><?php echo $meta_description; ?></p>
      <?php } ?>
      
      
      <div id="product">
            
                        
           <div class="form-group buy catalog_hide">
		<?php if ($options || $recurrings) { ?>
          <a href="<?php echo $product_href; ?>" class="btn btn-primary"><?php echo $basel_text_select_option; ?></a>
          <?php } else { ?>
          <input step="1" min="<?php echo $minimum; ?>" type="number" name="quantity" value="<?php echo $minimum; ?>" class="input-quantity" id="input-quantity" class="form-control" />
          <input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />
          <button type="button" onclick="cart.add('<?php echo $product_id; ?>', $('#input-quantity').val(), 'qv')" class="btn btn-primary"><?php if (($qty < 1) && ($stock_badge_status)) { ?><?php echo $basel_text_out_of_stock; ?><?php } else { ?><?php echo $button_cart; ?><?php } ?></button>
          <?php } ?>
          
          <a href="<?php echo $product_href; ?>" class="btn btn-outline details"><?php echo $basel_text_view_details; ?></a>
          
            </div>
            <?php if ($minimum > 1) { ?>
            <div class="alert alert-sm alert-info"><i class="fa fa-info-circle"></i> <?php echo $text_minimum; ?></div>
            <?php } ?>
          
          </div><!-- #product ends -->

    
    <div class="info-holder">
    
      <?php if ($price && $tax) { ?>
      <p class="info p-tax"><b><?php echo $text_tax; ?></b> <?php echo $tax; ?></p>
      <?php } ?>
      
      <?php if ($price && $points) { ?>
      <p class="info"><b><?php echo $text_points; ?></b> <?php echo $points; ?></p>
      <?php } ?>
      
      <p class="info <?php if ($qty > 0) { echo "in_stock"; } ?>"><b><?php echo $text_stock; ?></b> <?php echo $stock; ?></p>
      
      <?php if ($manufacturer) { ?>
      <p class="info"><b><?php echo $text_manufacturer; ?></b> <a class="hover_uline" href="<?php echo $manufacturers; ?>"><?php echo $manufacturer; ?></a></p>
      <?php } ?>
      
      <p class="info"><b><?php echo $text_model; ?></b> <?php echo $model; ?></p>
      
      <?php if ($reward) { ?>
      <p class="info"><b><?php echo $text_reward; ?></b> <?php echo $reward; ?></p>
      <?php } ?>
      
      
      <?php if ($basel_share_btn) { ?>
      <p class="info share"><b>Share:</b> 
        <a class="single_share fb_share external" rel="nofollow"><i class="fa fa-facebook"></i></a>
        <a class="single_share twitter_share external" rel="nofollow"><i class="fa fa-twitter"></i></a>
        <a class="single_share google_share external" rel="nofollow"><i class="icon-google-plus"></i></a>
        <a class="single_share pinterest_share external" rel="nofollow"><i class="fa fa-pinterest"></i></a>
        <a class="single_share vk_share external" rel="nofollow"><i class="fa fa-vk"></i></a>
      </p>
      <?php } ?>
     
     </div> <!-- .info-holder ends -->
     
	 </div> <!-- .inner ends -->
     </div> <!-- .table-cell.right ends -->
    
    


<script>
$('.qv_image').slick({
<?php if ($direction == 'rtl') { ?>
rtl: true,
<?php } ?>
prevArrow: "<a class=\"arrow-left within icon-arrow-left\"></a>",
nextArrow: "<a class=\"arrow-right within icon-arrow-right\"></a>",
arrows:true
});
// Sharing buttons
var share_url = encodeURIComponent('<?php echo $product_href; ?>');
var page_title = '<?php echo $heading_title ?>';
<?php if ($thumb) { ?>
var thumb = '<?php echo $thumb ?>';
<?php } ?>
$('.fb_share').attr("href", 'https://www.facebook.com/sharer/sharer.php?u=' + share_url + '');
$('.twitter_share').attr("href", 'https://twitter.com/intent/tweet?source=' + share_url + '&text=' + page_title + ': ' + share_url + '');
$('.google_share').attr("href", 'https://plus.google.com/share?url=' + share_url + '');
$('.pinterest_share').attr("href", 'http://pinterest.com/pin/create/button/?url=' + share_url + '&media=' + thumb + '&description=' + page_title + '');
$('.vk_share').attr("href", 'http://vkontakte.ru/share.php?url=' + share_url + '');

// Open external links in new tab //
$('a.external').on('click',function(e){
e.preventDefault();
window.open($(this).attr('href'));
});
</script>
</div> <!-- .product-info ends -->
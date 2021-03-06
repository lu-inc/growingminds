<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<!-- Meta that will help -->
<meta name="verify-v1" content="y615xP+SXRgM3ehxIogDxsEomEE33fZyK5hUwpXsmv8=" />
<meta HTTP-EQUIV="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="keywords" content=" Tutoring, Orton-Gillingham, one on one, LD, Oakville, Kingsway, Kingsway Village, Growing Minds, Tutor, Academic, Academic Therapy, Learning Disability, Learning Disabilities, Enrichment, Gifted,  Psychologists and Special Ed Consultants, physician's referral, tax-deductible, intellectually-superior, learning history, Psycho-Educational Assessment, emotional needs, therapeutic, developmental needs, school psychologists,  standardized testing, study skills, studying skills, math, reading and writing, academic performance, Emotional well-being, team, multi-disciplinary team, self-esteem">
<meta name="revisit-after" content="10 days">
<meta name="copyright" content="Copyright © 2002-2013 The content contained within this site, is owned by Growing Minds Inc. Reproduction, transmission or distribution of all or any part of this web site without the prior written authorization is prohibited.">
<meta name="robots" content="FOLLOW,INDEX">
<link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>
<meta name="google-site-verification" content="IEPsFlXx6kOAKRN-WUhLeoi-Y4LcKBSf0wd1xCmcENo" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<meta charset="<?php bloginfo('charset'); ?>" />
<title>
<?php
            /*
             * Print the <title> tag based on what is being viewed.
             */
            global $page, $paged;
            wp_title('|', true, 'right');
// Add the blog name.
            bloginfo('name');
// Add the blog description for the home/front page.
            // $site_description = get_bloginfo('description', 'display');
            // if ($site_description && ( is_home() || is_front_page() ))
            //     echo " | $site_description";
// Add a page number if necessary:
            if ($paged >= 2 || $page >= 2)
                echo ' | ' . sprintf(__('Page %s', 'regal'), max($paged, $page));
            ?>
</title>
<?php if (is_front_page()) { ?>
<?php if (inkthemes_get_option('inkthemes_keyword') != '') { ?>
<meta name="keywords" content="<?php echo inkthemes_get_option('inkthemes_keyword'); ?>" />
<?php } else {
                
            } ?>
<?php if (inkthemes_get_option('inkthemes_description') != '') { ?>

<?php } else {
                
            } ?>
<?php if (inkthemes_get_option('inkthemes_author') != '') { ?>
<meta name="author" content="<?php echo inkthemes_get_option('inkthemes_author'); ?>" />
<?php } else {
                
            } ?>
<?php } ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo('stylesheet_url'); ?>" />
<?php
        /* We add some JavaScript to pages with the comment form
         * to support sites with threaded comments (when in use).
         */
        if (is_singular() && get_option('thread_comments'))
           wp_enqueue_script('comment-reply');
        /* Always have wp_head() just before the closing </head>
         * tag of your theme, or you will break many plugins, which
         * generally use this hook to add elements to <head> such
         * as styles, scripts, and meta tags.
         */
        wp_head();
        ?>
<!--[if gte IE 9]>
        <script type="text/javascript">
        Cufon.set('engine', 'canvas');
        </script>
        <![endif]--> 
</head>
<body <?php body_class(); ?> id="regal_body" style="<?php if (inkthemes_get_option('inkthemes_bodybg') !='') { ?> background: fixed url('<?php echo inkthemes_get_option('inkthemes_bodybg'); ?>') <?php } ?>">
<!--Start Container-->
<div class="container">
  <div class="sixteen columns">
    <!--Start Main Container-->
      <!--Start Header-->
      <div class="header shadow-box">
          <!--Start Logo-->
          <div class="main-logo">
           <a href="<?php echo home_url(); ?>"><img src="<?php if (inkthemes_get_option('inkthemes_logo') != '') { ?><?php echo inkthemes_get_option('inkthemes_logo'); ?><?php } else { ?><?php echo get_template_directory_uri(); ?>/images/logo.png<?php } ?>" alt="<?php bloginfo('name'); ?>" /></a>
          </div>
          <!--End Logo-->
         <div class="logo">
             <?php inkthemes_nav(); ?>
        <!--Start Social Logo-->
                <div class="social-links">
      <ul class="social_logos">
          <?php if (inkthemes_get_option('inkthemes_facebook') != '') { ?>
                            <li><a href="<?php echo inkthemes_get_option('inkthemes_facebook'); ?>" title="Share On Facebook"><img src="<?php echo get_template_directory_uri(); ?>/images/fb.png" /></a></li>
                        <?php } ?>  
          <?php if (inkthemes_get_option('inkthemes_twitter') != '') { ?>
                            <li><a href="<?php echo inkthemes_get_option('inkthemes_twitter'); ?>" title="Follow Us On Twitter"><img src="<?php echo get_template_directory_uri(); ?>/images/tw.png" /></a></li>
                        <?php } ?> 
          <?php if (inkthemes_get_option('inkthemes_rss') != '') { ?>
                            <li><a href="<?php echo inkthemes_get_option('inkthemes_rss'); ?>" title="Rss Feed"><img src="<?php echo get_template_directory_uri(); ?>/images/rss.png" /></a></li>
                        <?php } ?>  
            <?php if (inkthemes_get_option('inkthemes_google') != '') { ?>
                            <li><a href="<?php echo inkthemes_get_option('inkthemes_google'); ?>" title="Gtalk"><img src="<?php echo get_template_directory_uri(); ?>/images/sms.png" /></a></li>
                        <?php } ?>       
        </ul>
      </div>  
        <div class="eight columns omega">
        <div class="top-search">
          <?php get_search_form(); ?>
        </div>
        </div>
        </div>
        <!--End Social Logo-->
        <div class="clear"></div>
        <div class="ten columns omega">
    <!--Start Menu_Wrapper-->
    <div class="menu_wrapper">
   
    </div>
    <!--End Menu Wrapper-->
        </div>
      </div>
      <!--End Header-->
      <div class="clear"></div>
<?php
/*
Plugin Name: WP Twitter Ticker Widget
Plugin URI: 
Description: WP Twitter Ticker Widget
Version: 1.0
Author: Paul Lunneberg
Author URI: http://pablosbrain.com
License: GPLv2
*/
function my_script_loader() {    
    wp_enqueue_script('marquee', '/wp-content/plugins/wp-twitter-ticker-widget/jquery.marquee.min.js');
}
class WP_Twitter_Ticker_Widget extends WP_Widget {
    function __construct() {
        parent::__construct(false, $name = 'WP Twitter Ticker Widget', array( 'description' => 'A Twitter Feed News Ticker.' ) );
        add_action( 'load-widgets.php', array(&$this, 'my_custom_load') );
    }

    function my_custom_load() {    
        wp_enqueue_script("jquery");
        wp_enqueue_style( 'wp-color-picker' );        
        wp_enqueue_script( 'wp-color-picker' );    
    }
	// Displays the widget form in the admin panel
	function form( $instance ) {
        $screen_name = esc_attr( $instance['screen_name'] );
        $php_timezone = (esc_attr( $instance['php_timezone'] )!="" ? esc_attr( $instance['php_timezone'] ) :'America/Chicago');
        $num_tweets = esc_attr( $instance['num_tweets'] );
        $tweet_background_color = esc_attr( $instance['tweet_background_color'] );
        $tweet_text_color = esc_attr( $instance['tweet_text_color'] );
        $links_color = esc_attr( $instance['links_color'] );
        $shell_border_color  = esc_attr( $instance['shell_border_color'] );
        $consumer_key         = $instance['consumer_key'];
		$consumer_secret      = $instance['consumer_secret'];
		$access_token         = $instance['access_token'];
		$access_token_secret   = $instance['access_token_secret'];
        $twit_date_format = (esc_attr( $instance['twit_date_format'] )!="" ? esc_attr( $instance['twit_date_format'] ) :'g:i A M jS');
        $twit_cache_min = esc_attr( $instance['twit_cache_min'] );
        $insert_after = esc_attr( $instance['insert_after'] );
        $insert_before = esc_attr( $instance['insert_before'] );
?>
        <p>
            This widget does not render in the normal widget location. It is inserted via javascript into a location on your web page.
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'insert_before' ); ?>">jQuery <a href="http://api.jquery.com/insertbefore/">.insertBefore()</a> (jQuery Selector / Blank = widget default location):</label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'insert_before' ); ?>" name="<?php echo $this->get_field_name( 'insert_before' ); ?>" type="text" value="<?php echo $insert_before; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'insert_after' ); ?>">jQuery <a href="http://api.jquery.com/insertafter/">.insertAfter()</a> (jQuery Selector / Blank = widget default location):</label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'insert_after' ); ?>" name="<?php echo $this->get_field_name( 'insert_after' ); ?>" type="text" value="<?php echo $insert_after; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'screen_name' ); ?>">Screen name:</label>
            @<input class="widefat" id="<?php echo $this->get_field_id( 'screen_name' ); ?>" name="<?php echo $this->get_field_name( 'screen_name' ); ?>" type="text" value="<?php echo $screen_name; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'php_timezone' ); ?>">Timezone name (ie "America/Chicago"):</label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'php_timezone' ); ?>" name="<?php echo $this->get_field_name( 'php_timezone' ); ?>" type="text" value="<?php echo $php_timezone; ?>" />
        </p>
        
        <hr />
        
        <b>Twitter keys (You'll need to visit <a href="https://dev.twitter.com">https://dev.twitter.com</a> and register to get these.</b>
        <p>
            <label for="<?php echo $this->get_field_id( 'consumer_key' ); ?>">Consumer/API Key:</label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'consumer_key' ); ?>" name="<?php echo $this->get_field_name( 'consumer_key' ); ?>" type="text" value="<?php echo $consumer_key; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'consumer_secret' ); ?>">Consumer/API Secret:</label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'consumer_secret' ); ?>" name="<?php echo $this->get_field_name( 'consumer_secret' ); ?>" type="text" value="<?php echo $consumer_secret; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'access_token' ); ?>">Access Token:</label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'access_token' ); ?>" name="<?php echo $this->get_field_name( 'access_token' ); ?>" type="text" value="<?php echo $access_token; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'access_token_secret' ); ?>">Access Token Secret:</label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'access_token_secret' ); ?>" name="<?php echo $this->get_field_name( 'access_token_secret' ); ?>" type="text" value="<?php echo $access_token_secret; ?>" />
        </p>
        
        <hr />
        
        <p>
            <label for="<?php echo $this->get_field_id( 'num_tweets' ); ?>">Number of Tweets:</label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'num_tweets' ); ?>" name="<?php echo $this->get_field_name( 'num_tweets' ); ?>" type="text" value="<?php echo $num_tweets; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'twit_cache_min' ); ?>">Cache Duration (Minutes / Minimum 5 min):</label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'twit_cache_min' ); ?>" name="<?php echo $this->get_field_name( 'twit_cache_min' ); ?>" type="text" value="<?php echo $twit_cache_min; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'twit_date_format' ); ?>">Date Format ('g:i A M jS'):</label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'twit_date_format' ); ?>" name="<?php echo $this->get_field_name( 'twit_date_format' ); ?>" type="text" value="<?php echo $twit_date_format; ?>" />
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id( 'shell_border_color' ); ?>">Border Color :</label><br />
            <input class="color-picker" id="<?php echo $this->get_field_id( 'shell_border_color' ); ?>" name="<?php echo $this->get_field_name( 'shell_border_color' ); ?>" type="text" value="<?php echo $shell_border_color; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'tweet_background_color' ); ?>">Background Color:</label><br />
            <input class="color-picker" id="<?php echo $this->get_field_id( 'tweet_background_color' ); ?>" name="<?php echo $this->get_field_name( 'tweet_background_color' ); ?>" type="text" value="<?php echo $tweet_background_color; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'tweet_text_color' ); ?>">Text Color:</label><br />
            <input class="color-picker" id="<?php echo $this->get_field_id( 'tweet_text_color' ); ?>" name="<?php echo $this->get_field_name( 'tweet_text_color' ); ?>" type="text" value="<?php echo $tweet_text_color; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'links_color' ); ?>">Link Color:</label><br />
            <input class="color-picker" id="<?php echo $this->get_field_id( 'links_color' ); ?>" name="<?php echo $this->get_field_name( 'links_color' ); ?>" type="text" value="<?php echo $links_color; ?>" />
        </p>
        <script>
            var elems = jQuery('#widgets-right .color-picker, .inactive-sidebar .color-picker');
            var widget_id = 'Twitter_Widget_Ticker';
            jQuery(document).ready(function($) {
                elems.wpColorPicker();
            }).ajaxComplete(function(e, xhr, settings) {
                if( settings.data.search('action=save-widget') != -1 && settings.data.search('id_base=' + widget_id) != -1 ) {  
                    elems.wpColorPicker();
                }
            });
        </script>
<?php
}
function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance = $new_instance;
    //$instance['background_color'] = $new_instance['background_color'];
    return $instance;
}
	// Renders Widget
function widget( $args, $instance ) {
    echo $args['before_widget'];
    require_once(dirname(__FILE__).'/tweetsticker.php');
    display_latest_tweets($instance['screen_name'], $instance);
?>
    <div class='tweettickercontainer'>
        <div class='tweettickertweet'>
            <div class='tweettickertweetcontent'>
            </div>
        </div>
    </div>
	<style>
		.wp-tweet-ticker-tweet {
            display: none;
        }
		.tweettickercontainer {
            border: solid 1px <?php echo $instance['shell_border_color']; ?>;
			padding: 0px;
			overflow: hidden;
			font-size: 11px;
			white-space: nowrap;
			background-color: <?php echo $instance['tweet_background_color']; ?>;
        }
		.tweettickertweetcontent {
            overflow: hidden;
            margin: 0px;
			color: <?php echo $instance['tweet_text_color']; ?>;
			white-space:nowrap;
			padding: 2px;
		}
		.tweettickertweetcontent .wp-tweet-ticker-tweet-date {
            margin-left: 10px;
        }
		.tweettickertweetcontent a {
            font-weight: bolder;
			color: <?php echo $instance['links_color']; ?>;
        }
		.tweetblocks {
            display: inline-block;
        }
    </style>
    <script type="text/javascript">
        var tweetnum = 0;
        var tweettotal = jQuery('.wp-tweet-ticker-tweet').length;
        console.log(tweettotal);
        var randtweetnum = Math.floor((Math.random() * tweettotal) + 1);
        var tweetuserhtml = "<a href='twitter.com/<?php echo $instance['screen_name']; ?>/'>@<?php echo $instance['screen_name']; ?></a> : ";
        var marqueeoptions = {
            //speed in milliseconds of the marquee
            duration: 15000,
            //gap in pixels between the tickers
            gap: 50,
            pauseOnHover: true,
            //time in milliseconds before the marquee will start animating
            delayBeforeStart: 0,
            //'left' or 'right'
            direction: 'left',
            //true or false - should the marquee be duplicated to show an effect of continues flow
            duplicated: false
        };
        jQuery(function() {
<?php if($instance['insert_after'] != ""){ ?>
            jQuery( ".tweettickercontainer" ).insertAfter( jQuery( "<?php echo $instance['insert_after']; ?>" ) );
<?php }elseif($instance['insert_before'] != ""){ ?>
            jQuery( ".tweettickercontainer" ).insertBefore( jQuery( "<?php echo $instance['insert_before']; ?>" ) );
<?php } ?>
            jQuery( ".tweettickercontainer" ).hide();
            randtweetnum = Math.floor((Math.random() * tweettotal) + 1);
            jQuery('.tweettickertweetcontent').html(tweetuserhtml+jQuery('.wp-tweet-ticker-tweet:nth-child('+randtweetnum+')').html());
    
            jQuery( ".tweettickercontainer" ).slideDown(400, function(){
                jQuery('.tweettickertweetcontent').bind('finished', function(){
                    //Change text to something else after first loop finishes
                    jQuery(this).marquee('destroy');
                    //Load new content using Ajax and update the marquee container
                    randtweetnum = Math.floor((Math.random() * tweettotal) + 1);
                    jQuery(this).html(tweetuserhtml+jQuery('.wp-tweet-ticker-tweet:nth-child('+randtweetnum+')').html()).marquee(marqueeoptions);
                }).marquee(marqueeoptions);
            });
	   });
    </script>
	<?php
		echo $args['after_widget'];
	}
};


add_action( 'wp_enqueue_scripts', 'my_script_loader' );
add_action( 'widgets_init', create_function( '', 'return register_widget( "WP_Twitter_Ticker_Widget" );' ) );
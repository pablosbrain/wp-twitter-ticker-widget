<?php

	/**
	 * Twitter feed which uses twitteroauth for authentication
	 * 
	 * @version	1.0
	 * @author	Andrew Biggart
	 * @link	https://github.com/andrewbiggart/latest-tweets-php-o-auth/
	 * 
	 * Notes:
	 * Caching is employed because Twitter only allows their RSS and json feeds to be accesssed 150
	 * times an hour per user client.
	 * --
	 * Dates can be displayed in Twitter style (e.g. "1 hour ago") by setting the 
	 * $twitter_style_dates param to true.
	 *
	 * You will also need to register your application with Twitter, to get your keys and tokens.
	 * You can do this here: (https://dev.twitter.com/).
	 *
	 * Don't forget to add your username to the bottom of the script.
	 * 
	 * Credits:
	 ***************************************************************************************
	 * Initial script before API v1.0 was retired
	 * http://f6design.com/journal/2010/10/07/display-recent-twitter-tweets-using-php/
	 *
	 * Which includes the following credits
	 * Hashtag/username parsing based on: http://snipplr.com/view/16221/get-twitter-tweets/
	 * Feed caching: http://www.addedbytes.com/articles/caching-output-in-php/
	 * Feed parsing: http://boagworld.com/forum/comments.php?DiscussionID=4639
	 ***************************************************************************************
	 *
	 ***************************************************************************************
	 * Authenticating a User Timeline for Twitter OAuth API V1.1
	 * http://www.webdevdoor.com/php/authenticating-twitter-feed-timeline-oauth/
	 ***************************************************************************************
	 *
	 ***************************************************************************************
	 * Twitteroauth which has been used for the authentication process
	 * https://github.com/abraham/twitteroauth
	 ***************************************************************************************
	 *
	 *
	**/
 
	// Session start
	session_start(); 
	
	// Set timezone. (Modify to match your timezone) If you need help with this, you can find it here. (http://php.net/manual/en/timezones.php)
	//date_default_timezone_set('Europe/London');
	//date_default_timezone_set('America/Chicago');
	date_default_timezone_set($instance['php_timezone']);

	
	// Require TwitterOAuth files. (Downloadable from : https://github.com/abraham/twitteroauth)
	require_once(dirname(__FILE__)."/twitteroauth/twitteroauth/twitteroauth.php");
	
	// Function to authenticate app with Twitter.
	function getConnectionWithAccessToken($cons_key, $cons_secret, $oauth_token, $oauth_token_secret) {
	  $connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
	  return $connection;
	}
	
	// Function to display the latest tweets.
	function display_latest_tweets(
		
		// Function parameters.
		$twitter_user_id,
		$instance,
		$cache_file          = 'tweets.txt',  // Change this to the path of your cache file. (Default : ./tweets.txt)
		$tweets_to_display   = 5, // Number of tweets you would like to display. (Default : 5)
		$ignore_replies      = false,           // Ignore replies from the timeline. (Default : false)
		$include_rts         = false,           // Include retweets. (Default : false)
		$twitter_wrap_open   = '<div class="wp-tweet-ticker">',
		$twitter_wrap_close  = '</div>',
		$tweet_wrap_open     = '<div class="wp-tweet-ticker-tweet">',
		$tweet_wrap_close    = '</div>',
		$meta_wrap_open      = '<span class="wp-tweet-ticker-tweet-date">',
		$meta_wrap_close     = '</span>',
		$date_format         = 'g:i A M jS',    // Date formatting. (http://php.net/manual/en/function.date.php)
		$twitter_style_dates = true
			
    ){           // Twitter style days. [about an hour ago] (Default : true)
			
        $cache_file = dirname(__FILE__)."/".$cache_file;
        $date_format = $instance['twit_date_format'];
        $tweets_to_display = (intval($instance['num_tweets']) > 0 ? intval($instance['num_tweets']) : $tweets_to_display);
		// Twitter keys (You'll need to visit https://dev.twitter.com and register to get these.
		$consumerkey         = ($instance['consumer_key'] ?: '');  //"xxxxxxxxxxxxxxxxxxxxxxxxxxx";
		$consumersecret      = ($instance['consumer_secret'] ?: ''); //"xxxxxxxxxxxxxxxxxxxxxxxxxxx";
		$accesstoken         = ($instance['access_token'] ?: ''); //"xxxxxxxxxxxxxxxxxxxxxxxxxxx";
		$accesstokensecret   = ($instance['access_token_secret'] ?: ''); //"xxxxxxxxxxxxxxxxxxxxxxxxxxx";
		//echo $consumerkey."<br />".$consumersecret."<br />".$accesstoken."<br />".$accesstokensecret."<br />";
		// Seconds to cache feed (Default : 1 minute).
		$cachetime           = 60*max(5,intval($instance['twit_cache_min']));//60*3;
		
		// Time that the cache was last updtaed.
		$cache_file_created  = ((file_exists($cache_file))) ? filemtime($cache_file) : 0;
 		//echo "cache_file_created: ".$cache_file_created."<br />";
		// A flag so we know if the feed was successfully parsed.
		$tweet_found         = false;
		
		// Show cached version of tweets, if it's less than $cachetime.
		if (time() - $cachetime < $cache_file_created) {
		//if (false == true) {
	 		$tweet_found = true;
			// Display tweets from the cache.
			readfile($cache_file);		 
		} else {
		
		// Cache file not found, or old. Authenticae app.
		$connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);
		//echo "connection : ";
		//echo var_dump($connection);
			if($connection){
				// Get the latest tweets from Twitter
 				$get_tweets = $connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$twitter_user_id."&count=".$tweets_to_display."&include_rts=".$include_rts."&exclude_replies=".$ignore_replies);
		//echo "get_tweets : ";
		//echo var_dump($get_tweets);				
				// Error check: Make sure there is at least one item.
				if (count($get_tweets)) {
 					
					// Define tweet_count as zero
					$tweet_count = 0;
 
					// Start output buffering.
					ob_start();
 
					// Open the twitter wrapping element.
					$twitter_html = $twitter_wrap_open;
 
					// Iterate over tweets.
					foreach($get_tweets as $tweet) {
						
							$tweet_found = true;
							$tweet_count++;
 							$tweet_desc = $tweet->text;
							// Add hyperlink html tags to any urls, twitter ids or hashtags in the tweet.
							$tweet_desc = preg_replace("/((http)+(s)?:\/\/[^<>\s]+)/i", "<a href=\"\\0\" target=\"_blank\">\\0</a>", $tweet_desc );
							$tweet_desc = preg_replace("/[@]+([A-Za-z0-9-_]+)/", "<a href=\"http://twitter.com/\\1\" target=\"_blank\">\\0</a>", $tweet_desc );
							$tweet_desc = preg_replace("/[#]+([A-Za-z0-9-_]+)/", "<a href=\"http://twitter.com/search?q=%23\\1\" target=\"_blank\">\\0</a>", $tweet_desc );
 
 							// Convert Tweet display time to a UNIX timestamp. Twitter timestamps are in UTC/GMT time.
							$tweet_time = strtotime($tweet->created_at);	
 							if ($twitter_style_dates){
								// Current UNIX timestamp.
								$current_time = time();
								$time_diff = abs($current_time - $tweet_time);
								switch ($time_diff) 
								{
									case ($time_diff < 60):
										$display_time = $time_diff.' seconds ago';                  
										break;      
									case ($time_diff >= 60 && $time_diff < 3600):
										$min = floor($time_diff/60);
										$display_time = $min.' minutes ago';                  
										break;      
									case ($time_diff >= 3600 && $time_diff < 86400):
										$hour = floor($time_diff/3600);
										$display_time = 'about '.$hour.' hour';
										if ($hour > 1){ $display_time .= 's'; }
										$display_time .= ' ago';
										break;          
									default:
										$display_time = date($date_format,$tweet_time);
										break;
								}
 							} else {
 								$display_time = date($date_format,$tweet_time);
 							}
 
							// Render the tweet.
							$twitter_html .= $tweet_wrap_open.html_entity_decode($tweet_desc).$meta_wrap_open.'<a href="http://twitter.com/'.$twitter_user_id.'">'.$display_time.'</a>'.$meta_wrap_close.$tweet_wrap_close;
 
						// If we have processed enough tweets, stop.
						if ($tweet_count >= $tweets_to_display){
							break;
						}
 
					}
 
					// Close the twitter wrapping element.
					$twitter_html .= $twitter_wrap_close;
					echo $twitter_html;
 
					// Generate a new cache file.
					$file = fopen($cache_file, 'w');
 
					// Save the contents of output buffer to the file, and flush the buffer. 
					fwrite($file, ob_get_contents()); 
					fclose($file); 
					ob_end_flush();
					
				}
				
			}
			
		}
		
	}
	
	// Display latest tweets. (Modify username to your Twitter handle)
	//display_latest_tweets('andrew_biggart');
?>

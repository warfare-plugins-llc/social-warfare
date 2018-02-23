<?php

/**
 * A class of functions used to render shortcodes for the user
 *
 * The SWP_Shortcodes Class used to add our shorcodes to WordPress
 * registry of registered functions.
 *
 * @package   SocialWarfare\Frontend-Output
 * @copyright Copyright (c) 2018, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0
 * @since     2.4.0 | 19 FEB 2018 | Refactored into a class-based system
 *
 */
class SWP_Shortcode {
	/**
	 * Constructs a new SWP_Shortcodes instance
	 *
	 * This function is used to add our shortcodes to WordPress' registry of
	 * shortcodes and to map our functions to each one.
	 *
	 * @since  2.4.0
	 * @param  none
	 * @return none
	 *
	 */
    public function __construct() {
		add_shortcode( 'social_warfare', array($this, 'buttons_shortcode') );
		add_shortcode( 'socialWarfare', array($this, 'buttons_shortcode_legacy' ) );
		add_shortcode( 'total_shares', array ($this, 'post_total_shares') );
		add_shortcode( 'sitewide_shares', array ($this, 'sitewide_total_shares') );
        add_shortcode( 'clickToTweet', array($this, 'click_to_tweet' ) );

	}


	/**
	 * Processing the shortcodes that populate a
	 * set of social sharing buttons directly in a WordPress post.
	 *
	 * This function will accept an array of arguments which WordPress
	 * will create from the shortcode attributes.
	 *
	 * @since  2.4.0
	 * @param  $atts Array An array converted from shortcode attributes.
	 *
	 * 		content: The content for the Social Warfare function to filter. In the case of
	 * 			shortcodes, this will be blank since this isn't a content filter.
	 *
	 * 		where: The buttons are designed to be appended to the content. This default
	 * 			tells the buttons to append after the content. Since shortcodes don't have
	 * 			any content, they'll just produce and return the HTML without any content.
	 * 			This will likely never actually be set by the shortcode, but is necessary
	 * 			for the HTML generator to know what to do.
	 *
	 * 		echo: True echos the HTML to the screen. False returns the HTML as a string.
	 *
	 * @return string The HTML of the Social Warfare buttons.
	 *
	 */
	public function buttons_shortcode( $array ) {
        if(!is_array($array)){
			$array = array();
		}

        // Paramters needed by the social_warfare() function to know how to process this request.
		$array['shortcode'] = true;
		$array['devs'] = true;

		// Set some defaults that are needed by the social_warfare() function
		$defaults = array(
                        'content'	=> false,
			'where'		=> 'after',
			'echo'		=> true,
		);

		// Merge the defaults into the $array that was passed into this function.
		foreach ($defaults as $key => $value) {
			if ( !isset($array[$key]) ) {
				$array[$key] = $value;
			}
		}

		return social_warfare( $array );
	}


	/**
	 * This is the legacy version of the social warfare button
	 * shortcodes. It is used for nothing more than to call the
	 * new version of the function. See above: $this->buttons_shortcode().
	 *
	 * This function will accept an array of arguments which WordPress
	 * will create from the shortcode attributes.
	 *
	 * @since  2.4.0
	 * @param  $atts Array An array converted from shortcode attributes.
	 * @return string The HTML of the Social Warfare buttons.
	 *
	 */
	public function buttons_shortcode_legacy( $settings ) {

            return $this->buttons_shortcode( array() );
	}


	/**
	 * This is used to process the total shares across all tracked
	 * social networks for any given WordPress post.
	 *
	 * This function will accept an array of arguments which WordPress
	 * will create from the shortcode attributes. However, it doesn't actually
	 * use any parameters. It is only included to prevent throwing an error
	 * in the event that someone tries to input a parameter on it.
	 *
	 * @since  2.4.0
	 * @param  $atts Array An array converted from shortcode attributes.
	 * @return string A string of text representing the total shares for the post.
	 *
	 */
	public function post_total_shares( $settings ) {
			$totes = get_post_meta( get_the_ID() , '_totes', true );
			$totes = swp_kilomega( $totes );
			return $totes;
	}


	/**
	 * This is used to process the total shares across all tracked
	 * social networks for all posts across the site as an aggragate count.
	 *
	 * This function will accept an array of arguments which WordPress
	 * will create from the shortcode attributes. However, it doesn't actually
	 * use any parameters. It is only included to prevent throwing an error
	 * in the event that someone tries to input a parameter on it.
	 *
	 * @since  2.4.0
	 * @param  $atts Array An array converted from shortcode attributes.
	 * @return string A string of text representing the total sitewide shares.
	 *
	 */
	public function sitewide_total_shares( $settings ) {
			global $wpdb;
			$sum = $wpdb->get_results( "SELECT SUM(meta_value) AS total FROM $wpdb->postmeta WHERE meta_key = '_totes'" );
			return swp_kilomega( $sum[0]->total );
	}

    /**
     * The function to build the click to tweets
     *
     * @param  array $atts The shortcode key/value attributes.
     * @return string The html of a click to tweet
     */
    function click_to_tweet( $atts ) {
        global $swp_user_options;
        $this->options = $swp_user_options;

    	$url = swp_process_url( get_permalink() , 'twitter' , get_the_ID() );
    	(strpos( $atts['tweet'],'http' ) !== false ? $urlParam = '&url=/' : $urlParam = '&url=' . $url );
    	$atts['tweet'] = rtrim( $atts['tweet'] );

    	$user_twitter_handle = get_post_meta( get_the_ID() , 'swp_twitter_username' , true );

    	if ( ! $user_twitter_handle ) :
    		$user_twitter_handle = $this->options['twitterID'];
    	endif;

    	if ( isset( $atts['theme'] ) && $atts['theme'] != 'default' ) :
    		$theme = $atts['theme'];
    	else :
    		$theme = $this->options['cttTheme'];
    	endif;

    	return '
    		<div class="sw-tweet-clear"></div>
    		<a class="swp_CTT ' . $theme . '" href="https://twitter.com/share?text=' . urlencode( html_entity_decode( $atts['tweet'], ENT_COMPAT, 'UTF-8' ) ) . $urlParam . '' . ($user_twitter_handle ? '&via=' . str_replace( '@','',$user_twitter_handle ) : '') . '" data-link="https://twitter.com/share?text=' . urlencode( html_entity_decode( $atts['tweet'], ENT_COMPAT, 'UTF-8' ) ) . $urlParam . '' . ($user_twitter_handle ? '&via=' . str_replace( '@','',$user_twitter_handle ) : '') . '" rel="nofollow" target="_blank"><span class="sw-click-to-tweet"><span class="sw-ctt-text">' . $atts['quote'] . '</span><span class="sw-ctt-btn">' . __( 'Click To Tweet','social-warfare' ) . '<i class="sw sw-twitter"></i></span></span></a>';
    }

}

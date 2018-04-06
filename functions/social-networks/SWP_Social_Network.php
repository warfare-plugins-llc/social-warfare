<?php

/**
 * SWP_Social_Network
 *
 * This is the class that is used for adding new social networks to the
 * buttons which can be selected on the options page and rendered in the
 * panel of buttons.
 *
 * @since 3.0.0 | 05 APR 2018
 *
 */
class SWP_Social_Network {


	/**
	 * The display name of the social network
	 *
	 * This is the 'pretty name' that users will see. It should generally
	 * reflect the official name of the network according to the way that
	 * network is publicly branded.
	 *
	 * @var string
	 *
	 */
	public $name = '';


	/**
	 * The snake_case name of the social network
	 *
	 * This is 'ugly name' of the network. This a snake_case key used for
	 * the purpose of eliminating spaces so that we can save things in the
	 * database and other such cool things.
	 *
	 * @var string
	 *
	 */
	public $key = '';


	/**
	 * The default state of this network
	 *
	 * This property will determine where the icon appears in the options page
 	 * prior to the user setting and saving it. If true, it will appear in the
 	 * active section. If false, it will appear in the inactive section. Once
 	 * the user has updated/saved their preferences, this property will no
 	 * longer do anything.
	 *
	 * @var bool If true, the button is turned on by default.
	 *
	 */
	public $default = true;


	/**
	 * The premium status of this network
	 *
	 * Whether this button is a premium network. An empty string refers to a
	 * non-premium network. A string containing the key of the premium addon
	 * to which this is a member is used for premium networks. For example,
	 * setting this to 'pro' means that it is a premium network dependant on
	 * the Social Warfare - Pro addon being installed and registered.
	 *
	 * @var string
	 *
	 */
	public $premium = '';


	/**
	 * The active status of this network
	 *
	 * If the user has this network activated on the options page, then this
	 * property will be set to true. If not, it will be set to false.
	 *
	 * @var bool
	 *
	 */
	public $active = false;


	/**
	 * The generated html for the button
	 *
	 * After the first time the HTML is generated, we will store it in this variable
	 * so that when it is needed for the second or third panel on the page, the render
	 * html method will not have to make all the computations again.
	 *
	 * The html will be stored in an array indexed by post ID's. For example $this->html[27]
	 * will contain the HTML for this button that was generated for post with 27 as ID.
	 *
	 * @var array
	 *
	 */
	public $html = array();


	public function add_to_global() {

		global $swp_social_networks;
		$swp_social_networks[$this->key] = $this;

	}


	/**
	 * A method for providing the object with a name.
	 *
	 * @since 3.0.0 | 05 APR 2018 | Created
	 * @param string $value The name of the object.
	 * @return object $this Allows chaining of methods.
	 * @access public
	 *
	 */
	public function set_name( $value ) {

        if ( !is_string( $value )  ||  empty( $value ) ) {
            $this->_throw("Please provide a string for your object's name." );
        }

        $this->name = $value;

        return $this;
    }


	/**
	 * A method for updating this network's default property.
	 *
	 * @since 3.0.0 | 05 APR 2018 | Created
	 * @param bool $value The default status of the network.
	 * @return object $this Allows chaining of methods.
	 * @access public
	 *
	 */
	public function set_default( $value ) {
		if ( !is_bool( $value ) || empty( $value ) ) {
			$this->_throw("Please provide a boolean value for your object's default state." );
		}

		$this->default = $value;

		return $this;
	}


	/**
	 * A method for updating this network's key property.
	 *
	 * @since 3.0.0 | 05 APR 2018 | Created
	 * @param string $value The key for the network.
	 * @return object $this Allows chaining of methods.
	 * @access public
	 *
	 */
	public function set_key( $value ) {

		if ( !is_string( $value ) ||  empty( $value ) ) {
			$this->_throw( 'Please provide a snake_case string for the key value.' );
		}

		$this->key = $value;
		return $this;
	}


	/**
	 * A method for updating this network's premium property.
	 *
	 * @since 3.0.0 | 05 APR 2018 | Created
	 * @param string $value A string corresponding to the key of the dependant premium addon.
	 * @return object $this Allows chaining of methods.
	 * @access public
	 *
	 */
	public function set_premium( $value ) {

		if ( !is_string( $value ) ||  empty( $value ) ) {
			$this->_throw( 'Please provide a string corresponding to the premium addon to which this network depends.' );
		}

		$this->premium = $value;
		return $this;
	}


	/**
	 * A method to return the 'active' status of this network.
	 *
	 * @since 3.0.0 | 06 APR 2018 | Created
	 * @param none
	 * @return bool
	 * @access public
	 *
	 */
	public function is_active() {
		return $this->active;
	}


	/**
	 * A method to set the 'active' status of this network.
	 *
	 * @since 3.0.0 | 06 APR 2018 | Created
	 * @param none
	 * @return none
	 * @access public
	 *
	 */
	public function set_active_state() {
		global $swp_user_options;
		if ( isset( $swp_user_options['order_of_icons'][$this->key] ) ) {
			$this->active = true;
		}
	}


	/**
	 * A method to save the generated HTML. This allows us to not have to
	 * run all of the computations every time. Instead, just reuse the HTML
	 * that was rendered by the method the first time it was created.
	 *
	 * @since  3.0.0 | 06 APR 2018 | Created
	 * @param  string  $html     The string of HTML to save in this property.
	 * @param  int     $post_id  The ID of the post that this belongs to.
	 * @return none
	 * @access public
	 *
	 */
	public function save_html( $html , $post_id ) {
		$this->html[$post_id] = $html;
	}

	
	/**
	 * Show Share Counts?
	 *
	 * A method to determine whether or not share counts need to be shown
	 * while rendering the HTML for this network's button.
	 *
	 * @since  3.0.0 | 06 APR 2018 | Created
	 * @param  array $array The array of data from the buttons panel.
	 * @return bool
	 * @access public
	 *
	 */
	public function show_share_count( $array ) {
		if( !$array['options']['network_shares'] ):
			return false;
		elseif( $array['shares']['total_shares'] < $array['options']['minimum_shares']):
			return false;
		elseif( $array['shares'][$this->key] <= 0 ):
			return false;
		else:
			return true;
		endif;
	}

}

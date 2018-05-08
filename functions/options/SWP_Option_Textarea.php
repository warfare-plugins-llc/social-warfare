<?PHP

/**
 * Used to create input options.
 *
 * This class is used to create each input option needed on the options page.
 *
 * @since  3.0.0   | Created | 02 MAR 2017
 * @access public
 */
class SWP_Option_Textarea extends SWP_Option {


    /**
    * Default
    *
    * The default value for this textarea.
    *
    * @var string $default
    *
    */
    public $default = '';


    /**
    * The required constructor for PHP classes.
    *
    * @param string $name The display name for the toggle.
    * @param string $key The database key for the user setting.
    *
    */
    public function __construct( $name, $key ) {
        parent::__construct( $name, $key );
        $this->default = '';
    }


    /**
    * Renders the HTML to create the <input type="text" /> element.
    *
    * @return string $html The fully qualified HTML.
    *
    */
    public function render_HTML() {
        $html = '<div class="sw-grid ' . $this->parent_size . ' sw-option-container ' . $this->key . '_wrapper" ';
        $html .= $this->render_dependency();
        $html .= $this->render_premium();
        $html .= '>';

            $html .= '<div class="sw-grid ' . $this->size . '">';
                $html .= '<p class="sw-input-label">' . $this->name . '</p>';
            $html .= '</div>';

            $html .= '<div class="sw-grid ' . $this->size . '">';
                $html .= '<textarea name="' . $this->key . '" data-swp-name="' . $this->key . '"  class="sw-grid-textarea" >'. $this->get_value() . '</textarea>';
            $html .= '</div>';

        $html .= '</div>';

        $this->html = $html;

        return $html;
    }


    /**
    * Defines the default value among this select's choices.
    *
    *
    * @param mixed $value The key associated with the default option.
    * @return SWP_Option_Select $this The calling instance, for method chaining.
    *
    */
    public function set_default( $default ) {
        if ( is_numeric( $default) ) :
            settype( $default, 'string' );
        endif;

        if ( !is_string( $default )  ) :
            $this->_throw( 'Please provide a default value as a string.' );
        endif;

        $this->default = $default;

        return $this;
    }
}

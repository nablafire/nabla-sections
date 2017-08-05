<?php
	/*
	Plugin Name: 	Nabla Sections
	Plugin URI: 	https://www.nablafire.com/
	Description: 	Nabla Sections Plugin
	Version: 		1.0.1
	Author: 		Nabla Fire
	Author URI: 	https://www.nablafire.com/
	Domain Path:    /languages
	Text Domain:	nabla-sections
	License: GPLv3 or later
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
	*/

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	// Autoloader ... this is load the class files that we call within this 
	// php file. 
	spl_autoload_register( 'nabla_front_page_register_classes' );

	function nabla_front_page_register_classes( $class_name ) {

		// If the class name we are calling contains the substring "nabla_sections"
		if (strpos(strtolower($class_name), 'nabla_sections') !== false) {

			// We will look in these directories for the class files. 
			if ( class_exists( $class_name ) ) {return;}
			$array_paths = array(
     			'sections/admin/', 
        		'sections/',        		
        		'inc/',
    		);

			//load the needed class files
		    foreach($array_paths as $path)
    		{		
    			// Class name is the class we call, and class file is the file the class
    			// is located in. Therefore, if we have a class called my_myclass then 
    			// we must store it in my-class.php in one of the directories above ... 
    			$class_file = str_replace( '_', '-', $class_name );
				$class_path = plugin_dir_path( __FILE__ ). $path . $class_file . '.php';
		
				if ( file_exists( $class_path ) ) {include $class_path; return;}
			} 
		}
	}

	// Initialize instance of the container class if it does not exist. Otherwise simply 
	// return the pointer. This method is a getter method for the entire plugin class.  
	if ( !function_exists('_nabla_sections') ) {
		function _nabla_sections() {
			// Instantiate the class --> calls new Nabla_Front_Page() 
			$nabla_front_page_pointer = nabla_sections::get_instance();
			return $nabla_front_page_pointer;

		}
	}

	// Begin Plugin Class
	if ( !class_exists('nabla_sections') ) {

		class nabla_sections {
		
			// Pointer to instance
			public static $instance = null;

			// Init. Calls constructor.
			public static function init() {
			    $class = __CLASS__;
		        new $class;
		    }

   			// Constructor 
		    function __construct() {

				// Declare this so we have access to the wp_customizer
				global $wp_customize;

		    	// Create a 'domain' which will be consistent everywhere ... 
				$this->plugin = 'nabla_sections';
				$this->domain = 'nabla-sections';

				// Set up section variables 
				$this->n_sections = false; // A number which we get from the settigs menu
				$this->sections   = null;  // An array of nabla_sections_section() objects			     

				add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ));
				add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ));

				// Register all plugin sidebars. These will be wigitized areas
				add_action( 'widgets_init',  array( $this, 'register_sidebars' ) );

				// Define some paths that we can use later
				if (!defined('NABLA_FRONT_PAGE_INC_URL')) {
					define( 'NABLA_FRONT_PAGE_INC_URL', plugin_dir_path( __FILE__ ) ."inc/");
				}

				if (!defined('NABLA_FRONT_PAGE_CSS_URL')) {
					define( 'NABLA_FRONT_PAGE_CSS_URL', plugin_dir_url( __FILE__ ).'css/' );
				}

				if (!defined('NABLA_FRONT_PAGE_JS_URL')) {
					define( 'NABLA_FRONT_PAGE_JS_URL', plugin_dir_url( __FILE__ ).'js/' );
				}

				// Generate Settings Page 
				if( is_admin() ){ $settings = new nabla_sections_settings($this->domain); }

				// Add width selectior to widgets. 
				add_action( 'in_widget_form', array( $this, 'nabla_in_widget_form' ), 4, 3 );

				// When widget_update_callbacks are called, nabla_in_widget_form__update will also be 
				// called. This will add our width to the corresponding widget options table. 
				add_filter( 'widget_update_callback', array( $this, 'nabla_in_widget_form_update' ), 4, 3 );

				// Inject width styling into <div class="nabla-widget" --HERE-- >. Note that this div 
				// wraps all widgets and is prepared in register sidebar. When dynamic_sidebar is called, 
				// this filter calls nabla_dynamic_sidebar_params. Priority is set to 5 to ensure that this
				// filter is added first ... This way we will not conflict with other plugins ...
        		add_filter( 'dynamic_sidebar_params', array( $this, 'nabla_dynamic_sidebar_params' ), 5 );

				// Add action
				add_action( $this->plugin, array( $this, 'nabla_echo_sections' ) );
				// do_action( 'nabla_front_page' ); --> found in /<theme>/front-page.php

			}	

		    // Load Scripts for frontend
			function load_scripts() { 
				wp_enqueue_style("font-awesome", "//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css");
				wp_enqueue_style($this->domain."-format-css", NABLA_FRONT_PAGE_CSS_URL."nabla-sections-format.css");
				wp_enqueue_style($this->domain."-css", NABLA_FRONT_PAGE_CSS_URL."nabla-sections.css");
				wp_enqueue_script($this->domain."-js", NABLA_FRONT_PAGE_JS_URL."nabla-sections.js");
			}
			// Load Scripts for backend
			function load_admin_scripts() { 
				wp_enqueue_style("font-awesome", "//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css");
				wp_enqueue_style($this->domain."-admin-css", NABLA_FRONT_PAGE_CSS_URL."admin/nabla-sections-admin.css");
				wp_enqueue_script($this->domain."-admin-js", NABLA_FRONT_PAGE_JS_URL."admin/nabla-sections-admin.js", array( 'jquery' ),'1.0.0', true );
			}

			// Get class instance
			public static function get_instance() {
				if ( self::$instance==null ) {self::$instance = new nabla_sections();}
				return self::$instance;
			}

			// Register Sidebars and initialize frontpage sections
			public function register_sidebars() {

				// Retrieve the number of sections we want to generate.
				// This option is set by nabla-sections-settings.php 
				$options = get_option("nabla_sections_options");
				$this->n_sections = (isset($options)) ? $options['n_sections'] : '5';

				// Initialize the customizer
				$this->customizer = new nabla_sections_customizer($this->n_sections, $this->domain);

				// Initialize array of nabla_sections_gen objects. Each of these is 
				// responsible for generating a section with given id. 
				$this->sections = array();
				for ($id = 1; $id <= $this->n_sections; $id++) {

					// Register Sidebar for Each Section
					register_sidebar( 
						array(
							'name'          => esc_html__( "Nabla Section $id", $this->domain ),
							'id'            => "nabla-section-$id",
							'description'   => esc_html__( 'Nabla Section', $this->domain ),
		                	'before_widget' => '<div class="nabla-section-widget">',
		                	'after_widget'  => '</div>',
							'before_title'  => '<h2><span>',
							'after_title'   => '</span></h2>',
						) 
					);
					// Initialize Section Class for Each Section and store
					// the pointer
					$this->sections[$id] = new nabla_sections_gen($id);
				}
			}


			public function nabla_echo_sections(){
				echo '<div class="nabla-sections">';
				foreach ($this->sections as $id => $section) {
					echo $section->echo_section();
				}
				echo '</div>';
			}





			//
			// *********************** ADD FORMATTING TO EACH SIDEBAR ***************************//
			//
			// Add width selector in widget form. This will add a width parameter for our nabla 
			// widgets which we will expose later.
			public function nabla_in_widget_form( $widget, $return, $instance ){ 

				// Add a width parameter to widget options.
				$instance = wp_parse_args( (array) $instance, array( 'nabla_format' => '') );
		        if ( !isset( $instance['nabla_format'] ) ) $instance['nabla_format'] = null; 

      			$instance = wp_parse_args( (array) $instance, array( 'nabla_width' => '') );
		        if ( !isset( $instance['nabla_width'] ) ) $instance['nabla_width'] = null; 


		        $formats = $this->get_nabla_formats();
		        $widths  = $this->get_nabla_widths();

				?>
				<div class="nabla-front-page-admin-fields">
				<h3 class="nabla-front-page-admin-toggle"><?php _e( 'Nabla Format', 'nabla-widgets' ); ?></h3>
        		<div class="nabla-front-page-admin-field" style="display: none;">
					
	        		<!--  The Format -->
					<p> 
					<label for="<?php echo $widget->get_field_id('nabla_format'); ?>"><?php _e( 'Row Formatting','nabla-front-page'); ?></label>

					 <select class="widefat" 
					 			id="<?php echo $widget->get_field_id('nabla_format'); ?>" 
					 			name="<?php echo $widget->get_field_name('nabla_format'); ?>">
                        
					 <?php foreach( $formats as $key => $value ) { ?>
                        <option <?php selected( $instance['nabla_format'], $key ); ?>value="<?php echo $key; ?>">
                        	<?php echo $value; ?>    	
                        </option>
                        <?php } ?>
	                </select>
                    <span><em>
                    	<?php _e( 'Format this widget', 'nabla-front-page' ); ?>	
                    </em></span>	
					</p>

		       		<!--  The Width -->
		       		<p> 
					<label for="<?php echo $widget->get_field_id('nabla_width'); ?>"><?php _e( 'Widget Width','nabla-front-page'); ?></label>

					 <select class="widefat" 
					 			id="<?php echo $widget->get_field_id('nabla_width'); ?>" 
					 			name="<?php echo $widget->get_field_name('nabla_width'); ?>">
                        
					 <?php foreach( $widths as $key => $value ) { ?>
                        <option <?php selected( $instance['nabla_width'], $key ); ?>value="<?php echo $key; ?>">
                        	<?php echo $value; ?>    	
                        </option>
                        <?php } ?>
	                </select>
                    <span><em>
                    	<?php _e( 'Set the widget width', 'nabla-front-page' ); ?>	
                    </em></span>	
					</p>

				</div>
				</div>
				<?php 

	       		$return = null;
	    	    return array( $widget, $return, $instance );
			}

			// A callback which we access when we return from the above function.
    		public function nabla_in_widget_form_update( $instance, $new_instance, $old_instance ) {
        		$instance['nabla_format'] = $new_instance['nabla_format'];
	      		$instance['nabla_width'] = $new_instance['nabla_width'];
		        return $instance;
    		}

    		// Here we will update the sidebar param "before_widget" to include our width
    	    public function nabla_dynamic_sidebar_params( $params ) {
		
		        global $wp_registered_widgets;

        		$widget_id  = $params[0]['widget_id'];
        		$widget_obj = $wp_registered_widgets[$widget_id];
        		$widget_opt = get_option( $widget_obj['callback'][0]->option_name );
        		$widget_num = $widget_obj['params'][0]['number'];


        		// The width injector. Note that we need to do this first in order for the resulting HTML to come 
        		// out ok after regex replace. This will add a class attribue to each widget which we can then write
        		// some corresponding CSS for ... 
           		if(isset( $widget_opt[$widget_num]['nabla_width']) && !empty( $widget_opt[$widget_num]['nabla_width'])){
           			$_before = ' '. $widget_opt[$widget_num]['nabla_width'] .'"'.' >';
    				$params[0]['before_widget'] = preg_replace( '/"\\s*>/', $_before, $params[0]['before_widget'], 1 );
        		}

    	   		// The div injector ....
        		if(isset( $widget_opt[$widget_num]['nabla_format']) && !empty( $widget_opt[$widget_num]['nabla_format'])){
        			$key = $widget_opt[$widget_num]['nabla_format'] ;

        			if ($key == "1"){
           				$_before = '<div class="nabla-row"><';
        				$_after  = '></div>';
		        		$params[0]['before_widget'] = preg_replace( '/</', $_before, $params[0]['before_widget'], 1 );
    		    		$params[0]['after_widget'] = preg_replace( '/>/', $_after, $params[0]['after_widget'], 1 );
        			}
        			if ($key == "2"){
           				$_before = '<div class="nabla-row"><';
       					$params[0]['before_widget'] = preg_replace( '/</', $_before, $params[0]['before_widget'], 1 );
           			}
           			if ($key == "4"){
           				$_after  = '></div>';
    		    		$params[0]['after_widget'] = preg_replace( '/>/', $_after, $params[0]['after_widget'], 1 );
           			}
        		}

    


		        return $params;
        	}

        	public function get_nabla_formats(){

        		$formats = array(
        			'0' => __( 'None' ),
        			'1' => __( 'Full Width Widget' ),
		            '2' => __( 'Begin Widget Row' ),
        		    '3' => __( 'Widget in Row' ),
            		'4' => __( 'End Widget Row' ),            		
        		);
        		return $formats;
        	}

        	public function get_nabla_widths(){

        		$formats = array(
        			'nabla-20-percent'  => __( '20%' ),
        			'nabla-25-percent'  => __( '25%' ),
        			'nabla-33-percent'  => __( '33%' ),
          			'nabla-40-percent'  => __( '40%' ),
           			'nabla-50-percent'  => __( '50%' ),
          			'nabla-60-percent'  => __( '60%' ),
        			'nabla-66-percent'  => __( '66%' ),
         			'nabla-75-percent'  => __( '75%' ),
         			'nabla-80-percent'  => __( '80%' ),
        			'nabla-100-percent' => __( '100%' ),
        		);
        		return $formats;
        	}




		} // END CONTAINER CLASS 
	} // END if(!class_exists('nabla_sections'))

	//run the plugin
	add_action( 'plugins_loaded', array( 'nabla_sections', 'init' ));	

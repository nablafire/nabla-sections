<?php
	
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	// Autoloader ... this is load the class files that we call within this 
	// php file. 
	spl_autoload_register( 'nabla_customizer_register_classes' );

	function nabla_customizer_register_classes( $class_name ) {


		if (strpos($class_name, 'WP_Customize') !== false) {

			// We will look in these directories for the class files. 
			if ( class_exists( $class_name ) ) {return;}
			$array_paths = array(	
        		'customizer/',
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

	class nabla_sections_customizer
	{
		
	    // Class constructor
    	public function __construct($n_sections, $domain)
 		{
 			// Number of panels/sections to generate.
 			$this->n_sections = $n_sections; 
 			$this->domain     = $domain; 

	 		$fonts_json   = file_get_contents( NABLA_FRONT_PAGE_INC_URL.'json/google-fonts.json' );
 			$this->fonts  = (array)json_decode( $fonts_json, true );

	 		$styles_json  = file_get_contents( NABLA_FRONT_PAGE_INC_URL.'json/font-styles.json' );
	 		$this->styles = (array)json_decode( $styles_json, true );

	 
 			// Register our custsomizer
 			add_action( 'customize_register',  array( $this, 'register_sections' ) );

 			// Add action on save after 
 			//add_action( 'customize_save_after', array($this, 'force_defaults'), 10, 1);
 		}


 		// Wrapper to register all sections in customizer
	 	public function register_sections($wp_customize){

	 		for ($id = 1; $id <= $this->n_sections; $id++) {
	 	
	 			$this->register_section($wp_customize, $id);
	 	
	 		}
 		}

 		public function register_section($wp_customize, $id){

 			//
		 	// Display Settings Section
	 		//
			$wp_customize->add_section( 'Display '.$id, array(
        		'title'         => esc_html__( 'Nabla Section '.$id, $this->domain ),
        		'description'   => esc_html__( 'Update Section Display Settings', $this->domain ),
        		'priority'      => 30,    			
    		) ); 

					//
					// Show this Section 
					//
					$wp_customize->add_setting( '_nabla_section_show_'.$id, array(
    	    			'type'          => 'option',
        				'transport'     => 'refresh',
        	 			'default'		=> '1',

		    		) );
					$wp_customize->add_control('_nabla_section_show_'.$id, array(
        				'type'      => 'checkbox',
				        'label'     => __( 'Show this section?', $this->domain ),
        				'section'   => 'Display '.$id,
        				'priority'  => 1,
    				) );

					//
					// Section ID
					//
					$wp_customize->add_setting( '_nabla_section_id_'.$id, array(
    	    			'type'          => 'option',
        				'transport'     => 'refresh',
        	 			'default'		=> 'Section'.$id,

		    		) );

					$wp_customize->add_control( '_nabla_section_id_'.$id, array(
		        		'label'         => esc_html__( 'Section ID', $this->domain ),
         				'description'   => esc_html__( 'HTML ID for this Section.', $this->domain),
         				'section'       => 'Display '.$id,
         				'priority'      => 2,
     				) );


					//
					// Background Color 
					//
					$wp_customize->add_setting( '_nabla_section_color_'.$id, array(
    	    			'type'          => 'option',
        				'transport'     => 'refresh',
        	 			'default'		=> '#FFF',

		    		) );
					$wp_customize->add_control( new WP_Customize_Color_Control( 
						$wp_customize, '_nabla_section_color_'.$id, 
						array(
							'label'       => __( 'Select Background Color', $this->domain ),
							'description' => esc_html__( 'Background Color for Section '.$id, $this->domain),
							'section'     => 'Display '.$id,
							'priority'	  => 2,
						)
					) );

					//
					// Parallax Effect 
					//
					$wp_customize->add_setting( '_nabla_section_parallax_'.$id, array(
    	    			'type'          => 'option',
        				'transport'     => 'refresh',
        	 			'default'		=> '0',

		    		) );
					$wp_customize->add_control('_nabla_section_parallax_'.$id, array(
        				'type'         	=> 'checkbox',
				        'label'     	=> __( 'Parallax Effect', $this->domain ),
				        'description' 	=> __( 'Parallax Effect for Section '.$id, $this->domain),
        				'section'     	=> 'Display '.$id,
        				'priority' 	 	=> 2,
    				) );

					// 
					// 
					// Background Image
					//
					$wp_customize->add_setting( '_nabla_section_image_'.$id, array(
    	    			'type'          => 'option',
        				'transport'     => 'refresh',
        	 			'default'		=> '',

		    		) );
					$wp_customize->add_control( new WP_Customize_Image_Control( 
						$wp_customize, '_nabla_section_image_'.$id, 
						array(
							'label'       => __( 'Select Background Image', $this->domain ),
							'description' => esc_html__( 'Background Image for Section '.$id, $this->domain),
							'section'     => 'Display '.$id,
							'priority'	  => 2,
						)
					) );

					//
					// Fix Div Height
					//
					$wp_customize->add_setting( '_nabla_section_height_'.$id, array(
    	    			'type'          => 'option',
        				'transport'     => 'refresh',
        	 			'default'		=> '0',

		    		) );
					$wp_customize->add_control( new WP_Customize_Dropdown_Control( $wp_customize, 
						'_nabla_section_height_'.$id, 	
						array(
							'label'	  		=> esc_html__( 'Section Height', $this->domain ),
							'description'   => esc_html__( 'Select Height for this Section (vh) : (0 = auto)', $this->domain),
							'section' 		=> 'Display '.$id,
							'priority'      => 2,
							'keyed'      	=> false,
						),
						(array) range ( 0 , 100, 10 ) 
					) );
			
 		}

 	} // Close Class
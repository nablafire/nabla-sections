<?php

	if ( ! class_exists( 'WP_Customize_Control' ) ) return NULL;

	// Custom Control 
	class WP_Customize_Dropdown_Control extends WP_Customize_Control
	{
    	private $options = false;
  		public function __construct($manager, $id, $args = array(), $options = array())
    	{
          // Set an option in wp_options object. This is the thing we will 
          // access to pull data out of the customizer. 
          $this->id      = $id;
        	$this->options = $options;
          $this->keyed   = $args['keyed'];
        	parent::__construct( $manager, $id, $args );
    	}
	    // Render the content on the theme customizer page
    	public function render_content()
   		{  
        ?>

          <?php $options_list = array_keys($this->options); ?>  
          <span class="customize-control-title"><?php echo $this->label; ?></span>
          <span class="description customize-control-description"><?php echo $this->description; ?></span>
          <p>
              <select name="<?php echo esc_attr($this->id); ?>" <?php $this->link(); ?> >
                
                <?php foreach($options_list as $_option) { ?>
                  <option value="<?php echo esc_attr($this->options[$_option]); ?>"   
                    <?php if(get_option($this->id) == $this->options[$_option]) { echo 'selected="selected"'; } ?>
                  >
                  <?php echo ( $this->keyed ? esc_attr($_option) : esc_attr($this->options[$_option]) ); ?>                    
                  </option>
                <?php } ?>

              </select>
          </p>

    	<?php }

	}


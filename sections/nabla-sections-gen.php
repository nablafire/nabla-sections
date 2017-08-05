<?php


	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	class nabla_sections_gen 
	{	

		private $id;
		private $section;
		private $widgets;
		private $options;

		function __construct($section_id)
		{
			$this->id      = $section_id; 
			$this->section = 'nabla-section-'.$this->id;
			$this->options = $this->get_section_options($this->id);
			$this->widgets = get_option('sidebars_widgets')[$this->section];
		}

		function get_section_options($id){

			// Get options from customizer. Note that the defaults here should match the defaults in 
			// the customizer ...get_option(_option_, default);
			$options = array(
				// Pagination  
				'show'		=> get_option('_nabla_section_show_'.$id, 1),
				'html_id'  	=> get_option('_nabla_section_html_id_'.$id, 'Section'.$id),

				// Display 
				'color'		=> get_option('_nabla_section_color_'.$id, '#FFF'),
				'image'		=> get_option('_nabla_section_image_'.$id, ''),
				'height'	=> get_option('_nabla_section_height_'.$id, 0),
				'parallax'	=> get_option('_nabla_section_parallax_'.$id, 0),
			);
			return $options;
		}


		function css_parallax(){ ?>

			<style>
				.nabla-section-parallax-<?php echo esc_html($this->id);?>{ 
					background-color: <?php echo esc_html($this->options['color']);?>;
				} 
				.nabla-section-parallax-<?php echo esc_html($this->id);?> [class*="bg-"]{ 
  			    	position 			  : relative;
				    background-attachment : fixed;
				    background-position   : top center;
   					background-size       : cover;
   				    <?php // If height is set then echo an additional CSS rule ...
   				    if ($this->options['height'] != 0):
   				    	echo 'height  :'.esc_html($this->options['height']).'vh;';  
   				    endif;
   				    ?>			 
				}
				.bg-nabla-section-parallax-<?php echo esc_html($this->id);?>  {
					background-image: url(<?php echo esc_html($this->options['image']);?>);
				}
			</style><?php 
        	return "nabla-section-parallax-".esc_html($this->id);
		}

		function css_no_parallax(){ ?>

		 	<style>
				.nabla-section-no-parallax-<?php echo esc_html($this->id);?>{ 
					background-color: <?php echo esc_html($this->options['color']);?>;
				} 
				.bg-nabla-section-no-parallax-<?php echo esc_html($this->id);?>{ 
					background-size:cover;
					background-image: url(<?php echo esc_html($this->options['image']);?>);
					<?php // If height is set then echo an additional CSS rule ...
   				    if ($this->options['height'] != 0):
   				    	echo 'height  :'.esc_html($this->options['height']).'vh;';  
   				    endif;
   				    ?>		 
				}
			</style><?php 
       		return "nabla-section-no-parallax-".esc_html($this->id);
		}

		function echo_section()
		{ ?>

			<?php //var_dump($this->options); //debug ?>
			<?php if ($this->options['show']): ?>
			<?php $class = (int)$this->options['parallax'] ? $this->css_parallax() : $this->css_no_parallax(); ?>	
		
			<div class="nabla-section <?php echo esc_html($class);?>" 
				  id="<?php echo esc_html($this->options['html_id'])?>">
				
				<div class="nabla-section bg-<?php echo esc_html($class);?>">

					<?php if(is_active_sidebar($this->section)): ?>
				
						<?php dynamic_sidebar( $this->section ); ?>
				
					<?php endif; ?>

				</div>
			
			</div>
		
			<?php endif; ?>

		<?php } //End echo section
	}


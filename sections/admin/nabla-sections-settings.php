<?php
// This class generates an options page in the Settings for our plugin. 
// The options we create will are stored in the wordpress database and 
// we can access them via calls to get_option('option_name')
//
// 1) Our option       : (array)nabla_sections_options
// 2) To access it     : get_option('nabla_sections_options');
// 3) Settings Created : nabla_sections_options['n_sections'];

class nabla_sections_settings{

    // Class constructor
    public function __construct($domain)
    {  
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'settings_page_init' ) );

        $this->domain = $domain;
    }


    // Add plugin page to the settings menu 
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Nabla Sections Settings', 
            'Nabla Sections Settings', 
            'manage_options', 
            $this->domain, 
            array( $this, 'create_admin_page' ) // Calls create_admin_page
        );
    }

    // Options page callback
    public function create_admin_page()
    {
        // Set $this->options to $options['nabla_sections_options']
        // Note that we create this option in register_setting with 
        // settings_page_init(). Note that $this->options = array()
        $this->options = get_option( 'nabla_sections_options' );

        // If this option is not set, then we can set a default 
        // value to appear in our selector. 
        if(!isset($this->options['n_sections'])){$this->options['n_sections'] = '5';} 
        ?>
        <div class="wrap">
            <h1>Nabla Front Page Settings</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'nabla_sections_options_group' );
                do_settings_sections( $this->domain );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    // Register our new option 'nabla_sections_options' and add a page 
    // to the wordpress backend settings menu. 
    public function settings_page_init()
    {        
        // Register a setting in the wordpress DB. This will give 
        // us access to get_option('Option name in DB'). 
        register_setting(
            'nabla_sections_options_group', // Option group
            'nabla_sections_options',       // Option name in DB           
            array( $this, 'sanitize' )      // Sanitize Callback
        );

        // Add a settings section and indicate which function to 
        // call to print it out.
        add_settings_section(
            'nabla_sections_settings',               // Section ID
            'Nabla Sections Settings',               // Title
            array( $this, 'print_settings_header' ), // Callback
            $this->domain                            // Domain
        );  

        // Create our option and indicate which section it should 
        // appear in as well as the function to call to print it out.  
        add_settings_field(
            'n_sections',                              // Option 
            'Number Of Nabla Sections',                // Title 
            array( $this, 'section_number_callback' ), // Callback
            $this->domain,                             // Domain
            'nabla_sections_settings'                  // Section ID           
        );  
    }

    // Sanatize user input (i.e. it should be an int)
    public function sanitize( $input )
    {
        $new_input = array();
        // Sanitize input data for the wp database
        if( isset( $input['n_sections'] ) )
            $new_input['n_sections'] = absint( $input['n_sections'] );
        // Here we only have one setting, but if we have 
        // more then we can add more sanatize clauses here.
        return $new_input;
    }

    // Print the Section text
    public function print_settings_header()
    {
        print 'Enter your settings below:';
    }

    // Set our plugin option. On submit, our input field will update
    // nabla_sections_options['n_sections'] in the DB via options.php
    // This is a builtin wordpress fuction which handles the SQL
    // Query to the wp database. 
    public function section_number_callback()
    {?>
        <?php // Get current setting value | generate a default setting ?>
        <?php $_value = isset( $this->options['n_sections']) ? esc_attr( $this->options['n_sections']) : '5';?>
        
        <?php // Write the input field ?>
        <input id="<?php echo $this->domain ?>-1" 
                type="number" min="1" max="10" step="1" 
                name="nabla_sections_options[n_sections]" 
                value="<?php echo $_value; ?>" 
                />',            
        
    <?php }
} 
<?php
class Rota {
    /**
     * Availability Intervals
     */
    public static $intervals = array();
    
    /**
     * Rota Days
     */
    public static $days = array();
    
    /**
     * Rota meta key
     */
    public static $user_key = 'rota_options';
    
    /**
     * Rota location key
     */
    public static $location_key = 'rota_location';
    
    /**
     * Static Constructor
     */
    function init() {
        add_filter( 'rota_days', array( __CLASS__, 'days' ) );
        add_filter( 'rota_intervals', array( __CLASS__, 'intervals' ) );
        add_action( 'personal_options', array( __CLASS__, 'options' ) );
        add_action( 'personal_options_update', array( __CLASS__, 'options_update' ) );
        // For Admins
        add_action( 'edit_user_profile_update', array( __CLASS__, 'options_update' ) );
        // Options page
        add_action( 'admin_menu', array( __CLASS__, 'menus' ) );
        add_action( 'wp', array( __CLASS__, 'ui' ) );
    }
    
    /**
     * Loads days for rota, applies filters too
     * @return Mixed, set of days
     */
    function get_days() {
        return apply_filters( 'rota_days', self::$days );
    }
    
    /**
     * Loads intervals for rota, applies filters too
     * @return Mixed, set of intervals
     */
    function get_intervals() {
        return apply_filters( 'rota_intervals', self::$intervals );
    }
    
    /**
     * Adds menu entries to `wp-admin`
     */
    function menus() {
        add_options_page(
            __( 'Rota Management', 'rota' ),
            __( 'Rota Management', 'rota' ),
            'administrator',
            'rota',
            array( __CLASS__, "screen" )
        );
    }
    
    /**
     * Menu screen handler in `wp-admin`
     */
    function screen() {
        $vars = array();
        $flash = null;
        $location_name = null;
        
        // Delete location
        if( isset( $_GET['_nonce'] ) && wp_verify_nonce( $_GET['_nonce'], 'rota_delete' ) ) {
            if( isset( $_GET['del'] ) && !empty( $_GET['del'] ) )
                if( self::delete_location( $_GET['del'] ) )
                    $flash = sprintf( __( 'Location: %s was deleted.', 'rota' ), $_GET['del'] );
                else
                    $flash = sprintf( __( 'Location: %s was not deleted.', 'rota' ), $_GET['del'] );
        }
        
        // Edit location
        if( isset( $_GET['_nonce'] ) && wp_verify_nonce( $_GET['_nonce'], 'rota_edit' ) )
            if( isset( $_GET['edit'] ) && !empty( $_GET['edit'] ) )
                $location_name = $_GET['edit'];
        
        // Add location
        if( isset( $_POST['rota_nonce'] ) && wp_verify_nonce( $_POST['rota_nonce'], 'rota' ) ) {
            if( isset( $_POST['location'] ) && count( $_POST['location'] ) != 0 )
                if( self::add_location( $_POST['location'] ) )
                    $flash = __( 'Locations were updated', 'rota' );
                else
                    $flash = __( 'Locations were not updated', 'rota' );
        }
        
        $vars['rota_permalink'] = menu_page_url( 'rota', false );
        $vars['edit_permalink'] = add_query_arg( '_nonce', wp_create_nonce('rota_edit'), $vars['rota_permalink'] );
        $vars['delete_permalink'] = add_query_arg( '_nonce', wp_create_nonce('rota_delete'), $vars['rota_permalink'] );
        $vars['flash'] = $flash;
        $vars['locations'] = get_option( self::$location_key );
        $vars['location'] = self::get_location( $location_name );
        self::template_render( 'settings', $vars );
    }
    
    /**
     * ui()
     *
     * Generates the App UI
     */
    function ui() {
        $vars = array();
        $users = array();
        $days = self::get_days();
        $intervals = self::get_intervals();
        $intervals_num = count( $intervals );
        $locations = get_option( self::$location_key );
        $undone_locations = array();
        $left_users = array();
        
        foreach ( $days as $d => $dayname )
            foreach ( $intervals as $i => $intname ) {
                // Get the available users list
                $userlist = self::usersByDayInt( $d, $i, $intervals_num );
                foreach ( $locations as $l ){
                    // To ignore notices set the variable
                    $users[$d][$i][ $l['name'] ] = array();
                    // Skip locations with 0 rota size
                    if( $l['size'] > 0 )
                        // Try to assign the needed size with busy users if number of busy users is enough big
                        if( count( $userlist['busy'] ) >= $l['size'] ) {
                            // Assign first available users
                            $users[$d][$i][ $l['name'] ] = array_slice( $userlist['busy'], 0, $l['size'] );
                            // Clear userlist from assigned users
                            $userlist['busy'] = array_diff( $userlist['busy'], $users[$d][$i][ $l['name'] ] );
                            
                        // If busy users size is not enough, assign available and update the required undone location size
                        } elseif( count( $userlist['busy'] ) < $l['size'] && count( $userlist['busy'] ) != 0 ) {
                            $busy_users = count( $userlist['busy'] );
                            // Get the available busy users
                            $users[$d][$i][ $l['name'] ] = $userlist['busy'];
                            // Reset the size of busy users
                            $userlist['busy'] = array();
                            $l['size'] -= $busy_users;
                            $undone_locations[ $l['name'] ] = $l;
                        } else
                            // Place the location for later users re-assignment
                            $undone_locations[ $l['name'] ] = $l;
                }
                
                // Cycle through all the available (free) users and try to assign them fairly across $undone_locations
                $free_users = count( $userlist['free'] );
                while( $free_users > 0 ) {
                    $l_index = 0; // Location index counter
                    // Randomize the locations to reduce the same location assignment probability
                    shuffle( $undone_locations );
                    foreach ( $undone_locations as $l ) {
                        // Check if the array was initiated already
                        if( is_array( $users[$d][$i][ $l['name'] ] ) )
                            $users[$d][$i][ $l['name'] ][] = array_shift( $userlist['free'] );
                        else
                            $users[$d][$i][ $l['name'] ] = array( array_shift( $userlist['free'] ) );
                        // Check if userlist size was achieved
                        if( $l['size'] == count( $users[$d][$i][ $l['name'] ] ) )
                            // Remove the location from undone
                            unset( $undone_locations[$l_index] );
                        // Proceed with next location
                        $l_index++;
                    }
                    $free_users--;
                }
                
                // Save left out users
                foreach( $userlist as $left )
                    $left_users = array_merge( $left );
            }
        
        $vars['users'] = $users;
        $vars['days'] = $days;
        $vars['intervals'] = $intervals;
        $vars['locations'] = $locations;
        $vars['undone_locations'] = $undone_locations;
        $vars['left_users'] = $left_users;
        self::template_render( 'ui', $vars );
        die();
    }
    
    /**
     * usersByDayInt( $day, $interval )
     *
     * Generates a users list by day/interval availability using availability as a priority
     * @param String $day, day id
     * @param String $interval, interval id
     * @param Int $intervals, number of intervals
     * @return Mixed array of user ids list
     */
    function usersByDayInt( $day, $interval, $intervals ) {
        // Busy for users with worst availability, free for the rest
        $users = array( 'busy' => array(), 'free' => array() );
        // Select only subscribers
        $uids = get_users( array( 'fields' => 'user_id', 'role' => 'subscriber' ) );
        foreach( $uids as $uid ){
            $uid_opts = self::get_user_options( $uid );
            // Check if user didn't exclude this interval
            if( !$uid_opts[$day][$interval] ) {
                // Calculate priority by availability options number
                if( count( array_filter( $uid_opts[$day] ) ) < $intervals )
                    $users['busy'][] = $uid; // A busy user
                else
                    $users['free'][] = $uid; // A less busy user
            }
        }
        return $users;
    }
    
    /**
     * get_location( $name )
     *
     * Find an existent location
     * @param String $name, the ID of the location
     * @return Mixed, location data array, null if doesn't exist
     */
    function get_location( $name ) {
        $name = sanitize_title( $name );
        
        if( !$name )
            return null;
        
        $locations = get_option( self::$location_key );
        foreach ( $locations as $l )
            if( $l['name'] == $name )
                return $l;
        
        return null;
    }
    
    /**
     * delete_location( $name )
     *
     * Find and delete an existent location
     * @param String $name, the ID of the location
     * @return Boolean, True on success, false on failure
     */
    function delete_location( $name ) {
        $name = sanitize_title( $name );
        $locations = get_option( self::$location_key );
        for( $i = 0; $i < count( $locations ); $i++ )
            if( $locations[$i]['name'] == $name ) {
                unset( $locations[$i] );
                update_option( self::$location_key, $locations );
                return true;
            }
        
        return false;
    }
    
    /**
     * add_location( $l )
     * 
     * Handle locations adding
     * @param Mixed $location, an array with location details
     * @return Boolean, True on success, false on failure
     */
    function add_location( $l ) {
        $is_duplicate = false;
        $locations = get_option( self::$location_key );
        $location = array(
            'name' => null,
            'title' => null,
            'size' => null
        );
        
        $location['name'] = sanitize_title( $l['title'] );
        $location['title'] = sanitize_text_field( $l['title'] );
        $location = array_filter( $location );
        $location['size'] = intval( $l['size'] );
        
        if( count( $location ) == 3 ) {
            // On duplicate, update old location
            for( $i = 0; ( $i < count( $locations ) ) && !$is_duplicate; $i++ )
                if( $locations[$i]['name'] == $location['name'] ) {
                    $locations[$i] = $location;
                    $is_duplicate = true;
                }
            
            if( !$locations )
                update_option( self::$location_key, array( $location ) );
            else {
                if( !$is_duplicate )
                    $locations[] = $location;
                update_option( self::$location_key, $locations );
            }
            return true;
        } else
            return false;
    }
    
    /**
     * get_user_options( $user_id )
     *
     * Fetches user rota options
     * @param Int $user_id, the id of the user to fetch, null for dummy data
     * @return Mixed, user options
     */
    function get_user_options( $user_id = null ) {
        // To skip PHP notices
        $days = self::get_days();
        $intervals = self::get_intervals();
        $user_options = null;
        
        $dummy_data = array();
        foreach( $days as $d => $d_name )
            $dummy_data[$d] = array_fill_keys( array_keys( $intervals ), null );
        
        if( $user_id != null )
            $user_options = get_user_meta( $user_id, self::$user_key, true );
        
        if( $user_options )
            return maybe_unserialize( $user_options );
        else
            return $dummy_data;
    }
    
    /**
     * Profile options screen
     */
    function options() {
        $vars = array();
        $vars['user_id'] = isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : get_current_user_id();
        $vars['rota_options'] = self::get_user_options( $vars['user_id'] );
        $vars['days'] = self::get_days();
        $vars['intervals'] = self::get_intervals();
        self::template_render( 'options', $vars );
    }
    
    /**
     * Handle user form options update
     */
    function options_update() {
        $user_id = get_current_user_id();
        $user_options = null;
        
        if( isset( $_POST['user_id'] ) && intval( $_POST['user_id'] ) != $user_id )
            if( current_user_can( 'edit_user' ) )
                $user_id = intval( $_POST['user_id'] );
            else
                return;
        
        if( isset( $_POST['rota_nonce'] ) && wp_verify_nonce( $_POST['rota_nonce'], 'rota' ) ) {
            if( isset( $_POST['rota'] ) && isset( $_POST['rota'][$user_id] ) )
                $user_options = $_POST['rota'][$user_id];
        }
        
        $options = self::get_user_options();
        foreach ( $options as $day => $intervals )
            foreach ( $intervals as $interval => $value )
                if( isset( $user_options[$day] ) && isset( $user_options[$day][$interval] ) )
                    if( $user_options[$day][$interval] )
                        $options[$day][$interval] = 1;
        
        update_user_meta( $user_id, self::$user_key, maybe_serialize( $options ) );
    }
    
    /**
     * intervals( $intervals )
     *
     * Populates the self::$intervals with translated values
     * @param Mixed $intervals, the initial values
     * @return Mixed, the populated data
     */
    function intervals( $intervals ) {
        return array(
            'morning' => __( 'Morning', 'rota' ),
            'midday' => __( 'Midday', 'rota' ),
            'afternoon' => __( 'Afternoon', 'rota' )
        );
    }
    
    /**
     * days( $days )
     *
     * Populates the self::$days with translated values
     * @param Mixed $days, the initial values
     * @return Mixed, the populated data
     */
    function days( $days ) {
        return array(
            'monday' => __( 'Monday', 'rota' ),
            'tuesday' => __( 'Tuesday', 'rota' ),
            'wednesday' => __( 'Wednesday', 'rota' ),
            'thursday' => __( 'Thursday', 'rota' ),
            'friday' => __( 'Friday', 'rota' ),
            'saturday' => __( 'Saturday', 'rota' ),
            'sunday' => __( 'Sunday', 'rota' )
        );
    }
    
    /**
     * template_render( $name, $vars = null, $echo = true )
     *
     * Helper to load and render templates easily
     * @param String $name, the name of the template
     * @param Mixed $vars, some variables you want to pass to the template
     * @param Boolean $echo, to echo the results or return as data
     * @return String $data, the resulted data if $echo is `false`
     */
    function template_render( $name, $vars = null, $echo = true ) {
        ob_start();
        if( !empty( $vars ) )
            extract( $vars );
        
        if( !isset( $path ) )
            $path = dirname( __FILE__ ) . '/templates/';
        
        include $path . $name . '.php';
        
        $data = ob_get_clean();
        
        if( $echo )
            echo $data;
        else
            return $data;
    }
}
?>
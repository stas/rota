<?php
class Rota {
    /**
     * Rota meta key
     */
    public static $user_key = 'rota_options';
    
    /**
     * Rota day key
     */
    public static $day_key = 'rota_day';
    
    /**
     * Rota availability intervals Key
     */
    public static $interval_key = 'rota_interval';
    
    /**
     * Rota location key
     */
    public static $location_key = 'rota_location';
    
    /**
     * Static Constructor
     */
    function init() {
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
        $days = get_option( self::$day_key );
        return apply_filters( 'rota_days', $days );
    }
    
    /**
     * Loads intervals for rota, applies filters too
     * @return Mixed, set of intervals
     */
    function get_intervals() {
        $intervals = get_option( self::$interval_key );
        return apply_filters( 'rota_intervals', $intervals );
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
        
        // Add day
        if( isset( $_POST['rota_day_nonce'] ) && wp_verify_nonce( $_POST['rota_day_nonce'], 'rota' ) ) {
            if( isset( $_POST['day'] ) && count( $_POST['day'] ) != 0 )
                if( self::add_day( $_POST['day'] ) )
                    $flash = __( 'Days were updated', 'rota' );
                else
                    $flash = __( 'Days were not updated', 'rota' );
        }
        
        // Delete day
        if( isset( $_GET['_nonce'] ) && wp_verify_nonce( $_GET['_nonce'], 'rota_delete_day' ) ) {
            if( isset( $_GET['del_day'] ) && !empty( $_GET['del_day'] ) )
                if( self::delete_day( $_GET['del_day'] ) )
                    $flash = sprintf( __( 'Day: %s was deleted.', 'rota' ), $_GET['del_day'] );
                else
                    $flash = sprintf( __( 'Day: %s was not deleted.', 'rota' ), $_GET['del_day'] );
        }
        
        // Add interval
        if( isset( $_POST['rota_interval_nonce'] ) && wp_verify_nonce( $_POST['rota_interval_nonce'], 'rota' ) ) {
            if( isset( $_POST['interval'] ) && count( $_POST['interval'] ) != 0 )
                if( self::add_interval( $_POST['interval'] ) )
                    $flash = __( 'Intervals were updated', 'rota' );
                else
                    $flash = __( 'Intervals were not updated', 'rota' );
        }
        
        // Delete interval
        if( isset( $_GET['_nonce'] ) && wp_verify_nonce( $_GET['_nonce'], 'rota_delete_interval' ) ) {
            if( isset( $_GET['del_interval'] ) && !empty( $_GET['del_interval'] ) )
                if( self::delete_interval( $_GET['del_interval'] ) )
                    $flash = sprintf( __( 'Interval: %s was deleted.', 'rota' ), $_GET['del_interval'] );
                else
                    $flash = sprintf( __( 'Interval: %s was not deleted.', 'rota' ), $_GET['del_interval'] );
        }
        
        $vars['rota_permalink'] = menu_page_url( 'rota', false );
        $vars['edit_permalink'] = add_query_arg( '_nonce', wp_create_nonce('rota_edit'), $vars['rota_permalink'] );
        $vars['delete_permalink'] = add_query_arg( '_nonce', wp_create_nonce('rota_delete'), $vars['rota_permalink'] );
        $vars['delete_day_permalink'] = add_query_arg( '_nonce', wp_create_nonce('rota_delete_day'), $vars['rota_permalink'] );
        $vars['delete_interval_permalink'] = add_query_arg( '_nonce', wp_create_nonce('rota_delete_interval'), $vars['rota_permalink'] );
        $vars['flash'] = $flash;
        $vars['locations'] = get_option( self::$location_key );
        $vars['days'] = self::get_days();
        $vars['intervals'] = self::get_intervals();
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
        $days = self::get_days();
        $intervals = self::get_intervals();
        $locations = get_option( self::$location_key );
        $results = array(
            'users' => null,
            'undone_locations' => null,
            'left_users' => null
        );
        
        // Do the scheduling
        if( $days && $intervals && $locations )
            $results = self::doTheMath( $days, $intervals, $locations );
        
        $vars['users'] = $results['users'];
        $vars['days'] = $days;
        $vars['intervals'] = $intervals;
        $vars['locations'] = $locations;
        $vars['undone_locations'] =  $results['undone_locations'];
        $vars['left_users'] =  $results['left_users'];
        $vars['today'] = strtolower( date( 'l' ) );
        self::template_render( 'ui', $vars );
        // Not fancy, I know :)
        die();
    }
    
    /**
     * randomize( $list, $step )
     *
     * A randomization algorith, that uses $step as the entropy criterion
     * @param Mixed $list, list to be randomized
     * @param Int $step, the entropy criterion
     * @return Mixed, the randomized list
     */
    function randomize( $list, $step ) {
        $new_list = array();
        $list_size = count( $list );
        $new_list_size = 0;
        $array = array_reverse( $list );
        
        // If the array is the same size as $step, return the reversed array
        if( $list_size == $step || $step < 2 )
            return $list;
        
        // Do this until the $new_list_size is the same as $list_size
        while( $list_size >= 0 ) {
            // Get the new index calculated using $step
            $index = $list_size % $step;
            // Move the index value from $list to $new_list
            if( isset( $list[$index] ) )
                $new_list[] = $list[$index];
            
            // Remove the moved value
            unset( $list[$index] );
            // (De/In)crement counters
            $new_list_size++;
            $list_size--;
            // Rebuild the array
            $list = array_values( $list );
        }
        
        return $new_list;
    }
    
    /**
     * doTheMath( $days, $intervals, $locations )
     *
     * Calculates the scheduling
     * @param Mixed $days, the days of the schedule
     * @param Mixed $intervals, the intervals of the schedule
     * @param Mixed $locations, the locations of the schedule
     * @return Mixed, the resulted userlist with left and undone users/locations
     */
    function doTheMath( $days, $intervals, $locations ) {
        $users = array();
        $intervals_num = count( $intervals );
        $undone_locations = array();
        $left_users = array();
        
        foreach ( $days as $d )
            foreach ( $intervals as $i ) {
                // Get the available users list
                $userlist = self::usersByDayInt( $d['name'], $i['name'], $intervals_num );
                $r = array_search( $i, $intervals ) + array_search( $d, $days );
                // Try to assign fairly busy people to $locations
                $busy_users_count = count( $userlist['busy'] );
                while( $busy_users_count > 0 )
                    foreach ( $locations as $l ){
                        // Stop if no more users left
                        if( $busy_users_count == 0 )
                            break;
                        
                        // To ignore notices set the variable
                        if( !isset( $users[ $d['name'] ][ $i['name'] ][ $l['name'] ] ) )
                            $users[ $d['name'] ][ $i['name'] ][ $l['name'] ] = array();
                        
                        // Skip locations with 0 rota size
                        if( $l['size'] > 0 && count( $users[ $d['name'] ][ $i['name'] ][ $l['name'] ] ) < $l['size'] ) {
                            // Randomize userlist
                            $userlist['busy'] = self::randomize( $userlist['busy'], $r * $l['size'] );
                            // Assign user
                            $users[ $d['name'] ][ $i['name'] ][ $l['name'] ][] = array_shift( $userlist['busy'] );
                            $busy_users_count--;
                        }
                    }
                
                // Cycle through all the available (free) users and try to assign them fairly across $undone_locations
                $free_users_count = count( $userlist['free'] );
                while( $free_users_count > 0 ) {
                    // Randomize the locations to reduce the same location assignment probability
                    foreach ( $locations as $l ) {
                        // Stop if no more users left
                        if( $free_users_count == 0 )
                            break;
                        
                        // Randomize free users
                        $userlist['free'] = self::randomize( $userlist['free'], $r * $l['size'] );
                        // Check if the array was initiated already
                        if( !isset( $users[ $d['name'] ][ $i['name'] ][ $l['name'] ] ) )
                            $users[ $d['name'] ][ $i['name'] ][ $l['name'] ] = array();
                        // Assign user to location
                        if( $l['size'] > 0 && count( $users[ $d['name'] ][ $i['name'] ][ $l['name'] ] ) < $l['size'] ) {
                            $users[ $d['name'] ][ $i['name'] ][ $l['name'] ][] = array_shift( $userlist['free'] );
                            $free_users_count--;
                        }
                        
                        // Location size was achieved
                        if ( count( $users[ $d['name'] ][ $i['name'] ][ $l['name'] ] ) == $l['size'] )
                            // Remove the location from undone
                            unset( $undone_locations[ $l['name'] ] );
                        else
                            $undone_locations[ $l['name'] ] = $l;
                    }
                }
                
                // Save left out users
                foreach( $userlist as $left )
                    $left_users = array_merge( $left_users, $left );
            }
        
        return array(
            'users' => $users,
            'undone_locations' => $undone_locations,
            'left_users' => $left_users
        );
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
                if( count( array_filter( $uid_opts[$day] ) ) > 0 )
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
     * delete_day( $name )
     *
     * Find and delete an existent day
     * @param String $name, the ID of the day
     * @return Boolean, True on success, false on failure
     */
    function delete_day( $name ) {
        $name = sanitize_title( $name );
        $days = self::get_days();
        for( $i = 0; $i < count( $days ); $i++ )
            if( $days[$i]['name'] == $name ) {
                unset( $days[$i] );
                update_option( self::$day_key, $days );
                return true;
            }
        
        return false;
    }
    
    /**
     * delete_interval( $name )
     *
     * Find and delete an existent interval
     * @param String $name, the ID of the interval
     * @return Boolean, True on success, false on failure
     */
    function delete_interval( $name ) {
        $name = sanitize_title( $name );
        $intervals = self::get_intervals();
        for( $i = 0; $i < count( $intervals ); $i++ )
            if( $intervals[$i]['name'] == $name ) {
                unset( $intervals[$i] );
                update_option( self::$interval_key, $intervals );
                return true;
            }
        
        return false;
    }
    
    /**
     * add_day( $d )
     * 
     * Handle day adding
     * @param Mixed $d, an array with day details
     * @return Boolean, True on success, false on failure
     */
    function add_day( $d ) {
        $days = get_option( self::$day_key );
        $day = array(
            'name' => null,
            'title' => null
        );
        
        $day['name'] = sanitize_title( $d['title'] );
        $day['title'] = sanitize_text_field( $d['title'] );
        $day = array_filter( $day );
        
        if( count( $day ) == 2 ) {
            if( !$days )
                update_option( self::$day_key, array( $day ) );
            else {
                $days[] = $day;
                update_option( self::$day_key, $days );
            }
            return true;
        } else
            return false;
    }
    
    /**
     * add_day( $i )
     * 
     * Handle interval adding
     * @param Mixed $i, an array with interval details
     * @return Boolean, True on success, false on failure
     */
    function add_interval( $i ) {
        $intervals = get_option( self::$interval_key );
        $interval = array(
            'name' => null,
            'title' => null
        );
        
        $interval['name'] = sanitize_title( $i['title'] );
        $interval['title'] = sanitize_text_field( $i['title'] );
        $interval = array_filter( $interval );
        
        if( count( $interval ) == 2 ) {
            if( !$intervals )
                update_option( self::$interval_key, array( $interval ) );
            else {
                $intervals[] = $interval;
                update_option( self::$interval_key, $intervals );
            }
            return true;
        } else
            return false;
    }
    
    /**
     * add_location( $l )
     * 
     * Handle locations adding
     * @param Mixed $l, an array with location details
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
        foreach( $days as $d )
            foreach( $intervals as $i )
                $dummy_data[ $d['name'] ][ $i['name'] ] = null;
        
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
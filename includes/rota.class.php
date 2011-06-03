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
     * Rota location delta key
     */
    public static $delta_key = 'rota_delta';
    
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
     * get_deltas( $days = null, $intervals = null )
     * 
     * Loads deltas for rota, applies filters too
     * @param Mixed $days, a set of days, leave default `null` to skip filling
     * @param Mixed $intervals, a set of intervals, leave default `null` to skip filling
     * @return Mixed, set of deltas
     */
    function get_deltas( $days = null, $intervals = null ) {
        $deltas = get_option( self::$delta_key );
        // Skip filling if not required
        if( !$days && !$intervals )
            return $deltas;
        
        $filled_deltas = array();
        // Fill required values instead of `all`
        foreach( $deltas as $dd ) {
            if( !isset( $filled_deltas[ $dd['location'] ] ) )
                $filled_deltas[ $dd['location'] ] = array();
            
            // Fill days
            if( $dd['day'] == 'all' ) {
                foreach ( $days as $d )
                    if( !isset( $filled_deltas[ $dd['location'] ][ $d['name'] ] ) )
                        $filled_deltas[ $dd['location'] ][ $d['name'] ] = array();
            } else
                $filled_deltas[ $dd['location'] ][ $dd['day'] ] = array();
            
            // Fill intervals
            if( $dd['interval'] == 'all' )
                foreach( $filled_deltas[ $dd['location'] ] as $day => $interval )
                    foreach ( $intervals as $i )
                        $filled_deltas[ $dd['location'] ][ $day ][ $i['name'] ] = $dd['size'];
            else 
                foreach( $filled_deltas[ $dd['location'] ] as $day => $interval )
                    $filled_deltas[ $dd['location'] ][ $day ][ $dd['interval'] ] = $dd['size'];
        }
        
        return apply_filters( 'rota_deltas', $filled_deltas );
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
        
        // Add delta
        if( isset( $_POST['rota_delta_nonce'] ) && wp_verify_nonce( $_POST['rota_delta_nonce'], 'rota' ) ) {
            if( isset( $_POST['delta'] ) && count( $_POST['delta'] ) != 0 )
                if( self::add_delta( $_POST['delta'] ) )
                    $flash = __( 'Deltas were updated', 'rota' );
                else
                    $flash = __( 'Deltas were not updated', 'rota' );
        }
        
        // Delete delta
        if( isset( $_GET['_nonce'] ) && wp_verify_nonce( $_GET['_nonce'], 'rota_delete_delta' ) ) {
            if( isset( $_GET['del_delta'] ) && !empty( $_GET['del_delta'] ) )
                if( self::delete_delta( $_GET['del_delta'] ) )
                    $flash = sprintf( __( 'Delta: %s was deleted.', 'rota' ), $_GET['del_delta'] );
                else
                    $flash = sprintf( __( 'Delta: %s was not deleted.', 'rota' ), $_GET['del_delta'] );
        }
        
        $vars['rota_permalink'] = menu_page_url( 'rota', false );
        $vars['edit_permalink'] = add_query_arg( '_nonce', wp_create_nonce('rota_edit'), $vars['rota_permalink'] );
        $vars['delete_permalink'] = add_query_arg( '_nonce', wp_create_nonce('rota_delete'), $vars['rota_permalink'] );
        $vars['delete_day_permalink'] = add_query_arg( '_nonce', wp_create_nonce('rota_delete_day'), $vars['rota_permalink'] );
        $vars['delete_interval_permalink'] = add_query_arg( '_nonce', wp_create_nonce('rota_delete_interval'), $vars['rota_permalink'] );
        $vars['delete_delta_permalink'] = add_query_arg( '_nonce', wp_create_nonce('rota_delete_delta'), $vars['rota_permalink'] );
        $vars['flash'] = $flash;
        $vars['locations'] = get_option( self::$location_key );
        $vars['days'] = self::get_days();
        $vars['intervals'] = self::get_intervals();
        $vars['deltas'] = self::get_deltas();
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
        $deltas = self::get_deltas( $days, $intervals );
        $user_options = array();
        $uids = get_users( array( 'fields' => 'user_id', 'role' => 'subscriber' ) );
        $results = array(
            'users' => null,
            'undone_locations' => null,
            'left_users' => null
        );
        
        /// Get users and options
        if( !empty( $uids ) )
            foreach( $uids as $uid )
                $user_options[$uid] = self::get_user_options( $uid );
        
        // Do the scheduling
        if( $days && $intervals && $locations )
            $results = self::doTheMath( $days, $intervals, $locations, $deltas );
        
        // Calculate every user usages
        if( !empty( $user_options ) && !empty( $results ) )
                foreach ( $results['users'] as $dayname => $d )
                    if( !empty( $d ) )
                        foreach ( $d as $iname => $i )
                            foreach ( $i as $lname => $l )
                                if( !empty( $l ) )
                                    foreach ( $l as $uid )
                                        if( $uid ) {
                                            if( !isset( $user_options[$uid]['counted'] ) )
                                                $user_options[$uid]['counted'] = 1;
                                            else {
                                                $user_options[$uid]['counted']++;
                                                if( $user_options[$uid]['counted'] > count( $days ) ) {
                                                    $uid_index = array_search( $uid, $l );
                                                    $results['users'][$dayname][$iname][$lname][$uid_index] = "$uid" . "*";
                                                }
                                            }
                                        }
        
        $vars['users'] = $results['users'];
        $vars['user_options'] = $user_options;
        $vars['days'] = $days;
        $vars['intervals'] = $intervals;
        $vars['locations'] = $locations;
        $vars['deltas'] = $deltas;
        $vars['undone_locations'] =  $results['undone_locations'];
        $vars['left_users'] =  $results['left_users'];
        $vars['today'] = strtolower( date( 'l' ) );
        // If we need csv export do that
        if( isset( $_GET['csv'] ) )
            self::template_render( 'csv', $vars );
        elseif( isset( $_GET['csv_users'] ) )
            self::template_render( 'csv_users', $vars );
        else
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
     * hasDelta( $deltas, $location, $day, $interval, $failsafe )
     * 
     * Checks for a delta for $location, at $day with $interval, returns $failsafe if none
     * @param Mixed $deltas
     * @param String $location
     * @param String $day
     * @param String $interval
     * @param Int $failsafe, default size
     * @return Int new size, or $failsafe on none
     */
    function hasDelta( $deltas, $location, $day, $interval, $failsafe ) {
        if( isset( $deltas[ $location ] ) )
            if( isset( $deltas[ $location ][ $day ] ) )
                if( isset( $deltas[ $location ][ $day ][ $interval ] ) )
                    return $deltas[ $location ][ $day ][ $interval ];
        return $failsafe;
    }
    
    /**
     * unused_first_location( $existing, $pool, $location )
     * 
     * Will reorder the $location $pool so an unused user in $existing day will be the first element
     * @param Mixed $existing, an array of existing day with intervals and locations
     * @param Mixed $pool, a pool of elements to search for unique
     * @param String $location, the location to be checked
     * @return Mixed $pool, modified initial $pool, or the same if nothing found
     */
    function unused_first_location( $existing, $pool, $location ) {
        $location_users = array();
        $found = null;
        
        // Find all locaton users across all intervals
        foreach ( $existing as $i ) {
            // Cleanup
            unset( $i[$location]['needed'] );
            $location_users = array_merge( $i[$location], $location_users );
        }
        
        // Try to find uniques
        $diff = array_diff( $pool, $location_users );
        
        // Find a non-repeating one from a list of uniques
        if( !empty( $diff ) )
            $found = array_shift( $diff );
        // Try to put it as first element
        if( $found ) {
            $found_index = array_search( $found, $pool );
            if( $found_index != 0 ) {
                unset( $pool[$found_index] );
                array_unshift( $pool, $found );
            }
        }
        
        return $pool;
    }
    
    /**
     * unused_first( $existing, $pool, $locations )
     * 
     * Will reorder the $locations $pool so an unused user in $existing day will be the first element
     * @param Mixed $existing, an array of existing day with intervals and locations
     * @param Mixed $pool, a pool of elements to search for unique
     * @param Mixed $locations, the locations to be checked
     * @return Mixed $pool, modified initial $pool, or the same if nothing found
     */
    function unused_first( $existing, $pool, $locations ) {
        $day_users = array();
        $found = null;
        
        // Find all locaton users across all intervals
        foreach ( $existing as $i )
            foreach ( $locations as $l ) {
                // Cleanup
                unset( $i[ $l['name'] ]['needed'] );
                $day_users = array_merge( $i[ $l['name'] ], $day_users );
            }
        
        // Try to find uniques
        $diff = array_diff( $pool, $day_users );
        
        // Find a non-repeating one from a list of uniques
        if( !empty( $diff ) )
            $found = array_shift( $diff );
        
        // Try to put it as first element
        if( $found ) {
            $found_index = array_search( $found, $pool );
            if( $found_index != 0 ) {
                unset( $pool[$found_index] );
                array_unshift( $pool, $found );
            }
        }
        
        return $pool;
    }
    
    /**
     * doTheMath( $days, $intervals, $locations, $deltas )
     *
     * Calculates the scheduling
     * @param Mixed $days, the days of the schedule
     * @param Mixed $intervals, the intervals of the schedule
     * @param Mixed $locations, the locations of the schedule
     * @param Mixed $deltas, the deltas of the locations
     * @return Mixed, the resulted userlist with left and undone users/locations
     */
    function doTheMath( $days, $intervals, $locations, $deltas ) {
        $users[] = array();
        $undone_locations[] = array();
        $left_users = array();
        $left_locations = array();
        
        /* Update locations size and populate $users */
        foreach ( $days as $d )
            foreach ( $intervals as $i )
                foreach ( $locations as $l ) {
                    // Get the delta if anyone exists
                    $size = self::hasDelta( $deltas, $l['name'], $d['name'], $i['name'], $l['size'] );
                    // This one to reduce notices noise
                    if( !isset( $users[ $d['name'] ][ $i['name'] ][ $l['name'] ] ) )
                            $users[ $d['name'] ][ $i['name'] ][ $l['name'] ] = array();
                    // Update the required size
                    $users[ $d['name'] ][ $i['name'] ][ $l['name'] ]['needed'] = $size;
                    // Add the location as undone
                    $undone_locations[ $d['name'] ][ $i['name'] ][ $l['name'] ] = $l;
                }
        
        // Try a fair distribution
        foreach ( $days as $d )
            foreach ( $intervals as $i ) {
                // Get the available users list
                $userlist = self::usersByDayInt( $d['name'], $i['name'] );
                $r = array_search( $i, $intervals ) + array_search( $d, $days ) + 1;
                
                $busy_left = count( $userlist['busy'] );
                $free_left = count( $userlist['free'] );
                $locations_left = count( $undone_locations[ $d['name'] ][ $i['name'] ] );
                // Fair distribution
                while( $locations_left > 0 && ( $busy_left + $free_left ) > 0 ) {
                    // Check the locations
                    foreach ( $undone_locations[ $d['name'] ][ $i['name'] ] as $l ) {
                        // Get the needed value
                        $needed = $users[ $d['name'] ][ $i['name'] ][ $l['name'] ]['needed'];
                        
                        // Try to assign the users
                        if ( $needed > 0 ) {
                            // Randomize userlist
                            $userlist['busy'] = self::randomize( $userlist['busy'], $r * $needed );
                            $userlist['free'] = self::randomize( $userlist['free'], $r * $needed );
                            
                            // Try the busy userlist
                            if( count( $userlist['busy'] ) > 0 ) {
                                // Find a non-repeating user
                                $userlist['busy'] = self::unused_first( $users[ $d['name'] ], $userlist['busy'], $locations );
                                // Assign user
                                $users[ $d['name'] ][ $i['name'] ][ $l['name'] ][] = array_shift( $userlist['busy'] );
                                // Decrement the `needed` key value
                                $users[ $d['name'] ][ $i['name'] ][ $l['name'] ]['needed']--;
                                // Decrement busy users
                                $busy_left--;
                            } elseif( count( $userlist['free'] ) > 0 ) {
                                // Find a non-repeating user
                                $userlist['free'] = self::unused_first( $users[ $d['name'] ], $userlist['free'], $locations );
                                // Assign user
                                $users[ $d['name'] ][ $i['name'] ][ $l['name'] ][] = array_shift( $userlist['free'] );
                                // Decrement the `needed` key value
                                $users[ $d['name'] ][ $i['name'] ][ $l['name'] ]['needed']--;
                                // Decrement free users
                                $free_left--;
                            }
                        } else {
                            // Location filled, decrement one from left
                            unset( $undone_locations[ $d['name'] ][ $i['name'] ][ $l['name'] ] );
                            $locations_left--;
                        }
                    }
                }
                // Save left out users
                foreach( $userlist as $left )
                    $left_users = array_merge( $left_users, $left );
                // Save left locations
                foreach ( $locations as $l ) {
                    $size = $users[ $d['name'] ][ $i['name'] ][ $l['name'] ]['needed'];
                    // Cleanup
                    unset( $users[ $d['name'] ][ $i['name'] ][ $l['name'] ]['needed'] );
                    if( $size > 0 )
                        $left_locations[ $l['name'] ] = $l;
                }
            }
        
        return array(
            'users' => $users,
            'undone_locations' => array_filter( $left_locations ),
            'left_users' => array_unique( $left_users )
        );
    }
    
    /**
     * usersByDayInt( $day, $interval )
     *
     * Generates a users list by day/interval availability using availability as a priority
     * @param String $day, day id
     * @param String $interval, interval id
     * @return Mixed array of user ids list
     */
    function usersByDayInt( $day, $interval ) {
        // Busy for users with worst availability, free for the rest
        $users = array( 'busy' => array(), 'free' => array() );
        // Select only subscribers
        $uids = get_users( array( 'fields' => 'user_id', 'role' => 'subscriber' ) );
        foreach( $uids as $uid ){
            $uid_opts = self::get_user_options( $uid );
            
            // Check if user didn't exclude this interval
            if( !$uid_opts[$day][$interval] ) {
                // Calculate priority by availability options number
                if( count( array_filter( $uid_opts[$day] ) ) > 1 )
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
        $locations = array_values( get_option( self::$location_key ) );
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
        $days = array_values( self::get_days() );
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
        $intervals = array_values( self::get_intervals() );
        for( $i = 0; $i < count( $intervals ); $i++ )
            if( $intervals[$i]['name'] == $name ) {
                unset( $intervals[$i] );
                update_option( self::$interval_key, $intervals );
                return true;
            }
        
        return false;
    }
    
    /**
     * delete_delta( $name )
     *
     * Find and delete an existent delta
     * @param String $name, the ID of the delta
     * @return Boolean, True on success, false on failure
     */
    function delete_delta( $name ) {
        $name = sanitize_title( $name );
        $deltas = array_values( self::get_deltas() );
        for( $i = 0; $i < count( $deltas ); $i++ )
            if( $deltas[$i]['name'] == $name ) {
                unset( $deltas[$i] );
                update_option( self::$delta_key, $deltas );
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
     * add_delta( $d )
     * 
     * Handle delta adding
     * @param Mixed $d, an array with delta details
     * @return Boolean, True on success, false on failure
     */
    function add_delta( $d ) {
        $deltas = get_option( self::$delta_key );
        $delta = array(
            'name' => null,
            'title' => null,
            'location' => null,
            'day' => null,
            'interval' => null,
            'size' => null
        );
        
        $delta['name'] = sanitize_title( "d" . time() );
        $delta['location'] = sanitize_title( $d['location'] );
        $delta['day'] = sanitize_title( $d['day'] );
        $delta['interval'] = sanitize_title( $d['interval'] );
        $delta = array_filter( $delta );
        $delta['size'] = intval( $d['size'] );
        
        if( count( $delta ) == 5 ) {
            if( !$deltas )
                update_option( self::$delta_key, array( $delta ) );
            else {
                $deltas[] = $delta;
                update_option( self::$delta_key, $deltas );
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
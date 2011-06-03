<?php
    // Serve as CSV mimetype
    header("Content-Type: text/csv");
    header("Content-Disposition: inline; filename=rota_" . time() . ".csv ");
    
    // Build the CSV content
    $counted_intervals = count( $intervals );
    if( $days && $intervals && $user_options ) {
        $days_line = "\t,";
        $intervals_line = "\t,";
        foreach ( $days as $d ) {
            foreach ( range(1, $counted_intervals) as $ci ) {
                $days_line .= '"' . $d['title'] . '",';
                $d['title'] = '';
            }
            
            foreach ( $intervals as $i )
                $intervals_line .= '"' . $i['title'] . '",';
        }
        echo $days_line;
        echo "\n";
        echo $intervals_line;
        echo "\n";
        
        // Parse users and availability
        foreach( $user_options as $uid => $uo ) {
            echo '"' . get_the_author_meta( 'display_name', $uid ) . '",';
            foreach ( $days as $d )
                foreach ( $intervals as $i )
                    echo $uo[ $d['name'] ][ $i['name'] ] ? "x," : ',';
            echo "\n";
        }
    }
?>
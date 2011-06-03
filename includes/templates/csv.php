<?php
// Serve as CSV mimetype
header("Content-Type: text/csv");
header("Content-Disposition: inline; filename=rota_" . time() . ".csv ");

// Build the CSV content
if( $days && $intervals && $locations ) {
    foreach ( $locations as $l ) {
        echo '"' . $l['title'] . '"';
        
        foreach ( $days as $d )
            echo ',"' . $d['title'] . '"';
        
        echo "\n";
        
        foreach ( $intervals as $i ) {
            for ( $j = 0; $j < $l['size']; $j++ ) {
                echo '"' . $i['title'] . '"';
                foreach ( $days as $d ) {
                    $uids = array_values( $users[ $d['name'] ][ $i['name'] ][ $l['name'] ] );
                    if ( isset( $uids[$j] ) )
                        echo ',"' . get_the_author_meta( 'display_name', $uids[$j] ) . '"';
                    else
                        echo ",n/a";
                }
                echo "\n";
            }
            echo "\n";
        }
        echo "\n";
    }
}
?>
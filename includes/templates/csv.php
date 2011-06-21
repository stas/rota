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
        $size = $l['size'];
        foreach ( $intervals as $i ) {
            $changed = false;
            if( $size )
                for ( $j = $size; $j >= 0; $j-- ) {
                    echo '"' . $i['title'] . '"';
                    foreach ( $days as $d ) {
                        if( !$changed ) {
                            $j = self::hasDelta( $deltas, $l['name'], $d['name'], $i['name'], $j ) - 1;
                            $changed = true;
                        }
                        $uids = array_values( $users[ $d['name'] ][ $i['name'] ][ $l['name'] ] );
                        if ( isset( $uids[$j] ) ) {
                            if( strchr( $uids[$j], "*" ) )
                                echo ',"' . get_the_author_meta( 'display_name', str_replace( "*", "", $uids[$j] ) ) . '~"';
                            else
                                echo ',"' . get_the_author_meta( 'display_name', $uids[$j] ) . '"';
                        } else
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
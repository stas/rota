<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php bloginfo('name'); ?> &mdash; <?php _e( 'Rota Management', 'rota' ) ?></title>
    <link rel="stylesheet" href="https://github.com/csswizardry/inuit.css/raw/master/css/inuit.css" id="inuitCSS">
    <style type="text/css">
        html { width: 99%; background: none; }
        body { width: 90%; margin: 10px auto; background: none; color: #666; }
        .week { border-bottom: 1px solid #CCC; margin-bottom: 20px; }
        .locations h5, .locations h6 { margin-bottom: 0; }
        .interval { float: right; width: 10px; font-size: 10px; text-decoration: underline; }
        .userlist { margin-left: 5px; list-style-position: inside; }
        .fail { color: #CC0000; }
        li.fail { list-style: none; font-size: small; }
        .vertical { -moz-transform: rotate(90deg); -moz-transform-origin: 50% 50%; -webkit-transform: rotate(90deg); -webkit-transform-origin: 50% 50%; }
    </style>
</head>
<body>
    <div id="container">
        <header>
            <h1><?php bloginfo('description'); ?></h1>
        </header>
        
        <div id="main" role="main">
            <div class="locations">
                <?php if( $locations ) : ?>
                    <?php foreach ( $locations as $l ) : ?>
                        <h5 class="location"><?php echo $l['title'] ?> (<?php echo $l['size'] ?>)</h5>
                        <div class="week grids">
                            <?php foreach ( $days as $d => $dayname ) : ?>
                                <div class="day grid grid-3">
                                    <h6><?php echo $dayname ?></h6>
                                    <?php foreach ( $intervals as $i => $intname ): ?>
                                        <em class="interval vertical"><?php echo $intname; ?></em>
                                        <ol class="userlist">
                                            <?php if( count( $users[$d][$i] ) > 0 ) : ?>
                                                <?php foreach ( $users[$d][$i] as $uid ) : ?>
                                                    <li><?php the_author_meta( 'display_name', $uid ); ?></li>
                                                <?php endforeach; ?>
                                            <?php else : ?>
                                                <li class="fail"><?php _e( 'No users available' ); ?></li>
                                            <?php endif; ?>
                                        </ol>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <h3 class="fail"><?php _e( 'No locations found, please add some.', 'rota' ) ?></h3>
                <?php endif; ?>
            </div>
        </div>
        
        <footer>
            
        </footer>
    </div>
</body>
</html>
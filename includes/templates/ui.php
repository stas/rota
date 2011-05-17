<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php bloginfo('name'); ?> &mdash; <?php _e( 'Rota Management', 'rota' ) ?></title>
    <link rel="stylesheet" href="https://github.com/csswizardry/inuit.css/raw/master/css/inuit.css" id="intuitCss">
    <style type="text/css">
        html { width: 99%; background: none; }
        body { width: 90%; margin: 10px auto; background: none; color: #666; }
        .week { border-bottom: 1px solid #CCC; margin-bottom: 20px; }
        .locations h5, .locations h6 { margin-bottom: 0; }
        .day em { text-align: right; font-size: small; font-weight: bolder; }
        .userlist { margin-left: 5px; list-style-position: inside; }
        li.no-users { list-style: none; color: #990000; font-size: small; }
    </style>
</head>
<body>
    <?php #var_dump( $users ); ?>
    <div id="container">
        <header>
            <h1><?php bloginfo('description'); ?></h1>
        </header>
        
        <div id="main" role="main">
            <div class="locations">
                <?php foreach ( $locations as $l ) : ?>
                    <h5 class="location"><?php echo $l['title'] ?> (<?php echo $l['size'] ?>)</h5>
                    <div class="week grids">
                        <?php foreach ( $days as $d => $dayname ) : ?>
                            <div class="day grid grid-3">
                                <h6><?php echo $dayname ?></h6>
                                <?php foreach ( $intervals as $i => $intname ): ?>
                                    <em><?php echo $intname; ?></em>
                                    <ol class="interval userlist">
                                        <?php if( count( $users[$d][$i] ) > 0 ) : ?>
                                            <?php foreach ( $users[$d][$i] as $uid ) : ?>
                                                <li><?php the_author_meta( 'user_nicename', $uid ); ?></li>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <li class="no-users"><?php _e( 'No users available' ); ?></li>
                                        <?php endif; ?>
                                    </ol>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <footer>
            
        </footer>
    </div>
</body>
</html>
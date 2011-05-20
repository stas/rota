<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php bloginfo('name'); ?> &mdash; <?php _e( 'Rota Management', 'rota' ) ?></title>
    <link rel="stylesheet" href="<?php echo ROTA_WEB_ROOT . '/css/inuit.css' ?>" id="inuitCSS">
    <link rel="stylesheet" href="<?php echo ROTA_WEB_ROOT . '/css/rota.css' ?>" id="rotaCSS">
</head>
<body>
    <div id="container">
        <header>
            <h1><?php bloginfo('description'); ?></h1>
            <div class="locations-list" id="top">
                <h2><?php _e( 'Locations list', 'rota' ) ?></h2>
                <?php if( $locations ) : ?>
                    <ul>
                    <?php foreach ( $locations as $l ) : ?>
                        <li><a href="#<?php echo $l['name'] ?>"><?php echo $l['title'] ?> (<?php echo $l['size'] ?>)</a></li>
                    <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="grids undone">
                <div class="grid grid-5">
                    <h4><?php _e( 'Left locations', 'rota' ) ?>: <?php echo count( $undone_locations ); ?></h4>
                    <?php if( count( $undone_locations ) > 0 ) : ?>
                    <ol>
                        <?php foreach( $undone_locations as $ul ) : ?>
                            <li><?php echo $ul['title']; ?></li>
                        <?php endforeach; ?>
                    </ol>
                    <?php endif; ?>
                </div>
                <div class="grid grid-5 end">
                    <h4><?php _e( 'Left people', 'rota' ) ?>: <?php echo count( $left_users ); ?></h4>
                    <?php if( count( $left_users ) > 0 ) : ?>
                    <ol>
                        <?php foreach( $left_users as $luid ) : ?>
                            <li><?php the_author_meta( 'display_name', $luid ); ?></li>
                        <?php endforeach; ?>
                    </ol>
                    <?php endif; ?>
                </div>
            </div>
        </header>
        
        <div id="main" role="main">
            <div class="locations">
                <?php if( $locations ) : ?>
                    <?php foreach ( $locations as $l ) : ?>
                        <h5 class="location" id="<?php echo $l['name'] ?>"><?php echo $l['title'] ?> (<?php echo $l['size'] ?>) <a href="#top">&uarr;</a></h5>
                        <div class="week grids">
                            <?php foreach ( $days as $d => $dayname ) : ?>
                                <div class="day grid grid-3 <?php echo ( $d == $today ) ? 'today' : ''; ?>">
                                    <h6><?php echo $dayname ?></h6>
                                    <?php foreach ( $intervals as $i => $intname ): ?>
                                        <em class="interval vertical"><?php echo $intname; ?></em>
                                        <ol class="userlist">
                                            <?php if( count( $users[$d][$i][ $l['name'] ] ) > 0 ) : ?>
                                                <?php foreach ( $users[$d][$i][ $l['name'] ] as $uid ) : ?>
                                                    <li><?php the_author_meta( 'display_name', $uid ); ?></li>
                                                <?php endforeach; ?>
                                            <?php else : ?>
                                                <li class="fail"><?php _e( 'None available' ); ?></li>
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
            <?php _e( 'Yes, it\'s a <a href="htt://wordpress.org/">WordPress</a>!', 'rota' ); ?> &#9996; <?php wp_loginout( admin_url() ); ?>.
        </footer>
    </div>
</body>
</html>
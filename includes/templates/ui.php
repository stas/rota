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
                        <?php foreach( $undone_locations as $ul_name => $ul ) : ?>
                            <li><?php echo $ul['title']; ?></li>
                        <?php endforeach; ?>
                    </ol>
                    <?php endif; ?>
                </div>
                <div class="grid grid-5 end">
                    <h4><?php _e( 'People', 'rota' ) ?>: <?php echo count( $user_options ); ?></h4>
                    <?php if( !empty( $left_users ) && !empty( $user_options ) ) : ?>
                    <ol>
                        <?php foreach( $user_options as $luid => $luo ) : ?>
                            <li>
                                <?php the_author_meta( 'display_name', $luid ); ?>
                                (<?php echo $user_options[$luid]['counted'] ?>)
                                <?php if( !in_array( $luid, $left_users ) ) echo "*"; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                    <?php endif; ?>
                </div>
            </div>
        </header>
        
        <div id="main" role="main">
            <div class="locations">
                <?php if( $days && $intervals && $locations ) : ?>
                    <?php foreach ( $locations as $l ) : ?>
                        <h5 class="location" id="<?php echo $l['name'] ?>"><?php echo $l['title'] ?> (<?php echo $l['size'] ?>) <a href="#top">&uarr;</a></h5>
                        <div class="week grids">
                            <?php foreach ( $days as $d ) : ?>
                                <div class="day grid grid-3 <?php echo ( $d['name'] == $today ) ? 'today' : ''; ?>">
                                    <h6><?php echo $d['title'] ?></h6>
                                    <?php foreach ( $intervals as $i ): ?>
                                        <em class="interval vertical"><?php echo $i['title']; ?></em>
                                        <ol class="userlist">
                                            <?php if( !empty( $users[$d['name']][$i['name']][ $l['name'] ] ) ) : ?>
                                                <?php foreach ( $users[ $d['name'] ][ $i['name'] ][ $l['name'] ] as $uid ) : ?>
                                                    <?php if( strchr( $uid, "*" ) ): ?>
                                                    <li>
                                                        <?php echo get_avatar( $uid, '20' ); ?>
                                                        <?php the_author_meta( 'display_name', str_replace( "*", "", $uid ) ); ?><sup>~</sup>
                                                    </li>
                                                    <?php else: ?>
                                                    <li>
                                                        <?php echo get_avatar( $uid, '20' ); ?>
                                                        <?php the_author_meta( 'display_name', $uid ); ?>
                                                    </li>
                                                    <?php endif; ?>
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
                    <h3 class="fail"><?php _e( 'No days or intervals or locations found, please add some.', 'rota' ) ?></h3>
                <?php endif; ?>
            </div>
        </div>
        
        <footer>
            <?php _e( 'Yes, it\'s a <a href="htt://wordpress.org/">WordPress</a>!', 'rota' ); ?> &#9996; <?php wp_loginout( admin_url( 'options-general.php?page=rota' ) ); ?>.
        </footer>
    </div>
</body>
</html>
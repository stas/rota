<?php if( $flash ) { ?>
    <div id="message" class="updated fade">
        <p><strong><?php echo $flash; ?></strong></p>
    </div>
<?php } ?>
<div id="icon-tools" class="icon32"><br /></div>
<div class="wrap">
    <h2><?php _e( 'Rota Management','rota' ); ?></h2>
    <div id="poststuff" class="metabox-holder">
        <div class="postbox">
            <h3 class="hndle" ><?php _e( 'Available locations and rota size','rota' )?></h3>
            <div class="inside">
                <ol>
                    <?php if( !empty( $locations ) ) : ?>
                        <?php foreach ( $locations as $l ) : ?>
                            <li>
                                <strong><?php echo $l['title'] ?> (<?php echo $l['size'] ?>)</strong> &mdash;
                                <a href="<?php echo $edit_permalink; ?>&amp;edit=<?php echo $l['name']; ?>" class="button"><?php _e( 'Edit' )?></a>
                                <a href="<?php echo $delete_permalink; ?>&amp;del=<?php echo $l['name']; ?>" class="button"><?php _e( 'Remove' )?></a>
                            </li>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <li style="list-style: disc;"><?php _e( 'No locations yet.', 'rota' ); ?></li>
                    <?php endif; ?>
                </ol>
            </div>
        </div>
        
        <div class="postbox">
            <h3 class="hndle" ><?php echo !$location ? __( 'Add a location','rota' ) : __( 'Editing ','rota' ) . $location['title'] ?></h3>
            <div class="inside">
                <p>
                    <?php _e( 'Please add a location <code>Name</code> and required roster <code>Size</code>.','rota' )?>
                </p>
                <form action="" method="post">
                    <?php wp_nonce_field( 'rota', 'rota_nonce' ); ?>
                    
                    <p class="form-field">
                        <label for="location-name">
                            <?php _e( 'Name, an explicit title' ); ?>
                        </label>
                        <br/>
                        <input type="text" id="location-name" name="location[title]" value="<?php echo $location ? $location['title'] : '' ?>" <?php echo $location ? 'readonly' : '' ?> />
                    </p>
                    
                    <p class="form-field">
                        <label for="location-size">
                            <?php _e( 'Size, how many people you need there' ); ?>
                        </label>
                        <br/>
                        <input type="text" id="location-size" name="location[size]" style="width: 100px;" value="<?php echo $location ? $location['size'] : '' ?>" />
                    </p>
                    
                    <p>
                        <input type="submit" class="button-primary" value="<?php _e( 'Save Changes' )?>"/>
                        <?php if( $location ) : ?>
                            <a href="<?php echo $rota_permalink; ?>" class="button"><?php _e( 'Cancel' ); ?></a>
                        <?php endif; ?>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
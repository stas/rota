<table class="form-table rota-options">
    <tbody>
        <tr>
            <th scope="row">
                <?php _e( 'Rota Availability', 'rota' ); ?>
                <br/>
                <em><small><?php _e( 'Check options when <strong>not</strong> available.', 'rota' ); ?></small></em>
                <?php wp_nonce_field( 'rota', 'rota_nonce' ); ?>
                <br/>
                <a href="#" id="rota-check-all" class="button"><?php _e( 'Check all', 'rota' ); ?></a>
                <a href="#" id="rota-uncheck-all" class="button"><?php _e( 'Uncheck all', 'rota' ); ?></a>
            </th>
            <?php foreach ( $days as $d ) : ?>
                <td>
                    <strong><?php echo $d['title']; ?></strong>
                    <ul>
                        <?php foreach ( $intervals as $i ) : ?>
                        <li>
                            <input name="rota[<?php echo $user_id; ?>][<?php echo $d['name']; ?>][<?php echo $i['name']; ?>]" <?php checked( $rota_options[ $d['name'] ][ $i['name'] ] ); ?> id="<?php echo "{$d['name']}_{$i['name']}"; ?>" type="checkbox" />
                            <label for="<?php echo "{$d['name']}_{$i['name']}"; ?>"><?php echo $i['title']; ?></label>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </td>
            <?php endforeach; ?>
        </tr>
    </tbody>
</table>
<script type="text/javascript">
    jQuery( '#rota-check-all' ).click( function() {
        jQuery( '.rota-options input[type="checkbox"]' ).attr( 'checked', 'on' );
    });
    jQuery( '#rota-uncheck-all' ).click( function() {
        jQuery( '.rota-options input[type="checkbox"]' ).attr( 'checked', false );
    });
</script>
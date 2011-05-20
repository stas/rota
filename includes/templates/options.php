<table class="form-table">
    <tbody>
        <tr>
            <th scope="row">
                <?php _e( 'Rota Availability', 'rota' ); ?>
                <br/>
                <em><small><?php _e( 'Check options when not available.', 'rota' ); ?></small></em>
                <?php wp_nonce_field( 'rota', 'rota_nonce' ); ?>
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

<table class="form-table">
    <tbody>
        <tr>
            <th scope="row">
                <?php _e( 'Rota Availability', 'rota' ); ?>
                <br/>
                <em><small><?php _e( 'Check options when not available.', 'rota' ); ?></small></em>
                <?php wp_nonce_field( 'rota', 'rota_nonce' ); ?>
            </th>
            <?php foreach ( $days as $d => $d_name ) : ?>
                <td>
                    <strong><?php echo $d_name; ?></strong>
                    <ul>
                        <?php foreach ( $intervals as $i => $i_name ) : ?>
                        <li>
                            <input name="rota[<?php echo $user_id; ?>][<?php echo $d; ?>][<?php echo $i; ?>]" <?php checked( $rota_options[$d][$i] ); ?> id="<?php echo "{$d}_{$i}"; ?>" type="checkbox" />
                            <label for="<?php echo "{$d}_{$i}"; ?>"><?php echo $i_name; ?></label>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </td>
            <?php endforeach; ?>
        </tr>
    </tbody>
</table>

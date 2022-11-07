<?php
/**
 * Copyright (C) 2020 Yeremi Felix Loli Saquiray
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>

<div class="wrap">
    <h1><?php _e('Cinemark API'); ?></h1>
    <hr/>

    <form method="post" action="<?php echo admin_url($this->admin_url); ?>">
        <?php settings_fields('cinemark-settings-group'); ?>
        <?php wp_nonce_field('cinemark_nonce'); ?>

        <h2>Autenticação</h2>
        <table class="form-table" role="presentation">
            <tbody>
            <tr>
                <th scope="row"><label for="oauthUrl">Url Autenticação</label></th>
                <td>
                    <input name="cinemark[oauthUrl]" type="text" id="oauthUrl" value="<?php echo $oauthUrl; ?>"
                           class="regular-text code" required>
                    <?php
                    if ($hasToken) {
                        echo '<span class="dashicons dashicons-yes-alt" style="color: #46b450; margin-top: 6px"></span>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="authorization">Authorization</label></th>
                <td>
                    <input name="cinemark[authorization]" type="text" id="authorization"
                           value="<?php echo $authorization; ?>" class="regular-text code" required>
                    <?php
                    if ($hasToken) {
                        echo '<span class="dashicons dashicons-yes-alt" style="color: #46b450; margin-top: 6px"></span>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="type">Type</label></th>
                <td><input name="cinemark[grant_type]" type="text" id="type"
                           value="<?php echo $grant_type; ?>" class="regular-text code" required>
                    <?php
                    if ($hasToken) {
                        echo '<span class="dashicons dashicons-yes-alt" style="color: #46b450; margin-top: 6px"></span>';
                    }
                    ?>
                </td>
            </tr>
            </tbody>
        </table>
        <?php if ($hasToken) { ?>
            <p>Token gerado: <?php echo $hasToken; ?></p>
            <p>Válido até às:
                <?php
                echo date('m/d/Y H:i:s', $oauth['expires_in']);
                ?>
            </p>
        <?php } ?>
        <hr/>
        <h2>Validar Conexão</h2>
        <table class="form-table" role="presentation">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="clientID">Client ID</label>
                </th>
                <td>
                    <input name="cinemark[clientID]" aria-describedby="clientID-description" type="text"
                           id="clientID" value="<?php echo $clientID; ?>" class="regular-text code">
                    <?php
                    if ($hasToken) {
                        echo '<span class="dashicons dashicons-warning" style="color: #ffba00; margin-top: 6px"></span>';
                    }
                    ?>

                    <br/><br/>
                    <a href="<?php echo admin_url($this->admin_url . '&validate-connection=true') ?>"
                       class="button button-secondary">Realizar teste de conexão com a Cinemark</a>
                    <p class="description"
                       id="clientID-description"><?php _e('Você deve realizar o teste de conexão antes de disponibilizar ou divulgar a url: <a href="' . site_url('cinema') . '">' . site_url('cinema') . '</a>'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
        <hr/>
        <h2>Url</h2>
        <table class="form-table" role="presentation">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="theaterUrl">Theater Url</label>
                </th>
                <td>
                    <input name="cinemark[theaterUrl]" aria-describedby="theaterUrl" type="text"
                           id="clientID" value="<?php echo $theaterUrl; ?>" class="regular-text code">
                    <br/><br/>
                </td>
            </tr>
            </tbody>
        </table>
        <hr/>
        <?php submit_button(); ?>
    </form>
</div>
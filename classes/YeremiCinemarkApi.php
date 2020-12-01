<?php
/**
 * YeremiCinemarkApi
 *
 * @package    Yeremi
 * @subpackage CinemarkApi
 * @author     Yeremi Loli <yeremiloli@yahoo.com>
 * @copyright  2020 Yeremi Loli
 * @license    https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @link       https://www.linkedin.com/in/yeremiloli/
 */

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

date_default_timezone_set('America/Sao_Paulo');

/**
 * Class YeremiCinemarkApi
 */
class YeremiCinemarkApi
{

    /**
     * @var string
     */
    const ENDPOINT = 'https://api-content.ingresso.com/v0';
    /**
     * @var boolean|string
     */
    public $response;
    private $health;
    private $id_do_cinema;
    private $partnership;
    private $city = 1;
    private $endpoint = '';
    private $oauthUrl = 'https://api-ss-sandbox.cinemark.com.br/oauth/access-token';
    private $clientID = '';
    private $auth = [];
    private $grant_type = 'client_credentials';
    private $admin_url = 'options-general.php?page=cinemark';
    private $authorization = '';

    public function __construct()
    {
        if (is_admin()) {
            if (!$this->get_option()['authorization']) {
                $this->set_option();
                add_action('admin_notices', array($this, 'admin_notices'));
            }
            add_action('admin_menu', array($this, 'adminMenu'));
        } else {
            add_action('template_include', array($this, 'setCinemaTemplate'), 99);
        }
    }

    protected function get_option()
    {
        $option_value = get_option('cinemark_oauth', false);

        if ($option_value) {
            return $option_value;
        }

        return $this->get_default_option();
    }

    protected function get_default_option()
    {
        return [
            'clientID'      => null,
            'grant_type'    => null,
            'access_token'  => null,
            'expires_in'    => null,
            'authorization' => null,
            'sandbox'       => null,
            'oauthUrl'      => null,
            'valid'         => null,
        ];
    }

    protected function set_option()
    {
        add_option('cinemark_oauth', $this->get_default_option());
    }

    public function admin_notices()
    {
        $description = sprintf(
            wp_kses(
                __('<strong>AUTENTICAÇÃO CINEMARK:</strong> Para exibir os filmes é necessário a configuração dos acessos. <a href="%1$s" target="_self" rel="noopener noreferrer">Clique aqui para configurar</a>.'),
                array(
                    'br'     => true,
                    'strong' => true,
                    'a'      => array(
                        'href'   => true,
                        'rel'    => true,
                        'target' => true,
                    ),
                )
            ),
            admin_url($this->admin_url)
        );
        echo '<div class="notice notice-error"><p>' . $description . '</p></div>';

    }

    public function adminMenu()
    {
        add_options_page(esc_html__('Cinemark'), esc_html__('Cinemark'), 'manage_options', 'cinemark', array($this, 'settingsPage'));
    }

    public function settingsPage()
    {
        if (isset($_POST['cinemark']) and isset($_POST['action']) and $_POST['action'] == 'update') {

            if (wp_verify_nonce($_POST['_wpnonce'], 'cinemark_nonce')) {
                $options = [
                    'clientID'      => trim($_POST['cinemark']['clientID']),
                    'theaterUrl'    => trim($_POST['cinemark']['theaterUrl']),
                    'grant_type'    => trim($_POST['cinemark']['grant_type']),
                    'authorization' => trim($_POST['cinemark']['authorization']),
                    'sandbox'       => isset($_POST['cinemark']['sandbox']) ? 1 : 0
                ];
                $this->update_option($options);
                try {
                    $this->update_cinemark();
                } catch (ErrorException $e) {
                }
            }

        }

        if (isset($_GET['validate-connection']) and $_GET['validate-connection'] === 'true') {
            $this->testConnection();
        }

        $oauth = $this->get_option();

        $clientID      = $oauth['clientID'] ? $oauth['clientID'] : $this->clientID;
        $authorization = $oauth['authorization'] ? $oauth['authorization'] : $this->authorization;
        $grant_type    = $oauth['grant_type'] ? $oauth['grant_type'] : $this->grant_type;
        $oauthUrl      = isset($oauth['oauthUrl']) ? $oauth['oauthUrl'] : $this->oauthUrl;
        $sandbox       = $oauth['sandbox'] ? 'checked' : '';
        $hasToken      = $oauth['access_token'] ? $oauth['access_token'] : false;
        $theaterUrl    = $oauth['theaterUrl'] ? $oauth['theaterUrl'] : '';

        include_once CINEMARK_API_PATH . "admin/views/setting.php";
    }

    protected function update_option(array $options = [])
    {
        update_option('cinemark_oauth', $options);
    }

    private function update_cinemark()
    {
        try {
            $this->authorization();
        } catch (ErrorException $e) {
        }

        $this->save_credential();
    }

    private function authorization()
    {
        $option = $this->get_option();

        if (empty($option['grant_type'])) {
            throw new InvalidArgumentException('Required option not passed: "grant_type"');
        }

        if (empty($option['authorization'])) {
            throw new InvalidArgumentException('Required option not passed: "authorization"');
        }

        $args = array(
            'body'    => wp_json_encode(['grant_type' => $option['grant_type']]),
            'headers' => array(
                'sslverify'     => true,
                'Content-Type'  => 'application/json',
                'Authorization' => $this->get_option()['authorization'],
            )
        );

        $response     = wp_safe_remote_post($this->oauthUrl, $args);
        $bodyResponse = wp_remote_retrieve_body($response);
        $decode       = json_decode($bodyResponse, true);

        if (isset($decode['message'])) {
            echo '<div class="notice notice-error"><p>' . $decode['message'] . '</p></div>';
        } else {
            $this->setAuth(json_decode($bodyResponse, true));
        }
    }

    /**
     * @param mixed $auth
     */
    private function setAuth($auth): void
    {
        $this->auth = $auth;
    }

    /**
     *
     */
    private function save_credential()
    {
        $options                 = $this->get_option();
        $options['access_token'] = $this->getAuth()['access_token'];
        $options['expires_in']   = time() + $this->getAuth()['expires_in'];

        $this->update_option($options);
    }

    /**
     * @return mixed
     */
    private function getAuth()
    {
        return $this->auth;
    }

    /**
     *
     */
    public function testConnection()
    {
        $endpointSandbox = 'https://api-ss-sandbox.cinemark.com.br/showtime/external/press/theater/688';
        $option          = $this->get_option();
        $date            = new DateTime();
        $now             = $date->getTimestamp();

        if ($now >= $option['expires_in']) {
            $this->authorization();
        }

        $args = array(
            'headers' => array(
                'sslverify'    => true,
                'Content-Type' => 'application/json',
                'client_id'    => $option['clientID'],
                'access_token' => $option['access_token'],
            )
        );

        $response     = wp_safe_remote_get($endpointSandbox, $args);
        $bodyResponse = wp_remote_retrieve_body($response);
        $decode       = json_decode($bodyResponse, true);

        if (isset($decode['Movies']) and count($decode['Movies']) > 0) {
            echo '<div class="notice notice-success"><p><code class="code">' . print_r($decode['Movies'], true) . '</code></p></div>';
        } else {
            echo '<div class="notice notice-error"><p>O teste na url <i>' . $endpointSandbox . '</i> retornou vaziu:<br/><br/><code class="code">' . print_r($decode, true) . '</code></p></div>';
        }
    }

    /**
     * @return string
     */
    public function getOauthUrl(): string
    {
        return $this->oauthUrl;
    }

    /**
     * @param string $oauthUrl
     */
    public function setOauthUrl(string $oauthUrl): void
    {
        $this->oauthUrl = $oauthUrl;
    }

    /**
     * @link   https://api-content.ingresso.com/v0/swagger/ui/index#!/Events/Events_GetByCityAsync
     * @return string
     */
    public function getMovies()
    {
        $option = $this->get_option();

        if (!isset($option['theaterUrl']) or empty($option['theaterUrl'])) {
            return false;
        }

        $date            = new DateTime();
        $now             = $date->getTimestamp();
        $endpointSandbox = $option['theaterUrl'];
        $old_expires_in  = $option['expires_in'];

        if ($now >= $option['expires_in']) {
            $this->renewToken();
        }

        $args = array(
            'headers' => array(
                'sslverify'    => true,
                'Content-Type' => 'application/json',
                'client_id'    => $option['clientID'],
                'access_token' => $option['access_token'],
            )
        );

        $response     = wp_safe_remote_get($endpointSandbox, $args);
        $bodyResponse = wp_remote_retrieve_body($response);

        return ($bodyResponse) ? $bodyResponse : false;

    }

    private function renewToken()
    {
        $option = $this->get_option();

        $args = array(
            'body'    => wp_json_encode(['grant_type' => $option['grant_type']]),
            'headers' => array(
                'sslverify'     => true,
                'Content-Type'  => 'application/json',
                'Authorization' => $this->get_option()['authorization'],
            )
        );

        $response     = wp_safe_remote_post($this->oauthUrl, $args);
        $bodyResponse = wp_remote_retrieve_body($response);
        $decode       = json_decode($bodyResponse, true);

        //$this->setAuth(json_decode($bodyResponse, true));
        //$this->save_credential();

        $options                 = $this->get_option();
        $options['access_token'] = $decode['access_token'];
        $options['expires_in']   = time() + $decode['expires_in'];

        $this->update_option($options);
    }

    public function setCinemaTemplate($template)
    {
        $file_name = 'cinemark-template.php';
        if (is_page('cinema')) {
            if (locate_template('/template-parts/' . $file_name)) {
                $template = locate_template('/template-parts/' . $file_name);
            } else {
                $template = CINEMARK_API_PATH . '/templates/' . $file_name;
            }
        }

        return $template;
    }


}

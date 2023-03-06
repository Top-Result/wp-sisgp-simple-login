<?php
/**
 * Plugin Name:       SISGP Simple Login
 * Plugin URI:        https://github.com/Top-Result/wp-sisgp-simple-login
 * Description:       Script que concede login entre WordPress e SisGP.
 * Version:           0.2r0003061742
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Jeimison Moreno
 * Author URI:        https://github.com/jeimison3
 * License:           GPL-3
 * License URI:       https://opensource.org/license/gpl-3-0/
 * Update URI:        https://github.com/Top-Result/wp-sisgp-simple-login
 * Text Domain:       wp-sisgp-simple-login
**/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
$plugin_data = get_plugin_data( __FILE__ );


define( 'sisgp_sl_api_extname', $plugin_data['Name'] );
define( 'sisgp_sl_api_reqwp', $plugin_data['RequiresWP'] );
define( 'sisgp_sl_api_reqphp', $plugin_data['RequiresPHP'] );


function toLog($msg){
    if(get_option('sisgpsl_api_login_option_debug_mode')){
        if(!defined('PHP_VERSION_ID')){
            $version = explode('.', PHP_VERSION);
            define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
        }
        if(!defined('PHP_TIME_PROC')){
            $version = explode('.', PHP_VERSION);
            if(((intval($version[0]) == 5) && (intval($version[1]) >= 2)) || (intval($version[0]) > 5)){
                $date = date_create('now', new DateTimeZone('America/Sao_Paulo'));
                if($date)
                    define('PHP_TIME_PROC', date_format($date, 'd/m/Y H:i:s'));
            }
        }
        error_log(sprintf("[PHP %s] [%s] %s%s", PHP_VERSION_ID, PHP_TIME_PROC, $msg, PHP_EOL), 3, plugin_dir_path(__FILE__).'debug.log');
    }
}

add_action( 'admin_post_sisgp_sl_action_registerpages', 'sisgp_sl_action_registerpages' );
function sisgp_sl_action_registerpages() {
    if ( ! current_user_can( 'activate_plugins' ) ) return;
        { toLog("-> Ativando."); }
        $page_slug = 'conclusao-cadastro'; // Slug of the Post
        $pg = get_page_by_path( $page_slug, OBJECT, 'page');
        
        $new_page = array(
            'post_type'     => 'page', 				// Post Type Slug eg: 'page', 'post'
            'post_title'    => 'Conclua seu cadastro',	// Title of the Content
            'post_content'  => "<form id='form-conclusao-cadastro' method='POST' action='".get_rest_url(null, "sisgp-login/v1/cadastro")."' enctype='multipart/form-data'>
    <div class='layout-wrap'>
    <h3 style='color:red;' id='avisos'></h3>
        <div class='register-section default-profile' id='basic-details-section'>
            <div class='bb-signup-field signup_email'>
                <label for='fname'>Primeiro nome </label>
                <input type='text' name='fname' id='fname' value='' aria-required='true' />
            </div>
            <div class='bb-signup-field signup_email'>
                <label for='lname'>Último nome </label>
                <input type='text' name='lname' id='lname' value='' aria-required='true' />
            </div>
            <div class='bb-signup-field signup_email'>
                <label for='email'>E-mail </label>
                <input type='email' name='email' id='email' value='' aria-required='true' />
            </div>
        </div>
    </div>
    <input type='hidden' name='u' aria-hidden='true' aria-required='true' />
    <input type='hidden' name='h' aria-hidden='true' aria-required='true' />
    <input type='hidden' name='k' aria-hidden='true' aria-required='true' />
    <div class='submit'>
        <input type='submit' value='Cadastrar'/>
    </div>
    <script>
    const urlParams = new URLSearchParams(window.location.search);
    document.getElementsByName('u')[0].value = urlParams.get('u')
    document.getElementsByName('h')[0].value = urlParams.get('h')
    document.getElementsByName('k')[0].value = urlParams.get('k')
    if( urlParams.get('m') != null ) {
        document.getElementById('avisos').innerHTML = urlParams.get('m');
    }
    if( urlParams.get('a') == null ) {
        document.getElementById('form-conclusao-cadastro').style.display='none';
        document.getElementsByClassName('wp-block-post-title')[0].innerHTML='Não autorizado.';
    } else if( (Number(urlParams.get('a')) > 0) ) {
        document.getElementById('form-conclusao-cadastro').style.display='none';
        document.getElementsByClassName('wp-block-post-title')[0].innerHTML='Cadastro já foi finalizado.';
    }
    </script>
</form>",	// Content
        'post_status'   => 'publish',			// Post Status
        'post_author'   => 1,					// Post Author ID
        'post_name'     => $page_slug			// Slug of the Post
    );
    if($pg)
        $new_page['ID'] = $pg->ID;
    $new_page_id = wp_insert_post($new_page);
    update_option( 'sisgpsl_api_login_url_cadastrofinal', get_permalink($new_page_id));
    if(!$pg)
        { toLog("LINK: ".get_permalink($new_page_id)); }
    
    if(isset($_GET["httpurl"]))
        if($_GET["httpurl"] != "")
            wp_redirect( $_GET["httpurl"] );
        else wp_redirect( wp_login_url() );
    else wp_redirect( wp_login_url() );

    exit(1);
    // 
    // die( __FUNCTION__ );
}

if ( ! function_exists( 'sisgp_sl_api_login_check' ) ) {
//     register_activation_hook( __FILE__, 'insert_page_on_activation' );
//     function insert_page_on_activation() {
//         if ( ! current_user_can( 'activate_plugins' ) ) return;
//         { toLog("-> Ativando."); }
//         $page_slug = 'registro'; // Slug of the Post
//         $pg = get_page_by_path( $page_slug, OBJECT, 'page');
        
//         $new_page = array(
//             'post_type'     => 'page', 				// Post Type Slug eg: 'page', 'post'
//             'post_title'    => 'Conclua seu cadastro',	// Title of the Content
//             'post_content'  => "<form id='form-conclusao-cadastro' method='POST' action='".get_rest_url(null, "sisgp-login/v1/cadastro")."' enctype='multipart/form-data'>
//     <div class='layout-wrap'>
//     <h3 style='color:red;' id='avisos'></h3>
//         <div class='register-section default-profile' id='basic-details-section'>
//             <div class='bb-signup-field signup_email'>
//                 <label for='fname'>Primeiro nome </label>
//                 <input type='text' name='fname' id='fname' value='' aria-required='true' />
//             </div>
//             <div class='bb-signup-field signup_email'>
//                 <label for='lname'>Último nome </label>
//                 <input type='text' name='lname' id='lname' value='' aria-required='true' />
//             </div>
//             <div class='bb-signup-field signup_email'>
//                 <label for='email'>E-mail </label>
//                 <input type='email' name='email' id='email' value='' aria-required='true' />
//             </div>
//         </div>
//     </div>
//     <input type='hidden' name='u' aria-hidden='true' aria-required='true' />
//     <input type='hidden' name='h' aria-hidden='true' aria-required='true' />
//     <input type='hidden' name='k' aria-hidden='true' aria-required='true' />
//     <div class='submit'>
//         <input type='submit' value='Cadastrar'/>
//     </div>
//     <script>
//     const urlParams = new URLSearchParams(window.location.search);
//     document.getElementsByName('u')[0].value = urlParams.get('u')
//     document.getElementsByName('h')[0].value = urlParams.get('h')
//     document.getElementsByName('k')[0].value = urlParams.get('k')
//     if( urlParams.get('m') != null ) {
//         document.getElementById('avisos').innerHTML = urlParams.get('m');
//     }
//     if( urlParams.get('a') == null ) {
//         document.getElementById('form-conclusao-cadastro').style.display='none';
//         document.getElementsByClassName('wp-block-post-title')[0].innerHTML='Não autorizado.';
//     } else if( (Number(urlParams.get('a')) > 0) ) {
//         document.getElementById('form-conclusao-cadastro').style.display='none';
//         document.getElementsByClassName('wp-block-post-title')[0].innerHTML='Cadastro já foi finalizado.';
//     }
//     </script>
// </form>",	// Content
//             'post_status'   => 'publish',			// Post Status
//             'post_author'   => 1,					// Post Author ID
//             'post_name'     => $page_slug			// Slug of the Post
//         );
//         if($pg)
//             $new_page['ID'] = $pg->ID;
//         $new_page_id = wp_insert_post($new_page);
//         update_option( 'sisgpsl_api_login_url_cadastrofinal', get_permalink($new_page_id));
//         if(!$pg)
//             { toLog("LINK: ".get_permalink($new_page_id)); }
//     }

	/**
	 * Check dependencies in core
	 */
	function sisgp_sl_api_login_check() {
        if((!is_php_version_compatible(sisgp_sl_api_reqphp)) || (!is_wp_version_compatible(sisgp_sl_api_reqwp))){
            function sisgp_sl_api_dependencies_err() {
                if(!is_php_version_compatible(sisgp_sl_api_reqphp))
                    echo '<div class="error"><p>' . sprintf( __( 'A extensão <strong>%s</strong> requer o PHP ao menos na versão <strong>%s</strong> para funcionar corretamente.' ), sisgp_sl_api_extname, sisgp_sl_api_reqphp ) . '</p></div>';
                if(!is_wp_version_compatible(sisgp_sl_api_reqwp))
                    echo '<div class="error"><p>' . sprintf( __( 'A extensão <strong>%s</strong> requer o WordPress ao menos na versão <strong>%s</strong> para funcionar corretamente.' ), sisgp_sl_api_extname, sisgp_sl_api_reqwp ) . '</p></div>';
            }
            add_action( 'admin_notices', 'sisgp_sl_api_dependencies_err' );
        } else
            jm_addFilters();
    }

    add_action( 'plugins_loaded', 'sisgp_sl_api_login_check', -20 );

}


function jm_addFilters(){
    // add_filter( 'um_submit_form_data', 'sisgp_sl_api_submit_data', 10, 2 ); // UltimateMember
    add_filter( 'authenticate', 'sisgp_sl_api_login_auth', 1000, 3 ); // WPLogin
}


function sisgpsl_api_login_register_settings() {
    add_option( 'sisgpsl_api_login_text_ambiente', '');
    add_option( 'sisgpsl_api_login_text_urlbase', '');
    add_option( 'sisgpsl_api_login_option_capacidade_forcar', false);
    add_option( 'sisgpsl_api_login_option_debug_mode', false);
    add_option( 'sisgpsl_api_login_option_capacidade_padrao', 'subscriber');

    register_setting( 'sisgpsl_api_login_options_group', 'sisgpsl_api_login_text_ambiente', 'sisgpsl_api_login_callback' );
    register_setting( 'sisgpsl_api_login_options_group', 'sisgpsl_api_login_text_urlbase', 'sisgpsl_api_login_callback' );
    register_setting( 'sisgpsl_api_login_options_group', 'sisgpsl_api_login_option_capacidade_forcar', 'sisgpsl_api_login_callback' );
    register_setting( 'sisgpsl_api_login_options_group', 'sisgpsl_api_login_option_capacidade_padrao', 'sisgpsl_api_login_callback' );
    register_setting( 'sisgpsl_api_login_options_group', 'sisgpsl_api_login_option_debug_mode', 'sisgpsl_api_login_callback' );
}
add_action( 'admin_init', 'sisgpsl_api_login_register_settings' );


function register_options_page() {
    add_options_page('Configurações SisGP Login', 'SisGP Login', 'manage_options', 'wp-sisgp-simple-login', 'sisgpsl_api_login_option_page');
    
    if(get_option('sisgpsl_api_login_option_debug_mode'))
        add_options_page('Logs SisGP Login', 'SisGP DEBUG', 'manage_options', 'wp-sisgp-simple-login-debug-get', 'sisgpsl_api_login_debug_page');
}
add_action('admin_menu', 'register_options_page');

// REST

add_action( 'rest_api_init', function () {
    register_rest_route( 'sisgp-login/v1', '/cadastro', [
        'methods' => 'POST',
        'callback' => 'sisgp_sl_api__cadastro_update',
        'permission_callback' => '__return_true',
        // 'args' => array()
    ] );
    register_rest_route( 'sisgp-login/v1', '/cadastro', [
        'methods' => 'GET',
        'callback' => 'sisgp_sl_api__my_rest_error',
        'permission_callback' => '__return_true',
        // 'args' => array()
    ] );
    // register_rest_route( '-api-login/v1', '/subpath/(?P<id>\d+)', array(
    //   'methods' => 'GET',
    //   'callback' => 'my_awesome_func',
    //   'args' => array(
    //     'id',
    //     'addr1',
    //     'addr2',
    //     'cidade',
    //     'cep',
    //     'id'
    //   ),
    // //   'permission_callback' => function () {
    // //     return current_user_can( 'edit_others_posts' );
    // //   }
    // ) );
} );

function sisgp_sl_api__my_rest_error(){
    return new WP_REST_Response(null, 403);
}

function sisgp_sl_api__cadastro_update( $data ) {
    $params = $data->get_params();

    $usernm = $params["u"];
    $p = $params["h"];
    $token = $params["k"];

    $chaves = ["u","h","k","email","lname","fname"];
    foreach($chaves as $k){
        if(!array_key_exists($k, $params)) {
            toLog("REST=> KEY AUSENTE:".$k);
            $params = sprintf("?u=%s&h=%s&k=%s&a=%d&m=%s", $usernm, $p, $res["token"], get_current_user_id(), "Preencha todos os campos");
            wp_redirect( get_option('sisgpsl_api_login_url_cadastrofinal').$params );
            exit(1);
        }
    }

    { toLog("REST=>".json_encode($params)); }
    $baseurl=get_option('sisgpsl_api_login_text_urlbase');
    $ambiente=get_option('sisgpsl_api_login_text_ambiente');
    $res = api_fluxus_login($baseurl,$ambiente,$usernm,$p);

    if($res){
        if(array_key_exists("userName", $res)) {
            if($token === $res["token"]) {
                { toLog("fluxusRESTAuthOK"); }
                $new_user_id = wp_insert_user( // wp_update_user
                    array( // 'ID' => ...
                        'user_email' => $params['email'],
                        'user_login' => $usernm,
                        'user_pass' => $p,
                        'first_name' => $params['fname'],
                        'last_name' => $params['lname']
                    )
                );
                if(is_wp_error($new_user_id)){
                    $errorMsg = $new_user_id->get_error_message();
                    { toLog("fluxusREST_insertUsrERROR=> ".$errorMsg); }
                    
                    $params = sprintf("?u=%s&h=%s&k=%s&a=%d&m=%s", $usernm, $p, $res["token"], get_current_user_id(),$errorMsg);
                    wp_redirect( get_option('sisgpsl_api_login_url_cadastrofinal').$params );
                    
                    exit(1);
                }
                
                { toLog("fluxusREST_UID=>".$new_user_id); }
                $user = new WP_User($new_user_id);
                if(get_option('sisgpsl_api_login_option_capacidade_padrao') != "")
                    $user->add_cap(get_option('sisgpsl_api_login_option_capacidade_padrao'));
                $user->add_cap("externo");


                // Autentica:
                wp_signon(array(
                    "user_login"=>$usernm,
                    "user_password"=>$p,
                ));
                // Encaminha para página inicial.
                wp_redirect( home_url() ); // SUCESSO
                exit(1);
            }
        } else {
            { toLog("fluxusRESTKeyNotExist=>userName"); }
            wp_redirect( wp_login_url() );
            exit(1);
        }
    } else {
        { toLog("fluxusRESTCallError=>NULL"); }
        wp_redirect( wp_login_url() );
        exit(1);
    }

    
    // if(isset($params["id"])){
    //     
    //     $atualizados = array();
    //     $tags = array(
    //         "linkedin", "facebook", "instagram", "youtube", "wikipedia", // Social links
    //         "description", // Descrição
    //         "Universidade",
    //         "Periodo",
    //         "shipping_address_1", "shipping_address_2", // Endereço, Número
    //         "shipping_neighborhood", // Bairro
    //         "shipping_postcode", // CEP
    //         "shipping_city", // Cidade
    //         "shipping_state", // UF
    //         "shipping_phone", // Telefone
    //     );
    //     foreach ($tags as $chave => $valor) {
    //         if(array_key_exists($valor, $params)){
    //             update_user_meta( $params["id"], $valor, $params[$valor]);
    //             array_push($atualizados, $valor);
    //         }
    //     }
    //     // update_user_meta( $data["id"], 'linkedin', $data["linkedin"]);
    //     return new WP_REST_Response(
    //         array(
    //             'updated' => $atualizados,
    //             'response' => "OK"
    //         )
    //     );
    // } else {
    //     return new WP_REST_Response(
    //         array(
    //             'response' => "FAILED"
    //         )
    //     );
    // }
    
}
// END REST


function sisgpsl_api_login_debug_page(){
    $file_url = plugin_dir_path(__FILE__).'debug.log';
    ?>
    <textarea style="width:100%;overflow-y: scroll;height: 600px;resize: none;"><?php readfile($file_url); ?></textarea>
    <?php
    exit();
}

function sisgpsl_api_login_option_page(){
?><div class="wrap">
<?php //screen_icon(); ?>
<h1>Configurações do plugin</h1>

<form method="post" action="options.php">
<?php settings_fields( 'sisgpsl_api_login_options_group' ); ?>
<!-- <h1>Propriedades dos usuários logados via API.</h1> -->
<h1 class="title">Parâmetros</h1>
<table class="form-table">
    <tr class="form-field" valign="top">
        <th scope="row"><label for="sisgpsl_api_login_text_ambiente">Nome do ambiente:</label></th>
        <td>
            <input type="text" name="sisgpsl_api_login_text_ambiente" id="sisgpsl_api_login_text_ambiente" value="<?=get_option('sisgpsl_api_login_text_ambiente')?>"/>
        </td>
    </tr>
    <tr class="form-field" valign="top">
        <th scope="row"><label for="sisgpsl_api_login_text_urlbase">URL base:</label></th>
        <td>
            <input type="text" name="sisgpsl_api_login_text_urlbase" id="sisgpsl_api_login_text_urlbase" value="<?=get_option('sisgpsl_api_login_text_urlbase')?>"/>
        </td>
    </tr>
</table>
<h1 class="title">Grupos</h1>
<table class="form-table">
    <tr class="form-field" valign="top">
        <th scope="row"><label for="sisgpsl_api_login_option_capacidade_padrao">Grupo padrão para novos logins:</label></th>
        <td>
            <select name="sisgpsl_api_login_option_capacidade_padrao">
                <option value="" <?php if(get_option('sisgpsl_api_login_option_capacidade_padrao')=="") echo "selected"; ?>>Nenhum</option>
                <option value="subscriber" <?php if(get_option('sisgpsl_api_login_option_capacidade_padrao')=="subscriber") echo "selected"; ?>>Assinante</option>
                <option value="contributor" <?php if(get_option('sisgpsl_api_login_option_capacidade_padrao')=="contributor") echo "selected"; ?>>Colaborador</option>
                <option value="author" <?php if(get_option('sisgpsl_api_login_option_capacidade_padrao')=="author") echo "selected"; ?>>Autor</option>
                <option value="editor" <?php if(get_option('sisgpsl_api_login_option_capacidade_padrao')=="editor") echo "selected"; ?>>Editor</option>
                <option value="administrator" <?php if(get_option('sisgpsl_api_login_option_capacidade_padrao')=="administrator") echo "selected"; ?>>Administrador</option>
            </select>
        </td>
    </tr>
    <tr class="form-field" valign="top">
        <th scope="row"><label for="sisgpsl_api_login_option_capacidade_forcar">Forçar grupo para todos logins [da API]:</label></th>
        <td>
            <input type="checkbox" name="sisgpsl_api_login_option_capacidade_forcar" id="sisgpsl_api_login_option_capacidade_forcar" value="on" <?php if(get_option('sisgpsl_api_login_option_capacidade_forcar')) echo "checked=\"checked\""; ?>>
        </td>
    </tr>
</table>
<h1 class="title">Adicionais</h1>
<table class="form-table">
    <tr class="form-field" valign="top">
        <th scope="row"><label for="sisgpsl_api_login_option_debug_mode">Ativar LOG:</label></th>
        <td>
            <input type="checkbox" name="sisgpsl_api_login_option_debug_mode" id="sisgpsl_api_login_option_debug_mode" value="on" <?php if(get_option('sisgpsl_api_login_option_debug_mode')) echo "checked=\"checked\""; ?>>
        </td>
    </tr>
</table>

<?php submit_button(); ?>
</form>
<p>Alterações entram em vigor apenas quando o usuário faz login.</p>
<br/>


<form action="<?php echo admin_url( 'admin-post.php' ); ?>">
<input type="hidden" name="action" value="sisgp_sl_action_registerpages">
<input type="hidden" name="httpurl" value="">
<?php submit_button( 'Setup do plugin' ); ?>

<table class="form-table">
    <tr class="form-field" valign="top">
        <th scope="row"><label for="sisgpsl_param_urlcadastro">URL de página de conclusão do cadastro:</label></th>
        <td>
            <input disabled="disabled" type="text" name="sisgpsl_param_urlcadastro" id="sisgpsl_param_urlcadastro" value="<?php
            if(get_option('sisgpsl_api_login_url_cadastrofinal')) 
            echo get_option('sisgpsl_api_login_url_cadastrofinal');
            else echo "Execute o Setup do plugin";
            ?>"/>
        </td>
    </tr>
</table>

</form>

<script>
document.getElementsByName('httpurl')[0].value = window?.location?.href;
</script>

</div>
<?php
}


function api_fluxus_login($baseurl, $ambiente, $usuario, $senha){
    $protocolo = "https";
    $apiurl = "FluxusCore/login";
    $headers = array(
        sprintf("a: %s", $ambiente),
        sprintf("u: %s", $usuario),
        sprintf("p: %s",$senha),
        "Accept: */*"
    );
    { toLog(sprintf("Logando: %s.", $usuario)); }
    $curltmp = curl_init();
    curl_setopt_array($curltmp, array(
        CURLOPT_URL => sprintf("%s://api-%s/%s", $protocolo, $baseurl, $apiurl),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => $headers,
    ));
    $responseApi = curl_exec($curltmp);
    // toLog(sprintf("curlInfo=>[ tempo_s=%f, httpCode=%d]", $info["total_time"],$info["http_code"]));
    if(!curl_errno($curltmp)) { // Não ocorreram erros:
        return json_decode($responseApi, true);
    }
    return null;
}

// add_filter( 'um_submit_form_data', 'sisgp_sl_api_submit_data', 10, 2 );


// =====================================================


function sisgp_sl_api_login_auth( $user, $username, $password ){
    if($username == '' || $password == '') return;
    
    // Verifica local
    $user_id_local = wp_authenticate($username, $password);

    { toLog("wp_auth=> ".print_r($user_id_local,true)); }
    if ($user_id_local)
        if($user_id_local instanceof WP_User){
            { toLog("api_login_auth=> User[".$user_id_local->ID."] encontrado"); }
            //$user = $user_id_local;//new WP_User($user_id_local);
            // Se for local, autentica!
            if(!$user_id_local->has_cap('externo')){
                { toLog("api_login_auth=> User[".$user_id_local->ID."] não é da API"); }
                return $user_id_local;
            }
        }
    

    // Verifica remoto
    $baseurl=get_option('sisgpsl_api_login_text_urlbase');
    $ambiente=get_option('sisgpsl_api_login_text_ambiente');
    $res = api_fluxus_login($baseurl,$ambiente,$username,md5($password));
    {toLog("CALL= ".json_encode($res));}

    $FALHOU = true;
    if($res)
        if(array_key_exists("userName", $res)) { // Não ocorreram erros:

            if($res["userName"] == $username) { // Se autenticou na API:
                { toLog("curlAuthOK"); }
                // Vamos descobrir se o usuário existe:
                $user = get_user_by( 'login', $username );


                if ($user) { // Se foi encontrado ID válido
                    $FALHOU = false;
                    // return new WP_Error( 'denied', __("<b>INFO:</b> Usuário existe.") );
                    return $user;
                    

                    // // Se forçado...
                    if(get_option('sisgpsl_api_login_option_capacidade_forcar')){
                        $user->remove_all_caps();
                        if(get_option('sisgpsl_api_login_option_capacidade_padrao') != "")
                            $user->add_cap(get_option('sisgpsl_api_login_option_capacidade_padrao'));//("contributor");
                        $user->add_cap("externo");
                    }

                } else { // Se usuário novo
                    $FALHOU = false;
                    $params = sprintf("?u=%s&h=%s&k=%s&a=%d", $username, md5($password), $res["token"], get_current_user_id());
                    {toLog("novoUsuario->requestFormRegister = ".get_option('sisgpsl_api_login_url_cadastrofinal').$params);}
                    wp_redirect( get_option('sisgpsl_api_login_url_cadastrofinal').$params );
                    exit(1);

                }
                // A seguinte linha impede a autenticação padrão do wordpress.
                // Comentar caso julgue necessário
                remove_action('authenticate', 'wp_authenticate_username_password', 20);
                
                // Por fim, retornamos o User criado ou atualizado:
                return $user;
            }
            else if ($res["userName"] == null) { // Se ocorreu erro:
                // { toLog("curlErrorCode:".$resultCode.":".$resultResponse); }
                return new WP_Error( 'incorrect_password', __(sprintf("<b>Erro:</b> %s",$res["error"])) );
            } else { // response indefinido:
                // { toLog("curlUndefinedServerResponse:".$resultCode.":".$resultResponse); }
                return new WP_Error( 'denied', __("Erro: resposta inesperada.") );
            }
            
        }
        // Caso falha
        if($FALHOU){
            // { toLog("curlError:".curl_errno($curltmp).":".curl_error($curltmp)); }
            // curl_close($curltmp);
            return new WP_Error( 'denied', __("Erro: resposta inesperada. Serviço offline.") );
        }
}                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          
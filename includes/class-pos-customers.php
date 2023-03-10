<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('pos_customers')) {
    class pos_customers {
        /**
         * Class constructor
         */
        public function __construct() {
            $this->create_tables();
        }

        public function list_pos_customers() {
            global $wpdb;
            $wp_pages = new wp_pages();
            //$curtain_agents = new curtain_agents();

            if( isset($_SESSION['line_user_id']) ) {
                $_wp_page = 'Users';
                $permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE line_user_id = %s AND wp_page_id= %d", $_SESSION['line_user_id'], $wp_pages->get_id($_wp_page) ), OBJECT );            
                if (is_null($permission) || !empty($wpdb->last_error)) {
                    if ( $_GET['_check_permission'] != 'false' ) {
                        return 'You have not permission to access this page. Please check to the administrators.';
                    }
                }
            } else {
                if ( $_GET['_check_permission'] != 'false' ) {
                    return 'You have not permission to access this page. Please check to the administrators.';
                }
            }

            if( isset($_POST['_update']) ) {
                $data=array();
                $data['display_name']=$_POST['_display_name'];
                $data['mobile_phone']=$_POST['_mobile_phone'];
                $data['curtain_agent_id']=$_POST['_curtain_agent_id'];
                $where=array();
                $where['curtain_user_id']=$_POST['_curtain_user_id'];
                $this->update_curtain_users($data, $where);

                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wp_pages WHERE wp_page_category LIKE '%admin%' OR wp_page_category LIKE '%system%'", OBJECT );
                foreach ($results as $index => $result) {
                    $_checkbox = '_checkbox'.$index;
                    if (isset($_POST[$_checkbox])) {
                        //$permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE curtain_user_id = %d AND wp_page_id= %d", $_POST['_curtain_user_id'], $result->wp_page_id ), OBJECT );            
                        $permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE line_user_id = %s AND wp_page_id= %d", $_POST['_line_user_id'], $result->wp_page_id ), OBJECT );
                        if (is_null($permission) || !empty($wpdb->last_error)) {
                            $data=array();
                            $data['line_user_id']=$_POST['_line_user_id'];
                            $data['wp_page_id']=$result->wp_page_id;
                            $wp_pages->insert_user_permission($data);
                        }    
                    } else {
                        //$permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE curtain_user_id = %d AND wp_page_id= %d", $_POST['_curtain_user_id'], $result->wp_page_id ), OBJECT );            
                        $permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE line_user_id = %s AND wp_page_id= %d", $_POST['_line_user_id'], $result->wp_page_id ), OBJECT );
                        if (!(is_null($permission) || !empty($wpdb->last_error))) {
                            $where=array();
                            $where['line_user_id']=$_POST['_line_user_id'];
                            $where['wp_page_id']=$result->wp_page_id;
                            $wp_pages->delete_user_permissions($where);
                        }    
                    }
                }
                ?><script>window.location.replace("?_update=");</script><?php
            }
        
            /** Curtain User List */
            if( isset($_POST['_where']) ) {
                $where='"%'.$_POST['_where'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE display_name LIKE {$where}", OBJECT );
                unset($_POST['_where']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_users", OBJECT );
            }
            $output  = '<h2>Curtain Users</h2>';
            $output .= '<div style="text-align: right; margin: 5px;">';
            $output .= '<form method="post">';
            $output .= '<input style="display:inline" type="text" name="_where" placeholder="Search...">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</div>';

            $output .= '<div class="ui-widget">';
            $output .= '<table id="users" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th>line_user_id</th>';
            $output .= '<th>name</th>';
            $output .= '<th>mobile</th>';
            $output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-edit-'.$result->curtain_user_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                $output .= '</td>';
                $output .= '<td>'.$result->line_user_id.'</td>';
                $output .= '<td>'.$result->display_name.'</td>';
                $output .= '<td>'.$result->mobile_phone.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="btn-chat-'.$result->line_user_id.'"><i class="fa-solid fa-user-tie"></i></span>';
                $output .= '<span>  </span>';
                $output .= '<span id="btn-del-'.$result->curtain_user_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="Curtain user update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" value="'.$row->curtain_user_id.'" name="_curtain_user_id">';
                $output .= '<input type="hidden" value="'.$row->line_user_id.'" name="_line_user_id">';
                $output .= '<label for="display-name">Display Name</label>';
                $output .= '<input type="text" name="_display_name" value="'.$row->display_name.'" id="display-name" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="mobile-phone">Mobile Phone</label>';
                $output .= '<input type="text" name="_mobile_phone" value="'.$row->mobile_phone.'" id="mobile-phone" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="curtain-agent-id">Agent</label>';
                $output .= '<select name="_curtain_agent_id">'.$curtain_agents->select_options($row->curtain_agent_id).'</select>';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wp_pages WHERE wp_page_category LIKE '%admin%' OR wp_page_category LIKE '%system%' ", OBJECT );
                $output .= '<label for="user-permissions">Permissions</label>';
                $output .= '<div style="border: 1px solid; padding: 10px;">';
                foreach ($results as $index => $result) {
                    $output .= '<input style="display: inline-block;" type="checkbox" id="checkbox'.$index.'" name="_checkbox'.$index.'" value="'.$result->wp_page_id.'"';
                    $permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE line_user_id = %s AND wp_page_id= %d", $row->line_user_id, $result->wp_page_id ), OBJECT );            
                    if (is_null($permission) || !empty($wpdb->last_error)) {
                        $output .= '>';
                    } else {
                        $output .= ' checked>';
                    }
                    $output .= '<label style="display: inline-block; margin-left: 8px;" for="checkbox'.$index.'"> '.$result->wp_page_title;
                    $output .= '('.$result->wp_page_category.')</label><br>';
                }
                $output .= '</div>';        

                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                $output .= '</form>';
                $output .= '</div>';
            }

            /** Chat Form */
            if( isset($_GET['_id']) ) {                
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $_GET['_id'] ), OBJECT );
                if (!(is_null($row) || !empty($wpdb->last_error))) {
                    $output .= '<div id="dialog" title="Chat with '.$row->display_name.'">';
                    $output .= '<input type="hidden" value="'.$row->line_user_id.'" class="chatboxtitle">';

                    $output .= '<div class="chatboxcontent">';
                    global $wpdb;
                    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}chat_messages", OBJECT );
                    foreach ( $results as $index=>$result ) {
                        if ($result->chat_to==$row->line_user_id && $result->chat_from==$_SESSION['line_user_id']) {
                            $output .= '<div class="chatboxmessage" style="float: right;"><div class="chatboxmessagetime">'.wp_date( get_option('time_format'), $result->create_timestamp ).'</div><div class="chatboxinfo">'.$result->chat_message.'</div></div><div style="clear: right;"></div>';
                        }
                        if ($result->chat_from==$row->line_user_id && $result->chat_to!=$_SESSION['line_user_id']) {
                            $output .= '<div class="chatboxmessage"><div class="chatboxmessagefrom">'.$row->display_name.':&nbsp;&nbsp;</div><div class="chatboxmessagecontent">'.$result->chat_message.'</div><div class="chatboxmessagetime">'.wp_date( get_option('time_format'), $result->create_timestamp ).'</div></div>';
                        }
                    }
                    $output .= '</div>';
        
                    $output .= '<div class="chatboxinput"><textarea class="chatboxtextarea"></textarea></div>';
                    $output .= '</div>';
                }
            }
            return $output;
        }

        public function insert_pos_user($data=[]) {
            global $wpdb;
            $line_user_id = $data['line_user_id'];
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $line_user_id ), OBJECT );            
            if ( is_null($row) || !empty($wpdb->last_error) ) {
                $table = $wpdb->prefix.'curtain_users';
                $data['create_timestamp'] = time();
                $data['update_timestamp'] = time();
                $wpdb->insert($table, $data);
                return $wpdb->insert_id;
            } else {
                return $row->curtain_user_id;
            }
        }

        public function update_pos_customers($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_users';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_pos_customers($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_users';
            $wpdb->delete($table, $where);
        }

        public function get_id( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}pos_customers WHERE pos_user_id = %d OR line_user_id = %s", $_id, $_id ), OBJECT );
            return $row->line_user_id;
        }

        public function get_name( $_id=0 ) {
            global $wpdb;
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}pos_customers WHERE pos_user_id = %d OR line_user_id = %s", $_id, $_id ), OBJECT );
            return $row->display_name;
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $sql = "CREATE TABLE {$wpdb->prefix}pos_customers (
                pos_user_id int NOT NULL AUTO_INCREMENT,
                line_user_id varchar(50) UNIQUE,
                display_name varchar(50),
                mobile_phone varchar(20),
                pos_agent_id int(10),
                user_role varchar(20),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (pos_user_id)
            ) $charset_collate;";
            dbDelta($sql);
        }

        function send_chat() {
            $line_webhook = new line_webhook();
            $wp_pages = new wp_pages();
            //$curtain_users = new curtain_users();

            $data=array();
            $data['chat_from']= $_SESSION['line_user_id'];
            $data['chat_to']= $_POST['to'];
            $data['chat_message']= $_POST['message'];
            $line_webhook->insert_chat_message($data);

            $hero = array();
            $hero[] = $this->get_name($_POST['to']);
            $body = array();
            $body[] = $_POST['message'];
            $_contents = array();
            $_contents['line_user_id'] = $_POST['to'];
            //$_contents['link_uri'] = get_site_url().'/'.$wp_pages->get_link('_chat_form').'/?_id='.$_POST['to'];
            $_contents['link_uri'] = get_site_url().'/'.$wp_pages->get_link('Users').'/?_id='.$_POST['to'];
            $_contents['hero'] = $hero;
            $_contents['body'] = $body;
            $wp_pages->push_bubble_messages( $_contents );

            $response = array();
            $response['currenttime'] = wp_date( get_option('time_format'), time() );
            echo json_encode( $response );
            wp_die();
        }

        function chatHeartbeat() {
            $items = array();
            $items['item']['t']=wp_date( get_option('time_format'), time() );
            $response = array();
            $response['items'] = $items;
            echo json_encode( $response );
            wp_die();
        }

        function enqueue_scripts() {
            wp_enqueue_script( 'custom-curtain-users', plugin_dir_url( __DIR__ ) . 'assets/js/custom-curtain-users.js', array( 'jquery' ), time(), true );
        }
    }
    $my_class = new pos_customers();
    add_shortcode( 'pos-user-list', array( $my_class, 'list_pos_customers' ) );
    add_action( 'wp_ajax_send_chat', array( $my_class, 'send_chat' ) );
    add_action( 'wp_ajax_nopriv_send_chat', array( $my_class, 'send_chat' ) );
                
    //add_action( 'wp_ajax_sendChat', array( __CLASS__, 'sendChat' ) );
    //add_action( 'wp_ajax_nopriv_sendChat', array( __CLASS__, 'sendChat' ) );
    //add_action( 'wp_ajax_chatHeartbeat', array( __CLASS__, 'chatHeartbeat' ) );
    //add_action( 'wp_ajax_nopriv_chatHeartbeat', array( __CLASS__, 'chatHeartbeat' ) );
    //add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

}
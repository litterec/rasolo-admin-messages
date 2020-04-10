<?php
/*
Plugin Name: RaSolo admin messages
Plugin URI: http://ra-solo.ru
Description: Send messages to the site administrator in the admin panel
Text Domain: rasolo-admin-messages
Domain Path: /languages
Version: 1.1
Author: Andrew Galagan
Author URI: http://ra-solo.com.ua
License: GPL2
*/

/*  Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : eastern@ukr.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if ( !class_exists( 'RasoloAdminMessages' ) ) {
class RasoloAdminMessages
       {
    static private  $MAX_MESSAGES_ALLOWED = 4;
    static private $SESSION_KEY = 'rasolo_admin_messages';

    static private $MSG_TYPES=array(
            'error'=>'error',
            'warning'=>'warning',
            'success'=>'success',
            'info'=>'info'
    );

    private $messages;

    function __construct() {

        $this->messages=array();

        if( is_admin() ) {
// This for static methods:
// add_action( 'admin_init', array('MyClass','getStuffDone' ) );

            if ( ! session_id() ) {
                session_start();
            }

            add_action('admin_init', array('RasoloAdminMessages','load_plugin_textdomain' ),99 );
//            add_action('admin_head',array( $this, 'test_admin_head' ));
//            add_action( 'plugins_loaded', 'my_plugin_load_plugin_textdomain' );
//         add_action('plugins_loaded???',array( $this, 'register_scripts' ));

//            if(!has_action('admin_notices', array($this,'display_messages'))){
            add_action('admin_notices', array($this,'display_messages'));
//            };

        }
    }

    public static function load_plugin_textdomain() {
        load_plugin_textdomain( 'rasolo-admin-messages', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
    }

//    public function test_admin_head(){
//        ? ><div class="a">< ?php
//        echo '================';
//        printf(__('There are messages (%s) that are not shown.','rasolo-admin-messages'),44);
//        ? >admin_head!!!!</div>
//< ?php
//
//    }
//    public function register_scripts() {
//        wp_register_script('wpps-three', plugin_dir_url(__FILE__) . 'lib/three.min.js', array(), '3.3', true);
//    }


    public function set_message($msg_content,
             $msg_mode='info',
             $dismiss=false)
    {
        if(empty($msg_content))return;
        if(empty($msg_mode))return;
        $this->messages[]=array(
            'msg_txt'=>$msg_content,
            'is_dismiss'=>$dismiss,
            'msg_mode'=>$msg_mode
        );
    } // The end of set_message

    public function display_messages()
    {
//        echo '<!-- display_messages has been run -->'.chr(10);
//        rasolo_debug_to_file($this,'$some_messages_object');
//        rasolo_debug_to_file($this,null);
//        die('$some_messages_object_8475934');
//        rasolo_debug_to_file($this,null);
        if(!current_user_can('manage_options')){
            return;
        }

//        $this->write_log();

        $this->get_msg_from_session();
//        rasolo_debug_to_file($sess_msg,'$sess_msg_disapay_messages');

        //        rasolo_debug_to_file($all_messages,'$all_messages_disapay_messages');
//        rasolo_debug_to_file(false,null);

        $msg_count=0;
        $total_messages=count($this->messages);
//        myvar_dump($total_messages,'$total_messages',1);
        foreach ($this->messages as $nth_msg) {
            if(empty($nth_msg['msg_txt'])){
                continue;
            }
            $msg_count++;
            $limiting_scream='';
            if($total_messages>self::$MAX_MESSAGES_ALLOWED
                    && $msg_count==self::$MAX_MESSAGES_ALLOWED){
                $limiting_scream=sprintf(__('There are messages (%s) that are not shown.','rasolo-admin-messages'),$total_messages-self::$MAX_MESSAGES_ALLOWED);
//                $limiting_scream=' (Есть еще сообщения, '.
//                    ($total_messages-self::$MAX_MESSAGES_ALLOWED).
//                    ' шт., которые не показаны.)';
            }
            if($msg_count>self::$MAX_MESSAGES_ALLOWED){
                break;
            };

//    myvar_dump($nth_msg,'$nth_msg');
//    die('$nth_msg');
            if(in_array($nth_msg['msg_mode'],array_flip(self::$MSG_TYPES))){
                $msg_mode=$nth_msg['msg_mode'];
            } else {
                $msg_mode='info';
            };

            ?>
            <div class="notice notice-<?php
            echo self::$MSG_TYPES[$msg_mode].($nth_msg['is_dismiss']?' is-dismissible':'');
            ?>">
                <p><strong><?php echo $nth_msg['msg_txt'].$limiting_scream;
                        ?></strong></p><?php
                if($nth_msg['is_dismiss']){
                    ?><button type="button" class="notice-dismiss">
                        <span class="screen-reader-text"><?php
                                    _e('Remove this message','rasolo-admin-messages');
                            ?></span>
                    </button><?php
                };
                ?>
            </div>
        <?php
//            rasolo_debug_to_file($this,'$some_messages_object_exit');
//            rasolo_debug_to_file($this,null);


        };
    } // the end of display_admin_messages

    public function set_session_message($msg_content,
                     $msg_mode='info',
                     $dismiss=false)
    {
        if ( empty( $msg_content ) ) return;
        if ( empty( $msg_mode ) ) return;

        $current_msg_array= $this->get_msg_from_session();
//        rasolo_debug_to_file($current_msg_array,'$current_msg_array_before');
        $current_msg_array[]= [
            'msg_txt' => $msg_content,
            'msg_mode' => $msg_mode,
            'is_dismiss' => $dismiss,
        ];
        $srl_current_msg_array=serialize( $current_msg_array);
//        $srl_current_msg_array=serialize( array());
        $_SESSION[ self::$SESSION_KEY ]= $srl_current_msg_array;
//        rasolo_debug_to_file($_SESSION,'$session_after_storing');
//        rasolo_debug_to_file(false,null);

//        global $messages;
        $this->messages[]=array(
            'msg_txt'=>$msg_content,
            'is_dismiss'=>$dismiss,
            'msg_mode'=>$msg_mode
        );
    } // The end of set_session_message

    private function get_msg_from_session()
    {
        if ( ! empty( $_SESSION[ self::$SESSION_KEY ] ) ) {
            $sess_unser = @unserialize( $_SESSION[ self::$SESSION_KEY ] );
            if ( ! is_array( $sess_unser ) ) {
                $sess_unser = array();
            };
            foreach ( $sess_unser as $nth_msg ) {
                if ( empty( $nth_msg[ 'msg_txt' ] ) ) {
                    continue;
                };
                $msg_mode =empty( $nth_msg[ 'msg_mode' ])?'info':$nth_msg[ 'msg_mode' ];
                $is_dismiss =empty( $nth_msg[ 'is_dismiss' ])?false:$nth_msg[ 'is_dismiss' ];

                $this->messages[]=[
                    'msg_txt'=>$nth_msg[ 'msg_txt' ],
                    'msg_mode'=>$msg_mode,
                    'is_dismiss'=>$is_dismiss,
                ];
            };
        };
        unset($_SESSION[ self::$SESSION_KEY ]);
//        rasolo_debug_to_file($messages_array,'get_mess_from_sess');
//        rasolo_debug_to_file(true,null);

    } // The end of get_admin_msg_from_session




       } // // The end of class RasoloAdminMessages

//add_action('admin_init',function(){
//    global $rasolo_admin_messages;
//    $rasolo_admin_messages=new RasoloAdminMessages();
//    $rasolo_admin_messages->set_message('testinsideplugin');
//    $rasolo_admin_messages->display_messages();
//    die('admin_init_closure');
//},0);

//add_action('init',function(){
//    global $rasolo_admin_messages;
//    $rasolo_admin_messages=new RasoloAdminMessages();
//});

//add_action('init','rasolo_create_admin_msg_instance');
//function rasolo_create_admin_msg_instance()
//       {
//global $rasolo_admin_messages;
//$rasolo_admin_messages=new RasoloAdminMessages();

//       } // The end if rasolo_create_admin_msg_instance
}


//RasoloAdminMessages::init();

<?php

class FlashMessage
{
    private static function generate_id() 
    {        
        $user_id = get_current_user_id();
        return md5( 'jwp_flash_'.$user_id );
    }

    public static function set_message( $code, $type = 'success', $message = '' ) 
    {
        $session_id = self::generate_id();
        $acceptable = array( 'secondary', 'success', 'danger', 'warning', 'info' );

        if ( !in_array( $type, $acceptable ) ) $type = 'default';

        $flash = json_encode( array( 'type' => $type, 'message' => $message ) );
		add_option( "_jwp_flash_{$code}_{$session_id}", $flash, '', 'no' );
    }

    public static function unset_message( $code ) 
    {
        $session_id = self::generate_id();
        delete_option( "_jwp_flash_{$code}_{$session_id}" );
    }

    public static function get_message_container( $code, $unsetOnDisplay = false ) 
    {
        $session_id = self::generate_id();
        $container = get_option( "_jwp_flash_{$code}_{$session_id}" );
        delete_option( "_jwp_flash_{$code}_{$session_id}" );
		return json_decode($container);
    }

    public static function get_message_div( $code, $unsetOnDisplay = true ) 
    {
        $container = self::get_message_container( $code, $unsetOnDisplay ); 
        if ( $container ) {
            $full_message = '<div class="alert alert-'. $container->type . '" role="alert">';
            $full_message .= $container->message. '</div>';
        }

        return ( $container && !empty($container->message) ? $full_message : '' ); 
    }
}
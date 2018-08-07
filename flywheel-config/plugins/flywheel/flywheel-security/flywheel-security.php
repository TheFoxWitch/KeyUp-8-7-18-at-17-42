<?php
/*
Plugin Name: Flywheel Security
Plugin URI:
Description: Flywheel Security Plugin
Version: 0.1
Author: Flywheel
Author URI: http://www.getflywheel.com
*/

/*
Copyright (C) 2009 Avery Fay

This software is provided 'as-is', without any express or implied
warranty.  In no event will the authors be held liable for any damages
arising from the use of this software.

Permission is granted to anyone to use this software for any purpose,
including commercial applications, and to alter it and redistribute it
freely, subject to the following restrictions:

1. The origin of this software must not be misrepresented; you must not
   claim that you wrote the original software. If you use this software
   in a product, an acknowledgment in the product documentation would be
   appreciated but is not required.
2. Altered source versions must be plainly marked as such, and must not be
   misrepresented as being the original software.
3. This notice may not be removed or altered from any source distribution.
*/

function flywheel_remote_addr()
{
	$IP = $_SERVER['REMOTE_ADDR'];

    if (!empty($_SERVER['X_FORWARDED_FOR'])) {
        $X_FORWARDED_FOR = explode(',', $_SERVER['X_FORWARDED_FOR']);
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $X_FORWARDED_FOR = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    if (!empty($X_FORWARDED_FOR)) {
        $IP = trim($X_FORWARDED_FOR[0]);
    }

    return $IP;
}

function flywheel_set_comment_ip()
{
	$CLIENT_IP = flywheel_remote_addr();

    return $CLIENT_IP;
}

function flywheel_spam_log_process_status( $comment_id, $comment_status )
{
	if ($comment_status != 'spam')
		return;

	$comment = get_comment($comment_id);
	if (empty($comment))
		return;

	if (($log_file = @fopen('/var/log/security.log', 'a')) === FALSE)
		return;

	$log_string =
		date('Y-m-d H:i:s') .
		" comment id=" . $comment_id .
		" from host=" . $comment->comment_author_IP .
		" marked as spam\n";
	fwrite($log_file, $log_string);
	fclose($log_file);
}

function flywheel_login_failed_403( $username )
{
	if (($log_file = @fopen('/var/log/security.log', 'a')) === FALSE)
		return;

	$log_string =
		date('Y-m-d H:i:s') .
		" Failed login attempt from host=" . flywheel_remote_addr() . "\n";
	fwrite($log_file, $log_string);
	fclose($log_file);
}

function flywheel_login_success( $username )
{
	if (($log_file = @fopen('/var/log/logins.log', 'a')) === FALSE)
		return;

	$log_string =
		date('Y-m-d H:i:s') .
		" user=" . $username . " host=" . flywheel_remote_addr() . "\n";
	fwrite($log_file, $log_string);
	fclose($log_file);
}

add_filter('pre_comment_user_ip', 'flywheel_set_comment_ip');

add_action("wp_login", "flywheel_login_success");
add_action('wp_login_failed', 'flywheel_login_failed_403');
add_action('comment_post', 'flywheel_spam_log_process_status', 10, 2);
add_action('wp_set_comment_status', 'flywheel_spam_log_process_status', 10, 2);

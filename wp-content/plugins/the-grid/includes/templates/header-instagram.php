<?php
/**
 * @package   The_Grid
 * @author    Themeone <themeone.master@gmail.com>
 * @copyright 2015 Themeone
 */

// Exit if accessed directly
if (!defined('ABSPATH')) { 
	exit;
}

if ($tg_grid_data['source_type'] == 'instagram') {
	
	try {
		
		$intagram  = new The_Grid_Instagram();
		$username  = $tg_grid_data['instagram_username'];
		
		if ( empty( $username) ) {
			return;
		}

		// retrieve instagram user data
		$user = $intagram->get_data('user_info', $username, '', 0);

		if ( ! isset($user) || empty($user) ) {
			return;
		}
		
		$base        = new The_Grid_Base();
		$id          = isset( $user->graphql->user->id ) ? $user->graphql->user->id : null;
		$username    = isset( $user->graphql->user->username ) ? $user->graphql->user->username : '';
		$fullname    = isset( $user->graphql->user->full_name ) ? $user->graphql->user->full_name : '';
		$url         = isset( $user->graphql->user->id ) ? 'https://www.instagram.com/'.$user->graphql->user->id.'/' : null;
		$avatar      = isset( $user->graphql->user->profile_pic_url ) ? $user->graphql->user->profile_pic_url : null;
		$bio         = isset( $user->graphql->user->biography ) ? $user->graphql->user->biography : null;
		$followed_by = isset( $user->graphql->user->edge_followed_by->count ) ? $user->graphql->user->edge_followed_by->count : null;
		$follows     = isset( $user->graphql->user->edge_follow->count ) ? $user->graphql->user->edge_follow->count : null;
		$website     = isset( $user->graphql->user->external_url ) ? $user->graphql->user->external_url : null;
		$media       = isset( $user->graphql->user->edge_owner_to_timeline_media->count ) ? $user->graphql->user->edge_owner_to_timeline_media->count : null;

		$instagram = '<div class="tg-instagram-user-header">';
			$instagram .= '<div class="tg-instagram-user-image">';
				$instagram .= '<img width="150" height="150" alt="'.esc_attr($username).'" src="'.esc_url($avatar).'">';
			$instagram .= '</div>';
			$instagram .= '<div class="tg-instagram-user-desc">';
				$instagram .= '<div class="tg-instagram-user-info">';
					$instagram .= '<h2 class="tg-instagram-user-name">'.esc_html($username).'</h2>';
					$instagram .= '<a class="tg-instagram-user-follow" rel="nofollow me" target="_blank" href="'.esc_url('https://www.instagram.com/'.$username).'/">'.__( 'Follow', 'tg-text-domain' ).'</a>';
					$instagram .= '</div>';
				$instagram .= '<div class="tg-instagram-user-info">';
					$instagram .= '<h3 class="tg-instagram-user-desc-fullname">'.esc_html($fullname).'</h3>';
					$instagram .= ' <span class="tg-instagram-user-bio">'.esc_html($bio).'</span>';
					$instagram .= $website ? ' <a class="tg-instagram-user-desc-url" rel="nofollow me" target="_blank" href="'.esc_url($website).'">'.esc_html($website).'</a>' : '';
				$instagram .= '</div>';
				$instagram .= '<div class="tg-instagram-user-info">';
					$instagram .= '<span class="tg-instagram-user-cout">';
						$instagram .= '<span>'.esc_html($base->shorten_number_format($media)).'</span>';
							$instagram .= '<span> '.__( 'posts', 'tg-text-domain' ).'</span>';
					$instagram .= '</span>';
					$instagram .= '<span class="tg-instagram-user-cout">';
						$instagram .= '<span>'.esc_html($base->shorten_number_format($followed_by)).'</span>';
						$instagram .= '<span> '.__( 'followers', 'tg-text-domain' ).'</span>';
					$instagram .= '</span>';
					$instagram .= '<span class="tg-instagram-user-cout">';
							$instagram .= '<span>'.esc_html($base->shorten_number_format($follows)).'</span>';
						$instagram .= '<span> '.__( 'following', 'tg-text-domain' ).'</span>';
					$instagram .= '</span>';
				$instagram .= '</div>';
			$instagram .= '</div>';
		$instagram .= '</div>';
				
		echo $instagram;
	
	} catch (Exception $e) {
		return false;
	}
	
}
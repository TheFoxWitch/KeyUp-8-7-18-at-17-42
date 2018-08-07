<?php
function flywheel_xmlrpc_methods_disable( $methods ) {
	unset( $methods['system.multicall'] );
	// unset( $methods['system.listMethods'] );
	// unset( $methods['system.getCapabilities'] );
	return $methods;
}
add_filter('xmlrpc_methods', 'flywheel_xmlrpc_methods_disable');

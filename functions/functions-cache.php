<?php

//очищаем кеш плагина раз в сутки
add_action( 'rcl_cron_daily', 'rcl_clear_cache', 20 );
function rcl_clear_cache() {
	$rcl_cache = new Rcl_Cache();
	$rcl_cache->clear_cache();
}

//удаление определенного файла кеша
function rcl_delete_file_cache( $string ) {
	$rcl_cache = new Rcl_Cache();
	$rcl_cache->get_file( $string );
	$rcl_cache->delete_file();
}

function rcl_cache_get( $string, $force = false ) {

	$cache = new Rcl_Cache();

	if ( $cache->is_cache || $force ) {

		$file = $cache->get_file( $string );

		if ( ! $file->need_update ) {

			return $cache->get_cache();
		}
	}

	return false;
}

function rcl_cache_add( $string, $content, $force = false ) {

	$cache = new Rcl_Cache();

	if ( $cache->is_cache || $force ) {

		$file = $cache->get_file( $string );

		if ( $file->need_update ) {

			return $cache->update_cache( $content );
		}
	}

	return false;
}

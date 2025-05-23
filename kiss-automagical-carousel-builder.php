<?php
/**
 * Plugin Name:  KISS Automagical Carousel Builder
 * Description:  Detects runs of 3–4 consecutive images at render‑time and
 *               replaces them with a Swiper carousel — entirely page‑cache‑safe.
 * Version:      1.1.7            ; NOTE FOR LLM MAINTAINERS — bump semver only
 * Author:       Your Name
 * License:      GPL‑2.0‑or‑later
 *
 * --------------------------------------------------------------------------
 * TABLE OF CONTENTS
 * --------------------------------------------------------------------------
 *  1. Constants
 *  2. Asset registration
 *  3. Render‑time filter
 *  4. Debug shortcode  [kacb debug="true"]
 * ------------------------------------------------------------------------ */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ---------------------------------------------------------------------- *
 * 1. CONSTANTS
 * ---------------------------------------------------------------------- */
const KACB_VER = '1.1.6';
define( 'KACB_URL',  plugin_dir_url( __FILE__ ) );
define( 'KACB_PATH', plugin_dir_path( __FILE__ ) );

/* ---------------------------------------------------------------------- *
 * 2. ASSET REGISTRATION
 * ---------------------------------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
	wp_register_style ( 'kacb-swiper', 'https://unpkg.com/swiper@11/swiper-bundle.min.css', [], '11.1.1' );
	wp_register_script( 'kacb-swiper', 'https://unpkg.com/swiper@11/swiper-bundle.min.js',  [], '11.1.1', true );
	wp_register_script( 'kacb-init',   KACB_URL . 'kacb.js', [ 'kacb-swiper' ], KACB_VER, true );
	wp_register_style ( 'kacb-inline', false );
}, 20 );

/* ---------------------------------------------------------------------- *
 * 3. RENDER‑TIME FILTER  (priority 9999 → run last)
 * ---------------------------------------------------------------------- */
add_filter( 'the_content', function ( $html ) {

	if ( is_admin() && ! wp_doing_ajax() ) return $html;   // safe‑guard

	$GLOBALS['kacb_filter_ran'] = true;                    // for debug panel

	libxml_use_internal_errors( true );
	$doc = new DOMDocument();
	$doc->loadHTML( '<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();

	$is_ws = static fn( $n ) => $n && $n->nodeType === XML_TEXT_NODE && trim( $n->textContent ) === '';

	$imgs  = iterator_to_array( $doc->getElementsByTagName( 'img' ) );
	$runs  = $buff = [];

	foreach ( $imgs as $img ) {

		$node = ( $img->parentNode->nodeName === 'p' && $img->parentNode->childNodes->length === 1 )
		      ? $img->parentNode
		      : $img;

		$nxt = $node->nextSibling;
		while ( $is_ws( $nxt ) ) $nxt = $nxt?->nextSibling;

		$is_next_img = $nxt instanceof DOMElement && (
			$nxt->nodeName === 'img' ||
			( $nxt->nodeName === 'p' && $nxt->childNodes->length === 1 &&
			  $nxt->firstChild->nodeName === 'img' )
		);

		$buff[] = $img;

		if ( ! $is_next_img ) {
			if ( count( $buff ) >= 3 && count( $buff ) <= 4 ) $runs[] = $buff;
			$buff = [];
		}
	}

	$GLOBALS['kacb_runs_found'] = count( $runs );
	if ( ! $runs ) return $html;

	/* enqueue once */
	wp_enqueue_style ( 'kacb-swiper' );
	wp_enqueue_script( 'kacb-swiper' );
	wp_enqueue_script( 'kacb-init'   );
	wp_enqueue_style ( 'kacb-inline' );

        wp_add_inline_style( 'kacb-inline', <<<CSS
                .kacb-carousel{position:relative}.kacb-slide{position:relative}
                .kacb-indicator{display:none;
                  position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
                  width:54px;height:54px;border-radius:50%;background:rgba(0,0,0,.65);color:#fff;
                  align-items:center;justify-content:center;font:600 14px/1 sans-serif;z-index:10}
                .kacb-filename{display:none;           /* ← hidden by default */
                  position:absolute;top:0;left:50%;transform:translateX(-50%);background:#fff;color:#000;
                  padding:.25em 1em;font:12px/1.4 sans-serif;z-index:9;width:max-content;text-align:center}
                .kacb-caption{position:absolute;bottom:0;left:50%;transform:translateX(-50%);background:#fff;color:#000;
                  padding:.5em 1em;min-height:3em;font:14px/1.4 sans-serif;display:flex;align-items:center;z-index:9;width:max-content;text-align:center}
                .swiper-button-prev,
                .swiper-button-next{
                  background:#fff;background-image:none;top:50%;transform:translateY(-50%);
                }
        CSS);

	/* build carousels */
	foreach ( $runs as $run ) {

		$parent = $run[0]->parentNode;
		$ref    = $run[0];

		$wrapper = $doc->createElement( 'div' );
		$wrapper->setAttribute( 'class', 'kacb-carousel swiper' );

		$inner = $doc->createElement( 'div' );
		$inner->setAttribute( 'class', 'swiper-wrapper' );
		$wrapper->appendChild( $inner );

		foreach ( $run as $img ) {

			$slide = $doc->createElement( 'div' );
			$slide->setAttribute( 'class', 'swiper-slide kacb-slide' );

			/* filename & caption with robust fallback chain */
			$filename = basename( $img->getAttribute( 'src' ) );
			$caption  = '';

			if ( preg_match( '/wp-image-(\d+)/', $img->getAttribute( 'class' ), $m ) ) {
				$id = (int) $m[1];
				$filename = basename( get_attached_file( $id ) );
				$caption  = wp_get_attachment_caption( $id );
				if ( trim( $caption ) === '' ) {
					$caption = get_post_field( 'post_excerpt', $id );
				}
				if ( trim( $caption ) === '' ) {
					$caption = get_the_title( $id );
				}
			}

			$slide->appendChild( $doc->createElement( 'div', esc_html( $filename ) ) )
			      ->setAttribute( 'class', 'kacb-filename' );

			$slide->appendChild( $img->cloneNode( true ) );

			if ( trim( $caption ) !== '' ) {                           // only if real caption
				$slide->appendChild( $doc->createElement( 'div', esc_html( $caption ) ) )
				      ->setAttribute( 'class', 'kacb-caption' );
			}

			$inner->appendChild( $slide );
		}

		$wrapper->appendChild( $doc->createElement( 'div' ) )
		        ->setAttribute( 'class', 'kacb-indicator' );

		$parent->insertBefore( $wrapper, $ref );
		foreach ( $run as $img ) $parent->removeChild( $img );
	}

	return preg_replace( '~^<!DOCTYPE.+?<body>|</body>$~is', '', $doc->saveHTML() );

}, 9999 );

/* ---------------------------------------------------------------------- *
 * 4. DEBUG SHORTCODE  [kacb debug="true"]
 * ---------------------------------------------------------------------- */
add_shortcode( 'kacb', function ( $atts ) {

	$atts = shortcode_atts( [ 'debug' => 'false' ], $atts );
	if ( strtolower( $atts['debug'] ) !== 'true' ) return '';

	/* defer actual printing to wp_footer so filter has finished */
	add_action( 'wp_footer', function () {

		$out  = sprintf( "KACB v%s\n---------------------------------\n", KACB_VER );
		$out .= sprintf( "Filter executed:         %s\n", ! empty( $GLOBALS['kacb_filter_ran'] ) ? 'YES' : 'NO' );
		$out .= sprintf( "Runs detected:           %d\n",  $GLOBALS['kacb_runs_found'] ?? 0 );
		$out .= sprintf( "DOMDocument present:     %s\n",  class_exists( 'DOMDocument', false ) ? 'YES' : 'NO!' );

		$css = wp_style_is(  'kacb-swiper', 'enqueued' ) || wp_style_is( 'kacb-swiper', 'done' );
		$js  = wp_script_is( 'kacb-swiper', 'enqueued' ) || wp_script_is( 'kacb-swiper', 'done' );
		$ini = wp_script_is( 'kacb-init',   'enqueued' ) || wp_script_is( 'kacb-init',   'done' );
		$disk= file_exists( KACB_PATH . 'kacb.js' );

		$out .= "\nAssets\n";
		$out .= sprintf( "  Swiper CSS queued:     %s\n", $css  ? 'YES' : 'NO' );
		$out .= sprintf( "  Swiper JS queued:      %s\n", $js   ? 'YES' : 'NO' );
		$out .= sprintf( "  kacb.js queued:        %s\n", $ini  ? 'YES' : 'NO' );
		$out .= sprintf( "  kacb.js on disk:       %s\n", $disk ? 'YES' : 'NO' );

		echo '<style>
		        .kacb-debug pre{background:#111;color:#0f0;padding:1em;font-size:13px;overflow:auto}
		        .kacb-indicator{display:flex !important}     /* show badge in debug mode */
		        .kacb-filename {display:block !important}    /* show filename overlay */
		      </style>';
		echo '<div class="kacb-debug"><pre>' . esc_html( $out ) . '</pre></div>';
	}, 9999 );

	return '';  // shortcode outputs nothing immediately
} );

/**
 * MarreiraMCP Bricks — interacoes da tela de admin.
 * Botoes de "copiar" para o endpoint e o token.
 */
( function () {
	'use strict';

	function flash( btn, label ) {
		var original = btn.getAttribute( 'data-label' ) || btn.textContent;
		btn.setAttribute( 'data-label', original );
		btn.textContent = label;
		window.setTimeout( function () {
			btn.textContent = original;
		}, 1600 );
	}

	function copyFrom( selector, btn ) {
		var el = document.querySelector( selector );
		if ( ! el ) {
			return;
		}
		var text = el.textContent.trim();

		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( text ).then(
				function () { flash( btn, btn.getAttribute( 'data-done' ) || 'Copiado!' ); },
				function () { flash( btn, 'Falhou' ); }
			);
		} else {
			// Fallback legado.
			var range = document.createRange();
			range.selectNodeContents( el );
			var sel = window.getSelection();
			sel.removeAllRanges();
			sel.addRange( range );
			try {
				document.execCommand( 'copy' );
				flash( btn, btn.getAttribute( 'data-done' ) || 'Copiado!' );
			} catch ( e ) {
				flash( btn, 'Falhou' );
			}
			sel.removeAllRanges();
		}
	}

	document.addEventListener( 'click', function ( ev ) {
		var btn = ev.target.closest( '.mmb-copy' );
		if ( ! btn ) {
			return;
		}
		ev.preventDefault();
		copyFrom( btn.getAttribute( 'data-target' ), btn );
	} );
} )();

<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\TabberNeue\Scribunto;

use MediaWiki\Extension\Scribunto\Engines\LuaCommon\LibraryBase;
use MediaWiki\Extension\Scribunto\Engines\LuaCommon\LuaError;

class LuaLibrary extends LibraryBase {

	/**
	 * @inheritDoc
	 */
	public function register(): array {
		$lib = [
			'render' => [ $this, 'render' ],
		];

		return $this->getEngine()->registerInterface( __DIR__ . DIRECTORY_SEPARATOR . 'mw.ext.tabber.lua', $lib, [] );
	}

	public function render( $tabData = null ): array {
		$this->checkType( 'mw.ext.tabber.render', 1, $tabData, 'table' );

		$tagParams = $this->convertToTagParams( $tabData );

		$parser = $this->getParser();
		$frame = $parser->getPreprocessor()->newFrame();

		return [ $parser->callParserFunction( $frame, '#tag', $tagParams)['text'] ];
	}

	/**
	 * @throws LuaError If the tab data is invalid.
	 */
	private function convertToTagParams( array $tabData ): array {
		$tagParams = [ 'tabber', '' ];
		$nextCounter = 0;
		foreach ( $tabData as $tab ) {
			if ( !is_array( $tab ) || !isset( $tab['label'], $tab['content'] ) ) {
				throw new LuaError( 'Tab must be an array with label and content keys' );
			}

			if ( !is_string( $tab['label'] ) || !is_string( $tab['content'] ) ) {
				throw new LuaError( 'Tab label and content must be strings' );
			}

			$nextCounter++;
			$tagParams['label' . $nextCounter] = $tab['label'];
			$tagParams['content' . $nextCounter] = $tab['content'];
		}

		if ($nextCounter == 0) {
			throw new LuaError( 'No arguments provided to tabber' );
		}

		return $tagParams;
	}
}

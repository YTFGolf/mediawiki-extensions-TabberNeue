<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\TabberNeue\Scribunto;

use MediaWiki\Extension\Scribunto\Engines\LuaCommon\LibraryBase;
use MediaWiki\Extension\Scribunto\Engines\LuaCommon\LuaError;
use MediaWiki\Extension\TabberNeue\Service\TabNameHelper;
use MediaWiki\Extension\TabberNeue\Tabber;
use MediaWiki\Html\TemplateParser;
use MediaWiki\Html\Html;
use MediaWiki\MediaWikiServices;


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

	public function render( $tabData = null): array {
		$this->checkType( 'mw.ext.tabber.render', 1, $tabData, 'table' );

		$templateParser = new TemplateParser( __DIR__ . '/templates' );
		$tabNameHelper = new TabNameHelper(true);
		$services = MediaWikiServices::getInstance();

		$config = $services->getMainConfig();

		$tabber = new Tabber(
			$config,
			$templateParser,
			$tabNameHelper,
		);

		$parser = $this->getParser();
		$parserOutput = $parser->getOutput();
		$parserOutput->addModuleStyles( [ 'ext.tabberNeue.init.styles' ] );
		$parserOutput->addModules( ['aa', 'ext.tabberNeue' ] );
		$parserOutput->addWarningMsg('lol');
		$parser->addTrackingCategory( 'tabberneue-tabber-category' );
		$parser->setOutputType(3);

		$frame = $parser->getPreprocessor()->newFrame();
		$rawHtml = $tabber->renderTabData($tabData, [], $parser, $frame);
		// echo "<pre>\n";
		// echo $rawHtml;
		// echo '<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><div style="clear:both"';
		// $po = $this->getParserOptions();
		// echo "\n\n";
		// print_r($po);
		// echo "\n</pre>";
		    // $parserOutput = new ParserOutput();
    $parserOutput->setText( $rawHtml );
	//   return ['lol'];
			return  [ $rawHtml ] ;
	}

	/**
	 * @throws LuaError If the tab data is invalid.
	 */
	private function convertToWikitext( array $tabData ): string {
		$wikitext = '';
		foreach ( $tabData as $tab ) {
			if ( !is_array( $tab ) || !isset( $tab['label'], $tab['content'] ) ) {
				throw new LuaError( 'Tab must be an array with label and content keys' );
			}

			if ( !is_string( $tab['label'] ) || !is_string( $tab['content'] ) ) {
				throw new LuaError( 'Tab label and content must be strings' );
			}

			$wikitext .= '{{!}}-{{!}}' . $tab['label'] . '=' . $tab['content'];
		}

		if ( $wikitext === '' ) {
			return '';
		}

		return '{{#tag:tabber|' . $wikitext . '}}';
	}
}

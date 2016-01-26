/*!
 * This file is part of TwistPHP.
 *
 * TwistPHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TwistPHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TwistPHP.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Shadow Technologies Ltd. <contact@shadow-technologies.co.uk>
 * @license    https://www.gnu.org/licenses/gpl.html LGPL License
 * @link       https://twistphp.com
 *
 * --------------
 * TwistPHP Debug
 * --------------
 */

(
		(function( root, factory ) {
			if( typeof define === 'function' &&
					define.amd ) {
				define(
						'twistdebug',
						['postal'],
						function( postal ) {
							return ( root.twistdebug = factory( postal ) );
						}
				);
			} else if( typeof module === 'object' &&
					module.exports ) {
				module.exports = ( root.twistdebug = factory( require( 'postal' ) ) );
			} else {
				root.twistdebug = factory( root.postal );
			}
		})(
				this,
				function( postal ) {
					var TwistDebug = function() {
						var arrThingsToLog = [],
								logJavaScriptError = function( strColour, strTitle, strErrorMessage, strURL, intLineNumber, intColumn, objError ) {
									var objToStack = {
										colour: strColour,
										title: strTitle,
										message: strErrorMessage,
										url: strURL,
										line: intLineNumber,
										column: intColumn,
										error: objError
									};

									arrThingsToLog.push( objToStack );
								};


						window.onerror = function( strErrorMessage, strURL, intLineNumber, intColumn, objError ) {
							logJavaScriptError( 'red', 'JS Error', strErrorMessage, strURL, intLineNumber, intColumn, objError );

							return true;
						};

						this.log = function() {
							if( window.console &&
									window.console.log &&
									arguments.length ) {
								for( var intArguement in arguments ) {
									window.console.log( arguments[intArguement] );
								}
							}
						};

						this.info = function() {
							if( window.console &&
									arguments.length ) {
								for( var intArguement in arguments ) {
									if( window.console.info ) {
										window.console.info( arguments[intArguement] );
									} else {
										log( 'INFO: ', arguments[intArguement] );
									}
								}
							}
						};

						this.error = function() {
							if( window.console &&
									arguments.length ) {
								for( var intArguement in arguments ) {
									if( window.console.error ) {
										window.console.error( arguments[intArguement] );
									} else {
										log( 'ERROR: ', arguments[intArguement] );
									}
								}
							}
						};

						this.warn = function() {
							if( window.console &&
									arguments.length ) {
								for( var intArguement in arguments ) {
									if( window.console.warn ) {
										window.console.warn( arguments[intArguement] );
									} else {
										log( 'WARNING: ', arguments[intArguement] );
									}
								}
							}
						};
					};

					return TwistDebug;
				}
		)
);


(function( window, document ) {
	var arrThingsToLog = [],
			logToDebugConsole = function( strColour, strTitle, strErrorMessage, strURL, intLineNumber, intColumn, objError ) {
				var objToStack = {
					colour: strColour,
					title: strTitle,
					message: strErrorMessage,
					url: strURL,
					line: intLineNumber,
					column: intColumn,
					error: objError
				};

				arrThingsToLog.push( objToStack );
			},
			log = function() {
				if( window.console &&
						window.console.log &&
						arguments.length ) {
					for( var intArguement in arguments ) {
						window.console.log( arguments[intArguement] );
					}
				}
			},
			info = function() {
				if( window.console &&
						arguments.length ) {
					for( var intArguement in arguments ) {
						if( window.console.info ) {
							window.console.info( arguments[intArguement] );
						} else {
							log( 'INFO: ', arguments[intArguement] );
						}
					}
				}
			},
			error = function() {
				if( window.console &&
						arguments.length ) {
					for( var intArguement in arguments ) {
						if( window.console.error ) {
							window.console.error( arguments[intArguement] );
						} else {
							log( 'ERROR: ', arguments[intArguement] );
						}
					}
				}
			},
			warn = function() {
				if( window.console &&
						arguments.length ) {
					for( var intArguement in arguments ) {
						if( window.console.warn ) {
							window.console.warn( arguments[intArguement] );
						} else {
							log( 'WARNING: ', arguments[intArguement] );
						}
					}
				}
			},
			funOriginalWindowError = window.onerror || function() {},
			blUseOriginalError = false;

	window.onerror = function( strErrorMessage, strURL, intLineNumber, intColumn, objError ) {
		logToDebugConsole( 'red', 'JS Error', strErrorMessage, strURL, intLineNumber, intColumn, objError );

		return true;
	};

	var blOtherJSLibrary = false,
			getScript = function( strURL, funSuccess ) {
				var domScript = document.createElement( 'script' ),
						domHead = document.getElementsByTagName( 'head' )[0],
						blDone = false;

				funSuccess = ( typeof funSuccess === 'function' ) ? funSuccess : function() {};

				domScript.src = strURL;
				domScript.onload = domScript.onreadystatechange = function() {
					if( !blDone &&
							( !this.readyState ||
							this.readyState === 'loaded' ||
							this.readyState === 'complete' ) ) {
						blDone = true;
						try {
							funSuccess();
						} catch( err ) {
							error( err );
						}
						domScript.onload = domScript.onreadystatechange = null;
						domHead.removeChild( domScript );
					}
				};

				domHead.appendChild( domScript );
			},
			loadDebugger = function( blNoConfilct ) {
				/* ===================================================== GO, GO, GO! ===================================================== */
				var $ = ( blNoConfilct === true ) ? window.jQuery.noConflict( true ) : window.jQuery,
						blMemoryChartLoaded = false,
						jqoTwistDebugBlocks = $( '#twist-debug-blocks' ),
						jqoTwistDebugDetails = $( '#twist-debug-details' );

				info( 'jQuery v.' + $.fn.jquery + ' is ready' );

				logToDebugConsole = function( strTitle, strColour, strErrorMessage, strURL, intLineNumber, intColumn, objError ) {
					var jqoLog = $( '<p/>' ).html( '<strong>' + strTitle + ':</strong> ' + strErrorMessage );

					$( '#twist-debug-messages' ).find( '.twist-debug-column-wrapper' ).append( jqoLog );

					jqoLog.wrap( '<div class="twist-debug-column-100"/>' ).wrap( '<div class="twist-debug-box-' + strColour + ' twist-debug-message" data-title="' + strURL + ', line ' + intLineNumber + '"/>' );

					var jqoErrorCount = $( '#twist-debug-errors' );

					jqoErrorCount.html( jqoErrorCount.find( 'i' )[0].outerHTML + ( parseInt( jqoErrorCount.text() ) + 1 ) ).removeClass( 'twist-debug-hidden' );
				};

				for( var intStackedLog in arrThingsToLog ) {
					logToDebugConsole( arrThingsToLog[intStackedLog].colour, arrThingsToLog[intStackedLog].title, arrThingsToLog[intStackedLog].message, arrThingsToLog[intStackedLog].url, arrThingsToLog[intStackedLog].line, arrThingsToLog[intStackedLog].column, arrThingsToLog[intStackedLog].error );
				}

				$( '.twist-debug-box, [class^="twist-debug-box-"], [class*=" twist-debug-box-"]' ).has( '.twist-debug-more-details' ).each(
						function() {
							var jqoMoreDetails = $( this ).find( '.twist-debug-more-details' );

							jqoMoreDetails.after( '<a href="#twist-debug-more-details" class="twist-debug-more-details">&ctdot;</a>' );
						}
				);
				jqoTwistDebugBlocks.on( 'click', 'a',
						function( e ) {
							e.preventDefault();
							var jqoThisBlock = $( this );
							if( jqoThisBlock.hasClass( 'current' ) ) {
								jqoTwistDebugDetails.removeClass( 'show' );
								jqoThisBlock.removeClass( 'current' );
							} else {
								var jqsTarget = jqoThisBlock.attr( 'href' );

								jqoTwistDebugDetails.addClass( 'show' ).children( 'div' ).hide().filter( jqsTarget ).show();
								jqoTwistDebugBlocks.find( 'a.current' ).removeClass( 'current' );
								jqoThisBlock.addClass( 'current' );

								if( !blMemoryChartLoaded &&
										jqsTarget === '#twist-debug-memory' ) {
									/*google.load('visualization', '1.1', {packages: ['line']});
									 var data = new google.visualization.DataTable();

									 data.addColumn( 'number', 'Time' );
									 data.addColumn( 'number', 'Memory (MB)' );
									 data.addRows(
									 [
									 [1, 37.8],
									 [2, 30.9],
									 [3, 25.4],
									 [6, 8.8],
									 [7, 7.6],
									 [8, 12.3],
									 [10,12.8],
									 [11, 5.3],
									 [14, 4.2]
									 ]
									 );

									 var chart = new google.charts.Line( document.getElementById( 'twist-debug-memory-chart' ) );

									 chart.draw( data,
									 {
									 chart: { title: 'Memory Usage' },
									 width: 900,
									 height: 500
									 }
									 );*/

									blMemoryChartLoaded = true;
								}
							}
						}
				);
				jqoTwistDebugDetails.on( 'click', 'a[href="#close-twist-debug-details"]',
						function( e ) {
							e.preventDefault();
							jqoTwistDebugBlocks.find( 'a.current' ).removeClass( 'current' );
							jqoTwistDebugDetails.removeClass( 'show' );
						}
				).on( 'click', 'a[href="#twist-debug-more-details"]',
						function( e ) {
							e.preventDefault();

							$( this ).prev( '.twist-debug-more-details' ).slideToggle();
						}
				);
			};

	if( typeof window.jQuery === 'undefined' ) {
		blOtherJSLibrary = ( typeof window.$ === 'function' );

		getScript( 'http' + ( location.protocol === 'https:' ? 's' : '' ) + '://code.jquery.com/jquery-1.12.0.min.js',
				function() {
					if( typeof window.jQuery === 'undefined' ) {
						error( 'This is embarrassing... jQuery couldn\'t be loaded' );
					} else {
						if( !blOtherJSLibrary ) {
							loadDebugger( false );
						} else {
							warn( 'Another JS library controls $' );
							loadDebugger( true );
						}
					}
				}
		);
	} else {
		info( 'jQuery v.' + $.fn.jquery + ' exists' );
		loadDebugger( false );
	}
})( window, document );
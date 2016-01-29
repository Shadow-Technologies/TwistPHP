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
 * TwistPHP AJAX
 * --------------
 */

(
		(function( root, factory ) {
			if( typeof define === 'function' &&
					define.amd ) {
				define(
						'twistajax',
						['postal'],
						function( postal ) {
							return ( root.twistajax = factory( postal ) );
						}
				);
			} else if( typeof module === 'object' &&
					module.exports ) {
				module.exports = ( root.twistajax = factory( require( 'postal' ) ) );
			} else {
				root.twistajax = factory( root.postal );
			}
		})(
				this,
				function( postal ) {
					try {
						if( window.jQuery ) {
							var TwistAJAX = function( strAJAXPostLocation, b, c, d, e, f ) {
								var thisTwistAJAX = this,
										debugMode = false,
										contains = function( strNeedle, strHaystack, blCaseSensitive ) {
											blCaseSensitive = ( typeof blCaseSensitive === 'boolean' ) ? blCaseSensitive : false;
											if( blCaseSensitive ) {
												return strHaystack.indexOf( strNeedle ) !== -1;
											} else {
												return strHaystack.toLowerCase().indexOf( strNeedle.toLowerCase() ) !== -1;
											}
										},
										hasOwnProperty = function( objSubject, strProperty ) {
											return ( oldIE() && Object.prototype.hasOwnProperty.call( objSubject, strProperty ) ) || objSubject.hasOwnProperty( strProperty );
										},
										isBlank = function( mxdValue ) {
											return mxdValue.replace( /[\s\t\r\n]*/g, '' ) === '';
										},
										oldIE = function() {
											return navigator.userAgent.indexOf( 'MSIE ' );
										},
										error = function() {
											if( debugMode ) {
												sudoError( arguments );
											}
										},
										sudoError = function() {
											var arrArguements = arguments;
											if( window.twistdebug &&
													arrArguements.length > 0 ) {
												for( var intArguementA in arrArguements ) {
													window.twistdebug.error( arrArguements[intArguementA] );
												}
											} else if( window.console &&
													window.console.log &&
													arrArguements.length > 0 ) {
												for( var intArguementB in arrArguements ) {
													window.console.log( arrArguements[intArguementB] );
												}
											}
										},
										objectLength = function( objIn ) {
											var intLength = 0;
											if( typeof objIn === 'object' ) {
												for( var mxdKey in objIn ) {
													if( hasOwnProperty( objIn, mxdKey ) ) {
														intLength++;
													}
												}
											}
											return intLength;
										},
										prettySize = function( intBytes, blUseSpace ) {
											var arrLimits = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
													intLimit = 0;
											while( arrLimits[intLimit] &&
											intBytes > Math.pow( 1024, intLimit + 1 ) ) {
												intLimit++;
											}
											return round( intBytes / Math.pow( 1024, intLimit ), 2 ) + ( typeof blUseSpace === 'boolean' && blUseSpace ? ' ' : '' ) + arrLimits[intLimit];
										},
										round = function( intNumber, intDP ) {
											intDP = ( typeof intDP !== 'number' ) ? 0 : intDP;
											return intDP === 0 ? parseInt( Math.round( intNumber * Math.pow( 10, intDP ) ) / Math.pow( 10, intDP ) ) : parseFloat( Math.round( intNumber * Math.pow( 10, intDP ) ) / Math.pow( 10, intDP ) );
										},
										serializeJSON = function( jqoForm ) {
											var objJSON = {},
													arrFormElements = [];

											jQuery.map( jqoForm.serializeArray(),
													function( arrElement, intIndex ) {
														arrFormElements.push( {name: arrElement.name, value: arrElement.value} );
													}
											);

											jqoForm.find( 'input[type="submit"][name][value], input[type="reset"][name][value], input[type="button"][name][value], button[name][value]' ).each(
													function() {
														var jqoElement = $( this );
														arrFormElements.push( {name: jqoElement.attr( 'name' ), value: jqoElement.val()} );
													}
											);

											var returnNameObject = function( strFullName, strNameSoFar, strName, mxdValue ) {
												var objOut = {},
														arrNameMatches = strName.match( /^(\[([^\[]*)\])((\[[^\[]*\])*)$/i );

												if( arrNameMatches ) {
													var strThisKey = arrNameMatches[2];

													if( isBlank( strThisKey ) ) {
														var intKey = 1,
																blKeyExists = true;

														do {
															var blKeyFree = true;
															for( var intFormElement in arrFormElements ) {
																if( contains( strNameSoFar + '[' + intKey + ']', arrFormElements[intFormElement].name ) ) {
																	intKey++;
																	blKeyFree = false;
																}
															}

															if( blKeyFree ) {
																var blKeyReplaced = false;

																for( var intFormElement2 in arrFormElements ) {
																	if( !blKeyReplaced &&
																			arrFormElements[intFormElement2].name === strNameSoFar + '[]' &&
																			arrFormElements[intFormElement2].value === mxdValue ) {
																		arrFormElements[intFormElement2].name = strNameSoFar + '[' + intKey + ']';
																		blKeyReplaced = true;
																	}
																}

																blKeyExists = false;
															}
														} while( blKeyExists );

														strThisKey = intKey;
													}

													if( arrNameMatches[3] ) {
														objOut[strThisKey] = returnNameObject( strFullName, strNameSoFar + '[' + strThisKey + ']', arrNameMatches[3], mxdValue );
													} else {
														objOut[strThisKey] = mxdValue;
													}
												}

												return objOut;
											};

											$.each( arrFormElements,
													function( intIndex, arrFormElement ) {
														var arrNameMatches = arrFormElement.name.match( /^([^\[]+)((\[[^\[]*\])+)$/i );

														if( arrNameMatches ) {
															var objThisName = {};
															objThisName[arrNameMatches[1]] = returnNameObject( arrFormElement.name, arrNameMatches[1], arrNameMatches[2], arrFormElement.value );

															objJSON = $.extend( true, objJSON, objThisName );
														} else {
															objJSON[arrFormElement.name] = arrFormElement.value;
														}
													}
											);

											return objJSON;
										},
										funMasterCallbackSuccess = function() {},
										funMasterCallbackFailure = function() {},
										objDefaultData = {},
										intMasterTimeout = 10000,
										strLoaderSize = 'medium',
										blCache = false,
										blShowLoader = true,
										send = function( strFunction, strMethod, blCache, b, c, d, e ) {
											thisTwistAJAX.count++;
											strMethod = ( typeof strMethod === 'string' ) ? strMethod : 'POST';

											$( '#twist-ajax-loader-size' ).text( 'Loading...' );

											if( thisTwistAJAX.count > 1 ) {
												$( '#twist-ajax-loader-count' ).text( thisTwistAJAX.count );
											}

											if( blShowLoader ) {
												$( '#twist-ajax-loader' ).stop().show().fadeTo( 0, 1 );
											}

											var objData = {},
													intTimeout = intMasterTimeout,
													funCallbackSuccess = function() {},
													funCallbackFailure = function() {};

											if( typeof b === 'object' ||
													( typeof b === 'string' &&
													( /^\#[0-9a-z_\-]+$/i ).test( b ) &&
													( $( b ) instanceof jQuery ||
													b.jquery ) ) ) {
												if( typeof b === 'object' ) {
													if( b instanceof jQuery ||
															b.jquery ) {
														objData = serializeJSON( b );
													} else {
														objData = ( objectLength( b ) === 0 ) ? objData : b;
													}
												} else {
													objData = serializeJSON( $( b ) );
												}
												if( typeof c === 'number' ) {
													intTimeout = c;
													if( typeof d === 'function' ) {
														funCallbackSuccess = d;
														if( typeof e === 'function' ) {
															funCallbackFailure = e;
														}
													}
												} else if( typeof c === 'function' ) {
													funCallbackSuccess = c;
													if( typeof d === 'function' ) {
														funCallbackFailure = d;
													}
												}
											} else if( typeof b === 'number' ) {
												intTimeout = b;
												if( typeof c === 'function' ) {
													funCallbackSuccess = c;
													if( typeof d === 'function' ) {
														funCallbackFailure = d;
													}
												}
											} else if( typeof b === 'function' ) {
												funCallbackSuccess = b;
												if( typeof c === 'function' ) {
													funCallbackFailure = c;
												}
											}

											var strFinalURL = strAJAXPostLocation + '/' + strFunction.replace( /^\//, '' ),
													objAJAXRequest = {
														type: strMethod.toUpperCase(),
														url: strFinalURL,
														data: objData,
														dataType: 'json',
														timeout: intTimeout,
														global: true,
														cache: blCache,
														complete: function( jqXHR, strStatusText ) {
															thisTwistAJAX.count--;

															if( thisTwistAJAX.count === 0 ) {
																$( '#twist-ajax-loader' ).stop().fadeTo( 200, 0,
																		function() {
																			$( this ).hide();
																		}
																);
															} else {
																$( '#twist-ajax-loader-count' ).text( thisTwistAJAX.count > 1 ? thisTwistAJAX.count : '' );
															}
														},
														success: function( objResponse, strStatusText, jqXHR ) {
															var strContentLength = prettySize( jqXHR.getResponseHeader( 'Content-Length' ) );
															$( '#twist-ajax-loader-size' ).text( 'Downloading ' + strContentLength + '...' );
															if( objResponse &&
																	typeof objResponse === 'object' &&
																	hasOwnProperty( objResponse, 'status' ) &&
																	objResponse.status === true ) {
																funCallbackSuccessEnd( objResponse, objAJAXRequest );
															} else {
																funCallbackFailureEnd( objResponse, objAJAXRequest );
															}
														},
														error: function( jqXHR, strStatusText, strError ) {
															switch( strStatusText ) {
																case 'abort':
																	funCallbackFailureEnd( {}, objAJAXRequest );
																	$( '#twist-ajax-loader-size' ).text( 'Aborted' );
																	error( 'The AJAX request was aborted' );
																	break;

																case 'timeout':
																	funCallbackFailureEnd( {}, objAJAXRequest );
																	$( '#twist-ajax-loader-size' ).text( 'Timeout' );
																	error( 'The AJAX request timed out' );
																	break;

																case 'parsererror':
																	var rexJSON = /{"status":(true|false),"message":"[^"]*","data":({.*}|\[\])(,"[^"]+":(true|false|("[^"]*")|({.*}|\[\])))*(,"debug":({.*}|\[\])(,"[^"]+":(true|false|("[^"]*")|({.*}|\[\])))*)?}/,
																			strContentLength = prettySize( jqXHR.getResponseHeader( 'Content-Length' ) ),
																			strSeperator = '===============================================';

																	if( rexJSON.test( jqXHR.responseText ) ) {
																		if( window.twistdebug ) {
																			sudoError( strSeperator + '<br>PARSER ERROR RECOVERY<br>' + strSeperator + '<br>Date:             ' + jqXHR.getResponseHeader( 'Date' ) + '<br>Location:         ' + strFinalURL + '<br>Timeout:          ' + ( intTimeout / 1000 ) + 's<br>Response length:  ' + strContentLength + ( ( typeof JSON !== 'undefined' ) ? '<br>Post data:        ' + JSON.stringify( objData ) : '{}' ) + '<br>' + strSeperator );
																		} else {
																			sudoError( strSeperator + '\nPARSER ERROR RECOVERY\n' + strSeperator + '\nDate:             ' + jqXHR.getResponseHeader( 'Date' ) + '\nLocation:         ' + strFinalURL + '\nTimeout:          ' + ( intTimeout / 1000 ) + 's\nResponse length:  ' + strContentLength + ( ( typeof JSON !== 'undefined' ) ? '\nPost data:        ' + JSON.stringify( objData ) : '{}' ) + '\n' + strSeperator );
																		}
																		var strResponse = jqXHR.responseText.match( rexJSON )[0];
																		if( $.parseJSON( strResponse ) !== null ) {
																			var objResponse = $.parseJSON( strResponse );
																			if( typeof objResponse === 'object' &&
																					'status' in objResponse &&
																					objResponse.status === true ) {
																				funCallbackSuccessEnd( objResponse, objAJAXRequest );
																			} else {
																				funCallbackFailureEnd( objResponse, objAJAXRequest );
																			}
																		} else {
																			funCallbackFailureEnd( {}, objAJAXRequest );
																			try {
																				thisTwistAJAX.onfail( strError );
																			} catch( err ) {
																				error( err );
																			}
																		}
																	} else {
																		sudoError( 'Parse error' );
																		funCallbackFailureEnd( {}, objAJAXRequest );
																		try {
																			thisTwistAJAX.onfail( strError );
																		} catch( err ) {
																			error( err );
																		}
																	}
																	break;

																//case 'error':
																default:
																	funCallbackFailureEnd( {}, objAJAXRequest );
																	$( '#twist-ajax-loader-size' ).text( 'Error' );
																	try {
																		thisTwistAJAX.onfail( strError );
																	} catch( err ) {
																		error( err );
																	}
																	break;
															}
														}
													},
													funCallbackSuccessEnd = function( objResponse, objRequest ) {
														try {
															funCallbackSuccess.call( objResponse, objRequest );
															funMasterCallbackSuccess.call( objResponse, objRequest );
															if( window.twistdebug ) {
																window.twistdebug.logAJAX( true, objResponse, objRequest );
															}
														} catch( err ) {
															error( err );
														}
													},
													funCallbackFailureEnd = function( objResponse, objRequest ) {
														try {
															funCallbackFailure.call( objResponse, objRequest );
															funMasterCallbackFailure.call( objResponse, objRequest );
															if( window.twistdebug ) {
																window.twistdebug.logAJAX( false, objResponse, objRequest );
															}
														} catch( err ) {
															error( err );
														}
													};

											$.each( thisTwistAJAX.defaultArray,
													function( strIndex, mxdValue ) {
														objData[strIndex] = mxdValue;
													}
											);

											objAJAXRequest.data = objData;

											return $.ajax( objAJAXRequest );
										};

								if( typeof strAJAXPostLocation !== 'string' ||
										strAJAXPostLocation === '' ) {
									throw new Error( 'Please specify an AJAX URI' );
								} else {
									strAJAXPostLocation = strAJAXPostLocation.replace( /\/$/, '' );
								}

								if( typeof b === 'function' ) {
									funMasterCallbackSuccess = b;
									if( typeof c === 'function' ) {
										funMasterCallbackFailure = c;
										if( typeof d === 'object' ) {
											objDefaultData = d;
											if( typeof e === 'number' ) {
												intMasterTimeout = e;
												if( typeof f === 'string' ) {
													strLoaderSize = f;
												}
											} else if( typeof e === 'string' ) {
												strLoaderSize = e;
											}
										} else if( typeof d === 'number' ) {
											intMasterTimeout = d;
											if( typeof e === 'string' ) {
												strLoaderSize = e;
											}
										} else if( typeof d === 'string' ) {
											strLoaderSize = d;
										}
									} else if( c === 'object' ) {
										objDefaultData = c;
										if( typeof d === 'number' ) {
											intMasterTimeout = d;
											if( typeof e === 'string' ) {
												strLoaderSize = e;
											}
										} else if( typeof d === 'string' ) {
											strLoaderSize = d;
										}
									} else if( c === 'number' ) {
										intMasterTimeout = c;
										if( typeof d === 'string' ) {
											strLoaderSize = d;
										}
									} else if( typeof c === 'string' ) {
										strLoaderSize = c;
									}
								} else if( typeof b === 'object' ) {
									objDefaultData = b;
									if( typeof c === 'number' ) {
										intMasterTimeout = c;
										if( typeof d === 'string' ) {
											strLoaderSize = d;
										}
									} else if( typeof c === 'string' ) {
										strLoaderSize = c;
									}
								} else if( typeof b === 'number' ) {
									intMasterTimeout = b;
									if( typeof c === 'string' ) {
										strLoaderSize = c;
									}
								} else if( typeof b === 'string' ) {
									strLoaderSize = b;
								}

								$.ajaxSetup(
										{
											url: strAJAXPostLocation,
											timeout: intMasterTimeout
										}
								);

								this.count = 0;

								this.debug = function( blEnable ) {
									debugMode = ( blEnable !== false );
									return thisTwistAJAX;
								};

								this.defaultArray = objDefaultData;

								this.delete = function( strFunction, b, c, d, e ) {
									if( strFunction ) {
										send( strFunction, 'DELETE', blCache, b, c, d, e );
									}
									return thisTwistAJAX;
								};

								this.disableCache = function() {
									blCache = false;
									return thisTwistAJAX;
								};

								this.disableLoader = function() {
									blShowLoader = false;
									return thisTwistAJAX;
								};

								this.enableCache = function() {
									blCache = true;
									return thisTwistAJAX;
								};

								this.enableLoader = function() {
									blShowLoader = true;
									return thisTwistAJAX;
								};

								this.get = function( strFunction, b, c, d, e ) {
									if( strFunction ) {
										send( strFunction, 'GET', blCache, b, c, d, e );
									}
									return thisTwistAJAX;
								};

								this.loaderSize = function( strSize ) {
									if( $( '#twist-ajax-loader' ).length ) {
										$( '#twist-ajax-loader' ).attr( 'class', '' ).addClass( strSize );
									}

									strLoaderSize = strSize;

									return thisTwistAJAX;
								};

								this.onfail = function( strMessage ) {
									var strErrorMessage = ( typeof strMessage === 'string' && strMessage !== '' ) ? strMessage : 'An unexpected AJAX response was given';
									error( strErrorMessage );

									return thisTwistAJAX;
								};

								this.patch = function( strFunction, b, c, d, e ) {
									send( strFunction, 'PATCH', blCache, b, c, d, e );
									return thisTwistAJAX;
								};

								this.post = function( strFunction, b, c, d, e ) {
									if( strFunction ) {
										send( strFunction, 'POST', blCache, b, c, d, e );
									}
									return thisTwistAJAX;
								};

								this.put = function( strFunction, b, c, d, e ) {
									if( strFunction ) {
										send( strFunction, 'PUT', blCache, b, c, d, e );
									}
									return thisTwistAJAX;
								};

								$( document ).ready(
										function() {
											if( !$( '#twist-ajax-loader' ).length ) {
												$( 'body' ).prepend( '<div id="twist-ajax-loader" class="' + strLoaderSize + '"><span id="twist-ajax-loader-count"></span><span id="twist-ajax-loader-size"></span></div>' );
											}
										}
								);

								return true;
							};

							return TwistAJAX;
						} else {
							throw 'Twist AJAX requires jQuery to run';
						}
					} catch( err ) {
						if( window.twistdebug ) {
							window.twistdebug.error( err );
						} else if( window.console &&
								window.console.log ) {
							console.error( err );
						}
					}
				}
		)
);
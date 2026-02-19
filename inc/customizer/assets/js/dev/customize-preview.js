/**
 * Update Customizer settings live.
 *
 * @since 1.0.0
 */
(function ($) {
	'use strict';

	// Declare variables
	var api = wp.customize,
		$body = $('body'),
		$head = $('head'),
		$style_tag,
		$link_tag,
		prisma_core_visibility_classes = 'prisma-core-hide-mobile prisma-core-hide-tablet prisma-core-hide-mobile-tablet',
		prisma_core_style_tag_collection = [],
		prisma_core_link_tag_collection = [];

	/**
	 * Helper function to get style tag with id.
	 */
	function prisma_core_get_style_tag(id) {
		if (prisma_core_style_tag_collection[id]) {
			return prisma_core_style_tag_collection[id];
		}

		$style_tag = $('head').find('#prisma-core-dynamic-' + id);

		if (!$style_tag.length) {
			$('head').append('<style id="prisma-core-dynamic-' + id + '" type="text/css" href="#"></style>');
			$style_tag = $('head').find('#prisma-core-dynamic-' + id);
		}

		prisma_core_style_tag_collection[id] = $style_tag;

		return $style_tag;
	}

	/**
	 * Helper function to get link tag with id.
	 */
	function prisma_core_get_link_tag(id, url) {
		if (prisma_core_link_tag_collection[id]) {
			return prisma_core_link_tag_collection[id];
		}

		$link_tag = $('head').find('#prisma-core-dynamic-link-' + id);

		if (!$link_tag.length) {
			$('head').append('<link id="prisma-core-dynamic-' + id + '" type="text/css" rel="stylesheet" href="' + url + '"/>');
			$link_tag = $('head').find('#prisma-core-dynamic-link-' + id);
		} else {
			$link_tag.attr('href', url);
		}

		prisma_core_link_tag_collection[id] = $link_tag;

		return $link_tag;
	}

	/*
	 * Helper function to print visibility classes.
	 */
	function prisma_core_print_visibility_classes($element, newval) {
		if (!$element.length) {
			return;
		}

		$element.removeClass(prisma_core_visibility_classes);

		if ('all' !== newval) {
			$element.addClass('prisma-core-' + newval);
		}
	}

	/*
	 * Helper function to convert hex to rgba.
	 */
	function prisma_core_hex2rgba(hex, opacity) {
		if ('rgba' === hex.substring(0, 4)) {
			return hex;
		}

		// Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF").
		var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;

		hex = hex.replace(shorthandRegex, function (m, r, g, b) {
			return r + r + g + g + b + b;
		});

		var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);

		if (opacity) {
			if (opacity > 1) {
				opacity = 1;
			}

			opacity = ',' + opacity;
		}

		if (result) {
			return 'rgba(' + parseInt(result[1], 16) + ',' + parseInt(result[2], 16) + ',' + parseInt(result[3], 16) + opacity + ')';
		}

		return false;
	}

	/**
	 * Helper function to lighten or darken the provided hex color.
	 */
	function prisma_core_luminance(hex, percent) {
		// Convert RGB color to HEX.
		if (hex.includes('rgb')) {
			hex = prisma_core_rgba2hex(hex);
		}

		// Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF").
		var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;

		hex = hex.replace(shorthandRegex, function (m, r, g, b) {
			return r + r + g + g + b + b;
		});

		var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);

		var isColor = /^#[0-9A-F]{6}$/i.test(hex);

		if (!isColor) {
			return hex;
		}

		var from, to;

		for (var i = 1; i <= 3; i++) {
			result[i] = parseInt(result[i], 16);
			from = percent < 0 ? 0 : result[i];
			to = percent < 0 ? result[i] : 255;
			result[i] = result[i] + Math.ceil((to - from) * percent);
		}

		result = '#' + prisma_core_dec2hex(result[1]) + prisma_core_dec2hex(result[2]) + prisma_core_dec2hex(result[3]);

		return result;
	}

	/**
	 * Convert dec to hex.
	 */
	function prisma_core_dec2hex(c) {
		var hex = c.toString(16);
		return hex.length == 1 ? '0' + hex : hex;
	}

	/**
	 * Convert rgb to hex.
	 */
	function prisma_core_rgba2hex(c) {
		var a, x;

		a = c.split('(')[1].split(')')[0].trim();
		a = a.split(',');

		var result = '';

		for (var i = 0; i < 3; i++) {
			x = parseInt(a[i]).toString(16);
			result += 1 === x.length ? '0' + x : x;
		}

		if (result) {
			return '#' + result;
		}

		return false;
	}

	/**
	 * Check if is light color.
	 */
	function prisma_core_is_light_color(color = '') {
		var r, g, b, brightness;

		if (color.match(/^rgb/)) {
			color = color.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/);
			r = color[1];
			g = color[2];
			b = color[3];
		} else {
			color = +('0x' + color.slice(1).replace(color.length < 5 && /./g, '$&$&'));
			r = color >> 16;
			g = (color >> 8) & 255;
			b = color & 255;
		}

		brightness = (r * 299 + g * 587 + b * 114) / 1000;

		return brightness > 137;
	}

	/**
	 * Detect if we should use a light or dark color on a background color.
	 */
	function prisma_core_light_or_dark(color, dark = '#000000', light = '#FFFFFF') {
		return prisma_core_is_light_color(color) ? dark : light;
	}

	/**
	 * Spacing field CSS.
	 */
	function prisma_core_spacing_field_css(selector, property, setting, responsive) {
		if (!Array.isArray(setting) && 'object' !== typeof setting) {
			return;
		}

		// Set up unit.
		var unit = 'px',
			css = '';

		if ('unit' in setting) {
			unit = setting.unit;
		}

		var before = '',
			after = '';

		Object.keys(setting).forEach(function (index, el) {
			if ('unit' === index) {
				return;
			}

			if (responsive) {
				if ('tablet' === index) {
					before = '@media only screen and (max-width: 768px) {';
					after = '}';
				} else if ('mobile' === index) {
					before = '@media only screen and (max-width: 480px) {';
					after = '}';
				} else {
					before = '';
					after = '';
				}

				css += before + selector + '{';

				Object.keys(setting[index]).forEach(function (position) {
					if ('border' === property) {
						position += '-width';
					}

					if (setting[index][position]) {
						css += property + '-' + position + ': ' + setting[index][position] + unit + ';';
					}
				});

				css += '}' + after;
			} else {
				if ('border' === property) {
					index += '-width';
				}

				css += property + '-' + index + ': ' + setting[index] + unit + ';';
			}
		});

		if (!responsive) {
			css = selector + '{' + css + '}';
		}

		return css;
	}

	/**
	 * Range field CSS.
	 */
	function prisma_core_range_field_css(selector, property, setting, responsive, unit) {
		var css = '',
			before = '',
			after = '';

		if (responsive && (Array.isArray(setting) || 'object' === typeof setting)) {
			Object.keys(setting).forEach(function (index, el) {
				if (setting[index]) {
					if ('tablet' === index) {
						before = '@media only screen and (max-width: 768px) {';
						after = '}';
					} else if ('mobile' === index) {
						before = '@media only screen and (max-width: 480px) {';
						after = '}';
					} else if ('desktop' === index) {
						before = '';
						after = '';
					} else {
						return;
					}

					css += before + selector + '{' + property + ': ' + setting[index] + unit + '; }' + after;
				}
			});
		}

		if (!responsive) {
			if (setting.value) {
				setting = setting.value;
			} else {
				setting = 0;
			}

			css = selector + '{' + property + ': ' + setting + unit + '; }';
		}

		return css;
	}

	/**
	 * Typography field CSS.
	 */
	function prisma_core_typography_field_css(selector, setting) {
		var css = '';

		css += selector + '{';

		if ('default' === setting['font-family']) {
			css += 'font-family: ' + prisma_core_customizer_preview.default_system_font + ';';
		} else if (setting['font-family'] in prisma_core_customizer_preview.fonts.standard_fonts.fonts) {
			css += 'font-family: ' + prisma_core_customizer_preview.fonts.standard_fonts.fonts[setting['font-family']]['fallback'] + ';';
		} else if ('inherit' !== setting['font-family']) {
			css += 'font-family: "' + setting['font-family'] + '";';
		}

		css += 'font-weight:' + setting['font-weight'] + ';';
		css += 'font-style:' + setting['font-style'] + ';';
		css += 'text-transform:' + setting['text-transform'] + ';';

		if ('text-decoration' in setting) {
			css += 'text-decoration:' + setting['text-decoration'] + ';';
		}

		if ('letter-spacing' in setting) {
			css += 'letter-spacing:' + setting['letter-spacing'] + setting['letter-spacing-unit'] + ';';
		}

		if ('line-height-desktop' in setting) {
			css += 'line-height:' + setting['line-height-desktop'] + ';';
		}

		if ('font-size-desktop' in setting && 'font-size-unit' in setting) {
			css += 'font-size:' + setting['font-size-desktop'] + setting['font-size-unit'] + ';';
		}

		css += '}';

		if ('font-size-tablet' in setting && setting['font-size-tablet']) {
			css += '@media only screen and (max-width: 768px) {' + selector + '{' + 'font-size: ' + setting['font-size-tablet'] + setting['font-size-unit'] + ';' + '}' + '}';
		}

		if ('line-height-tablet' in setting && setting['line-height-tablet']) {
			css += '@media only screen and (max-width: 768px) {' + selector + '{' + 'line-height:' + setting['line-height-tablet'] + ';' + '}' + '}';
		}

		if ('font-size-mobile' in setting && setting['font-size-mobile']) {
			css += '@media only screen and (max-width: 480px) {' + selector + '{' + 'font-size: ' + setting['font-size-mobile'] + setting['font-size-unit'] + ';' + '}' + '}';
		}

		if ('line-height-mobile' in setting && setting['line-height-mobile']) {
			css += '@media only screen and (max-width: 480px) {' + selector + '{' + 'line-height:' + setting['line-height-mobile'] + ';' + '}' + '}';
		}

		return css;
	}

	/**
	 * Load google font.
	 */
	function prisma_core_enqueue_google_font(font) {
		if (prisma_core_customizer_preview.fonts.google_fonts.fonts[font]) {
			var id = 'google-font-' + font.trim().toLowerCase().replace(' ', '-');
			var url = prisma_core_customizer_preview.google_fonts_url + '/css?family=' + font + ':' + prisma_core_customizer_preview.google_font_weights;

			var tag = prisma_core_get_link_tag(id, url);
		}
	}

	/**
	 * Design Options field CSS.
	 */
	function prisma_core_design_options_css(selector, setting, type) {
		var css = '',
			before = '',
			after = '';

		if ('background' === type) {
			var bg_type = setting['background-type'];

			css += selector + '{';

			if ('color' === bg_type) {
				setting['background-color'] = setting['background-color'] ? setting['background-color'] : 'inherit';
				css += 'background: ' + setting['background-color'] + ';';
			} else if ('gradient' === bg_type) {
				css += 'background: ' + setting['gradient-color-1'] + ';';

				if ('linear' === setting['gradient-type']) {
					css +=
						'background: -webkit-linear-gradient(' +
						setting['gradient-linear-angle'] +
						'deg, ' +
						setting['gradient-color-1'] +
						' ' +
						setting['gradient-color-1-location'] +
						'%, ' +
						setting['gradient-color-2'] +
						' ' +
						setting['gradient-color-2-location'] +
						'%);' +
						'background: -o-linear-gradient(' +
						setting['gradient-linear-angle'] +
						'deg, ' +
						setting['gradient-color-1'] +
						' ' +
						setting['gradient-color-1-location'] +
						'%, ' +
						setting['gradient-color-2'] +
						' ' +
						setting['gradient-color-2-location'] +
						'%);' +
						'background: linear-gradient(' +
						setting['gradient-linear-angle'] +
						'deg, ' +
						setting['gradient-color-1'] +
						' ' +
						setting['gradient-color-1-location'] +
						'%, ' +
						setting['gradient-color-2'] +
						' ' +
						setting['gradient-color-2-location'] +
						'%);';
				} else if ('radial' === setting['gradient-type']) {
					css +=
						'background: -webkit-radial-gradient(' +
						setting['gradient-position'] +
						', circle, ' +
						setting['gradient-color-1'] +
						' ' +
						setting['gradient-color-1-location'] +
						'%, ' +
						setting['gradient-color-2'] +
						' ' +
						setting['gradient-color-2-location'] +
						'%);' +
						'background: -o-radial-gradient(' +
						setting['gradient-position'] +
						', circle, ' +
						setting['gradient-color-1'] +
						' ' +
						setting['gradient-color-1-location'] +
						'%, ' +
						setting['gradient-color-2'] +
						' ' +
						setting['gradient-color-2-location'] +
						'%);' +
						'background: radial-gradient(circle at ' +
						setting['gradient-position'] +
						', ' +
						setting['gradient-color-1'] +
						' ' +
						setting['gradient-color-1-location'] +
						'%, ' +
						setting['gradient-color-2'] +
						' ' +
						setting['gradient-color-2-location'] +
						'%);';
				}
			} else if ('image' === bg_type) {
				css +=
					'' +
					'background-image: url(' +
					setting['background-image'] +
					');' +
					'background-size: ' +
					setting['background-size'] +
					';' +
					'background-attachment: ' +
					setting['background-attachment'] +
					';' +
					'background-position: ' +
					setting['background-position-x'] +
					'% ' +
					setting['background-position-y'] +
					'%;' +
					'background-repeat: ' +
					setting['background-repeat'] +
					';';
			}

			css += '}';

			// Background image color overlay.
			if ('image' === bg_type && setting['background-color-overlay'] && setting['background-image']) {
				css += selector + '::after { background-color: ' + setting['background-color-overlay'] + '; }';
			} else {
				css += selector + '::after { background-color: initial; }';
			}
		} else if ('color' === type) {
			setting['text-color'] = setting['text-color'] ? setting['text-color'] : 'inherit';
			setting['link-color'] = setting['link-color'] ? setting['link-color'] : 'inherit';
			setting['link-hover-color'] = setting['link-hover-color'] ? setting['link-hover-color'] : 'inherit';

			css += selector + ' { color: ' + setting['text-color'] + '; }';
			css += selector + ' a { color: ' + setting['link-color'] + '; }';
			css += selector + ' a:hover { color: ' + setting['link-hover-color'] + ' !important; }';
		} else if ('border' === type) {
			setting['border-color'] = setting['border-color'] ? setting['border-color'] : 'inherit';
			setting['border-style'] = setting['border-style'] ? setting['border-style'] : 'solid';
			setting['border-left-width'] = setting['border-left-width'] ? setting['border-left-width'] : 0;
			setting['border-top-width'] = setting['border-top-width'] ? setting['border-top-width'] : 0;
			setting['border-right-width'] = setting['border-right-width'] ? setting['border-right-width'] : 0;
			setting['border-bottom-width'] = setting['border-bottom-width'] ? setting['border-bottom-width'] : 0;

			css += selector + '{';
			css += 'border-color: ' + setting['border-color'] + ';';
			css += 'border-style: ' + setting['border-style'] + ';';
			css += 'border-left-width: ' + setting['border-left-width'] + 'px;';
			css += 'border-top-width: ' + setting['border-top-width'] + 'px;';
			css += 'border-right-width: ' + setting['border-right-width'] + 'px;';
			css += 'border-bottom-width: ' + setting['border-bottom-width'] + 'px;';
			css += '}';
		} else if ('separator_color' === type) {
			css += selector + ':after{ background-color: ' + setting['separator-color'] + '; }';
		}

		return css;
	}

	/**
	 * Logo max height.
	 */
	api('prisma_core_logo_max_height', function (value) {
		value.bind(function (newval) {
			var $logo = $('.prisma-core-logo');

			if (!$logo.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_logo_max_height');
			var style_css = '';

			style_css += prisma_core_range_field_css('.prisma-core-logo img', 'max-height', newval, true, 'px');
			style_css += prisma_core_range_field_css('.prisma-core-logo img.pr-svg-logo', 'height', newval, true, 'px');

			$style_tag.html(style_css);
		});
	});

	/**
	 * Logo text font size.
	 */
	api('prisma_core_logo_text_font_size', function (value) {
		value.bind(function (newval) {
			var $logo = $('#prisma-core-header .prisma-core-logo .site-title');

			if (!$logo.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_logo_text_font_size');
			var style_css = '';

			style_css += prisma_core_range_field_css('#prisma-core-header .prisma-core-logo .site-title', 'font-size', newval, true, newval.unit);

			$style_tag.html(style_css);
		});
	});

	/**
	 * Logo margin.
	 */
	api('prisma_core_logo_margin', function (value) {
		value.bind(function (newval) {
			var $logo = $('.prisma-core-logo');

			if (!$logo.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_logo_margin');

			var style_css = prisma_core_spacing_field_css('.prisma-core-logo .logo-inner', 'margin', newval, true);
			$style_tag.html(style_css);
		});
	});

	/**
	 * Tagline.
	 */
	api('blogdescription', function (value) {
		value.bind(function (newval) {
			if ($('.prisma-core-logo').find('.site-description').length) {
				$('.prisma-core-logo').find('.site-description').html(newval);
			}
		});
	});

	/**
	 * Site Title.
	 */
	api('blogname', function (value) {
		value.bind(function (newval) {
			if ($('.prisma-core-logo').find('.site-title').length) {
				$('.prisma-core-logo').find('.site-title').find('a').html(newval);
			}
		});
	});

	/**
	 * Site Layout.
	 */
	api('prisma_core_site_layout', function (value) {
		value.bind(function (newval) {
			$body.removeClass(function (index, className) {
				return (className.match(/(^|\s)prisma-core-layout__\S+/g) || []).join(' ');
			});

			$body.addClass('prisma-core-layout__' + newval);
		});
	});

	/**
	 * Sticky Sidebar.
	 */
	api('prisma_core_sidebar_sticky', function (value) {
		value.bind(function (newval) {
			$body.removeClass(function (index, className) {
				return (className.match(/(^|\s)pr-sticky-\S+/g) || []).join(' ');
			});

			if (newval) {
				$body.addClass('pr-sticky-' + newval);
			}
		});
	});

	/**
	 * Sidebar width.
	 */
	api('prisma_core_sidebar_width', function (value) {
		value.bind(function (newval) {
			var $sidebar = $('#secondary');

			if (!$sidebar.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_sidebar_width');
			var style_css = '#secondary { width: ' + newval.value + '%; }';
			style_css += 'body:not(.prisma-core-no-sidebar) #primary { ' + 'max-width: ' + (100 - parseInt(newval.value)) + '%;' + '};';

			$style_tag.html(style_css);
		});
	});

	/**
	 * Sidebar style.
	 */
	api('prisma_core_sidebar_style', function (value) {
		value.bind(function (newval) {
			$body.removeClass(function (index, className) {
				return (className.match(/(^|\s)prisma-core-sidebar-style-\S+/g) || []).join(' ');
			});

			$body.addClass('prisma-core-sidebar-style-' + newval);
		});
	});

	/**
	 * Responsive sidebar position.
	 */
	api('prisma_core_sidebar_responsive_position', function (value) {
		value.bind(function (newval) {
			$body.removeClass(function (index, className) {
				return (className.match(/(^|\s)pr-sidebar-r__\S+/g) || []).join(' ');
			});

			if (newval) {
				$body.addClass('pr-sidebar-r__' + newval);
			}
		});
	});

	/**
	 * Featured Image Position (Horizontal Blog layout)
	 */
	api('prisma_core_blog_image_position', function (value) {
		value.bind(function (newval) {
			$('.pr-blog-entry-wrapper').removeClass(function (index, className) {
				return (className.match(/(^|\s)pr-thumb-\S+/g) || []).join(' ');
			});

			$('.pr-blog-entry-wrapper').addClass('pr-thumb-' + newval);
		});
	});

	/**
	 * Single page - title in header alignment.
	 */
	api('prisma_core_single_title_alignment', function (value) {
		value.bind(function (newval) {
			$body.removeClass(function (index, className) {
				return (className.match(/(^|\s)pr-page-title-align-\S+/g) || []).join(' ');
			});

			$body.addClass('pr-page-title-align-' + newval);
		});
	});

	/**
	 * Single Page title spacing.
	 */
	api('prisma_core_single_title_spacing', function (value) {
		value.bind(function (newval) {
			var $page_header = $('.page-header');

			if (!$page_header.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_single_title_spacing');

			var style_css = prisma_core_spacing_field_css('.pr-single-title-in-page-header #page .page-header .pr-page-header-wrapper', 'padding', newval, true);

			$style_tag.html(style_css);
		});
	});

	/**
	 * Single post narrow container width.
	 */
	api('prisma_core_single_narrow_container_width', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_single_narrow_container_width');
			var style_css = '';

			style_css +=
				'.single-post.narrow-content .entry-content > :not([class*="align"]):not([class*="gallery"]):not(.wp-block-image):not(.quote-inner):not(.quote-post-bg), ' +
				'.single-post.narrow-content .mce-content-body:not([class*="page-template-full-width"]) > :not([class*="align"]):not([data-wpview-type*="gallery"]):not(blockquote):not(.mceTemp), ' +
				'.single-post.narrow-content .entry-footer, ' +
				'.single-post.narrow-content .post-nav, ' +
				'.single-post.narrow-content .entry-content > .alignwide, ' +
				'.single-post.narrow-content p.has-background:not(.alignfull):not(.alignwide)' +
				'.single-post.narrow-content #prisma-core-comments-toggle, ' +
				'.single-post.narrow-content #comments, ' +
				'.single-post.narrow-content .entry-content .aligncenter, ' +
				'.single-post.narrow-content .pr-narrow-element, ' +
				'.single-post.narrow-content.pr-single-title-in-content .entry-header, ' +
				'.single-post.narrow-content.pr-single-title-in-content .entry-meta, ' +
				'.single-post.narrow-content.pr-single-title-in-content .post-category, ' +
				'.single-post.narrow-content.prisma-core-no-sidebar .pr-page-header-wrapper, ' +
				'.single-post.narrow-content.prisma-core-no-sidebar .pr-breadcrumbs > .pr-container > nav {' +
				'max-width: ' +
				parseInt(newval.value) +
				'px; margin-left: auto; margin-right: auto; ' +
				'}';

			style_css += '.single-post.narrow-content .author-box, ' + '.single-post.narrow-content .entry-content > .alignwide { ' + 'max-width: ' + (parseInt(newval.value) + 70) + 'px;' + '}';

			$style_tag.html(style_css);
		});
	});

	/**
	 * Single post content font size.
	 */
	api('prisma_core_single_content_font_size', function (value) {
		value.bind(function (newval) {
			var $content = $('.single-post');

			if (!$content.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_single_content_font_size');
			var style_css = '';

			style_css += prisma_core_range_field_css('.single-post .entry-content', 'font-size', newval, true, newval.unit);

			$style_tag.html(style_css);
		});
	});

	/**
	 * Header container width.
	 */
	api('prisma_core_header_container_width', function (value) {
		value.bind(function (newval) {
			var $header = $('#prisma-core-header');

			if (!$header.length) {
				return;
			}

			if ('full-width' === newval) {
				$header.addClass('pr-container__wide');
			} else {
				$header.removeClass('pr-container__wide');
			}
		});
	});

	/**
	 * Main navigation disply breakpoint.
	 */
	api('prisma_core_main_nav_mobile_breakpoint', function (value) {
		value.bind(function (newval) {
			var $nav = $('#prisma-core-header-inner .prisma-core-nav');

			if (!$nav.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_main_nav_mobile_breakpoint');
			var style_css = '';

			style_css += '@media screen and (min-width: ' + parseInt(newval) + 'px) {#prisma-core-header-inner .prisma-core-nav {display:flex} .pr-mobile-nav {display:none;} }';
			style_css += '@media screen and (max-width: ' + parseInt(newval) + 'px) {#prisma-core-header-inner .prisma-core-nav {display:none} .pr-mobile-nav {display:inline-flex;} }';

			$style_tag.html(style_css);
		});
	});

	/**
	 * Mobile Menu Button Label.
	 */
	api('prisma_core_main_nav_mobile_label', function (value) {
		value.bind(function (newval) {
			if ($('.pr-hamburger-prisma-core-primary-nav').find('.hamburger-label').length) {
				$('.pr-hamburger-prisma-core-primary-nav').find('.hamburger-label').html(newval);
			}
		});
	});

	/**
	 * Main Nav Font color.
	 */
	api('prisma_core_main_nav_font_color', function (value) {
		value.bind(function (newval) {
			var $navigation = $('#prisma-core-header-inner .prisma-core-nav');

			if (!$navigation.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_main_nav_font_color');
			var style_css = '';

			// Link color.
			newval['link-color'] = newval['link-color'] ? newval['link-color'] : 'inherit';
			style_css += '#prisma-core-header-inner .prisma-core-nav > ul > li > a { color: ' + newval['link-color'] + '; }';

			// Link hover color.
			newval['link-hover-color'] = newval['link-hover-color'] ? newval['link-hover-color'] : api.value('prisma_core_accent_color')();
			style_css +=
				'#prisma-core-header-inner .prisma-core-nav > ul > li > a:hover, ' +
				'#prisma-core-header-inner .prisma-core-nav > ul > li.menu-item-has-children:hover > a, ' +
				'#prisma-core-header-inner .prisma-core-nav > ul > li.current-menu-item > a, ' +
				'#prisma-core-header-inner .prisma-core-nav > ul > li.current-menu-ancestor > a ' +
				'#prisma-core-header-inner .prisma-core-nav > ul > li.page_item_has_children:hover > a, ' +
				'#prisma-core-header-inner .prisma-core-nav > ul > li.current_page_item > a, ' +
				'#prisma-core-header-inner .prisma-core-nav > ul > li.current_page_ancestor > a ' +
				'{ color: ' +
				newval['link-hover-color'] +
				'; }';

			$style_tag.html(style_css);
		});
	});

	/**
	 * Main Nav Background.
	 */
	api('prisma_core_main_nav_background', function (value) {
		value.bind(function (newval) {
			var $navigation = $('.prisma-core-header-layout-3 .pr-nav-container');

			if (!$navigation.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_main_nav_background');
			var style_css = prisma_core_design_options_css('.prisma-core-header-layout-3 .pr-nav-container', newval, 'background');

			$style_tag.html(style_css);
		});
	});

	/**
	 * Main Nav Border.
	 */
	api('prisma_core_main_nav_border', function (value) {
		value.bind(function (newval) {
			var $navigation = $('.prisma-core-header-layout-3 .pr-nav-container');

			if (!$navigation.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_main_nav_border');
			var style_css = prisma_core_design_options_css('.prisma-core-header-layout-3 .pr-nav-container', newval, 'border');

			$style_tag.html(style_css);
		});
	});

	/**
	 * Main Nav font size.
	 */
	api('prisma_core_main_nav_font_size', function (value) {
		value.bind(function (newval) {
			var $nav = $('#prisma-core-header-inner');

			if (!$nav.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_main_nav_font_size');
			var style_css = '';

			style_css += prisma_core_range_field_css('.prisma-core-nav.pr-header-element, .prisma-core-header-layout-1 .pr-header-widgets, .prisma-core-header-layout-2 .pr-header-widgets', 'font-size', newval, false, newval.unit);

			$style_tag.html(style_css);
		});
	});

	/**
	 * Top Bar container width.
	 */
	api('prisma_core_top_bar_container_width', function (value) {
		value.bind(function (newval) {
			var $topbar = $('#prisma-core-topbar');

			if (!$topbar.length) {
				return;
			}

			if ('full-width' === newval) {
				$topbar.addClass('pr-container__wide');
			} else {
				$topbar.removeClass('pr-container__wide');
			}
		});
	});

	/**
	 * Top Bar visibility.
	 */
	api('prisma_core_top_bar_visibility', function (value) {
		value.bind(function (newval) {
			var $topbar = $('#prisma-core-topbar');

			prisma_core_print_visibility_classes($topbar, newval);
		});
	});

	/**
	 * Top Bar widgets separator.
	 */
	api('prisma_core_top_bar_widgets_separator', function (value) {
		value.bind(function (newval) {
			$body.removeClass(function (index, className) {
				return (className.match(/(^|\s)prisma-core-topbar__separators-\S+/g) || []).join(' ');
			});

			$body.addClass('prisma-core-topbar__separators-' + newval);
		});
	});

	/**
	 * Top Bar background.
	 */
	api('prisma_core_top_bar_background', function (value) {
		value.bind(function (newval) {
			var $topbar = $('#prisma-core-topbar');

			if (!$topbar.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_top_bar_background');
			var style_css = prisma_core_design_options_css('#prisma-core-topbar', newval, 'background');

			$style_tag.html(style_css);
		});
	});

	/**
	 * Top Bar color.
	 */
	api('prisma_core_top_bar_text_color', function (value) {
		value.bind(function (newval) {
			var $topbar = $('#prisma-core-topbar');

			if (!$topbar.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_top_bar_text_color');
			var style_css = '';

			newval['text-color'] = newval['text-color'] ? newval['text-color'] : 'inherit';
			newval['link-color'] = newval['link-color'] ? newval['link-color'] : 'inherit';
			newval['link-hover-color'] = newval['link-hover-color'] ? newval['link-hover-color'] : 'inherit';

			// Text color.
			style_css += '#prisma-core-topbar { color: ' + newval['text-color'] + '; }';

			// Link color.
			style_css += '.pr-topbar-widget__text a, ' + '.pr-topbar-widget .prisma-core-nav > ul > li > a, ' + '.pr-topbar-widget__socials .prisma-core-social-nav > ul > li > a, ' + '#prisma-core-topbar .pr-topbar-widget__text .pr-icon { color: ' + newval['link-color'] + '; }';

			// Link hover color.
			style_css +=
				'#prisma-core-topbar .prisma-core-nav > ul > li > a:hover, ' +
				'#prisma-core-topbar .prisma-core-nav > ul > li.menu-item-has-children:hover > a,  ' +
				'#prisma-core-topbar .prisma-core-nav > ul > li.current-menu-item > a, ' +
				'#prisma-core-topbar .prisma-core-nav > ul > li.current-menu-ancestor > a, ' +
				'#prisma-core-topbar .pr-topbar-widget__text a:hover, ' +
				'#prisma-core-topbar .prisma-core-social-nav > ul > li > a .pr-icon.bottom-icon { color: ' +
				newval['link-hover-color'] +
				'; }';

			$style_tag.html(style_css);
		});
	});

	/**
	 * Top Bar border.
	 */
	api('prisma_core_top_bar_border', function (value) {
		value.bind(function (newval) {
			var $topbar = $('#prisma-core-topbar');

			if (!$topbar.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_top_bar_border');
			var style_css = prisma_core_design_options_css('#prisma-core-topbar', newval, 'border');

			style_css += prisma_core_design_options_css('#prisma-core-topbar .pr-topbar-widget', newval, 'separator_color');

			$style_tag.html(style_css);
		});
	});

	/**
	 * Header menu item hover animation.
	 */
	api('prisma_core_main_nav_hover_animation', function (value) {
		value.bind(function (newval) {
			$body.removeClass(function (index, className) {
				return (className.match(/(^|\s)prisma-core-menu-animation-\S+/g) || []).join(' ');
			});

			$body.addClass('prisma-core-menu-animation-' + newval);
		});
	});

	/**
	 * Header widgets separator.
	 */
	api('prisma_core_header_widgets_separator', function (value) {
		value.bind(function (newval) {
			$body.removeClass(function (index, className) {
				return (className.match(/(^|\s)prisma-core-header__separators-\S+/g) || []).join(' ');
			});

			$body.addClass('prisma-core-header__separators-' + newval);
		});
	});

	/**
	 * Header background.
	 */
	api('prisma_core_header_background', function (value) {
		value.bind(function (newval) {
			var $header = $('#prisma-core-header-inner');

			if (!$header.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_header_background');
			var style_css = prisma_core_design_options_css('#prisma-core-header-inner', newval, 'background');

			if ('color' === newval['background-type'] && newval['background-color']) {
				style_css += '.pr-header-widget__cart .pr-cart .pr-cart-count { border: 2px solid ' + newval['background-color'] + '; }';
			} else {
				style_css += '.pr-header-widget__cart .pr-cart .pr-cart-count { border: none; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Header font color.
	 */
	api('prisma_core_header_text_color', function (value) {
		value.bind(function (newval) {
			var $header = $('#prisma-core-header');

			if (!$header.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_header_text_color');
			var style_css = '';

			// Text color.
			style_css += '.prisma-core-logo .site-description { color: ' + newval['text-color'] + '; }';

			// Link color.
			if (newval['link-color']) {
				style_css += '#prisma-core-header, ' + '.pr-header-widgets a:not(.pr-btn), ' + '.prisma-core-logo a,' + '.pr-hamburger { color: ' + newval['link-color'] + '; }';
				style_css += '.hamburger-inner,' + '.hamburger-inner::before,' + '.hamburger-inner::after { background-color: ' + newval['link-color'] + '; }';
			}

			// Link hover color.
			if (newval['link-hover-color']) {
				style_css +=
					'.pr-header-widgets a:not(.pr-btn):hover, ' +
					'#prisma-core-header-inner .pr-header-widgets .prisma-core-active,' +
					'.prisma-core-logo .site-title a:hover, ' +
					'.pr-hamburger:hover .hamburger-label, ' +
					'.is-mobile-menu-active .pr-hamburger .hamburger-label,' +
					'#prisma-core-header-inner .prisma-core-nav > ul > li > a:hover,' +
					'#prisma-core-header-inner .prisma-core-nav > ul > li.menu-item-has-children:hover > a,' +
					'#prisma-core-header-inner .prisma-core-nav > ul > li.current-menu-item > a,' +
					'#prisma-core-header-inner .prisma-core-nav > ul > li.current-menu-ancestor > a,' +
					'#prisma-core-header-inner .prisma-core-nav > ul > li.page_item_has_children:hover > a,' +
					'#prisma-core-header-inner .prisma-core-nav > ul > li.current_page_item > a,' +
					'#prisma-core-header-inner .prisma-core-nav > ul > li.current_page_ancestor > a { color: ' +
					newval['link-hover-color'] +
					'; }';

				style_css +=
					'.pr-hamburger:hover .hamburger-inner,' +
					'.pr-hamburger:hover .hamburger-inner::before,' +
					'.pr-hamburger:hover .hamburger-inner::after,' +
					'.is-mobile-menu-active .pr-hamburger .hamburger-inner,' +
					'.is-mobile-menu-active .pr-hamburger .hamburger-inner::before,' +
					'.is-mobile-menu-active .pr-hamburger .hamburger-inner::after { background-color: ' +
					newval['link-hover-color'] +
					'; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Header border.
	 */
	api('prisma_core_header_border', function (value) {
		value.bind(function (newval) {
			var $header = $('#prisma-core-header-inner');

			if (!$header.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_header_border');
			var style_css = prisma_core_design_options_css('#prisma-core-header-inner', newval, 'border');

			// Separator color.
			newval['separator-color'] = newval['separator-color'] ? newval['separator-color'] : 'inherit';
			style_css += '.pr-header-widget:after { background-color: ' + newval['separator-color'] + '; }';

			$style_tag.html(style_css);
		});
	});

	/**
	 * Hero container width.
	 */
	api('prisma_core_hero_hover_slider_container', function (value) {
		value.bind(function (newval) {
			var $hero_container = $('#hero .pr-hero-container');

			if (!$hero_container.length) {
				return;
			}

			if ('full-width' === newval) {
				$hero_container.addClass('pr-container__wide');
			} else {
				$hero_container.removeClass('pr-container__wide');
			}
		});
	});

	/**
	 * Hero overlay style.
	 */
	api('prisma_core_hero_hover_slider_overlay', function (value) {
		value.bind(function (newval) {
			var $hero = $('#hero .pr-hover-slider');

			if (!$hero.length) {
				return;
			}

			$hero
				.removeClass(function (index, className) {
					return (className.match(/(^|\s)slider-overlay-\S+/g) || []).join(' ');
				})
				.addClass('slider-overlay-' + newval);
		});
	});

	/**
	 * Hero height.
	 */
	api('prisma_core_hero_hover_slider_height', function (value) {
		value.bind(function (newval) {
			var $hero = $('#hero');

			if (!$hero.length) {
				return;
			}

			$hero.find('.hover-slide-item').css('height', newval.value + 'px');
		});
	});

	/**
	 * Hero visibility.
	 */
	api('prisma_core_hero_visibility', function (value) {
		value.bind(function (newval) {
			prisma_core_print_visibility_classes($('#hero'), newval);
		});
	});

	/**
	 * Custom input style.
	 */
	api('prisma_core_custom_input_style', function (value) {
		value.bind(function (newval) {
			if (newval) {
				$body.addClass('pr-input-supported');
			} else {
				$body.removeClass('pr-input-supported');
			}
		});
	});

	/**
	 * Pre Footer Call to Action Enable.
	 */
	api('prisma_core_enable_pre_footer_cta', function (value) {
		value.bind(function (newval) {
			if (newval) {
				$body.addClass('pr-pre-footer-cta-style-' + api.value('prisma_core_pre_footer_cta_style')());
			} else {
				$body.removeClass(function (index, className) {
					return (className.match(/(^|\s)pr-pre-footer-cta-style-\S+/g) || []).join(' ');
				});
			}
		});
	});

	/**
	 * Pre Footer Call to Action visibility.
	 */
	api('prisma_core_pre_footer_cta_visibility', function (value) {
		value.bind(function (newval) {
			var $cta = $('.pr-pre-footer-cta');

			if (!$cta.length) {
				return;
			}

			prisma_core_print_visibility_classes($cta, newval);
		});
	});

	/**
	 * Pre Footer Call to Action Text.
	 */
	api('prisma_core_pre_footer_cta_text', function (value) {
		value.bind(function (newval) {
			var $cta = $('#pr-pre-footer .pr-pre-footer-cta');

			if (!$cta.length) {
				return;
			}

			$cta.find('p.h3').html(newval);
		});
	});

	/**
	 * Pre Footer Call to Action Style.
	 */
	api('prisma_core_pre_footer_cta_style', function (value) {
		value.bind(function (newval) {
			$body
				.removeClass(function (index, className) {
					return (className.match(/(^|\s)pr-pre-footer-cta-style-\S+/g) || []).join(' ');
				})
				.addClass('pr-pre-footer-cta-style-' + api.value('prisma_core_pre_footer_cta_style')());
		});
	});

	/**
	 * Pre Footer Call to Action Button Text.
	 */
	api('prisma_core_pre_footer_cta_btn_text', function (value) {
		value.bind(function (newval) {
			var $cta = $('#pr-pre-footer .pr-pre-footer-cta');

			if (!$cta.length) {
				return;
			}

			if (newval) {
				$cta.find('a').css('display', 'inline-flex').html(newval);
			} else {
				$cta.find('a').css('display', 'none').html('');
			}
		});
	});

	/**
	 * Pre Footer Call to Action Background.
	 */
	api('prisma_core_pre_footer_cta_background', function (value) {
		value.bind(function (newval) {
			var $cta = $('#pr-pre-footer .pr-pre-footer-cta');

			if (!$cta.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_pre_footer_cta_background');
			var style_css = '';

			if ('color' === newval['background-type']) {
				style_css += prisma_core_design_options_css('.pr-pre-footer-cta-style-1 #pr-pre-footer .pr-flex-row::before, .pr-pre-footer-cta-style-2 #pr-pre-footer::before', newval, 'background');
				style_css += '.pr-pre-footer-cta-style-1 #pr-pre-footer .pr-flex-row::after,' + '.pr-pre-footer-cta-style-2 #pr-pre-footer::after' + '{ background-image: none; }';
			} else {
				style_css += prisma_core_design_options_css('.pr-pre-footer-cta-style-1 #pr-pre-footer .pr-flex-row::after', newval, 'background');
				style_css += prisma_core_design_options_css('.pr-pre-footer-cta-style-2 #pr-pre-footer::after', newval, 'background');
			}

			if ('image' === newval['background-type'] && newval['background-color-overlay'] && newval['background-image']) {
				style_css += '.pr-pre-footer-cta-style-1 #pr-pre-footer .pr-flex-row::before,' + '.pr-pre-footer-cta-style-2 #pr-pre-footer::before' + '{ background-color: ' + newval['background-color-overlay'] + '; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Pre Footer Call to Action Text Color.
	 */
	api('prisma_core_pre_footer_cta_text_color', function (value) {
		value.bind(function (newval) {
			var $cta = $('#pr-pre-footer .pr-pre-footer-cta');

			if (!$cta.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_pre_footer_cta_text_color');
			var style_css = '';

			style_css += prisma_core_design_options_css('#pr-pre-footer .h2', newval, 'color');
			style_css += prisma_core_design_options_css('#pr-pre-footer .h3', newval, 'color');
			style_css += prisma_core_design_options_css('#pr-pre-footer .h4', newval, 'color');

			$style_tag.html(style_css);
		});
	});

	/**
	 * Pre Footer Call to Action Border.
	 */
	api('prisma_core_pre_footer_cta_border', function (value) {
		value.bind(function (newval) {
			var $cta = $('#pr-pre-footer .pr-pre-footer-cta');

			if (!$cta.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_pre_footer_cta_border');
			var style_css = prisma_core_design_options_css('.pr-pre-footer-cta-style-1 #pr-pre-footer .pr-flex-row::before, .pr-pre-footer-cta-style-2 #pr-pre-footer::before', newval, 'border');

			$style_tag.html(style_css);
		});
	});

	/**
	 * Pre Footer CTA font size.
	 */
	api('prisma_core_pre_footer_cta_font_size', function (value) {
		value.bind(function (newval) {
			var $cta = $('#pr-pre-footer .pr-pre-footer-cta');

			if (!$cta.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_pre_footer_cta_font_size');
			var style_css = prisma_core_range_field_css('#pr-pre-footer .h3', 'font-size', newval, true, newval.unit);

			$style_tag.html(style_css);
		});
	});

	/**
	 * WooCommerce sale badge text.
	 */
	api('prisma_core_product_sale_badge_text', function (value) {
		value.bind(function (newval) {
			var $badge = $('.woocommerce ul.products li.product .onsale, .woocommerce span.onsale').not('.sold-out');

			if (!$badge.length) {
				return;
			}

			$badge.html(newval);
		});
	});

	/**
	 * Accent color.
	 */
	api('prisma_core_accent_color', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_accent_color');
			var style_css;

			// Background colors.
			style_css =
				'.pr-header-widgets .pr-cart .pr-cart-count,' +
				'#pr-scroll-top:hover::before, ' +
				'.prisma-core-menu-animation-underline #prisma-core-header-inner .prisma-core-nav > ul > li > a > span::before, ' +
				'.pr-btn, ' +
				'#infinite-handle span, ' +
				'input[type=submit], ' +
				'.comment-form input[type=checkbox]:checked, ' +
				'#comments .bypostauthor-badge, ' +
				'input[type=radio]:checked::before, ' +
				'.single .post-tags a:hover, ' +
				'.single .post-category .cat-links a:hover, ' +
				'#main .mejs-controls .mejs-time-rail .mejs-time-current, ' +
				'.pr-hamburger:hover .hamburger-inner, ' +
				'.pr-hamburger:hover .hamburger-inner::before, ' +
				'.pr-hamburger:hover .hamburger-inner::after, ' +
				'.tagcloud a:hover, ' +
				'.pr-btn.prisma-core-read-more::after, ' +
				'.post_format-post-format-quote .pr-blog-entry-content .quote-post-bg::after, ' +
				'.pr-hover-slider .post-category a,' +
				'.pr-single-title-in-page-header.single .page-header .post-category a,' +
				'.pr-pre-footer-cta-style-1 #pr-pre-footer .pr-flex-row::after,' +
				'.pr-pre-footer-cta-style-2 #pr-pre-footer::after,' +
				'.entry-media > a:hover .entry-media-icon::before, ' +
				'.pr-woo-steps .pr-step.is-active > span:first-child, ' +
				'.pr-pre-footer-cta-style-1 #pr-pre-footer .pr-flex-row::after, ' +
				'.pr-pre-footer-cta-style-2 #pr-pre-footer::after, ' +
				'.site-main .woocommerce #respond input#submit, ' +
				'.site-main .woocommerce a.button, ' +
				'.site-main .woocommerce button.button, ' +
				'.site-main .woocommerce input.button, ' +
				'.select2-container--default .select2-results__option--highlighted[data-selected], ' +
				'.pr-input-supported input[type=radio]:checked:before, ' +
				'.pr-input-supported input[type=checkbox]:checked, ' +
				'.woocommerce ul.products li.product .onsale, ' +
				'.woocommerce span.onsale, ' +
				'.woocommerce-store-notice, ' +
				'p.demo_store, ' +
				'.woocommerce ul.products li.product .button, ' +
				'.prisma-core-sidebar-style-2 #secondary .widget-title:before, ' +
				'.widget.woocommerce .wc-layered-nav-term:hover .count, ' +
				'.widget.woocommerce .product-categories li a:hover ~ .count, ' +
				'.widget.woocommerce .woocommerce-widget-layered-nav-list .woocommerce-widget-layered-nav-list__item.chosen a:before, ' +
				'.woocommerce .widget_rating_filter ul li.chosen a::before, ' +
				'.widget.woocommerce .wc-layered-nav-term.chosen .count, ' +
				'.widget.woocommerce .product-categories li.current-cat > .count, ' +
				'.woocommerce .widget_price_filter .ui-slider .ui-slider-handle, ' +
				'.woocommerce .widget_price_filter .ui-slider .ui-slider-handle:after, ' +
				'.woocommerce .widget_layered_nav_filters ul li a:hover, ' +
				'.woocommerce div.product form.cart .button, ' +
				'.widget.woocommerce .wc-layered-nav-rating a:hover em, ' +
				'.widget.woocommerce .wc-layered-nav-rating.chosen a em, ' +
				'.widget .cat-item a:hover + span, ' +
				'.widget_archive li a:hover + span, ' +
				'.widget .cat-item.current-cat a + span, ' +
				'#prisma-core-footer .widget .cat-item a:hover + span, ' +
				'#prisma-core-footer .widget_archive li a:hover + span, ' +
				'#prisma-core-footer .widget .cat-item.current-cat a + span, ' +
				'.pr-btn.btn-outline:hover, ' +
				'.pr-hamburger:hover .hamburger-inner, ' +
				'.pr-hamburger:hover .hamburger-inner::before, ' +
				'.pr-hamburger:hover .hamburger-inner::after, ' +
				'.is-mobile-menu-active .pr-hamburger .hamburger-inner, ' +
				'.is-mobile-menu-active .pr-hamburger .hamburger-inner::before, ' +
				'.is-mobile-menu-active .pr-hamburger .hamburger-inner::after, ' +
				'.woocommerce div.product div.images .woocommerce-product-gallery__trigger:hover:before, ' +
				'.woocommerce #review_form #respond .form-submit input { ' +
				'background-color: ' +
				newval +
				';' +
				'}';

			// Hover accent background color.
			style_css +=
				'.pr-btn:hover, ' +
				'input[type=submit]:hover, ' +
				'#infinite-handle span:hover, ' +
				'.site-main .woocommerce #respond input#submit, ' +
				'.site-main .woocommerce a.button:hover, ' +
				'.site-main .woocommerce button.button:hover, ' +
				'.site-main .woocommerce input.button:hover, ' +
				'.pr-hover-slider .post-category a:hover, ' +
				'.pr-single-title-in-page-header.single .page-header .post-category a:hover, ' +
				'.woocommerce ul.products li.product .button:hover, ' +
				'.woocommerce .widget_price_filter .ui-slider .ui-slider-range, ' +
				'.wc-layered-nav-rating a:hover .star-rating span:before, ' +
				'.woocommerce #review_form #respond .form-submit input:hover { ' +
				'background-color: ' +
				prisma_core_luminance(newval, 0.15) +
				';' +
				'}';

			// Hover accent color.
			style_css += '.wc-layered-nav-rating a:hover .star-rating span:before { ' + 'color: ' + prisma_core_luminance(newval, 0.15) + ';' + '}';

			style_css += 'code, ' + 'kbd, ' + 'var, ' + 'samp, ' + 'mark, ' + 'span.highlight, ' + 'tt { ' + 'background-color: ' + prisma_core_hex2rgba(newval, 0.12) + ';' + '}';

			style_css += 'code.block { ' + 'background-color: ' + prisma_core_hex2rgba(newval, 0.075) + ';' + '}';

			// Colors.
			style_css +=
				'.content-area a:not(.pr-btn):not(.wp-block-button__link),' +
				'.pr-sidebar-container a:hover:not(.pr-btn), ' +
				'.pr-header-widgets .pr-header-widget:hover, ' +
				'.pr-header-widgets .pr-header-widget.prisma-core-active .pr-icon.pr-search, ' +
				'#prisma-core-header-inner .pr-header-widgets .prisma-core-active, ' +
				'.prisma-core-logo .site-title a:hover,' +
				'#prisma-core-header-inner .prisma-core-nav > ul > li > a:hover, ' +
				'#prisma-core-header-inner .prisma-core-nav > ul > li.menu-item-has-children:hover > a, ' +
				'#prisma-core-header-inner .prisma-core-nav > ul > li.current-menu-item > a, ' +
				'#prisma-core-header-inner .prisma-core-nav > ul > li.current-menu-ancestor > a, ' +
				'#prisma-core-header-inner .prisma-core-nav > ul > li.page_item_has_children:hover > a, ' +
				'#prisma-core-header-inner .prisma-core-nav > ul > li.current_page_item > a, ' +
				'#prisma-core-header-inner .prisma-core-nav > ul > li.current_page_ancestor > a, ' +
				'#prisma-core-topbar .prisma-core-nav > ul > li > a:hover, ' +
				'#prisma-core-topbar .prisma-core-nav > ul > li.menu-item-has-children:hover > a,  ' +
				'#prisma-core-topbar .prisma-core-nav > ul > li.current-menu-item > a, ' +
				'#prisma-core-topbar .prisma-core-nav > ul > li.current-menu-ancestor > a, ' +
				'.pr-topbar-widget__text a:hover, ' +
				'.pr-topbar-widget__text a, ' +
				'.pr-header-widgets a:not(.pr-btn):hover, ' +
				'.prisma-core-social-nav > ul > li > a .pr-icon.bottom-icon, ' +
				'.prisma-core-pagination .navigation .nav-links .page-numbers:hover, ' +
				'.widget .cat-item.current-cat > a, ' +
				'.widget ul li.current_page_item > a, ' +
				'#main .search-form .search-submit:hover, ' +
				'#cancel-comment-reply-link:hover, ' +
				'.comment-form .required, ' +
				'.navigation .nav-links .page-numbers:hover, ' +
				'#main .entry-meta a:hover, ' +
				'#main .author-box-title a, ' +
				'.single .post-category a, ' +
				'.page-links span:hover, ' +
				'.site-content .page-links span:hover, ' +
				'.wc-cart-widget-header .pr-cart-subtotal span, ' +
				'.pr-header-widget__cart:hover > a, ' +
				'.woocommerce #yith-wcwl-form table.shop_table .product-subtotal .amount, ' +
				'.woocommerce .woocommerce-cart-form table.shop_table .product-subtotal .amount, ' +
				'.pr-woo-steps .pr-step.is-active, ' +
				'.cart_totals .order-total td, ' +
				'.navigation .nav-links .page-numbers.current, ' +
				'.page-links > span, ' +
				'.site-content .page-links > span, ' +
				'.woocommerce ul.products li.product .price, ' +
				'.woocommerce .woocommerce-checkout-review-order .order-total .woocommerce-Price-amount.amount, ' +
				'.woocommerce-info::before, ' +
				'#main .woocommerce-MyAccount-navigation li.is-active, ' +
				'.woocommerce .star-rating span::before, ' +
				'.widget.woocommerce .wc-layered-nav-term:hover a, ' +
				'.widget.woocommerce .wc-layered-nav-term a:hover,' +
				'.widget.woocommerce .product-categories li a:hover, ' +
				'.widget.woocommerce .product-categories li.current-cat > a, ' +
				'.woocommerce ins .amount, ' +
				'.woocommerce .widget_rating_filter ul li.chosen a::before, ' +
				'.widget.woocommerce .woocommerce-widget-layered-nav-list .woocommerce-widget-layered-nav-list__item.chosen a, ' +
				'.woocommerce .widget_shopping_cart .total .amount, ' +
				'.woocommerce .widget_shopping_cart .total .tax_label, ' +
				'.woocommerce.widget_shopping_cart .total .amount, ' +
				'.woocommerce.widget_shopping_cart .total .tax_label, ' +
				'.pr-btn.btn-outline, ' +
				'.woocommerce .widget_shopping_cart .cart_list li a.remove:hover:before, ' +
				'.woocommerce div.product .woocommerce-tabs ul.tabs li.active > a,' +
				'.woocommerce.widget_shopping_cart .cart_list li a.remove:hover:before, ' +
				'.woocommerce div.product p.price, ' +
				'.woocommerce div.product span.price, ' +
				'.woocommerce div.product #reviews .comment-form-rating .stars a, ' +
				'.woocommerce div.product .woocommerce-pagination ul li span.current, ' +
				'.woocommerce div.product .woocommerce-pagination ul li a:hover, ' +
				'code, ' +
				'kbd, ' +
				'var, ' +
				'samp, ' +
				'tt, ' +
				'.is-mobile-menu-active .pr-hamburger .hamburger-label, ' +
				'.pr-hamburger:hover .hamburger-label, ' +
				'.single #main .post-nav a:hover, ' +
				'#prisma-core-topbar .pr-topbar-widget__text .pr-icon, ' +
				'.prisma-companion-custom-list-widget .pr-widget-icon {' +
				'color: ' +
				newval +
				';' +
				'}';

			// Selection.
			style_css += '#main ::-moz-selection { background-color: ' + newval + '; color: #FFF; }';
			style_css += '#main ::selection { background-color: ' + newval + '; color: #FFF; }';

			// Border color.
			style_css +=
				'#comments .comment-actions .reply a:hover, ' +
				'.comment-form input[type=checkbox]:checked, .comment-form input[type=checkbox]:focus, ' +
				'.comment-form input[type=radio]:checked, .comment-form input[type=radio]:focus, ' +
				'.single .post-category a, ' +
				'#secondary .widget-title, ' +
				'.pr-hover-slider .post-category a, ' +
				'.pr-single-title-in-page-header.single .page-header .post-category a, ' +
				'.entry-content blockquote, ' +
				'.wp-block-quote.is-style-large, ' +
				'.wp-block-quote.is-large, ' +
				'.wp-block-quote.has-text-align-right, ' +
				'[type="radio"]:checked + label:before, ' +
				'.pr-input-supported input[type=radio]:checked, ' +
				'.pr-input-supported input[type=checkbox]:checked, ' +
				'.widget.woocommerce .woocommerce-widget-layered-nav-list .woocommerce-widget-layered-nav-list__item.chosen a:before, ' +
				'.widget.woocommerce .widget_rating_filter.chosen a:after, ' +
				'.pr-btn.btn-outline, ' +
				'.page-links > span, .site-content .page-links > span, ' +
				'.navigation .nav-links .page-numbers.current, ' +
				'.woocommerce div.product div.images .flex-control-thumbs li img.flex-active, ' +
				'.woocommerce div.product .woocommerce-pagination ul li span.current {' +
				'border-color: ' +
				newval +
				';' +
				'}';

			// Border bottom color.
			style_css +=
				'#masthead .pr-header-widgets .dropdown-item::after, ' +
				'.prisma-core-nav > ul .sub-menu::after,' +
				'textarea:focus, ' +
				'input[type="text"]:focus, ' +
				'input[type="email"]:focus, ' +
				'input[type=password]:focus, ' +
				'input[type=tel]:focus, ' +
				'input[type=url]:focus, ' +
				'input[type=search]:focus, ' +
				'input[type=date]:focus, ' +
				'#add_payment_method table.cart td.actions .coupon .input-text:focus, ' +
				'.woocommerce-cart table.cart td.actions .coupon .input-text:focus, ' +
				'.woocommerce-checkout table.cart td.actions .coupon .input-text:focus  {' +
				'border-bottom-color: ' +
				newval +
				';' +
				'}';

			// Border top color.
			style_css += '.pr-header-widgets .dropdown-item, ' + '.site .woocommerce-info, ' + '.preloader-1 > div, ' + '.pr-header-element.prisma-core-nav .sub-menu {' + 'border-top-color: ' + newval + ';' + '}';

			// Fill color.
			style_css +=
				'.prisma-core-animate-arrow:hover .arrow-handle, ' +
				'.prisma-core-animate-arrow:hover .arrow-bar, ' +
				'.prisma-core-animate-arrow:focus .arrow-handle, ' +
				'.prisma-core-animate-arrow:focus .arrow-bar, ' +
				'.prisma-core-pagination .navigation .nav-links .page-numbers.next:hover .prisma-core-animate-arrow .arrow-handle,' +
				'.prisma-core-pagination .navigation .nav-links .page-numbers.prev:hover .prisma-core-animate-arrow .arrow-handle,' +
				'.prisma-core-pagination .navigation .nav-links .page-numbers.next:hover .prisma-core-animate-arrow .arrow-bar,' +
				'.prisma-core-pagination .navigation .nav-links .page-numbers.prev:hover .prisma-core-animate-arrow .arrow-bar {' +
				'fill: ' +
				newval +
				';' +
				'}';

			// Box shadow.
			style_css += '.pr-input-supported input[type=checkbox]:focus:hover { ' + 'box-shadow: inset 0 0 0 2px ' + newval + '; ' + '}';

			// Gradient.
			style_css +=
				'.pr-pre-footer-cta-style-1 #pr-pre-footer .pr-flex-row::before,' +
				'.pr-pre-footer-cta-style-2 #pr-pre-footer::before { ' +
				'background: linear-gradient(to right, ' +
				prisma_core_hex2rgba(newval, 0.9) +
				' 0%, ' +
				prisma_core_hex2rgba(newval, 0.82) +
				' 35%, ' +
				prisma_core_hex2rgba(newval, 0.4) +
				' 100% );' +
				'-webkit-gradient(linear, left top, right top, from(' +
				prisma_core_hex2rgba(newval, 0.9) +
				'), color-stop(35%, ' +
				prisma_core_hex2rgba(newval, 0.82) +
				'), to(' +
				prisma_core_hex2rgba(newval, 0.4) +
				')); }';

			$style_tag.html(style_css);
		});
	});

	/**
	 * Content background color.
	 */
	api('prisma_core_boxed_content_background_color', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_boxed_content_background_color');
			var style_css = '';

			if (newval) {
				style_css =
					'.prisma-core-layout__boxed #page, ' +
					'.prisma-core-layout__boxed-separated #content, ' +
					'.prisma-core-layout__boxed-separated.prisma-core-sidebar-style-3 #secondary .pr-widget, ' +
					'.prisma-core-layout__boxed-separated.prisma-core-sidebar-style-3 .elementor-widget-sidebar .pr-widget, ' +
					'.prisma-core-layout__boxed-separated.blog .prisma-core-article, ' +
					'.prisma-core-layout__boxed-separated.search-results .prisma-core-article, ' +
					'.prisma-core-layout__boxed-separated.category .prisma-core-article { background-color: ' +
					newval +
					'; }';

				style_css += '@media screen and (max-width: 960px) { ' + '.prisma-core-layout__boxed-separated #page { background-color: ' + newval + '; }' + '}';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Content text color.
	 */
	api('prisma_core_content_text_color', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_content_text_color');
			var style_css = '';

			if (newval) {
				style_css =
					'body { ' +
					'color: ' +
					newval +
					';' +
					'}' +
					'.comment-form .comment-notes, ' +
					'#comments .no-comments, ' +
					'#page .wp-caption .wp-caption-text,' +
					'#comments .comment-meta,' +
					'.comments-closed,' +
					'.entry-meta,' +
					'.pr-entry cite,' +
					'legend,' +
					'.pr-page-header-description,' +
					'.page-links em,' +
					'.site-content .page-links em,' +
					'.single .entry-footer .last-updated,' +
					'.single .post-nav .post-nav-title,' +
					'#main .widget_recent_comments span,' +
					'#main .widget_recent_entries span,' +
					'#main .widget_calendar table > caption,' +
					'.post-thumb-caption, ' +
					'.wp-block-image figcaption, ' +
					'.pr-cart-item .pr-x,' +
					'.woocommerce form.login .lost_password a,' +
					'.woocommerce form.register .lost_password a,' +
					'.woocommerce a.remove,' +
					'#add_payment_method .cart-collaterals .cart_totals .woocommerce-shipping-destination, ' +
					'.woocommerce-cart .cart-collaterals .cart_totals .woocommerce-shipping-destination, ' +
					'.woocommerce-checkout .cart-collaterals .cart_totals .woocommerce-shipping-destination,' +
					'.woocommerce ul.products li.product .pr-loop-product__category-wrap a,' +
					'.woocommerce ul.products li.product .pr-loop-product__category-wrap,' +
					'.woocommerce .woocommerce-checkout-review-order table.shop_table thead th,' +
					'#add_payment_method #payment div.payment_box, ' +
					'.woocommerce-cart #payment div.payment_box, ' +
					'.woocommerce-checkout #payment div.payment_box,' +
					'#add_payment_method #payment ul.payment_methods .about_paypal, ' +
					'.woocommerce-cart #payment ul.payment_methods .about_paypal, ' +
					'.woocommerce-checkout #payment ul.payment_methods .about_paypal,' +
					'.woocommerce table dl,' +
					'.woocommerce table .wc-item-meta,' +
					'.widget.woocommerce .reviewer,' +
					'.woocommerce.widget_shopping_cart .cart_list li a.remove:before,' +
					'.woocommerce .widget_shopping_cart .cart_list li a.remove:before,' +
					'.woocommerce .widget_shopping_cart .cart_list li .quantity, ' +
					'.woocommerce.widget_shopping_cart .cart_list li .quantity,' +
					'.woocommerce div.product .woocommerce-product-rating .woocommerce-review-link,' +
					'.woocommerce div.product .woocommerce-tabs table.shop_attributes td,' +
					'.woocommerce div.product .product_meta > span span:not(.pr-woo-meta-title), ' +
					'.woocommerce div.product .product_meta > span a,' +
					'.woocommerce .star-rating::before,' +
					'.woocommerce div.product #reviews #comments ol.commentlist li .comment-text p.meta,' +
					'.ywar_review_count,' +
					'.woocommerce .add_to_cart_inline del, ' +
					'.woocommerce div.product p.price del, ' +
					'.woocommerce div.product span.price del { color: ' +
					prisma_core_hex2rgba(newval, 0.75) +
					'; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Content link hover color.
	 */
	api('prisma_core_content_link_hover_color', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_content_link_hover_color');
			var style_css = '';

			if (newval) {
				// Content link hover.
				style_css +=
					'.content-area a:not(.pr-btn):not(.wp-block-button__link):hover, ' +
					'.pr-woo-before-shop select.custom-select-loaded:hover ~ #pr-orderby, ' +
					'#add_payment_method #payment ul.payment_methods .about_paypal:hover, ' +
					'.woocommerce-cart #payment ul.payment_methods .about_paypal:hover, ' +
					'.woocommerce-checkout #payment ul.payment_methods .about_paypal:hover, ' +
					'.pr-breadcrumbs a:hover, ' +
					'.woocommerce div.product .woocommerce-product-rating .woocommerce-review-link:hover, ' +
					'.woocommerce ul.products li.product .meta-wrap .woocommerce-loop-product__link:hover, ' +
					'.woocommerce ul.products li.product .pr-loop-product__category-wrap a:hover { ' +
					'color: ' +
					newval +
					';' +
					'}';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Content text color.
	 */
	api('prisma_core_headings_color', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_headings_color');
			var style_css = '';

			if (newval) {
				style_css = 'h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .prisma-core-logo .site-title, .error-404 .page-header h1 { ' + 'color: ' + newval + ';' + '}';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Scroll Top visibility.
	 */
	api('prisma_core_scroll_top_visibility', function (value) {
		value.bind(function (newval) {
			prisma_core_print_visibility_classes($('#pr-scroll-top'), newval);
		});
	});

	/**
	 * Page Preloader visibility.
	 */
	api('prisma_core_preloader_visibility', function (value) {
		value.bind(function (newval) {
			prisma_core_print_visibility_classes($('#pr-preloader'), newval);
		});
	});

	/**
	 * Footer visibility.
	 */
	api('prisma_core_footer_visibility', function (value) {
		value.bind(function (newval) {
			prisma_core_print_visibility_classes($('#prisma-core-footer'), newval);
		});
	});

	/**
	 * Footer background.
	 */
	api('prisma_core_footer_background', function (value) {
		value.bind(function (newval) {
			var $footer = $('#colophon');

			if (!$footer.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_footer_background');
			var style_css = prisma_core_design_options_css('#colophon', newval, 'background');

			$style_tag.html(style_css);
		});
	});

	/**
	 * Footer font color.
	 */
	api('prisma_core_footer_text_color', function (value) {
		var $footer = $('#prisma-core-footer'),
			copyright_separator_color,
			style_css;

		value.bind(function (newval) {
			if (!$footer.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_footer_text_color');

			style_css = '';

			newval['text-color'] = newval['text-color'] ? newval['text-color'] : 'inherit';
			newval['link-color'] = newval['link-color'] ? newval['link-color'] : 'inherit';
			newval['link-hover-color'] = newval['link-hover-color'] ? newval['link-hover-color'] : 'inherit';
			newval['widget-title-color'] = newval['widget-title-color'] ? newval['widget-title-color'] : 'inherit';

			// Text color.
			style_css += '#colophon { color: ' + newval['text-color'] + '; }';

			// Link color.
			style_css += '#colophon a { color: ' + newval['link-color'] + '; }';

			// Link hover color.
			style_css += '#colophon a:hover, #colophon li.current_page_item > a, #colophon .prisma-core-social-nav > ul > li > a .pr-icon.bottom-icon ' + '{ color: ' + newval['link-hover-color'] + '; }';

			// Widget title color.
			style_css += '#colophon .widget-title { color: ' + newval['widget-title-color'] + '; }';

			// Copyright separator color.
			copyright_separator_color = prisma_core_light_or_dark(newval['text-color'], 'rgba(255,255,255,0.1)', 'rgba(0,0,0,0.1)');
			// copyright_separator_color = prisma_core_luminance( newval['text-color'], 0.8 );

			style_css += '#prisma-core-copyright.contained-separator > .pr-container:before { background-color: ' + copyright_separator_color + '; }';
			style_css += '#prisma-core-copyright.fw-separator { border-top-color: ' + copyright_separator_color + '; }';

			$style_tag.html(style_css);
		});
	});

	/**
	 * Footer border.
	 */
	api('prisma_core_footer_border', function (value) {
		value.bind(function (newval) {
			var $footer = $('#prisma-core-footer');

			if (!$footer.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_footer_border');
			var style_css = '';

			if (newval['border-top-width']) {
				style_css += '#colophon { ' + 'border-top-width: ' + newval['border-top-width'] + 'px;' + 'border-top-style: ' + newval['border-style'] + ';' + 'border-top-color: ' + newval['border-color'] + ';' + '}';
			}

			if (newval['border-bottom-width']) {
				style_css += '#colophon { ' + 'border-bottom-width: ' + newval['border-bottom-width'] + 'px;' + 'border-bottom-style: ' + newval['border-style'] + ';' + 'border-bottom-color: ' + newval['border-color'] + ';' + '}';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Copyright layout.
	 */
	api('prisma_core_copyright_layout', function (value) {
		value.bind(function (newval) {
			$body.removeClass(function (index, className) {
				return (className.match(/(^|\s)prisma-core-copyright-layout-\S+/g) || []).join(' ');
			});

			$body.addClass('prisma-core-copyright-' + newval);
		});
	});

	/**
	 * Copyright separator.
	 */
	api('prisma_core_copyright_separator', function (value) {
		value.bind(function (newval) {
			var $copyright = $('#prisma-core-copyright');

			if (!$copyright.length) {
				return;
			}

			$copyright.removeClass('fw-separator contained-separator');

			if ('none' !== newval) {
				$copyright.addClass(newval);
			}
		});
	});

	/**
	 * Copyright visibility.
	 */
	api('prisma_core_copyright_visibility', function (value) {
		value.bind(function (newval) {
			prisma_core_print_visibility_classes($('#prisma-core-copyright'), newval);
		});
	});

	/**
	 * Copyright background.
	 */
	api('prisma_core_copyright_background', function (value) {
		value.bind(function (newval) {
			var $copyright = $('#prisma-core-copyright');

			if (!$copyright.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_copyright_background');
			var style_css = prisma_core_design_options_css('#prisma-core-copyright', newval, 'background');

			$style_tag.html(style_css);
		});
	});

	/**
	 * Copyright text color.
	 */
	api('prisma_core_copyright_text_color', function (value) {
		value.bind(function (newval) {
			var $copyright = $('#prisma-core-copyright');

			if (!$copyright.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_copyright_text_color');
			var style_css = '';

			newval['text-color'] = newval['text-color'] ? newval['text-color'] : 'inherit';
			newval['link-color'] = newval['link-color'] ? newval['link-color'] : 'inherit';
			newval['link-hover-color'] = newval['link-hover-color'] ? newval['link-hover-color'] : 'inherit';

			// Text color.
			style_css += '#prisma-core-copyright { color: ' + newval['text-color'] + '; }';

			// Link color.
			style_css += '#prisma-core-copyright a { color: ' + newval['link-color'] + '; }';

			// Link hover color.
			style_css +=
				'#prisma-core-copyright a:hover, #prisma-core-copyright .prisma-core-social-nav > ul > li > a .pr-icon.bottom-icon, #prisma-core-copyright li.current_page_item > a, #prisma-core-copyright .prisma-core-nav > ul > li.current-menu-item > a, #prisma-core-copyright .prisma-core-nav > ul > li.current-menu-ancestor > a #prisma-core-copyright .prisma-core-nav > ul > li:hover > a, #prisma-core-copyright .prisma-core-social-nav > ul > li > a .pr-icon.bottom-icon { color: ' +
				newval['link-hover-color'] +
				'; }';

			$style_tag.html(style_css);
		});
	});

	/**
	 * Container width.
	 */
	api('prisma_core_container_width', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_container_width');
			var style_css;

			style_css = '.pr-container,' + '.alignfull > div { ' + 'max-width: ' + newval.value + 'px;' + '}';

			style_css +=
				'.prisma-core-layout__boxed #page, .prisma-core-layout__boxed.pr-sticky-header.prisma-core-is-mobile #prisma-core-header-inner, ' +
				'.prisma-core-layout__boxed.pr-sticky-header:not(.prisma-core-header-layout-3) #prisma-core-header-inner, ' +
				'.prisma-core-layout__boxed.pr-sticky-header:not(.prisma-core-is-mobile).prisma-core-header-layout-3 #prisma-core-header-inner .pr-nav-container > .pr-container { max-width: ' +
				(parseInt(newval.value) + 100) +
				'px; }';

			$style_tag.html(style_css);
		});
	});

	/**
	 * Transparent Header Logo max height.
	 */
	api('prisma_core_tsp_logo_max_height', function (value) {
		value.bind(function (newval) {
			var $logo = $('.pr-tsp-header .prisma-core-logo');

			if (!$logo.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_tsp_logo_max_height');
			var style_css = '';

			style_css += prisma_core_range_field_css('.pr-tsp-header .prisma-core-logo img', 'max-height', newval, true, 'px');
			style_css += prisma_core_range_field_css('.pr-tsp-header .prisma-core-logo img.pr-svg-logo', 'height', newval, true, 'px');

			$style_tag.html(style_css);
		});
	});

	/**
	 * Transparent Header Logo margin.
	 */
	api('prisma_core_tsp_logo_margin', function (value) {
		value.bind(function (newval) {
			var $logo = $('.pr-tsp-header .prisma-core-logo');

			if (!$logo.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_tsp_logo_margin');

			var style_css = prisma_core_spacing_field_css('.pr-tsp-header .prisma-core-logo .logo-inner', 'margin', newval, true);
			$style_tag.html(style_css);
		});
	});

	/**
	 * Transparent header - Main Header & Topbar background.
	 */
	api('prisma_core_tsp_header_background', function (value) {
		value.bind(function (newval) {
			var $tsp_header = $('.pr-tsp-header');

			if (!$tsp_header.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_tsp_header_background');

			var style_css = '';
			style_css += prisma_core_design_options_css('.pr-tsp-header #prisma-core-header-inner', newval, 'background');

			$style_tag.html(style_css);
		});
	});

	/**
	 * Transparent header - Main Header & Topbar font color.
	 */
	api('prisma_core_tsp_header_font_color', function (value) {
		value.bind(function (newval) {
			var $tsp_header = $('.pr-tsp-header');

			if (!$tsp_header.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_tsp_header_font_color');

			var style_css = '';

			newval['text-color'] = newval['text-color'] ? newval['text-color'] : 'inherit';
			newval['link-color'] = newval['link-color'] ? newval['link-color'] : 'inherit';
			newval['link-hover-color'] = newval['link-hover-color'] ? newval['link-hover-color'] : 'inherit';

			/** Header **/

			// Text color.
			style_css += '.pr-tsp-header .prisma-core-logo .site-description { color: ' + newval['text-color'] + '; }';

			// Link color.
			if (newval['link-color']) {
				style_css += '.pr-tsp-header #prisma-core-header, ' + '.pr-tsp-header .pr-header-widgets a:not(.pr-btn), ' + '.pr-tsp-header .prisma-core-logo a,' + '.pr-tsp-header .pr-hamburger, ' + '.pr-tsp-header #prisma-core-header-inner .prisma-core-nav > ul > li > a { color: ' + newval['link-color'] + '; }';
				style_css += '.pr-tsp-header .hamburger-inner,' + '.pr-tsp-header .hamburger-inner::before,' + '.pr-tsp-header .hamburger-inner::after { background-color: ' + newval['link-color'] + '; }';
			}

			// Link hover color.
			if (newval['link-hover-color']) {
				style_css +=
					'.pr-tsp-header .pr-header-widgets a:not(.pr-btn):hover, ' +
					'.pr-tsp-header #prisma-core-header-inner .pr-header-widgets .prisma-core-active,' +
					'.pr-tsp-header .prisma-core-logo .site-title a:hover, ' +
					'.pr-tsp-header .pr-hamburger:hover .hamburger-label, ' +
					'.is-mobile-menu-active .pr-tsp-header .pr-hamburger .hamburger-label,' +
					'.pr-tsp-header #prisma-core-header-inner .prisma-core-nav > ul > li > a:hover,' +
					'.pr-tsp-header #prisma-core-header-inner .prisma-core-nav > ul > li.menu-item-has-children:hover > a,' +
					'.pr-tsp-header #prisma-core-header-inner .prisma-core-nav > ul > li.current-menu-item > a,' +
					'.pr-tsp-header #prisma-core-header-inner .prisma-core-nav > ul > li.current-menu-ancestor > a,' +
					'.pr-tsp-header #prisma-core-header-inner .prisma-core-nav > ul > li.page_item_has_children:hover > a,' +
					'.pr-tsp-header #prisma-core-header-inner .prisma-core-nav > ul > li.current_page_item > a,' +
					'.pr-tsp-header #prisma-core-header-inner .prisma-core-nav > ul > li.current_page_ancestor > a { color: ' +
					newval['link-hover-color'] +
					'; }';

				style_css +=
					'.pr-tsp-header .pr-hamburger:hover .hamburger-inner,' +
					'.pr-tsp-header .pr-hamburger:hover .hamburger-inner::before,' +
					'.pr-tsp-header .pr-hamburger:hover .hamburger-inner::after,' +
					'.is-mobile-menu-active .pr-tsp-header .pr-hamburger .hamburger-inner,' +
					'.is-mobile-menu-active .pr-tsp-header .pr-hamburger .hamburger-inner::before,' +
					'.is-mobile-menu-active .pr-tsp-header .pr-hamburger .hamburger-inner::after { background-color: ' +
					newval['link-hover-color'] +
					'; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Transparent header - Main Header & Topbar border.
	 */
	api('prisma_core_tsp_header_border', function (value) {
		value.bind(function (newval) {
			var $tsp_header = $('.pr-tsp-header');

			if (!$tsp_header.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_tsp_header_border');

			var style_css = '';

			style_css += prisma_core_design_options_css('.pr-tsp-header #prisma-core-header-inner', newval, 'border');

			// Separator color.
			newval['separator-color'] = newval['separator-color'] ? newval['separator-color'] : 'inherit';
			style_css += '.pr-tsp-header .pr-header-widget:after { background-color: ' + newval['separator-color'] + '; }';

			$style_tag.html(style_css);
		});
	});

	/**
	 * Page Header layout.
	 */
	api('prisma_core_page_header_alignment', function (value) {
		value.bind(function (newval) {
			if ($body.hasClass('single-post')) {
				return;
			}

			$body.removeClass(function (index, className) {
				return (className.match(/(^|\s)pr-page-title-align-\S+/g) || []).join(' ');
			});

			$body.addClass('pr-page-title-align-' + newval);
		});
	});

	/**
	 * Page Header spacing.
	 */
	api('prisma_core_page_header_spacing', function (value) {
		value.bind(function (newval) {
			var $page_header = $('.page-header');

			if (!$page_header.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_page_header_spacing');

			var style_css = prisma_core_spacing_field_css('.pr-page-title-align-left .page-header.pr-has-page-title, .pr-page-title-align-right .page-header.pr-has-page-title, .pr-page-title-align-center .page-header .pr-page-header-wrapper', 'padding', newval, true);

			$style_tag.html(style_css);
		});
	});

	/**
	 * Page Header background.
	 */
	api('prisma_core_page_header_background', function (value) {
		value.bind(function (newval) {
			var $page_header = $('.page-header');

			if (!$page_header.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_page_header_background');

			var style_css = '';
			style_css += prisma_core_design_options_css('.page-header', newval, 'background');
			style_css += prisma_core_design_options_css('.pr-tsp-header:not(.pr-tsp-absolute) #masthead', newval, 'background');

			$style_tag.html(style_css);
		});
	});

	/**
	 * Header Text color.
	 */
	api('prisma_core_page_header_text_color', function (value) {
		value.bind(function (newval) {
			var $page_header = $('.page-header');

			if (!$page_header.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_page_header_text_color');
			var style_css = '';

			newval['text-color'] = newval['text-color'] ? newval['text-color'] : 'inherit';
			newval['link-color'] = newval['link-color'] ? newval['link-color'] : 'inherit';
			newval['link-hover-color'] = newval['link-hover-color'] ? newval['link-hover-color'] : 'inherit';

			// Text color.
			style_css += '.page-header .page-title { color: ' + newval['text-color'] + '; }';
			style_css += '.page-header .pr-page-header-description' + '{ color: ' + prisma_core_hex2rgba(newval['text-color'], 0.75) + '}';

			// Link color.
			style_css += '.page-header .pr-breadcrumbs a' + '{ color: ' + newval['link-color'] + '; }';

			style_css += '.page-header .pr-breadcrumbs span,' + '.page-header .breadcrumb-trail .trail-items li::after, .page-header .pr-breadcrumbs .separator' + '{ color: ' + prisma_core_hex2rgba(newval['link-color'], 0.75) + '}';

			$style_tag.html(style_css);
		});
	});

	/**
	 * Page Header border.
	 */
	api('prisma_core_page_header_border', function (value) {
		value.bind(function (newval) {
			var $page_header = $('.page-header');

			if (!$page_header.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_page_header_border');
			var style_css = prisma_core_design_options_css('.page-header', newval, 'border');

			$style_tag.html(style_css);
		});
	});

	/**
	 * Breadcrumbs alignment.
	 */
	api('prisma_core_breadcrumbs_alignment', function (value) {
		value.bind(function (newval) {
			var $breadcrumbs = $('#main > .pr-breadcrumbs > .pr-container');

			if (!$breadcrumbs.length) {
				return;
			}

			$breadcrumbs.removeClass(function (index, className) {
				return (className.match(/(^|\s)pr-text-align\S+/g) || []).join(' ');
			});

			$breadcrumbs.addClass('pr-text-align-' + newval);
		});
	});

	/**
	 * Breadcrumbs spacing.
	 */
	api('prisma_core_breadcrumbs_spacing', function (value) {
		value.bind(function (newval) {
			var $breadcrumbs = $('.pr-breadcrumbs');

			if (!$breadcrumbs.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_breadcrumbs_spacing');

			var style_css = prisma_core_spacing_field_css('.pr-breadcrumbs', 'padding', newval, true);

			$style_tag.html(style_css);
		});
	});

	/**
	 * Breadcrumbs Background.
	 */
	api('prisma_core_breadcrumbs_background', function (value) {
		value.bind(function (newval) {
			var $breadcrumbs = $('.pr-breadcrumbs');

			if (!$breadcrumbs.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_breadcrumbs_background');
			var style_css = prisma_core_design_options_css('.pr-breadcrumbs', newval, 'background');

			$style_tag.html(style_css);
		});
	});

	/**
	 * Breadcrumbs Text Color.
	 */
	api('prisma_core_breadcrumbs_text_color', function (value) {
		value.bind(function (newval) {
			var $breadcrumbs = $('.pr-breadcrumbs');

			if (!$breadcrumbs.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_breadcrumbs_text_color');
			var style_css = prisma_core_design_options_css('.pr-breadcrumbs', newval, 'color');

			$style_tag.html(style_css);
		});
	});

	/**
	 * Breadcrumbs Border.
	 */
	api('prisma_core_breadcrumbs_border', function (value) {
		value.bind(function (newval) {
			var $breadcrumbs = $('.pr-breadcrumbs');

			if (!$breadcrumbs.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_breadcrumbs_border');
			var style_css = prisma_core_design_options_css('.pr-breadcrumbs', newval, 'border');

			$style_tag.html(style_css);
		});
	});

	/**
	 * Base HTML font size.
	 */
	api('prisma_core_html_base_font_size', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_html_base_font_size');
			var style_css = prisma_core_range_field_css('html', 'font-size', newval, true, 'px');
			$style_tag.html(style_css);
		});
	});

	/**
	 * Font smoothing.
	 */
	api('prisma_core_font_smoothing', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_font_smoothing');

			if (newval) {
				$style_tag.html('*,' + '*::before,' + '*::after {' + '-moz-osx-font-smoothing: grayscale;' + '-webkit-font-smoothing: antialiased;' + '}');
			} else {
				$style_tag.html('*,' + '*::before,' + '*::after {' + '-moz-osx-font-smoothing: auto;' + '-webkit-font-smoothing: auto;' + '}');
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_html_base_font_size');
			var style_css = prisma_core_range_field_css('html', 'font-size', newval, true, 'px');
			$style_tag.html(style_css);
		});
	});

	/**
	 * Body font.
	 */
	api('prisma_core_body_font', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_body_font');
			var style_css = prisma_core_typography_field_css('body', newval);

			prisma_core_enqueue_google_font(newval['font-family']);

			$style_tag.html(style_css);
		});
	});

	/**
	 * Headings font.
	 */
	api('prisma_core_headings_font', function (value) {
		var style_css, selector;
		value.bind(function (newval) {
			selector = 'h1, .h1, .prisma-core-logo .site-title, .page-header h1.page-title';
			selector += ', h2, .h2, .woocommerce div.product h1.product_title';
			selector += ', h3, .h3, .woocommerce #reviews #comments h2';
			selector += ', h4, .h4, .woocommerce .cart_totals h2, .woocommerce .cross-sells > h4, .woocommerce #reviews #respond .comment-reply-title';
			selector += ', h5, h6';

			style_css = prisma_core_typography_field_css(selector, newval);

			prisma_core_enqueue_google_font(newval['font-family']);

			$style_tag = prisma_core_get_style_tag('prisma_core_headings_font');
			$style_tag.html(style_css);
		});
	});

	/**
	 * Heading 1 font.
	 */
	api('prisma_core_h1_font', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_h1_font');

			var style_css = prisma_core_typography_field_css('h1, .h1, .prisma-core-logo .site-title, .page-header h1.page-title', newval);

			prisma_core_enqueue_google_font(newval['font-family']);

			$style_tag.html(style_css);
		});
	});

	/**
	 * Heading 2 font.
	 */
	api('prisma_core_h2_font', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_h2_font');

			var style_css = prisma_core_typography_field_css('h2, .h2, .woocommerce div.product h1.product_title', newval);

			prisma_core_enqueue_google_font(newval['font-family']);

			$style_tag.html(style_css);
		});
	});

	/**
	 * Heading 3 font.
	 */
	api('prisma_core_h3_font', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_h3_font');

			var style_css = prisma_core_typography_field_css('h3, .h3, .woocommerce #reviews #comments h2', newval);

			prisma_core_enqueue_google_font(newval['font-family']);

			$style_tag.html(style_css);
		});
	});

	/**
	 * Heading 4 font.
	 */
	api('prisma_core_h4_font', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_h4_font');

			var style_css = prisma_core_typography_field_css('h4, .h4, .woocommerce .cart_totals h2, .woocommerce .cross-sells > h4, .woocommerce #reviews #respond .comment-reply-title', newval);

			prisma_core_enqueue_google_font(newval['font-family']);

			$style_tag.html(style_css);
		});
	});

	/**
	 * Heading 5 font.
	 */
	api('prisma_core_h5_font', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_h5_font');
			var style_css = prisma_core_typography_field_css('h5', newval);

			prisma_core_enqueue_google_font(newval['font-family']);

			$style_tag.html(style_css);
		});
	});

	/**
	 * Heading 6 font.
	 */
	api('prisma_core_h6_font', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_h6_font');
			var style_css = prisma_core_typography_field_css('h6', newval);

			prisma_core_enqueue_google_font(newval['font-family']);

			$style_tag.html(style_css);
		});
	});

	/**
	 * Heading emphasized font.
	 */
	api('prisma_core_heading_em_font', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_heading_em_font');
			var style_css = prisma_core_typography_field_css('h1 em, h2 em, h3 em, h4 em, h5 em, h6 em, .h1 em, .h2 em, .h3 em, .h4 em, .prisma-core-logo .site-title em, .error-404 .page-header h1 em', newval);

			prisma_core_enqueue_google_font(newval['font-family']);

			$style_tag.html(style_css);
		});
	});

	/**
	 * Sidebar widget title font size.
	 */
	api('prisma_core_sidebar_widget_title_font_size', function (value) {
		value.bind(function (newval) {
			var $widget_title = $('#main .widget-title');

			if (!$widget_title.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_sidebar_widget_title_font_size');
			var style_css = '';

			style_css += prisma_core_range_field_css('#main .widget-title', 'font-size', newval, true, newval.unit);

			$style_tag.html(style_css);
		});
	});

	/**
	 * Footer widget title font size.
	 */
	api('prisma_core_footer_widget_title_font_size', function (value) {
		value.bind(function (newval) {
			var $widget_title = $('#colophon .widget-title');

			if (!$widget_title.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_footer_widget_title_font_size');
			var style_css = '';

			style_css += prisma_core_range_field_css('#colophon .widget-title', 'font-size', newval, true, newval.unit);

			$style_tag.html(style_css);
		});
	});

	/**
	 * Page title font size.
	 */
	api('prisma_core_page_header_font_size', function (value) {
		value.bind(function (newval) {
			var $page_title = $('.page-header .page-title');

			if (!$page_title.length) {
				return;
			}

			$style_tag = prisma_core_get_style_tag('prisma_core_page_header_font_size');
			var style_css = '';

			style_css += prisma_core_range_field_css('#page .page-header .page-title', 'font-size', newval, true, newval.unit);

			$style_tag.html(style_css);
		});
	});

	var $btn_selectors =
		'.pr-btn, ' +
		'body:not(.wp-customizer) input[type=submit], ' +
		'.site-main .woocommerce #respond input#submit, ' +
		'.site-main .woocommerce a.button, ' +
		'.site-main .woocommerce button.button, ' +
		'.site-main .woocommerce input.button, ' +
		'.woocommerce ul.products li.product .added_to_cart, ' +
		'.woocommerce ul.products li.product .button, ' +
		'.woocommerce div.product form.cart .button, ' +
		'.woocommerce #review_form #respond .form-submit input, ' +
		'#infinite-handle span';

	var $btn_hover_selectors =
		'.pr-btn:hover, ' +
		'.pr-btn:focus, ' +
		'body:not(.wp-customizer) input[type=submit]:hover, ' +
		'body:not(.wp-customizer) input[type=submit]:focus, ' +
		'.site-main .woocommerce #respond input#submit:hover, ' +
		'.site-main .woocommerce #respond input#submit:focus, ' +
		'.site-main .woocommerce a.button:hover, ' +
		'.site-main .woocommerce a.button:focus, ' +
		'.site-main .woocommerce button.button:hover, ' +
		'.site-main .woocommerce button.button:focus, ' +
		'.site-main .woocommerce input.button:hover, ' +
		'.site-main .woocommerce input.button:focus, ' +
		'.woocommerce ul.products li.product .added_to_cart:hover, ' +
		'.woocommerce ul.products li.product .added_to_cart:focus, ' +
		'.woocommerce ul.products li.product .button:hover, ' +
		'.woocommerce ul.products li.product .button:focus, ' +
		'.woocommerce div.product form.cart .button:hover, ' +
		'.woocommerce div.product form.cart .button:focus, ' +
		'.woocommerce #review_form #respond .form-submit input:hover, ' +
		'.woocommerce #review_form #respond .form-submit input:focus, ' +
		'#infinite-handle span:hover';

	/**
	 * Primary button background color.
	 */
	api('prisma_core_primary_button_bg_color', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_primary_button_bg_color');
			var style_css = '';

			if (newval) {
				style_css = $btn_selectors + '{ background-color: ' + newval + '; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Primary button hover background color.
	 */
	api('prisma_core_primary_button_hover_bg_color', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_primary_button_hover_bg_color');
			var style_css = '';

			if (newval) {
				style_css = $btn_hover_selectors + ' { background-color: ' + newval + '; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Primary button text color.
	 */
	api('prisma_core_primary_button_text_color', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_primary_button_text_color');
			var style_css = '';

			if (newval) {
				style_css = $btn_selectors + ' { color: ' + newval + '; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Primary button hover text color.
	 */
	api('prisma_core_primary_button_hover_text_color', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_primary_button_hover_text_color');
			var style_css = '';

			if (newval) {
				style_css = $btn_hover_selectors + ' { color: ' + newval + '; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Primary button border width.
	 */
	api('prisma_core_primary_button_border_width', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_primary_button_border_width');
			var style_css = '';

			if (newval) {
				style_css = $btn_selectors + ' { border-width: ' + newval.value + 'px; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Primary button border radius.
	 */
	api('prisma_core_primary_button_border_radius', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_primary_button_border_radius');
			var style_css = '';

			if (newval) {
				style_css = $btn_selectors + ' { ' + 'border-top-left-radius: ' + newval['top-left'] + 'px;' + 'border-top-right-radius: ' + newval['top-right'] + 'px;' + 'border-bottom-left-radius: ' + newval['bottom-left'] + 'px;' + 'border-bottom-right-radius: ' + newval['bottom-right'] + 'px; }';

				console.log(style_css);
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Primary button border color.
	 */
	api('prisma_core_primary_button_border_color', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_primary_button_border_color');
			var style_css = '';

			if (newval) {
				style_css = $btn_selectors + ' { border-color: ' + newval + '; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Primary button hover border color.
	 */
	api('prisma_core_primary_button_hover_border_color', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_primary_button_hover_border_color');
			var style_css = '';

			if (newval) {
				style_css = $btn_hover_selectors + ' { border-color: ' + newval + '; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Primary button typography.
	 */
	api('prisma_core_primary_button_typography', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_primary_button_typography');
			var style_css = prisma_core_typography_field_css($btn_selectors, newval);

			prisma_core_enqueue_google_font(newval['font-family']);

			$style_tag.html(style_css);
		});
	});

	// Secondary button.
	var $btn_sec_selectors = '.btn-secondary, .pr-btn.btn-secondary';

	var $btn_sec_hover_selectors = '.btn-secondary:hover, ' + '.btn-secondary:focus, ' + '.pr-btn.btn-secondary:hover, ' + '.pr-btn.btn-secondary:focus';

	/**
	 * Secondary button background color.
	 */
	api('prisma_core_secondary_button_bg_color', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_secondary_button_bg_color');
			var style_css = '';

			if (newval) {
				style_css = $btn_sec_selectors + '{ background-color: ' + newval + '; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Secondary button hover background color.
	 */
	api('prisma_core_secondary_button_hover_bg_color', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_secondary_button_hover_bg_color');
			var style_css = '';

			if (newval) {
				style_css = $btn_sec_hover_selectors + '{ background-color: ' + newval + '; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Secondary button text color.
	 */
	api('prisma_core_secondary_button_text_color', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_secondary_button_text_color');
			var style_css = '';

			if (newval) {
				style_css = $btn_sec_selectors + '{ color: ' + newval + '; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Secondary button hover text color.
	 */
	api('prisma_core_secondary_button_hover_text_color', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_secondary_button_hover_text_color');
			var style_css = '';

			if (newval) {
				style_css = $btn_sec_hover_selectors + '{ color: ' + newval + '; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Secondary button border width.
	 */
	api('prisma_core_secondary_button_border_width', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_secondary_button_border_width');
			var style_css = '';

			if (newval) {
				style_css = $btn_sec_selectors + ' { border-width: ' + newval.value + 'px; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Secondary button border radius.
	 */
	api('prisma_core_secondary_button_border_radius', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_secondary_button_border_radius');
			var style_css = '';

			if (newval) {
				style_css = $btn_sec_selectors + ' { ' + 'border-top-left-radius: ' + newval['top-left'] + 'px;' + 'border-top-right-radius: ' + newval['top-right'] + 'px;' + 'border-bottom-left-radius: ' + newval['bottom-left'] + 'px;' + 'border-bottom-right-radius: ' + newval['bottom-right'] + 'px; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Secondary button border color.
	 */
	api('prisma_core_secondary_button_border_color', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_secondary_button_border_color');
			var style_css = '';

			if (newval) {
				style_css = $btn_sec_selectors + ' { border-color: ' + newval + '; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Secondary button hover border color.
	 */
	api('prisma_core_secondary_button_hover_border_color', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_secondary_button_hover_border_color');
			var style_css = '';

			if (newval) {
				style_css = $btn_sec_hover_selectors + ' { border-color: ' + newval + '; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Secondary button typography.
	 */
	api('prisma_core_secondary_button_typography', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_secondary_button_typography');
			var style_css = prisma_core_typography_field_css($btn_sec_selectors, newval);

			prisma_core_enqueue_google_font(newval['font-family']);

			$style_tag.html(style_css);
		});
	});

	// Text button.
	var $btn_text_selectors = '.pr-btn.btn-text-1, .btn-text-1';

	var $btn_text_hover_selectors = '.pr-btn.btn-text-1:hover, .pr-btn.btn-text-1:focus, .btn-text-1:hover, .btn-text-1:focus';

	/**
	 * Text button text color.
	 */
	api('prisma_core_text_button_text_color', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_text_button_text_color');
			var style_css = '';

			if (newval) {
				style_css = $btn_text_selectors + '{ color: ' + newval + '; }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Text button hover text color.
	 */
	api('prisma_core_text_button_hover_text_color', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_text_button_hover_text_color');
			var style_css = '';

			if (newval) {
				style_css = $btn_text_hover_selectors + '{ color: ' + newval + '; }';
				style_css += '.pr-btn.btn-text-1 > span::before { background-color: ' + newval + ' }';
			}

			$style_tag.html(style_css);
		});
	});

	/**
	 * Text button typography.
	 */
	api('prisma_core_text_button_typography', function (value) {
		value.bind(function (newval) {
			$style_tag = prisma_core_get_style_tag('prisma_core_text_button_typography');
			var style_css = prisma_core_typography_field_css($btn_text_selectors, newval);

			prisma_core_enqueue_google_font(newval['font-family']);

			$style_tag.html(style_css);
		});
	});

	// Selective refresh.
	if (api.selectiveRefresh) {
		// Bind partial content rendered event.
		api.selectiveRefresh.bind('partial-content-rendered', function (placement) {
			// Hero Hover Slider.
			if ('prisma_core_hero_hover_slider_post_number' === placement.partial.id || 'prisma_core_hero_hover_slider_elements' === placement.partial.id) {
				document.querySelectorAll(placement.partial.params.selector).forEach((item) => {
					prismaCoreHoverSlider(item);
				});

				// Force refresh height.
				api('prisma_core_hero_hover_slider_height', function (newval) {
					newval.callbacks.fireWith(newval, [newval.get()]);
				});
			}

			// Preloader style.
			if ('prisma_core_preloader_style' === placement.partial.id) {
				$body.removeClass('pr-loaded');

				setTimeout(function () {
					window.prismaCore.preloader();
				}, 300);
			}
		});
	}

	// Custom Customizer Preview class (attached to the Customize API)
	api.prismaCoreCustomizerPreview = {
		// Init
		init: function () {
			var self = this; // Store a reference to "this"
			var previewBody = self.preview.body;

			previewBody.on('click', '.prisma-core-set-widget', function () {
				self.preview.send('set-footer-widget', $(this).data('sidebar-id'));
			});
		},
	};

	/**
	 * Capture the instance of the Preview since it is private (this has changed in WordPress 4.0)
	 *
	 * @see https://github.com/WordPress/WordPress/blob/5cab03ab29e6172a8473eb601203c9d3d8802f17/wp-admin/js/customize-controls.js#L1013
	 */
	var prismaCoreOldPreview = api.Preview;
	api.Preview = prismaCoreOldPreview.extend({
		initialize: function (params, options) {
			// Store a reference to the Preview
			api.prismaCoreCustomizerPreview.preview = this;

			// Call the old Preview's initialize function
			prismaCoreOldPreview.prototype.initialize.call(this, params, options);
		},
	});

	// Document ready
	$(function () {
		// Initialize our Preview
		api.prismaCoreCustomizerPreview.init();
	});
})(jQuery);

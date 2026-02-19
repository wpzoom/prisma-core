const { src, dest, watch, series, parallel } = require( 'gulp' );
const sass = require( 'gulp-sass' )( require( 'sass' ) );
const postcss = require( 'gulp-postcss' );
const autoprefixer = require( 'autoprefixer' );
const cleanCSS = require( 'gulp-clean-css' );
const terser = require( 'gulp-terser' );
const rename = require( 'gulp-rename' );
const fs = require( 'fs' );
const path = require( 'path' );

// ─── Paths ────────────────────────────────────────────────────────────

const paths = {
	// Pipeline 1: Customizer control SCSS → CSS → min.CSS
	controlScss: 'inc/customizer/controls/*/*.scss',

	// Pipeline 2: Plain CSS → autoprefixed min.CSS
	css: [
		'assets/css/style.css',
		'assets/css/editor-style.css',
		'assets/css/woocommerce.css',
		'assets/css/compatibility/woocommerce.css',
		'assets/css/compatibility/elementor.css',
		'assets/css/compatibility/elementor-editor-style.css',
		'inc/admin/assets/css/sinatra-admin.css',
		'inc/admin/assets/css/sinatra-meta-boxes.css',
		'inc/admin/assets/css/sinatra-block-editor-styles.css',
		'inc/customizer/assets/css/sinatra-customizer.css',
		'inc/customizer/assets/css/sinatra-customizer-preview.css',
	],

	// Pipeline 3: Frontend JS (dev/ → parent directory)
	frontJs: [
		'assets/js/dev/sinatra.js',
		'assets/js/dev/sinatra-slider.js',
		'assets/js/dev/sinatra-wc.js',
		'assets/js/dev/skip-link-focus-fix.js',
	],

	// Pipeline 4: Admin/Customizer JS (dev/ → parent .min.js only)
	adminJs: 'inc/admin/assets/js/dev/sinatra-admin.js',
	customizerJs: 'inc/customizer/assets/js/dev/*.js',

	// Pipeline 5: Customizer control JS (in-place minify)
	controlJs: [
		'inc/customizer/controls/*/*.js',
		'!inc/customizer/controls/*/*.min.js',
	],
};

// ─── Pipeline 1: Customizer Control SCSS ──────────────────────────────

function controlScss() {
	return src( paths.controlScss, { base: '.' } )
		.pipe( sass().on( 'error', sass.logError ) )
		.pipe( postcss( [ autoprefixer() ] ) )
		.pipe( dest( '.' ) )
		.pipe( cleanCSS() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( dest( '.' ) );
}

// ─── Pipeline 2: Plain CSS → Autoprefixed min.CSS ─────────────────────

function css() {
	return src( paths.css, { base: '.' } )
		.pipe( postcss( [ autoprefixer() ] ) )
		.pipe( cleanCSS() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( dest( '.' ) );
}

// ─── Pipeline 3: Frontend JS (dev/ → parent copy + min) ──────────────

function frontJsCopy() {
	return src( paths.frontJs )
		.pipe( dest( 'assets/js/' ) );
}

function frontJsMin() {
	return src( paths.frontJs )
		.pipe( terser() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( dest( 'assets/js/' ) );
}

const frontJs = parallel( frontJsCopy, frontJsMin );

// ─── Pipeline 4: Admin/Customizer JS (dev/ → parent min) ─────────────

function adminJsMin() {
	return src( paths.adminJs )
		.pipe( terser() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( dest( 'inc/admin/assets/js/' ) );
}

function customizerJsMin() {
	return src( paths.customizerJs )
		.pipe( terser() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( dest( 'inc/customizer/assets/js/' ) );
}

// ─── Pipeline 5: Customizer Control JS (in-place minify) ─────────────

function controlJs() {
	return src( paths.controlJs, { base: '.' } )
		.pipe( terser() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( dest( '.' ) );
}

// ─── Aggregate tasks ──────────────────────────────────────────────────

const buildCss = parallel( controlScss, css );
const buildJs = parallel( frontJs, adminJsMin, customizerJsMin, controlJs );
const build = parallel( buildCss, buildJs );

// ─── Watch ────────────────────────────────────────────────────────────

function watchFiles() {
	watch( 'inc/customizer/controls/*/*.scss', controlScss );
	watch( paths.css, css );
	watch( paths.frontJs, frontJs );
	watch( paths.adminJs, adminJsMin );
	watch( paths.customizerJs, customizerJsMin );
	watch( paths.controlJs, controlJs );
}

// ─── Version bump ────────────────────────────────────────────────────
// Usage: gulp version-bump --ver=1.5.0

function versionBump( cb ) {
	const arg = process.argv;
	const verFlag = arg.find( ( a ) => a.startsWith( '--ver=' ) );

	if ( ! verFlag ) {
		console.error( 'Usage: gulp version-bump --ver=x.y.z' );
		return cb( new Error( 'Missing --ver argument' ) );
	}

	const newVer = verFlag.split( '=' )[ 1 ];

	if ( ! /^\d+\.\d+\.\d+$/.test( newVer ) ) {
		return cb( new Error( 'Version must be in semver format: x.y.z' ) );
	}

	// 1. package.json
	const pkgPath = path.resolve( 'package.json' );
	const pkg = JSON.parse( fs.readFileSync( pkgPath, 'utf8' ) );
	pkg.version = newVer;
	fs.writeFileSync( pkgPath, JSON.stringify( pkg, null, '  ' ) + '\n' );

	// 2. style.css — "Version: x.y.z"
	replaceInFile(
		'style.css',
		/^Version:\s*.+$/m,
		'Version: ' + newVer
	);

	// 3. functions.php — "$version = 'x.y.z'"
	replaceInFile(
		'functions.php',
		/\$version\s*=\s*'[^']*'/,
		"$version = '" + newVer + "'"
	);

	// 4. readme.txt — "Stable tag: x.y.z"
	replaceInFile(
		'readme.txt',
		/^Stable tag:\s*.+$/m,
		'Stable tag: ' + newVer
	);

	console.log( 'Version bumped to ' + newVer );
	cb();
}

function replaceInFile( filePath, search, replacement ) {
	let content = fs.readFileSync( filePath, 'utf8' );
	content = content.replace( search, replacement );
	fs.writeFileSync( filePath, content );
}

// ─── Generate README.md from readme.txt ──────────────────────────────

function readme( cb ) {
	let txt = fs.readFileSync( 'readme.txt', 'utf8' );

	// Convert WordPress readme headings to Markdown.
	// === Title === → # Title #
	txt = txt.replace( /^=== (.+?) ===\s*$/gm, '# $1 #' );
	// == Section == → ## Section ##
	txt = txt.replace( /^== (.+?) ==\s*$/gm, '## $1 ##' );
	// = Subsection = → ### Subsection ###
	txt = txt.replace( /^= (.+?) =\s*$/gm, '### $1 ###' );

	// Convert "Contributors: a, b" to linked format.
	txt = txt.replace(
		/^(Contributors:\s*)(.+)$/m,
		function ( _match, prefix, names ) {
			const linked = names.split( ',' ).map( ( n ) => {
				n = n.trim();
				return '[' + n + '](https://github.com/' + n + ')';
			} ).join( ', ' );
			return '**' + prefix.trim() + '** ' + linked + '  ';
		}
	);

	// Format metadata as "**Key:** value  " (bold key, trailing double-space for line break).
	const metaKeys = [
		'Tags',
		'Requires at least',
		'Tested up to',
		'Requires PHP',
		'License',
		'License URI',
	];
	metaKeys.forEach( function ( key ) {
		const re = new RegExp( '^' + key + ':\\s*(.+)$', 'm' );
		txt = txt.replace( re, '**' + key + ':** $1  ' );
	} );

	// Convert "Stable tag" to "Version" for GitHub display.
	txt = txt.replace( /^Stable tag:\s*(.+)$/m, '**Version:** $1  ' );

	// WordPress » → &raquo; (already works in GH markdown).
	txt = txt.replace( / » /g, ' &raquo; ' );

	// Rename "Resources" to "Copyright" and add license preamble.
	txt = txt.replace(
		'## Resources ##',
		'## Copyright ##\n\n' +
		'Sinatra WordPress Theme, Copyright 2025 ciorici\n' +
		'Originally created by Sinatra Team (https://sinatrawp.com).\n' +
		'Sinatra is distributed under the terms of the GNU GPL.\n\n' +
		'Sinatra bundles the following third-party resources:'
	);

	// Custom header — matches the Inspiro pattern.
	const customHeader =
		'# Sinatra #\n' +
		'### A lightweight and highly customizable multi-purpose theme that makes it easy for anyone to create their perfect website.\n\n' +
		'Community-maintained fork of the original Sinatra theme by [Sinatra Team](https://sinatrawp.com). ' +
		'Includes security fixes, PHP 8.2+ compatibility, WordPress 6.9+ support, and updated WooCommerce templates.\n\n' +
		'[Download Latest Release](https://github.com/ciorici/sinatra/releases) ' +
		'&nbsp;&middot;&nbsp; ' +
		'[View on WordPress.org](https://wordpress.org/themes/flavor/)\n\n' +
		'![Sinatra Theme Screenshot](screenshot.jpg)\n\n';

	// Replace everything before "**Contributors:" with custom header.
	const contribIndex = txt.indexOf( '**Contributors:' );
	if ( contribIndex !== -1 ) {
		txt = customHeader + txt.substring( contribIndex );
	}

	fs.writeFileSync( 'README.md', txt );
	console.log( 'README.md generated from readme.txt' );
	cb();
}

// ─── Exports ──────────────────────────────────────────────────────────

exports.build = build;
exports.watch = series( build, watchFiles );
exports.readme = readme;
exports[ 'version-bump' ] = series( versionBump, readme );
exports.default = build;

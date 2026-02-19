const { src, dest, watch, series, parallel } = require( 'gulp' );
const sass = require( 'gulp-sass' )( require( 'sass' ) );
const postcss = require( 'gulp-postcss' );
const autoprefixer = require( 'autoprefixer' );
const cleanCSS = require( 'gulp-clean-css' );
const terser = require( 'gulp-terser' );
const rename = require( 'gulp-rename' );

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

// ─── Exports ──────────────────────────────────────────────────────────

exports.build = build;
exports.watch = series( build, watchFiles );
exports.default = build;

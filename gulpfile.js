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
		'inc/admin/assets/css/prisma-core-admin.css',
		'inc/admin/assets/css/prisma-core-meta-boxes.css',
		'inc/admin/assets/css/prisma-core-block-editor-styles.css',
		'inc/customizer/assets/css/prisma-core-customizer.css',
		'inc/customizer/assets/css/prisma-core-customizer-preview.css',
	],

	// Pipeline 3: Frontend JS (dev/ → parent directory)
	frontJs: [
		'assets/js/dev/prisma-core.js',
		'assets/js/dev/prisma-core-slider.js',
		'assets/js/dev/prisma-core-wc.js',
		'assets/js/dev/skip-link-focus-fix.js',
	],

	// Pipeline 4: Admin/Customizer JS (dev/ → parent .min.js only)
	adminJs: [
		'inc/admin/assets/js/dev/prisma-core-admin.js',
		'inc/admin/assets/js/dev/prisma-core-page-options.js',
	],
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
const build = parallel( buildCss, buildJs, readme );

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
		'Prisma Core WordPress Theme, Copyright 2026 wpzoom\n' +
		'Originally created by Sinatra Team.\n' +
		'Prisma Core is distributed under the terms of the GNU GPL.\n\n' +
		'Prisma Core bundles the following third-party resources:'
	);

	// Custom header — matches the Inspiro pattern.
	const customHeader =
		'# Prisma Core #\n' +
		'### A lightweight and highly customizable multi-purpose theme that makes it easy for anyone to create their perfect website.\n\n' +
		'Community-maintained fork of the original Sinatra theme. ' +
		'Includes security fixes, PHP 8.2+ compatibility, WordPress 6.9+ support, and updated WooCommerce templates.\n\n' +
		'[Download Latest Release](https://github.com/wpzoom/prisma-core/releases) ' +
		'&nbsp;&middot;&nbsp; ' +
		'![Prisma Core Theme Screenshot](screenshot.png)\n\n';

	// Replace everything before "**Contributors:" with custom header.
	const contribIndex = txt.indexOf( '**Contributors:' );
	if ( contribIndex !== -1 ) {
		txt = customHeader + txt.substring( contribIndex );
	}

	fs.writeFileSync( 'README.md', txt );
	console.log( 'README.md generated from readme.txt' );
	cb();
}

// ─── Theme rename ────────────────────────────────────────────────────
// Usage: gulp rename --name=PrismaCore [--prefix=pr]
//
// One-time task that renames the Prisma Core theme to a new name.
// Supports CamelCase multi-word names: PrismaCore → slug "prisma-core".
// Does NOT touch the prisma-companion plugin (rename that separately).
// Review the result with `git diff`.

function themeRename( cb ) {
	const args = process.argv;
	const nameFlag = args.find( ( a ) => a.startsWith( '--name=' ) );
	const prefixFlag = args.find( ( a ) => a.startsWith( '--prefix=' ) );

	if ( ! nameFlag ) {
		console.error( 'Usage: gulp rename --name=PrismaCore [--prefix=pr]' );
		return cb( new Error( 'Missing --name argument' ) );
	}

	const rawName = nameFlag.split( '=' )[ 1 ]; // e.g. "PrismaCore"

	if ( ! /^[A-Z][a-zA-Z]+$/.test( rawName ) ) {
		return cb( new Error( 'Name must be CamelCase, letters only (e.g. Flavor, PrismaCore)' ) );
	}

	// Split CamelCase into words: "PrismaCore" → ["Prisma", "Core"].
	const words = rawName.match( /[A-Z][a-z]*/g ) || [ rawName ];
	const displayName = words.join( ' ' ); // "Prisma Core"
	const slug = words.join( '-' ).toLowerCase(); // "prisma-core"
	const classPrefix = words.join( '_' ); // "Prisma_Core"
	const funcPrefix = words.join( '_' ).toLowerCase(); // "prisma_core"
	const constPrefix = words.join( '_' ).toUpperCase(); // "PRISMA_CORE"
	const camelCase = words[ 0 ].toLowerCase() + words.slice( 1 ).join( '' ); // "prismaCore"
	const prefix = prefixFlag
		? prefixFlag.split( '=' )[ 1 ]
		: words[ 0 ].substring( 0, 2 ).toLowerCase(); // "pr"

	// Verify the theme hasn't already been renamed.
	const styleCss = fs.readFileSync( 'style.css', 'utf8' );
	if ( ! /Theme Name:\s*Prisma Core/.test( styleCss ) ) {
		return cb( new Error( 'Theme already renamed. This task can only run once.' ) );
	}

	console.log( 'Renaming: Prisma Core → ' + displayName );
	console.log( '  slug:         prisma-core → ' + slug );
	console.log( '  class prefix: Prisma_Core_ → ' + classPrefix + '_' );
	console.log( '  func prefix:  prisma_core_ → ' + funcPrefix + '_' );
	console.log( '  constants:    PRISMA_CORE_ → ' + constPrefix + '_' );
	console.log( '  CSS prefix:   pr- → ' + prefix + '-' );
	console.log( '  JS camelCase: prisma-core → ' + camelCase );
	console.log( '' );

	const themeDir = path.resolve( '.' );
	const extensions = [
		'.php', '.css', '.scss', '.js', '.json', '.xml',
		'.txt', '.md', '.pot', '.svg',
	];
	const excludedDirs = [ 'node_modules', '.git', 'vendor' ];

	// ── Text replacements ──
	// Phase 1: Tokenize prisma-companion PLUGIN references (protect from rename).
	// Phase 2: Rename theme identifiers.
	// Phase 3: Restore plugin tokens.

	const replacements = [
		// ── Phase 1: Tokenize plugin references ──
		[ /\bPRISMA_COMPANION_/g, '{{__PC_CONST__}}' ],
		[ /\bPrisma_Companion_/g, '{{__PC_CLASS__}}' ],
		[ /\bprisma_companion_/g, '{{__PC_FUNC__}}' ],
		[ /\bprisma_companion\b/g, '{{__PC_NAME__}}' ],
		[ /prisma-companion/g, '{{__PC_SLUG__}}' ],

		// ── Phase 2: Theme rename ──
		// PHP constants.
		[ /\bPRISMA_CORE_THEME_/g, constPrefix + '_THEME_' ],
		[ /\bPRISMA_CORE_/g, constPrefix + '_' ],

		// PHP class names.
		[ /\bPrisma Core_/g, classPrefix + '_' ],

		// PHP function/hook names.
		[ /\bprisma-core_/g, funcPrefix + '_' ],

		// JS camelCase identifiers.
		[ /\bprisma-core([A-Z])/g, camelCase + '$1' ],
		[ /window\.prisma-core\b/g, 'window.' + camelCase ],
		[ /\bprisma-core\(\)/g, funcPrefix + '()' ],

		// Text domains (quoted strings).
		[ /'prisma-core'/g, "'" + slug + "'" ],
		[ /"prisma-core"/g, '"' + slug + '"' ],

		// Hyphenated identifiers (CSS classes, file refs).
		[ /prisma-core-/g, slug + '-' ],

		// Short CSS prefix.
		[ /\bsi-/g, prefix + '-' ],

		// Remaining display text.
		[ /Prisma Core/g, displayName ],
		[ /prisma-core/g, slug ],
		[ /PRISMA_CORE/g, constPrefix ],

		// ── Phase 3: Restore plugin tokens ──
		[ /\{\{__PC_CONST__\}\}/g, 'PRISMA_COMPANION_' ],
		[ /\{\{__PC_CLASS__\}\}/g, 'Prisma_Companion_' ],
		[ /\{\{__PC_FUNC__\}\}/g, 'prisma_companion_' ],
		[ /\{\{__PC_NAME__\}\}/g, 'prisma_companion' ],
		[ /\{\{__PC_SLUG__\}\}/g, 'prisma-companion' ],
	];

	// ── Helper: walk directory tree ──

	function walkDir( dir ) {
		const results = [];
		const entries = fs.readdirSync( dir, { withFileTypes: true } );
		for ( const entry of entries ) {
			if ( excludedDirs.includes( entry.name ) ) {
				continue;
			}
			const full = path.join( dir, entry.name );
			if ( entry.isDirectory() ) {
				results.push( ...walkDir( full ) );
			} else if ( extensions.some( ( ext ) => entry.name.endsWith( ext ) ) ) {
				results.push( full );
			}
		}
		return results;
	}

	// ── 1. Replace file contents (theme only) ──

	let count = 0;
	const files = walkDir( themeDir );
	for ( const filePath of files ) {
		let content = fs.readFileSync( filePath, 'utf8' );
		const original = content;
		for ( const [ search, replacement ] of replacements ) {
			content = content.replace( search, replacement );
		}
		if ( content !== original ) {
			fs.writeFileSync( filePath, content );
			count++;
		}
	}
	console.log( 'Content: ' + count + ' files updated.' );

	// ── 2. Rename files and directories ──

	function collectRenames( dir ) {
		const results = [];
		const entries = fs.readdirSync( dir, { withFileTypes: true } );
		for ( const entry of entries ) {
			if ( excludedDirs.includes( entry.name ) ) {
				continue;
			}
			const full = path.join( dir, entry.name );
			if ( entry.isDirectory() ) {
				results.push( ...collectRenames( full ) );
			}
			if ( entry.name.includes( 'prisma-core' ) ) {
				results.push( {
					fullPath: full,
					dir: dir,
					name: entry.name,
					isDir: entry.isDirectory(),
				} );
			}
		}
		return results;
	}

	const entries = collectRenames( themeDir );
	let renamed = 0;

	// Files first.
	entries
		.filter( ( e ) => ! e.isDir )
		.forEach( ( e ) => {
			const newName = e.name.replace( /prisma-core/g, slug );
			fs.renameSync( e.fullPath, path.join( e.dir, newName ) );
			renamed++;
		} );

	// Directories deepest-first.
	entries
		.filter( ( e ) => e.isDir )
		.sort( ( a, b ) => b.fullPath.length - a.fullPath.length )
		.forEach( ( e ) => {
			const newName = e.name.replace( /prisma-core/g, slug );
			const newPath = path.join( path.dirname( e.fullPath ), newName );
			if ( ! fs.existsSync( newPath ) ) {
				fs.renameSync( e.fullPath, newPath );
				renamed++;
			}
		} );

	console.log( 'Renamed: ' + renamed + ' files/dirs.' );

	// ── 3. Post-rename fixups ──

	// Restore migration class backwards-compat constants.
	const migrationFile = path.join(
		themeDir, 'inc', 'core', 'class-' + slug + '-migration.php'
	);
	if ( fs.existsSync( migrationFile ) ) {
		let mc = fs.readFileSync( migrationFile, 'utf8' );
		mc = mc.replace(
			"const OLD_SLUG = '" + slug + "'",
			"const OLD_SLUG = 'prisma-core'"
		);
		mc = mc.replace(
			"const OLD_PREFIX = '" + funcPrefix + "_'",
			"const OLD_PREFIX = 'prisma_core_'"
		);
		fs.writeFileSync( migrationFile, mc );
		console.log( 'Fixed: migration class OLD_SLUG/OLD_PREFIX restored.' );
	}

	console.log( '\nDone! Review changes with: git diff' );
	console.log( 'Note: prisma-companion plugin was NOT renamed — do that separately.' );
	cb();
}

// ─── Exports ──────────────────────────────────────────────────────────

exports.build = build;
exports.watch = series( build, watchFiles );
exports.readme = readme;
exports[ 'version-bump' ] = series( versionBump, readme );
exports.rename = themeRename;
exports.default = build;

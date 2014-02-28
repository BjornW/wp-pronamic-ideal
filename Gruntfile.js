module.exports = function( grunt ) {
	// Project configuration.
	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),
		phplint: {
			options: {
				phpArgs: {
					'-lf': null
				}
			},
			all: [ 'classes/**/*.php' ]
		},
		phpunit: {
			classes: {},
			options: {
				configuration: 'phpunit.xml'
			}
		},
		jshint: {
			files: ['Gruntfile.js', 'admin/js/*.js' ],
			options: {
				// options here to override JSHint defaults
				globals: {
					jQuery: true,
					console: true,
					module: true,
					document: true
				}
			}
		},
		checkwpversion: {
			options: {
				readme: 'readme.txt',
				plugin: 'pronamic-ideal.php',
			},
			check: {
				version1: 'plugin',
				version2: 'readme',
				compare: '=='
			},
			check2: {
				version1: 'plugin',
				version2: '<%= pkg.version %>',
				compare: '=='
			}
		},
		makepot: {
			target: {
				options: {
					cwd: '',
					domainPath: 'languages',
					type: 'wp-plugin'
				}
			}
		}
	} );

	grunt.loadNpmTasks( 'grunt-phplint' );
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-phpunit' );
	grunt.loadNpmTasks( 'grunt-checkwpversion' );
	grunt.loadNpmTasks( 'grunt-wp-i18n' );

	// Default task(s).
	grunt.registerTask( 'default', [ 'jshint', 'phplint', 'phpunit', 'checkwpversion', 'makepot' ] );
};
'use strict';
module.exports = function(grunt) {


	// load all tasks
	require('load-grunt-tasks')(grunt, {scope: 'devDependencies'});

    grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
	    makepot: {
	        target: {
	            options: {
	                domainPath: '/translations/', // Where to save the POT file.
	                potFilename: 'wp-typography.pot', // Name of the POT file.
	                type: 'wp-plugin',
	                exclude: ['build/.*'],
	                updateTimestamp: false,
	                updatePoFiles: true
	            }
	        }
	    },
	    phpunit: {
	        default: {
	            options: {
	            	testsuite: 'wpTypography',
	            }
	        },
	        coverage: {
	            options: {
	            	testsuite: 'wpTypography',
	            	coverageHtml: 'tests/coverage/',
	            }
	        },   
	        options: {
	            colors: true,
	            configuration: 'phpunit.xml',
	        }
	    },
		copy: {
			main: {
				files:[
					{expand: true, nonull: true, src: ['readme.txt','*.php'], dest: 'build/'},
					{expand: true, nonull: true, src: ['includes/**','php-typography/*.php','php-typography/lang/*.php','php-typography/diacritics/*.php','admin/**','vendor/**'], dest: 'build/'},
				],
			}
		},
	    clean: {
	    	  build: ["build/*"]//,
	    },		
	    wp_deploy: {
            options: {
                plugin_slug: 'wp-typography',
                // svn_user: 'your-wp-repo-username',  
                build_dir: 'build', //relative path to your build directory
                assets_dir: 'wp-assets', //relative path to your assets directory (optional).
                max_buffer: 1024 * 1024
            },
            release: {
            	// nothing
	        },
	        trunk: {
	        	options: {
	            	deploy_trunk: true,
	            	deploy_assets: true,
	            	deploy_release: false,
	        	}
            },
            assets: {
            	options: { 
            		deploy_assets: true,
            		deploy_trunk: false,
            		deploy_release: false,
            	}
            }            
	    },
        sass: {
            dist: {
                options: {
                    style: 'compressed',
                    sourcemap: 'none'
                },
                files: [ { expand: true,
		                   cwd: 'admin/scss',
		                   src: [ '*.scss' ],
		                   dest: 'build/admin/css',
		                   ext: '.min.css' },
	                     { expand: true,
		                   cwd: 'public/scss',
		                   src: [ '*.scss' ],
		                   dest: 'build/public/css',
		                   ext: '.min.css' } ]
            },
            dev: {
                options: {
                    style: 'expanded',
                    sourcemap: 'none'
                },
                files: [ { expand: true,
		                   cwd: 'admin/scss',
		                   src: [ '*.scss' ],
		                   dest: 'admin/css',
		                   ext: '.css' },
	                     { expand: true,
		                   cwd: 'public/scss',
		                   src: [ '*.scss' ],
		                   dest: 'public/css',
		                   ext: '.css' } ]
            }
        },
        curl: {
        	'update-iana': {
        		src: 'https://data.iana.org/TLD/tlds-alpha-by-domain.txt',
        		dest: 'vendor/IANA/tlds-alpha-by-domain.txt'
        	}
        },
        regex_extract: {
        	options: {
        		
        	},
        	language_names: {
        		options: {
        			regex: 'Language\\s*=\\s*.*(("|\')[\\w() ]+\\2)',
        			modifiers: 'g',
        			output: '<?php __( $1, \'wp-typography\' ); ?>',
        			verbose: false,
        			includePath: false
        		},
            	files: {
            		
		            "php-typography/_language_names.php": [ 'php-typography/lang/*.php', 'php-typography/diacritics/*.php' ],
            	}
        	}
        },
	});

	grunt.registerTask( 'build', [
//		'wp_readme_to_markdown',
		'clean:build',
		'regex_extract:language_names',
		'copy',
		'sass:dist'
  	]);

  	grunt.registerTask('deploy', [
 	    'phpunit:default',
		'clean:build',
		'regex_extract:language_names',
  		'copy',
		'sass:dist',
  		'wp_deploy:release'
  	]);
  	grunt.registerTask('trunk', [
  	                     	    'phpunit:default',
  	                    		'clean:build',
  	                      		'regex_extract:language_names',
  	                      		'copy',
  	                    		'sass:dist',
  	                      		'wp_deploy:trunk'
  	]);
  	grunt.registerTask('assets', [
  	                    		'clean:build',
  	                      		'copy',
  	                      		'wp_deploy:assets'
	]);	
  	grunt.registerTask('iana', [
    	                    		'curl:update-iana',
  	]);	
  	
	grunt.registerTask( 'default', [
	    'phpunit:default',
		'regex_extract:language_names',
		'makepot',
		'sass:dev'
    ]);
};

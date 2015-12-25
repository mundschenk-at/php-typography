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
	                updateTimestamp: false
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
					{expand: true, nonull: true, src: ['includes/**','php-typography/**','admin/**','vendor/**'], dest: 'build/'},
				],
			}
		},
	    clean: {
	    	  build: ["build/*"]//,
	    },		
	    wp_deploy: {
	        deploy: { 
	            options: {
	                plugin_slug: 'wp-typography',
	                // svn_user: 'your-wp-repo-username',  
	                build_dir: 'build', //relative path to your build directory
	                assets_dir: 'wp-assets' //relative path to your assets directory (optional).
	            },
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
        }
	});

	grunt.registerTask( 'build', [
//		'wp_readme_to_markdown',
		'clean:build',
		'copy',
		'sass:dist'
  	]);

  	grunt.registerTask('deploy' ,[
 	    'phpunit:default',
//  		'wp_readme_to_markdown',
		'clean:build',
  		'copy',
		'sass:dist',
  		'wp_deploy'
  	]);

	grunt.registerTask( 'default', [
	    'phpunit:default',
		'makepot',
		'sass:dev'
    ]);
};

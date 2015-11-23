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
	        classes: {
	            options: {
	            	testsuite: 'wpTypography',
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
					{expand: true, nonull: true, src: ['php-typography/**','templates/**','translations/**,vendor/**'], dest: 'build/'},
				],
			}
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
	    }
	});

	grunt.registerTask( 'build', [
//		'wp_readme_to_markdown',
		'copy',
  	]);

  	grunt.registerTask('deploy' ,[
 	    'phpunit',
//  		'wp_readme_to_markdown',
  		'copy',
  		'wp_deploy'
  	]);

	grunt.registerTask( 'default', [
	    'phpunit',
		'makepot',
    ]);
};
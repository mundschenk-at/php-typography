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
	                updateTimestamp: true
	            }
	        }
	    }
	});

	grunt.registerTask( 'default', [
		'makepot',
    ]);

};

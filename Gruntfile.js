'use strict';
module.exports = function(grunt) {

    // load all tasks
    require('load-grunt-tasks')(grunt, {
        scope: 'devDependencies'
    });

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        shell: {
            update_html5: {
                tmpDir: 'vendor/tmp',
                sourceDir: '<%= shell.update_html5.tmpDir %>/src',
                targetDir: 'vendor/Masterminds',
                repository: 'https://github.com/Masterminds/html5-php.git',
                command: [
                    'mkdir -p <%= shell.update_html5.tmpDir %>',
                    'git clone <%= shell.update_html5.repository %> <%= shell.update_html5.tmpDir %>',
                    'cp -a <%= shell.update_html5.sourceDir %>/* <%= shell.update_html5.targetDir %>',
                    'rm -rf <%= shell.update_html5.tmpDir %>' // cleanup
                ].join(' && ')
            },

            update_patterns: {
                targetDir: 'php-typography/lang',
                command: (function() {
                    var cli = [];
                    grunt.file.readJSON('php-typography/bin/patterns.json').list.forEach(function(element) {
                        cli.push('php php-typography/bin/pattern2json.php -l "' + element.name + '" -f ' + element.url + ' > <%= shell.update_patterns.targetDir %>/' + element.short + '.json');
                    });

                    return cli;
                })().join(' && ')
            }
        },

        jshint: {
            files: [
                'js/**/*.js',
            ],
            options: {
                expr: true,
                globals: {
                    jQuery: true,
                    console: true,
                    module: true,
                    document: true
                }
            }
        },

        jscs: {
            src: [
                'js/**/*.js'
            ],
            options: {}
        },

	    phpcs: {
	        plugin: {
	            src: ['includes/**/*.php', 'php-typography/**/*.php', 'admin/**/*.php', 'tests/**/*.php']
	        },
	        options: {
	        	bin: '/usr/local/opt/php-code-sniffer@2.9/bin/phpcs -p -s -v -n --ignore=php-typography/_language_names.php --ignore=tests/perf.php',
	            standard: './phpcs.xml'
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
                files: [{
                    expand: true,
                    nonull: true,
                    src: [
                        'readme.txt',
                        'CHANGELOG.md',
                        '*.php',
                        'includes/**',
                        'php-typography/*.php',
                        'php-typography/lang/*.json',
                        'php-typography/diacritics/*.json',
                        'admin/**',
                        'vendor/**',
                        'js/**',
                    ],
                    dest: 'build/'
                }],
            }
        },

        clean: {
            build: ["build/*"] //,
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
                    deploy_tag: false,
                }
            },
            assets: {
                options: {
                    deploy_trunk: false,
                    deploy_tag: false,
                }
            }
        },

        delegate: {
            sass: {
                src: ['<%= sass.dev.files.src %>**/*.scss'],
                dest: '<%= sass.dev.files.dest %>'
            }
        },

        sass: {
            dist: {
                options: {
                    outputStyle: 'compressed',
                    sourceComments: false,
                    sourcemap: 'none'
                },
                files: [{
                        expand: true,
                        cwd: 'admin/scss',
                        src: ['*.scss'],
                        dest: 'build/admin/css',
                        ext: '.min.css'
                    },
                    {
                        expand: true,
                        cwd: 'public/scss',
                        src: ['*.scss'],
                        dest: 'build/public/css',
                        ext: '.min.css'
                    }
                ]
            },
            dev: {
                options: {
                    outputStyle: 'expanded',
                    sourceComments: false,
                    sourceMapEmbed: true,
                },
                files: [{
                        expand: true,
                        cwd: 'admin/scss',
                        src: ['*.scss'],
                        dest: 'admin/css',
                        ext: '.css'
                    },
                    {
                        expand: true,
                        cwd: 'public/scss',
                        src: ['*.scss'],
                        dest: 'public/css',
                        ext: '.css'
                    }
                ]
            }
        },

        postcss: {
            options: {
                map: true, // inline sourcemaps.
                processors: [
                    require('autoprefixer')({
                        browsers: ['>1%', 'last 2 versions', 'IE 9', 'IE 10']
                    }) // add vendor prefixes
                ]
            },
            dev: {
                files: [{
                    expand: true,
                    cwd: 'admin/css',
                    src: ['**/*.css', '!default-styles.css'],
                    dest: 'admin/css',
                    ext: '.css'
                }]
            },
            dev_default_styles: {
                files: [{
                    expand: true,
                    cwd: 'admin/css',
                    src: ['default-styles.css'],
                    dest: 'admin/css',
                    ext: '.css'
                }],
                options: {
                    map: false,
                }
            },
            dist: {
                files: [{
                    expand: true,
                    cwd: 'build/admin/css',
                    src: ['**/*.css'],
                    dest: 'build/admin/css',
                    ext: '.css'
                }]
            }
        },

        // uglify targets are dynamically generated by the minify task
        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> <%= ugtargets[grunt.task.current.target].filename %> <%= grunt.template.today("yyyy-mm-dd h:MM:ss TT") %> */\n',
                report: 'min',
            },
        },

        minify: {
            dist: {
                files: grunt.file.expandMapping(['js/**/*.js', '!js/**/*min.js'], '', {
                    rename: function(destBase, destPath) {
                        return destBase + destPath.replace('.js', '.min.js');
                    }
                })
            },
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
                    regex: '"language"\\s*:\\s*.*("|\')([\\w() ]+)\\1',
                    modifiers: 'g',
                    output: "<?php _x( '$2', 'language name', 'wp-typography' ); ?>",
                    verbose: false,
                    includePath: false
                },
                files: {

                    "php-typography/_language_names.php": ['php-typography/lang/*.json', 'php-typography/diacritics/*.json'],
                }
            }
        },
    });

    // update various components
    grunt.registerTask('update:iana', ['curl:update-iana']);
    grunt.registerTask('update:html5', ['shell:update_html5']);
    grunt.registerTask('update:patterns', ['shell:update_patterns']);

    // delegate stuff
    grunt.registerTask('delegate', function() {
        grunt.task.run(this.args.join(':'));
    });

    // dynamically generate uglify targets
    grunt.registerMultiTask('minify', function() {
        this.files.forEach(function(file) {
            var path = file.src[0],
                target = path.match(/([^.]*)\.js/)[1];

            // store some information about this file in config
            grunt.config('ugtargets.' + target, {
                path: path,
                filename: path.split('/').pop()
            });

            // create and run an uglify target for this file
            grunt.config('uglify.' + target + '.files', [{
                src: [path],
                dest: path.replace(/^(.*)\.js$/, '$1.min.js')
            }]);
            grunt.task.run('uglify:' + target);
        });
    });

    grunt.registerTask('build', [
        //		'wp_readme_to_markdown',
        'clean:build',
        'regex_extract:language_names',
        'copy',
        'newer:delegate:sass:dist',
        'newer:postcss:dist',
        'newer:minify'
    ]);

    grunt.registerTask('deploy', [
        'phpunit:default',
        'phpcs',
        'jscs',
        'build',
        'wp_deploy:release'
    ]);
    grunt.registerTask('trunk', [
        'phpunit:default',
        'phpcs',
        'build',
        'wp_deploy:trunk'
    ]);
    grunt.registerTask('assets', [
        'clean:build',
        'copy',
        'wp_deploy:assets'
    ]);

    grunt.registerTask('default', [
        //'phpunit:default',
        'phpcs',
        'jscs',
        'regex_extract:language_names',
        'newer:delegate:sass:dev',
        'newer:postcss:dev',
        'newer:postcss:dev_default_styles',
        'newer:minify'
    ]);
};

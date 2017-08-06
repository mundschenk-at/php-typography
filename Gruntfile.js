'use strict';
module.exports = function (grunt) {

    // load all tasks
    require('load-grunt-tasks')(grunt, {
        scope: 'devDependencies',
      });

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        shell: {
            update_patterns: {
                targetDir: 'php-typography/lang',
                command: (function () {
                    var cli = [];
                    grunt.file.readJSON('php-typography/bin/patterns.json').list.forEach(
                        function (element) {
                            cli.push('php php-typography/bin/pattern2json.php -l "'
                                + element.name + '" -f ' + element.url
                                + ' > <%= shell.update_patterns.targetDir %>/'
                                + element.short + '.json');
                          });

                    return cli;
                  })().join(' && '),
              },
          },

        phpcs: {
            plugin: {
              src: ['php-typography/**/*.php', 'tests/**/*.php'],
            },
            options: {
                bin: '/usr/local/opt/php-code-sniffer@2.9/bin/phpcs -p -s -v -n --ignore=php-typography/_language_names.php --ignore=tests/perf.php',
                standard: './phpcs.xml',
              },
          },

        phpunit: {
            default: {
                options: {
                    testsuite: 'wpTypography',
                  },
              },
            coverage: {
                options: {
                    testsuite: 'wpTypography',
                    coverageHtml: 'tests/coverage/',
                  },
              },
            options: {
                colors: true,
                configuration: 'phpunit.xml',
              },
          },

        regex_extract: {
          options: {},
          language_names: {
              options: {
                  regex: '"language"\\s*:\\s*.*("|\')([\\w() ]+)\\1',
                  modifiers: 'g',
                  output: "<?php _x( '$2', 'language name', 'wp-typography' ); ?>",
                  verbose: false,
                  includePath: false,
                },
              files: {
                  'php-typography/_language_names.php':
                    ['php-typography/lang/*.json', 'php-typography/diacritics/*.json'],
                },
            },
        },
      });

    // update various components
    grunt.registerTask('update:patterns', ['shell:update_patterns']);

    grunt.registerTask('default', [
        'phpunit:default',
        'phpcs',
    ]);
  };

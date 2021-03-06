module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        watch: {
            js: {
                files: ['static/js/sub/**'],
                tasks: ['uglify']
            },
            css: {
                files: ['static/assets/style/sub/**'],
                tasks: ['less']
            }
        },
        less: {
            development: {
                options: {
                    paths: ["static/assets/style"]
                },
                files: {
                    "static/assets/style/style.css": "static/assets/style/style.less"
                }
            },
            production: {
                options: {
                    paths: ["static/assets/style"],
                    yuicompress: true
                },
                files: {
                    "static/assets/style/style.min.css": "static/assets/style/style.less"
                }
            }
        },
        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n' //添加banner
            },
            main: {
                options: {
                    mangle: true, //混淆变量名
                    preserveComments: false, //删除所有注释
                    footer: '\n/*! <%= pkg.name %> 最后修改于： <%= grunt.template.today("yyyy-mm-dd") %> */'
                },
                files: {
                    'static/js/libs.min.js': ['static/libs/jquery/jquery-1.11.1.js', 'static/libs/jquery-migrate/jquery-migrate-1.2.1.js', 'static/libs/bootstrap/js/bootstrap.js', 'static/libs/jquery-cookie/jquery.cookie.js', 'static/libs/handlebars/handlebars-v1.3.0.js', 'static/libs/handlebars/handlebars-helper.js', 'static/libs/highcharts/highcharts.js', 'static/libs/nprogress/nprogress.js', 'static/libs/jquery-html5Validate/jquery-html5Validate.js'],
                    'static/js/script.min.js': ['static/js/sub/base.js', 'static/js/sub/index.js', 'static/js/sub/monitor.js'],
                    'static/js/login.min.js': ['static/libs/matter/matter.min.js', 'static/js/sub/login.js'],
                    'static/js/check.min.js': ['static/js/sub/check.js']
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('default', ['less', 'uglify', 'watch']);
}
<?php
namespace Deployer;

require 'recipe/symfony4.php';

// Project name
set('application', 'salve-bolt');

// Project repository
set('repository', 'https://github.com/andersbjorkland/salve-bolt.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false);
set('ssh_multiplexing', false);


// Shared files/dirs between deploys 
add('shared_files', ['config/extensions/andersbjorkland-matomoanalyticsextension.yaml', 'config/extensions/andersbjorkland-matomoanalyticsextension_local.yaml']);
add('shared_dirs', []);

// Writable dirs by web server 
add('writable_dirs', []);
set('allow_anonymous_stats', false);

// Hosts

host('salve')
    ->roles('app')
    ->set('deploy_path', '~/bolt');
    //->set('deploy_path', '/customers/f/9/d/salve.digital/httpd.private/bolt');

// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

task('copy:public', function() {
    run('cp -R {{release_path}}/public/*  /www && cp -R {{release_path}}/public/.[^.]* /www');
});

task('salve:clear', function() {
    run('cd {{release_path}} && php bin/console cache:clear');
});

task('salve:demo', function() {
    $result = run('cd ~/bolt && pwd');
    writeln($result);
});

task('tailwind:build', function() {
   run('yarn encore production');
})->local();

task('upload:build', function() {
    upload("public/build/", '{{release_path}}/public/build/');
});

task('symlink:public', function() {
    run('ln -s {{release_path}}/public/*  /www && ln -s {{release_path}}/public/.[^.]* /www');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
after('deploy:vendors', 'upload:build');
after('upload:build', 'copy:public');


// Migrate database before symlink new release.

before('deploy:symlink', 'database:migrate');


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
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server 
add('writable_dirs', []);
set('allow_anonymous_stats', false);

// Hosts

host('salve')
    ->roles('app')
    //->set('deploy_path', '/home/salve.digital/bolt');
    ->set('deploy_path', '/customers/f/9/d/salve.digital/httpd.private/bolt');

// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

task('copy:public', function() {
    run('cp -R {{release_path}}/public/*  /www && cp -R {{release_path}}/public/.[^.]* /www');
});

task('salve:clear', function() {
    run('php {{release_path}}/bin/console  cache:clear');
});

task('tailwind:build', function() {
   run('yarn encore production');
})->local();

task('upload:build', function() {
    //$result = run('ls ' . __DIR__ . '/public/build');
    $path = __DIR__ . '\public\build/';
    $path = str_replace('\\', '/', $path);
    //run('mkdir {{release_path}}/public/build');
    $deployPath = get('deploy_path');
    //writeln('{{release_path}}/public/build/');
    //writeln($deployPath);
    upload("public/build/", '{{release_path}}/public/build/');
});

task('symlink:public', function() {
    run('ln -s {{release_path}}/public/*  /www && ln -s {{release_path}}/public/.[^.]* /www');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
after('deploy:vendors', 'copy:public');

// Migrate database before symlink new release.

before('deploy:symlink', 'database:migrate');


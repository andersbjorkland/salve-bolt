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

host('horseish')
    ->roles('app')
    ->set('deploy_path', '/customers/b/b/f/horseish.online/httpd.private/bolt');

// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

task('copy:public', function() {
    run('cp -R {{release_path}}/public/*  /www && cp -R {{release_path}}/public/.[^.]* /www');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
after('deploy:unlock', 'copy:public');

// Migrate database before symlink new release.

before('deploy:symlink', 'database:migrate');


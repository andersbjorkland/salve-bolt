<?php
namespace Deployer;

require 'recipe/symfony4.php';

// Project name
set('application', 'salve');

// Project repository
set('repository', 'https://github.com/andersbjorkland/salve-bolt.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false);
set('ssh_multiplexing', false);

set('composer_options', '{{composer_action}} --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader');


// Shared files/dirs between deploys 
add('shared_files', ['.env', 'config/extensions/andersbjorkland-matomoanalyticsextension.yaml']);
add('shared_dirs', []);

// Writable dirs by web server 
add('writable_dirs', []);
set('allow_anonymous_stats', false);

// Hosts

host('hostserver')
    ->set('deploy_path', '~/{{application}}');
    
// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

/* Is used when symlink from public folder doesn't behave as expected.
 * The downside of using it this way is that it doesn't remove files no longer present in git repo.
 * Assumed public directory is /www
 */
task('copy:public', function() {
    run('cp -R {{release_path}}/public/*  /www && cp -R {{release_path}}/public/.[^.]* /www');
});

task('cache:clear', function() {
    run('{{bin/php}} {{bin/console}} cache:clear {{console_options}}');
});
/* Uploads built assets from local to remote. Requires rsync.
 * Useful when you use Symfony encore/webpack and remote machine doesn't support npm/yarn.
 */
task('upload:build', function() {
    upload("public/build/", '{{release_path}}/public/build/');
});

task('tailwind:build', function() {
    run('yarn encore production');
})->local();

task('initialize', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:unlock',
    'cleanup',
]);

task('mydeploy', [
    'deploy:unlock',
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'tailwind:build',
    'upload:build',
    'deploy:cache:clear',
    'deploy:symlink',
    'copy:public',
    'deploy:unlock',
    'cleanup',
]);

fail('deploy:lock', 'deploy:unlock');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

after('mydeploy', 'success');


// Migrate database before symlink new release.

//before('deploy:symlink', 'database:migrate');


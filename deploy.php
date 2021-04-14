<?php
namespace Deployer;

/**
 * Deploy recipe for Bolt 4 on a shared hosting service.
 * I've used this recipe for a project on one.com.
 * This will mean that the Bolt project is split up in the following way:
 *
 *   / [remote root]
 *   |--- home
 *   |     |
 *   |    user --- bolt [project]
 *   |                  |--- release [symlink to latest release/aka project root]
 *   |                  |--- releases
 *   |                  |--- shared
 *   |
 *   |--- www [public]
 *  ...     |--- index.php [needs configure to find project root]
 *         ...
 *
 * For an example of how to configure index.php to work on shared hosting, see this:
 * https://github.com/andersbjorkland/salve-bolt/blob/main/public/index.php
 *
 * When you have the basic structure for your remote it may be a good idea to set up a .local.env file in the shared
 * folder with the following instructions:
 *
 * APP_ENV=prod
 * APP_DEBUG=0
 * DATABASE_URL="mysql://db_usr:db_password@example.com.mysql:3306/db_name"
 * MAILER_DSN="smtp://mailer@example.com:sensitivepassword@send.example.com:465"
 * PUBLIC_DIR=/../../../../httpd.www/
 *
 * NOTE: Running this has it executing a cache:clear instruction. For me it results in a database error.
 * This is probably due to it not being able to read the remote env when executing from local machine.
 * To finalize the deployment I SSH into the remote and go into ~/bolt/release and run php bin/console cache:clear.
 */

// Github place of recipes: https://github.com/deployphp/deployer/tree/6.x/recipe
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
/* I've edited a ssh config file on local with name corresponding to
 * host name. Here's an example of the file ~/.ssh/config
 *
 * Host remote_domain
 *      HostName ssh.domain.com
 *      User domain.com
 *      IdentityFile /c/Users/user1/.ssh/id_rsa
 */
host('salve')
    ->roles('app')
    ->set('deploy_path', '~/bolt');


// Tasks

// If your server supports public symlinks. Assumed public directory is /www
task('symlink:public', function() {
    run('ln -s {{release_path}}/public/*  /www && ln -s {{release_path}}/public/.[^.]* /www');
});

/* Is used when symlink from public folder doesn't behave as expected.
 * The downside of using it this way is that it doesn't remove files no longer present in git repo.
 * Assumed public directory is /www
 */
task('copy:public', function() {
    run('cp -R {{release_path}}/public/*  /www && cp -R {{release_path}}/public/.[^.]* /www');
});

// In case you have tailwind setup with Symfony encore and want to do a local build which you'll upload.
task('tailwind:build', function() {
   run('yarn encore production');
})->local();

/* Uploads built assets from local to remote. Requires rsync.
 * Useful when you use Symfony encore/webpack and remote machine doesn't support npm/yarn.
 */
task('upload:build', function() {
    upload("public/build/", '{{release_path}}/public/build/');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

/* Instruct when you want to upload your built assets.
 * The used points here is because cache:clear is breaking for me,
 * so I do upload after all other reloads. The upload is then
 * followed by copying public files to public directory on remote.
 * [This is how I do on a shared hosting (one.com)]
 */
after('deploy:unlock', 'upload:build');
after('upload:build', 'copy:public');


// Migrate database before symlink new release.
before('deploy:symlink', 'database:migrate');


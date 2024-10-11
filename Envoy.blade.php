@servers(['web' => ['pnchangewr@91.225.201.50']])

@setup
    $releases_dir = $server_dir . '/releases/' . $remove_dir . '/src';
    $releases_git_dir = $server_dir . '/releases/' . $remove_dir . '/.git';
    $app_dir = $server_dir . '/app';
@endsetup

@story('deploy')
    preparation
    run_composer
    update_symlinks
    artisan_command
    remove_old_releases
    copy_react_build
@endstory

@task('preparation')
    echo 'Move Folder'
    rm -rf {{$releases_git_dir}}
    cd {{$server_dir}}
    mkdir -p storage
@endtask

@task('run_composer')
    echo "composer install"
    cd {{ $server_dir }}
    php73 -d memory_limit=-1 composer.phar install --prefer-dist --no-scripts -q -o -d {{ $releases_dir }}
@endtask

@task('update_symlinks')
    echo "Linking storage directory"
    rm -rf {{$releases_dir}}/storage
    ln -s {{$server_dir}}/storage {{$releases_dir}}/storage

    echo 'Linking .env file'
    ln -s {{$server_dir}}/.env {{$releases_dir }}/.env

    echo 'Linking current release'
    rm -rf {{$app_dir}}
    ln -s {{ $releases_dir }} {{ $app_dir }}
@endtask

@task('artisan_command')
    echo 'Artisan Command'
    php73 {{ $releases_dir }}/artisan view:clear --quiet
    php73 {{ $releases_dir }}/artisan cache:clear --quiet
    php73 {{ $releases_dir }}/artisan config:clear --quiet
    php73 {{ $releases_dir }}/artisan migrate --force
    php73 {{ $releases_dir }}/artisan storage:link
    php73 {{ $releases_dir }}/artisan queue:restart --quiet
    php73 {{ $releases_dir }}/artisan route:clear --quiet
    echo "Cache cleared"
@endtask

@task('remove_old_releases')
    echo 'Remove OLD release'
    cd {{$server_dir}}/releases
    rm -rf `ls -t | tail -n +2`
@endtask

# Isn't working properly
# @task('copy_react_build')
#     echo '*********Start react build*********'
#     cd www/pnchangewr.groupbwt.com/pnchangewr/app/public/
#     rm -r react-app
#     git clone http://Igor_Nosatov:e5s6z7x8c9@gitlab.groupbwt.com/root/cwr-front.git
#     cd cwr-front
#     npm -v
#     node -v
#     npm install
#     npm run build
#     cd ..
#     cp -r cwr-front/build react-app
#     rm -r cwr-front
#     ls -l
#     echo '*********End react build*********'
# @endtask

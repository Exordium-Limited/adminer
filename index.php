<?php

namespace docker {
    function adminer_object()
    {
        require_once('plugins/plugin.php');

        class Adminer extends \AdminerPlugin
        {
            function _callParent($function, $args)
            {
                if ($function === 'loginForm') {
                    ob_start();
                    $return = \Adminer::loginForm();
                    $form = ob_get_clean();

                    // Set default values via env vars.
                    $defaultDbDriver = getenv('ADMINER_DEFAULT_DB_DRIVER') ?: 'server';
                    $defaultDbHost = getenv('ADMINER_DEFAULT_DB_HOST') ?: '';
                    $defaultDb = getenv('ADMINER_DEFAULT_DB_NAME') ?: '';
                    $defaultUser = getenv('ADMINER_DEFAULT_DB_USER') ?: '';
                    $defaultPasswd = getenv('ADMINER_DEFAULT_DB_PASSWD') ?: '';

                    $defaultDbDriver = $defaultDbDriver == 'mysql' ? 'server' : $defaultDbDriver;

                    echo str_replace(
                        [
                            'name="auth[server]" value="" title="hostname[:port]"',
                            'value="' . $defaultDbDriver . '"',
                            'selected="">MySQL',
                            'name="auth[db]" value=""',
                            'name="auth[password]"',
                            'name="auth[username]" id="username" value=""'
                        ],
                        [
                            'name="auth[server]" value="' . $defaultDbHost . '" title="hostname[:port]"',
                            'value="' . $defaultDbDriver . '" selected="selected"',
                            '>MySQL',
                            'name="auth[db]" value="' . $defaultDb . '"',
                            'name="auth[password]" value="' . $defaultPasswd . '"',
                            'name="auth[username]" id="username" value="' . $defaultUser . '"'
                        ],
                        $form
                    );

                    return $return;
                }

                return parent::_callParent($function, $args);
            }
        }

        $plugins = [];
        foreach (glob('plugins-enabled/*.php') as $plugin) {
            $plugins[] = require($plugin);
        }

        return new Adminer($plugins);
    }
}

namespace {
    if (basename($_SERVER['DOCUMENT_URI'] ?? $_SERVER['REQUEST_URI']) === 'adminer.css' && is_readable('adminer.css')) {
        header('Content-Type: text/css');
        readfile('adminer.css');
        exit;
    }

    function adminer_object()
    {
        return \docker\adminer_object();
    }

    require('adminer.php');
}

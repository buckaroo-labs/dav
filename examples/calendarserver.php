<?php

/*
CalendarServer example
This server features CalDAV support
*/

// settings
date_default_timezone_set('Canada/Eastern');

// If you want to run the SabreDAV server in a custom location (using mod_rewrite for instance)
// You can override the baseUri here.
$baseUri = '/dav/calendarserver.php';

/* Database */
//$pdo = new PDO('sqlite:data/db.sqlite');
$pdo = new PDO('mysql:dbname=sabredav;host=127.0.0.1', 'username', 'password');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Files we need
require_once 'vendor/autoload.php';

// Backends
$authBackend = new Sabre\DAV\Auth\Backend\PDO($pdo);
$calendarBackend = new Sabre\CalDAV\Backend\PDO($pdo);
$principalBackend = new Sabre\DAVACL\PrincipalBackend\PDO($pdo);
$authBackend->setRealm('SabreDAV');

// Directory structure
$tree = [
    new Sabre\CalDAV\Principal\Collection($principalBackend),
    new Sabre\CalDAV\CalendarRoot($principalBackend, $calendarBackend),
];

$server = new Sabre\DAV\Server($tree);

if (isset($baseUri)) {
    $server->setBaseUri($baseUri);
}

/* Server Plugins */
$authPlugin = new Sabre\DAV\Auth\Plugin($authBackend);
$server->addPlugin($authPlugin);

$aclPlugin = new Sabre\DAVACL\Plugin();
$server->addPlugin($aclPlugin);

/* CalDAV support */
$caldavPlugin = new Sabre\CalDAV\Plugin();
$server->addPlugin($caldavPlugin);

/* Calendar subscription support */
$server->addPlugin(
    new Sabre\CalDAV\Subscriptions\Plugin()
);

/* Calendar scheduling support */
$server->addPlugin(
    new Sabre\CalDAV\Schedule\Plugin()
);

/* WebDAV-Sync plugin */
$server->addPlugin(new Sabre\DAV\Sync\Plugin());

/* CalDAV Sharing support */
$server->addPlugin(new Sabre\DAV\Sharing\Plugin());
$server->addPlugin(new Sabre\CalDAV\SharingPlugin());

// Support for html frontend
$browser = new Sabre\DAV\Browser\Plugin();
$server->addPlugin($browser);

// And off we go!
$server->exec();

<?php

// Dependency checking
if(!ClassInfo::exists('CronTab')) {
	$view = new DebugView();
	if(!headers_sent()) header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
	$view->writeHeader();
	$view->writeInfo('Dependency Error', 'The blog syndicator module requires the pseudo cron module.');
	$view->writeParagraph('Please install the <a href="https://github.com/lrc/silverstripe-pseudo-cron">pseudo cron</a> module.');
	$view->writeFooter();
	exit;
}

Object::add_extension('BlogHolder', 'BlogSyndicatorHolderExtension');
Object::add_extension('BlogEntry', 'BlogSyndicatorEntryExtension');
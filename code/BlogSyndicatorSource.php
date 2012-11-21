<?php

/**
 * A source for syndicated data.
 *
 * @author Simon Elvery
 */
class BlogSyndicatorSource extends DataObject {
	
	public static $db = array(
		'Name' => 'Varchar(20)',
		'SourceURL' => 'Varchar(300)',
		'LastUpdate' => 'Int'
	);
	
	public static $has_one = array(
		'BlogHolder' => 'BlogHolder'
	);	
}
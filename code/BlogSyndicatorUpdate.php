<?php

/**
 * Updates syndicated blogs from all sources setup in the database.
 */
class BlogSyndicatorUpdate extends Controller {
	
	private static $excludedFields = array(
		'ClassName',
		'Created', 
		'LastEdited',
		'Version',
		'ID',
		'RecordClassName'
	);
	
	/**
	 * Update all syndicated blog content.
	 */
	public static function run() {
		$sources = DataObject::get('BlogSyndicatorSource');
		
		if (!$sources) return;
		
		foreach ($sources as $source) {
			// Get the source
			$ch = curl_init();
			$url = Controller::join_links($source->SourceURL, $source->LastUpdate);
			
			$options = array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => true
			);
			curl_setopt_array($ch, $options);
			$results = curl_exec($ch);
			$results = Convert::json2obj($results);
			
			
			if ( isset($results->data) && is_array($results->data) ) {
				foreach ($results->data as $entryData) {
					
					if ( isset($entryData->ClassName) && $entryData->ClassName == 'BlogSyndicatorDeletedEntry' ) {
						$entry = DataObject::get_one('BlogEntry', '"SyndicatorSourceID" = \'' . Convert::raw2sql($entryData->EntryID) . "'");
						if ($entry) {
							$entry->deleteFromStage('Live');
							$entry->delete();
						}
					} else {
						
						$entryData->SyndicatorSourceID = $entryData->ID;
						unset($entryData->ID);

						$entry = DataObject::get_one('BlogEntry', '"SyndicatorSourceID" = \'' . Convert::raw2sql($entryData->SyndicatorSourceID) . "'");
						
						// Try getting it from the draft site.
						if ( !$entry ) {
							$entry = Versioned::get_one_by_stage('BlogEntry', 'Stage', '"SyndicatorSourceID" = \'' . Convert::raw2sql($entryData->SyndicatorSourceID) . "'");
						}

						// If there's still no entry create a new one
						if ( !$entry ) {
							$entry = Object::create('BlogEntry', (array) $entryData);
						} 
						
						// If it's not new, update fields.
						if ($entry->ID) {
							$fields = array_diff(array_keys($entry->toMap()), self::$excludedFields);
							foreach ($fields as $field) {
								if (isset($entryData->$field)) {
									$entry->$field = $entryData->$field;
								}
							}
						}

						// Always set some fields.
						$entry->ParentID = $source->BlogHolder()->ID;
						$entry->Syndicated = true;
						$entry->Syndicate = false;
						$entry->Date = $entryData->Date; // For some reason I can't understand Date doesn't seem to always update correctly without this.
						$entry->write();
						
						if ($entryData->Status == 'Published') {
							$entry->doPublish();
						}
						if ($entryData->Status == 'Unpublished') {
							$entry->doUnpublish();
						}
					} 
					
				}
			}
			$source->LastUpdate = time();
			$source->write();
		}
	}
	
}
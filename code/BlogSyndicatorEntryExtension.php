<?php

/**
 * Description of BlogSyndicatorEntryExtension
 *
 * @author Simon
 */
class BlogSyndicatorEntryExtension extends DataObjectDecorator {
	
	public function extraStatics() {
		return array (
			'db' => array(
				'Syndicated' => 'Boolean',
				'Syndicate' => 'Boolean',
				'SyndicatorSourceID' => 'Int'
			),
			'defaults' => array(
				'Syndicate' => true
			)
		);
	}
	
	public function updateCMSFields(FieldSet $fields) {
		if ( $this->owner->Syndicated ) {
			$fields->insertBefore(new LiteralField('Warning', '<p class="message warning">This is a syndicated post and should be edited at the source. Any changes made here will be overridden by changes made at the source.</p>'), 'Title');
		} else {
			$fields->addFieldToTab('Root.Behaviour', new CheckboxField('Syndicate', _t('BlogSyndicatorEntryExtension.SyndicateFieldLabel', 'Allow entry to be syndicated?')));
		}
	}
	
	public function onBeforeDelete() {
		if ( !$this->owner->Syndicated && ($this->owner->Status == 'Unpublished' || $this->owner->IsDeletedFromStage) ) {
			$record = new BlogSyndicatorDeletedEntry();
			$record->EntryID = $this->owner->ID;
			$record->write();
		}
	}
	
	public function requireDefaultRecords() {
		
		// Setup the entry import cron
		$cron = DataObject::get_one('CronJob', "Name = 'Syndicate Blog Entries'");
		if ( !$cron ) {
			CronTab::add(array(
				'Name' => 'Syndicate Blog Entries',
				'Callback' => array('BlogSyndicatorUpdate','run'),
				'Increment' => 600, // 10 mins
				'Description' => 'Update all syndicated blogs.',
				'Notify' => 'admin'
			));
		}
	}
	
}

?>

<?php
/**
 * Represents a single blog which is syndicated from another site.
 *
 * @author Simon Elvery
 */
class BlogSyndicatorHolderExtension extends DataObjectDecorator {
	
	/**
	 * Add extra statics
	 * @return array
	 */
	public function extraStatics() {
		return array(
			'has_many' => array(
				'Sources' => 'BlogSyndicatorSource'
			)
		);
	}
	
	/**
	 * Add syndication fields to the CMS.
	 *
	 * @param FieldSet $fields The form fields.
	 */
	public function updateCMSFields(FieldSet $fields) {
		$fields->addFieldToTab('Root.Content.Syndicated', new DataObjectManager($this->owner, 'Sources', 'BlogSyndicatorSource'));
		$fields->addFieldToTab('Root.Content.Syndicated', new ReadonlyField('SyndicationURL', _t('BlogSyndicatorHolderExtension.SyndicationURLFieldLabel','Syndication URL'), Controller::join_links(Director::absoluteBaseURL(), 'BlogSyndicatorAPI','updated',$this->owner->ID)));
	}
	
}

?>

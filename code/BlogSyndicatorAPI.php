<?php
/**
 * Provides JSON access to blog updates
 *
 * @author Simon
 */
class BlogSyndicatorAPI extends Controller {
	
	const STATUS_OK = 'ok';
	const STATUS_ERR = 'error';
	
	/**
	 * Return blog posts which have been updated and deleted since a given time.
	 */
	public function updated() {
		
		$parent = Convert::raw2sql($this->request->param('ID'));
		
		$since = ($time = $this->request->param('OtherID')) ? date('Y-m-d H:i:s', $time) : false;
		
		if ( $since ) {
			$entries = DataObject::get('BlogEntry', "\"Syndicate\" AND \"ParentID\" = '$parent' AND \"LastEdited\" >= '$since'");
			$deleted = DataObject::get('BlogSyndicatorDeletedEntry', "\"LastEdited\" >= '$since'");
		} else {
			$entries = DataObject::get('BlogEntry', "\"Syndicate\" AND \"ParentID\" = '$parent'");
			$deleted = DataObject::get('BlogSyndicatorDeletedEntry');
		}
		$return = array_merge($this->convert($entries), $this->convert($deleted));
		return $this->output($return, self::STATUS_OK);
	}
	
	/**
	 * Converts a DataObjectSet to an array.
	 * 
	 * @param DataObjectSet $set The set to convert
	 * @return array
	 */
	private function convert(DataObjectSet $set) {
		$ret = array();
		if ($set) {
			foreach ($set as $item) {
				if (method_exists($item, 'toMap')) {
					$ret[] = $item->toMap();
				} else {
					$ret[] = $item;
				}
			}
		}
		return $ret;
	}
	
	
	/**
	 * Adds a wrapper around some data with status and an optional message and returns JSON.
	 * Also sets the content header response type to javascript/json.
	 * 
	 * @param stdClass $data The data to return
	 * @param string $status The status to return.
	 * @param string $message A message to attach to the status
	 */
	private function output($data, $status, $message = null) {
		
		$output = new stdClass();
		
		$output->status = $status;
		if ($message) $output->message = $message;
		if (!is_null($data)) $output->data = $data;
		
		$this->response->addHeader('Content-Type', 'javascript/json');
		
		return Convert::raw2json($output);
		
	}
	
	

}
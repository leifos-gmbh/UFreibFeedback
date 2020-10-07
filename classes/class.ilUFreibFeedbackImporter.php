<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/UFreibFeedback/classes/class.ilObjUFreibFeedback.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/UFreibFeedback/classes/class.ilUFreibFeedbackPlugin.php");

/**
 * Class ilUFreibFeedbackImporter
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUFreibFeedbackImporter extends ilXmlImporter {

	/**
	 * Import xml representation
	 *
	 * @param    string        entity
	 * @param    string        target release
	 * @param    string        id
	 * @return    string        xml string
	 */
	public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping) {
		$xml = simplexml_load_string($a_xml);
		$pl = new ilUFreibFeedbackPlugin();
		$entity = new ilObjUFreibFeedback();
		$entity->setTitle((string) $xml->title." ".$pl->txt("copy"));
		$entity->setDescription((string) $xml->description);
		$entity->setOnline((string) $xml->online);
		$entity->setImportId($a_id);
		$entity->create();
		$new_id = $entity->getId();
		$a_mapping->addMapping("Plugins/UFreibFeedback", "xfrf", $a_id, $new_id);
		return $new_id;
	}
}
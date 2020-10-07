<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUFreibFeedbackPlugin extends ilRepositoryObjectPlugin
{
	const ID = "xfrf";

	// must correspond to the plugin subdirectory
	function getPluginName()
	{
		return "UFreibFeedback";
	}

	protected function uninstallCustom() {
		// TODO: Nothing to do here.
	}

	/**
	 * @inheritdoc
	 */
	public function allowCopy()
	{
		return true;
	}

}
?>
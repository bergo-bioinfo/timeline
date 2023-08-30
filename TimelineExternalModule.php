<?php
namespace InstitutBergonie\TimelineExternalModule;

class TimelineExternalModule extends \ExternalModules\AbstractExternalModule {
	function redcap_module_link_check_display($project_id, $link) {
		return $link;
	}
}

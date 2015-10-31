<?php

/**
 * Description of Support
 *
 * @author Faizan Ayubi
 */
use Framework\Registry as Registry;

class Support extends Manage {
    
	public function tickets() {
		$this->seo(array("title" => "Manage Tickets","view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $tickets = Ticket::all(array("organization_id = ?" => $this->organization->id));
        $view->set("tickets", $tickets);
	}
}

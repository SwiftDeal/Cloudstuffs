<?php

/**
 * Description of Finance
 *
 * @author Faizan Ayubi
 */
use Framework\Registry as Registry;
use Framework\RequestMethods as RequestMethods;

class Finance extends Manage {
    
    /**
     * @before _secure, manageLayout
     */
    public function invoice($payment_id) {
    	$this->seo(array("title" => "Invoices","view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $projects = Service::all(array("organization_id = ?" => $this->organization->id), array("property", "bill_id", "created"), "created", "desc", 10, 1);
        $view->set("projects", $projects);
    }

    /**
     * @before _secure, manageLayout
     */
    public function bills() {
        $this->seo(array("title" => "Bills","view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $services = Service::all(array("organization_id = ?" => $this->organization->id), array("property", "bill_id", "property_id"), "created", "desc", 10, 1);
        $view->set("services", $services);
    }

    /**
     * @before _secure, manageLayout
     */
    public function payments($bill_id) {
    	$this->seo(array("title" => "Payments","view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $payments = Payment::all(array("organization_id = ?" => $this->organization->id, "bill_id = ?" => $bill_id), array("property", "bill_id", "property_id"));
        $view->set("payments", $payments);
    }

    public function convertCurrency($amount=1, $from="USD", $to="INR") {
        $this->noview();
        $session = Registry::get("session");
        $currency = $session->get("currency");
        if(!$currency) {
            $url = "http://www.google.com/finance/converter?a=$amount&from=$from&to=$to"; 
            $request = curl_init(); 
            $timeOut = 0;
            curl_setopt ($request, CURLOPT_URL, $url);
            curl_setopt ($request, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($request, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)"); 
            curl_setopt ($request, CURLOPT_CONNECTTIMEOUT, $timeOut);
            $response = curl_exec($request); 
            curl_close($request);

            $regularExpression     = '#\<span class=bld\>(.+?)\<\/span\>#s';
            preg_match($regularExpression, $response, $finalData);
            
            $currency = explode(" ", strip_tags($finalData[0]));
            $session->set("currency", $currency);
        }

        return $currency[0];
    }
}

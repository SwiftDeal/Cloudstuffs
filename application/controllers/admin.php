<?php

/**
 * Description of admin
 *
 * @author Faizan Ayubi
 */
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;

class Admin extends Auth {

    /**
     * @readwrite
     */
    protected $_organization;

    /**
     * @readwrite
     */
    protected $_member;

    public function render() {
        if ($this->organization) {
            if ($this->actionView) {
                $this->actionView->set("organization", $this->organization);
            }

            if ($this->layoutView) {
                $this->layoutView->set("organization", $this->organization);
            }
        }

        if ($this->member) {
            if ($this->actionView) {
                $this->actionView->set("member", $this->member);
            }

            if ($this->layoutView) {
                $this->layoutView->set("member", $this->member);
            }
        }
        parent::render();
    }

    /**
     * @before _secure, changeLayout, _admin
     */
    public function index() {
        $this->seo(array("title" => "Dashboard", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $now = strftime("%Y-%m-%d", strtotime('now'));
        
        $view->set("now", $now);
    }

    /**
     * Searchs for data and returns result from db
     * @param type $model the data model
     * @param type $property the property of modal
     * @param type $val the value of property
     * @before _secure, changeLayout, _admin
     */
    public function search($model = NULL, $property = NULL, $val = 0, $page = 1, $limit = 10) {
        $this->seo(array("title" => "Search", "keywords" => "admin", "description" => "admin", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $model = RequestMethods::get("model", $model);
        $property = RequestMethods::get("key", $property);
        $val = RequestMethods::get("value", $val);
        $page = RequestMethods::get("page", $page);
        $limit = RequestMethods::get("limit", $limit);
        $sign = RequestMethods::get("sign", "equal");

        $view->set("items", array());
        $view->set("values", array());
        $view->set("model", $model);
        $view->set("models", Shared\Markup::models());
        $view->set("page", $page);
        $view->set("limit", $limit);
        $view->set("property", $property);
        $view->set("val", $val);
        $view->set("sign", $sign);

        if ($model) {
            if ($sign == "like") {
                $where = array("{$property} LIKE ?" => "%{$val}%");
            } else {
                $where = array("{$property} = ?" => $val);
            }

            $objects = $model::all($where, array("*"), "created", "desc", $limit, $page);
            $count = $model::count($where);
            $i = 0;
            if ($objects) {
                foreach ($objects as $object) {
                    $properties = $object->getJsonData();
                    foreach ($properties as $key => $property) {
                        $key = substr($key, 1);
                        $items[$i][$key] = $property;
                        $values[$i][] = $key;
                    }
                    $i++;
                }
                $view->set("items", $items);
                $view->set("values", $values[0]);
                $view->set("count", $count);
                //echo '<pre>', print_r($values[0]), '</pre>';
                $view->set("success", "Total Results : {$count}");
            } else {
                $view->set("success", "No Results Found");
            }
        }
    }

    /**
     * Shows any data info
     * 
     * @before _secure, changeLayout, _admin
     * @param type $model the model to which shhow info
     * @param type $id the id of object model
     */
    public function info($model = NULL, $id = NULL) {
        $this->seo(array("title" => "{$model} info", "keywords" => "admin", "description" => "admin", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        $items = array();
        $values = array();

        $object = $model::first(array("id = ?" => $id));
        $properties = $object->getJsonData();
        foreach ($properties as $key => $property) {
            $key = substr($key, 1);
            if (strpos($key, "_id")) {
                $child = ucfirst(substr($key, 0, -3));
                $childobj = $child::first(array("id = ?" => $object->$key));
                $childproperties = $childobj->getJsonData();
                foreach ($childproperties as $k => $prop) {
                    $k = substr($k, 1);
                    $items[$k] = $prop;
                    $values[] = $k;
                }
            } else {
                $items[$key] = $property;
                $values[] = $key;
            }
        }
        $view->set("items", $items);
        $view->set("values", $values);
        $view->set("model", $model);
    }

    /**
     * Updates any data provide with model and id
     * 
     * @before _secure, changeLayout, _admin
     * @param type $model the model object to be updated
     * @param type $id the id of object
     */
    public function update($model = NULL, $id = NULL) {
        $this->seo(array("title" => "Update", "keywords" => "admin", "description" => "admin", "view" => $this->getLayoutView()));
        $view = $this->getActionView();

        $object = $model::first(array("id = ?" => $id));

        $vars = $object->columns;
        $array = array();
        foreach ($vars as $key => $value) {
            array_push($array, $key);
            $vars[$key] = htmlentities($object->$key);
        }
        if (RequestMethods::post("action") == "update") {
            foreach ($array as $field) {
                $object->$field = RequestMethods::post($field, $vars[$field]);
                $vars[$field] = htmlentities($object->$field);
            }
            $object->save();
            $view->set("success", true);
        }

        $view->set("vars", $vars);
        $view->set("array", $array);
        $view->set("model", $model);
        $view->set("id", $id);
    }

    /**
     * Edits the Value and redirects user back to Referer
     * 
     * @before _secure, changeLayout, _admin
     * @param type $model
     * @param type $id
     * @param type $property
     * @param type $value
     */
    public function edit($model, $id, $property, $value) {
        $this->JSONview();
        $view = $this->getActionView();

        $object = $model::first(array("id = ?" => $id));
        $object->$property = $value;
        $object->save();

        $view->set("object", $object);

        self::redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * Updates any data provide with model and id
     * 
     * @before _secure, changeLayout, _admin
     * @param type $model the model object to be updated
     * @param type $id the id of object
     */
    public function delete($model = NULL, $id = NULL) {
        $view = $this->getActionView();
        $this->JSONview();
        
        $object = $model::first(array("id = ?" => $id));
        $object->delete();
        $view->set("deleted", true);
        
        self::redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * @before _secure, changeLayout, _admin
     */
    public function dataAnalysis() {
        $this->seo(array("title" => "Data Analysis", "keywords" => "admin", "description" => "admin", "view" => $this->getLayoutView()));
        $view = $this->getActionView();
        if (RequestMethods::get("action") == "dataAnalysis") {
            $startdate = RequestMethods::get("startdate");
            $enddate = RequestMethods::get("enddate");
            $model = ucfirst(RequestMethods::get("model"));

            $diff = date_diff(date_create($startdate), date_create($enddate));
            for ($i = 0; $i < $diff->format("%a"); $i++) {
                $date = date('Y-m-d', strtotime($startdate . " +{$i} day"));
                $count = $model::count(array("created LIKE ?" => "%{$date}%"));
                $obj[] = array('y' => $date, 'a' => $count);
            }
            $view->set("data", \Framework\ArrayMethods::toObject($obj));
        }
        $view->set("models", Shared\Markup::models());
    }
    
    public function sync($model) {
        $this->noview();
        $db = Framework\Registry::get("database");
        $db->sync(new $model);
    }

    /**
     * @before _secure
     */
    public function fields($model = "user") {
        $this->noview();
        $class = ucfirst($model);
        $object = new $class;

        echo json_encode($object->columns);
    }

    public function changeLayout() {
        $this->organization = Registry::get("session")->get("organization");
        $this->member = Registry::get("session")->get("member");

        $this->defaultLayout = "layouts/admin";
        $this->setLayout();
    }
}

<?php

/**
 * Description of organization
 *
 * @author Faizan Ayubi
 */
class Organization extends Shared\Model {

    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_name;

    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_details;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 45
     */
    protected $_website;

    /**
     * @column
     * @readwrite
     * @type text
     * @length 45
     */
    protected $_image;
}

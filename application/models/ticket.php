<?php

/**
 * Description of member
 *
 * @author Faizan Ayubi
 */
class Ticket extends Shared\Model {
    
    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_user_id;

    /**
     * @column
     * @readwrite
     * @type integer
     * @index
     */
    protected $_assigned;
    
    /**
     * @column
     * @readwrite
     * @type text
     * @length 32
     */
    protected $_status;

    /**
     * @column
     * @readwrite
     * @type text
     */
    protected $_details;
}

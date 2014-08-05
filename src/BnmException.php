<?php
namespace Fruitware\Bnm;

use Exception;
/**
 * Created by JetBrains PhpStorm.
 * User: Vladimir
 * Date: 05.08.14
 * Time: 14:55
 * To change this template use File | Settings | File Templates.
 */

class BnmException extends Exception {
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }
    public function __toString() {
        return __CLASS__ . ": {$this->message}\n";
    }
}
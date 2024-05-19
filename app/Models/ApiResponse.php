<?php

namespace App\Models;

class ApiResponse{
    public $issuccess;
    public $result;
    public $errorcode;
    public $errormessage;

    public function __construct($issuccess, $result, $errorcode, $errormessage) {
        $this->issuccess = $issuccess;
        $this->result = $result;
        $this->errorcode = $errorcode;
        $this->errormessage = $errormessage;
    }
}

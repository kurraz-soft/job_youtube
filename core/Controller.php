<?php

/**
 * Controls http requests
 */
class Controller
{
    public $db;

    function __construct($config)
    {
        $this->db = new mysqli($config['db_host'],$config['db_user'],$config['db_pass'],$config['db_name']);
    }

    public function run()
    {
        switch($_GET['r'])
        {
            case '/get-data':
                break;
            default:
                $this->index();
        }
    }

    public function index()
    {
        require(__DIR__.'/../template.php');
    }
}
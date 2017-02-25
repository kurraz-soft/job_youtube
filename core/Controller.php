<?php
require_once(__DIR__ . '/Parser.php');

/**
 * Controls http requests
 */
class Controller
{
    public $db;
    public $parser;

    private $config;

    function __construct($config)
    {
        $this->config = $config;
        $this->db = new mysqli($config['db_host'],$config['db_user'],$config['db_pass'],$config['db_name']);

        if (mysqli_connect_errno()) {
            printf("Не удалось подключиться: %s\n", mysqli_connect_error());
            exit();
        }

        $this->parser = new Parser([
            'parse_rows_num' => $this->config['parse_rows_num'],
        ]);
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

    public function run_parser()
    {
        $q = $this->db->query('SELECT * FROM queries');
        while($res_query = $q->fetch_assoc())
        {
            $this->run_parser_once($res_query);
        }
    }

    public function run_parser_once($res_query)
    {
        $result = $this->parser->crawler($res_query['text']);

        foreach($result as $res)
        {
            $q = $this->db->prepare('SELECT COUNT(*) as cnt FROM results WHERE name = ?');
            $q->bind_param("s", $res['name']);
            $q->execute();
            $q->bind_result($cnt);
            $q->fetch();
            $q->close();
            if(!$cnt)
            {
                $q = $this->db->prepare('INSERT INTO results SET query_id = ?, rating = ?, `name` = ?, description = ?');
                if(!$q)
                {
                    echo "prepare() Error: (" . $this->db->errno . ") " . $this->db->error;
                    die();
                }
                if(!$q->bind_param('iiss', $res_query['id'], $res['rating'], $res['name'], $res['desc']))
                {
                    die("bind_param() Error: (" . $q->errno . ") " . $q->error);
                }
                if (!$q->execute())
                {
                    die("execute() Error: (" . $q->errno . ") " . $q->error);
                }
                $q->close();
            }
        }
    }
}
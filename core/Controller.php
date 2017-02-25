<?php
require_once(__DIR__ . '/Parser.php');

/**
 * Обработчик http запросов
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

    /**
     * Старт обработчика http запросов
     */
    public function run()
    {
        switch($_GET['r'])
        {
            case 'get-data':
                $this->getData();
                break;
            case 'get-queries':
                $this->getQueries();
                break;
            case 'add-query':
                $this->addQuery();
                break;
            default:
                $this->index();
        }
    }

    /**
     * Выводит общий шаблон(layout)
     */
    public function index()
    {
        require(__DIR__.'/../templates/main.php');
    }

    /**
     * Добавляет запрос в базу данных
     */
    public function addQuery()
    {
        if(empty($_POST['raw_query'])) return;

        $queries = explode(',',$_POST['raw_query']);
        foreach($queries as $query)
        {
            //---UNIQUE CHECK---
            $query = trim($query);

            if(!$q = $this->db->prepare('SELECT COUNT(*) as cnt FROM queries WHERE text = ?'))
            {
                die("prepare() Error: (" . $this->db->errno . ") " . $this->db->error);
            }
            if(!$q->bind_param('s', $query))
            {
                die("bind_param() Error: (" . $q->errno . ") " . $q->error);
            }
            $q->execute();
            $q->bind_result($cnt);
            $q->fetch();
            $q->close();

            if($cnt) continue;
            //--\UNIQUE CHECK---

            if(!($q = $this->db->prepare('INSERT INTO queries SET text = ?')))
            {
                die("prepare() Error: (" . $this->db->errno . ") " . $this->db->error);
            }

            if(!$q->bind_param('s', $query))
            {
                die("bind_param() Error: (" . $q->errno . ") " . $q->error);
            }

            if (!$q->execute())
            {
                die("execute() Error: (" . $q->errno . ") " . $q->error);
            }
            $q->close();
            echo "OK\n";
        }
    }

    /**
     * Выводит список запросов из базы данных
     */
    public function getQueries()
    {
        $q = $this->db->query('SELECT * FROM queries');
        $queries = $q->fetch_all(MYSQLI_ASSOC);
        require(__DIR__ . '/../templates/_queries.php');
    }

    /**
     * Выводит данные по запросу в json
     */
    public function getData()
    {
        if(empty($_GET['id'])) die('id is not set');

        if(!$q = $this->db->prepare('
            SELECT COUNT(*), AVG(rating), t1.`text` FROM results AS t2
                INNER JOIN queries AS t1 ON t1.id = t2.`query_id`
            WHERE t1.id = ?
        '))
        {
            die("prepare() Error: (" . $this->db->errno . ") " . $this->db->error);
        }
        if(!$q->bind_param('i', $_GET['id']))
        {
            die("bind_param() Error: (" . $q->errno . ") " . $q->error);
        }
        $q->execute();
        $q->bind_result($cnt, $avg_rating, $name);
        $q->fetch();
        $q->close();

        $data['summary'] = [
            'cnt' => $cnt,
            'avg_rating' => number_format($avg_rating,2),
            'name' => $name,
        ];

        if(!$q = $this->db->prepare('SELECT * FROM results WHERE query_id = ?'))
        {
            die("prepare() Error: (" . $this->db->errno . ") " . $this->db->error);
        }
        if(!$q->bind_param('i', $_GET['id']))
        {
            die("bind_param() Error: (" . $q->errno . ") " . $q->error);
        }
        $q->execute();
        $results = $q->get_result()->fetch_all(MYSQLI_ASSOC);
        $q->close();

        ob_start();
        require(__DIR__ . '/../templates/_results.php');
        $data['results'] = ob_get_contents();
        ob_end_clean();

        echo json_encode($data);
    }

    /**
     * Запуск парсера по всем запросам в таблице
     */
    public function run_parser()
    {
        $q = $this->db->query('SELECT * FROM queries');
        while($res_query = $q->fetch_assoc())
        {
            $this->run_parser_once($res_query);
            sleep(3);
        }
    }

    /**
     * Запуск парсера по конкретному запросу
     *
     * @param array $res_query
     * Данные из таблицы queries
     */
    public function run_parser_once($res_query)
    {
        $result = $this->parser->crawler($res_query['text']);

        foreach($result as $res)
        {
            //---UNIQUE CHECK---
            $q = $this->db->prepare('SELECT COUNT(*) as cnt FROM results WHERE name = ?');
            $q->bind_param("s", $res['name']);
            $q->execute();
            $q->bind_result($cnt);
            $q->fetch();
            $q->close();
            if($cnt) continue;
            //--\UNIQUE CHECK---

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
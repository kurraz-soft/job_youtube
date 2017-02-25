<?php
require_once(__DIR__ . '/phpQuery.php');

/**
 *
 */
class Parser
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function crawler($query_text)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.youtube.com/results?search_query=" . urlencode($query_text));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36");

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $out = curl_exec($ch);

        $doc = phpQuery::newDocument($out);

        $rows = $doc->find('.item-section > li');

        $i = 0;
        $result = [];

        foreach($rows as $row)
        {

            $url = pq($row)->find('.yt-lockup-title > a:first')->attr('href');
            if(!preg_match('#^/watch\?v=[^&]+$#',$url)) continue;

            $rating = preg_replace('#\D#','',pq($row)->find('.yt-lockup-meta-info li:last')->text());
            $name = trim(pq($row)->find('.yt-uix-tile-link')->text());
            $desc = trim(pq($row)->find('.yt-lockup-description')->text());

            $result[] = [
                'rating' => $rating,
                'name' => $name,
                'desc' => $desc,
            ];

            $i++;
            if($i == $this->config['parse_rows_num']) break;
        }

        $doc->unloadDocument();

        return $result;
    }
}
<?php

namespace Fruitware\Bnm;

use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;


class BnmCurs
{

    public $folder = 'file';
    private $source = '';
    private $rates = array();

    public function __construct($date = null, $save_to_file = true)
    {
        $cur_date = date('d.m.Y');
        if (!isset($date)) {
            $load_date = $cur_date;
        } else if ($date instanceof DateTime) {
            $load_date = $date->format('d.m.Y');
            if($cur_date != $load_date) {
                throw new BnmException('Date has an invalid format');
            }
        } else {
            throw new BnmException('Date has an invalid format');
        }
        $this->folder = trim($this->folder, '/');
        $dir = dirname ( __FILE__ ).'/'.$this->folder;
        $this->source = $dir.'/'.'rates'.$load_date.'.xml';
        if(!is_dir($dir)) {
            mkdir($dir, '0700');
        }

        if($save_to_file) {
            if ( !file_exists($this->source) ) {
                $this->saveRates($this->source, $load_date);
            }
            $this->rates = simplexml_load_file($this->source);
        } else {
            $this->rates = $this->loadRates($load_date);
        }
        if(!isset($this->rates, $this->rates->Valute)) {
            throw new BnmException('Error loading');
        }
    }

    /**
     * @param $quantity string
     * @param $currency string
     * @return float
     */
    public function exchange($quantity, $currency)
    {
        $curr 		= 	$this->getRate($currency);
        $rate 		=	(double) $curr->Value;
        $nominal 	= 	(double) $curr->Nominal;

        $result = (double) ( $quantity / $nominal / $rate );

        return $result;
    }

    private function getRate($currCode)
    {
        foreach ($this->rates->Valute as $row) {
            if ($row->CharCode == $currCode) {
                return $row;
            }
        }
        throw new BnmException('Such currency does not exist');
    }

    private function loadRates($date)
    {
        $client = new Client();
        $res = $client->get('http://www.bnm.md/ru/official_exchange_rates', [
            'query' => ['get_xml' => '1', 'date' => $date]
        ]);
        if($res->getStatusCode() == "200") {
            try {
                return $res->xml();
            } catch(BnmException $e) {
                throw new BnmException('Error loading xml');
            }
        }
        return '';
    }

    private function saveRates($filename, $date)
    {
        $rates = $this->loadRates($date);
        $rates->asXML($filename);
    }

}
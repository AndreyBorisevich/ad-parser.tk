<?php

namespace app\models;

use Yii;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Response;
/**
 * This is the model class for table "ads".
 *
 * @property int $id
 * @property string $title
 * @property int $key
 * @property int $cost
 * @property string $address
 * @property string $link
 */
class Ads extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ads';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'key', 'cost', 'address', 'link'], 'required'],
            [['key'], 'integer'],
            [['cost'], 'double'],
            [['title', 'address', 'link'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'key' => 'Key',
            'cost' => 'Cost',
            'address' => 'Address',
            'link' => 'Link',
        ];
    }
    public function parse(){
        $client = new Client();
        $res = $client->request('GET', 'https://realt.by/sale/offices/', ['query' => ['search' => 'all', 'view' => '1']]);
        $body = $res->getBody();
        $document = \phpQuery::newDocumentHTML($body);
        $ads = $document->find(".bd-item");
        $items = array();
        $count_page = intval( $document->find(".uni-paging a:last ")->text() );
        foreach ($ads as $elem) {
            $pq = pq($elem);
            $item = array();
            $item[] = $pq->find('.title > a')->text();
            $item[] = str_replace( 'Код: ', '', $pq->find('.bd-item-right-top .fr:last')->text() );
            $item[] = floatval( str_replace( ',', '.', $pq->find('.price-byr')->text() ) );
            $item[] = self::getAddress( $pq->find('.title > a')->text(), $pq->find('.bd-item-right-center > p:first')->text() );
            $item[] = $pq->find('.title > a')->attr('href');
            $items[] = $item;
        }

        if( !empty($items) ){
            Ads::deleteAll();
            Yii::$app->db->createCommand()->batchInsert('ads', ['title', 'key', 'cost', 'address', 'link'], $items)->execute();
        }

        $client = new Client(['base_uri' => 'https://realt.by/sale/offices/']);

        $requestGenerator = function($total) use ($client) {
            for ($i = 1; $i < $total; $i++){
                (yield $i => function() use ($client, $i) {
                    return $client->getAsync('?search=all&view=1&page='.$i);
                });
            }
        };

        $pool = new Pool($client, $requestGenerator($count_page), [
            'concurrency' => 21,
            'fulfilled' => function(Response $response, $index) {
                $body = $response->getBody();
                $document = \phpQuery::newDocumentHTML($body);
                $ads = $document->find(".bd-item");
                $items = array();

                foreach ($ads as $elem) {
                    $pq = pq($elem);
                    $item = array();
                    $title = $pq->find('.title > a')->text();
                    $item[] = $title;
                    $item[] = str_replace( 'Код: ', '', $pq->find('.bd-item-right-top .fr:last')->text() );
                    $item[] = floatval( str_replace( ',', '.', $pq->find('.price-byr')->text() ) );
                    $item[] = self::getAddress( $title, $pq->find('.bd-item-right-center > p:first')->text() );
                    $item[] = $pq->find('.title > a')->attr('href');
                    $items[] = $item;
                }
                if( !empty($items) ){
                    Yii::$app->db->createCommand()->batchInsert('ads', ['title', 'key', 'cost', 'address', 'link'], $items)->execute();
                }
            },
            'rejected' => function(Exception $reason, $index) {
                echo "Requested search term: ", $index, "\n";
                echo $reason->getMessage(), "\n\n";
            },
        ]);
        $promise = $pool->promise();
        $promise->wait();

        return true;
    }

    private static function getAddress($title, $p) {
        $address = $p;
        $title_to_arr = explode(',', $title);
        if( count($title_to_arr) > 1 ){
            if (strpos($address, $title_to_arr[1]) !== false ){
                $address .= ' ' . implode( ',', array_slice($title_to_arr, 2) );
            }else{
                $address .= ' ' . implode( ',', array_slice($title_to_arr, 1) );
            }
        }

        return $address;

    }
}

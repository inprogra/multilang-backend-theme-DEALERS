<?php

namespace Classes;

use Classes\StockCar;
use Classes\DolStatus;
use Closure;

class VolvoSync
{
    public const API_URL = 'https://volvo-sync.easyapi.space/api';

    public StockCar $stockCar;

    public function __construct(StockCar $stockCar)
    {
        $this->stockCar = $stockCar;
    }
    public function flushVinCache() {
        $m = new \Memcached();
        $m->addServer('localhost', 11211);
        $m->set('sync',[],time() + 86400);
        exit('done');
    }
    public function import_and_update_status()
    {
        $dealers = json_decode(file_get_contents('https://main.volvocars-partner.pl/api/getDealers'));
        
        $cars = [];
        $posts = [];
        $m = new \Memcached();
        $m->addServer('localhost', 11211);
        // $m->set('sync',[],time() + 86400);
        if (!$m->get('sync')) {
            $m->set('sync',[],time() + 86400);
        }
        $data = $m->get('sync');

        
        foreach($dealers as $d) {
            if ($d->cars > 0) {
               
               
                if (!array_key_exists($d->blog_id, $data)) {
                switch_to_blog($d->blog_id);

                $posts = $this->get_cars_for_import();
                $x=0;
                foreach ($posts as $post) {
                   
                        array_push($cars, $this->prepare_data_for_import($post, $d->blog_id));
                      
                        $x++;
                    }
                
                $data[$d->blog_id] = $cars;
                $m->set('sync',$data,time() + 86400);
                $splitted_cars = array_chunk($cars, 30);
              
                foreach($splitted_cars as $cars) {
                    $response = $this->send_post_curl(self::API_URL . '/importVin', wp_json_encode($cars));   
                    if (is_wp_error($response)) {
                        $error_message = $response->get_error_message();
                        echo "Something went wrong: $error_message";
                    } else {
                        $response = json_decode($response['body'], true);
            
                        //tutaj w posts mamy tylko auta z ostatniej strony bloga - dlatego nie aktualizuja sie statusy
                        foreach ($posts as $car) {
                            $car_id = $car;
                       
                            $key = get_field('vin', $car_id);
            
                            if (!$key) {
                                $key = get_field('con', $car_id);
                            }
                          
                            if (array_key_exists($key, $response)) {
                               
                                // if (DolStatus::data_in_dol($car_id) != $response[$key]) {
                                    $value = $response[$key]['status'] ? 1 : 2;
                                 

                                    update_field('dol_sync', $value, $car_id);
                                // }
                            }
                        }
                    }




                }
                
                
               
                



                restore_current_blog();
                exit('Dealer added');
            }
            }
            
        }
     
        exit();
       // $response = $this->send_post_curl(self::API_URL . '/importVin', wp_json_encode($cars));

       
    }

    private function prepare_data_for_import(int $car_id, int $blog_id): array
    {
        $data = [
            'blog_id' => $blog_id,
            'post_id' => $car_id,
            'imported' => false,
            'vin' => null,
            'con' => null
        ];

        $vin = get_field('vin', $car_id);

        if ($vin) {
            $data['vin'] = $vin;
        } else {
            $data['con'] = get_field('con', $car_id);
        }

        return $data;
    }

    // trzeba zmienic sposob pobierania na mysql, zeby nie odpytywac za kazdym razem o get_field()
    private function get_cars_for_import()
    {
        // zakomentowane, bo nie mam lokalnie zadnych "nowych"
        $args = [
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'post_status' => 'any',
            'meta_query' => [
                // 'relation' => 'AND',
                // [
                'relation' => 'OR',
                [
                    'key' => 'con',
                    'value' => '',
                    'compare' => '!='
                ],
                [
                    'key' => 'vin',
                    'value' => '',
                    'compare' => '!='
                ],          
            ],
        ];

        return $this->stockCar->get_stock_cars($args);
    }

    private function send_post_curl(string $url, string $json_body)
    {
        $args = array(
            'timeout' => 45,
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => $json_body,
        );

        return wp_remote_post($url, $args);
    }

    private function execute_for_each_blog(Closure $closure)
    {
        $sites = get_sites();

        if ($sites) {
            foreach ($sites as $site) {
                $blog_id = $site->blog_id;
                switch_to_blog($blog_id);

                $closure($blog_id);

                restore_current_blog();
            }
        }
    }
}

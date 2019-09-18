<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA
 * Author: 张伯发
 * Date  : 2019/9/10
 * Time  : 16:19
 */

namespace Happgo\Framework;

use Happgo\Framework\Contract\AppInterface;
use Happgo\Lib\Collection;

/**
 * 启动类
 * Class HappyApplication
 * @author 张伯发 2019/9/10 16:29
 */
class HappgoApplication implements AppInterface
{

    public function beforeInit()
    {
        dump('beforeInit');
        (new Collection())->hello();
    }

    public function run()
    {
        $this->beforeInit();

        $http = new \Swoole\Http\Server("0.0.0.0", 9501);
        $http->on('request', function ($request, $response) {

            if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
                return $response->end();
            }
            var_dump($request->get, $request->post);


            $response->header("Content-Type", "text/html; charset=utf-8");
            $response->end("<h1>Hello Swoole. !!!! #".rand(1000, 9999)."</h1>");
        });

        $http->start();


        $this->afterInit();

    }

    public function afterInit()
    {
        dump('afterInit');
    }
}
<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA
 * Author: 张伯发
 * Date  : 2019/9/10
 * Time  : 16:19
 */

namespace Happgo\Framework;

use Happgo\Framework\Contract\AppInterface;

use Happgo\Lib\ComposerHelper;
use Happgo\Lib\FSHelper;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

/**
 * 启动类
 * Class HappyApplication
 * @author 张伯发 2019/9/10 16:29
 */
class HappgoApplication implements AppInterface
{
    /**
     * 基本路径
     * @var string
     */
    private $basePath = '';

    private $appPath = '@base/app';



    public function beforeInit()
    {
        var_dump('beforeInit');
    }

    public function run()
    {
        $this->beforeInit();

        $this->setBasePath($this->findBasePath());

        Happgo::setAlias('@base', $this->getBasePath());
        Happgo::setAlias('@app', $this->getAppPath());


        $http = new Server("0.0.0.0", 6699);

        $http->set([
            'worker_num'    => 2,
            'max_request'   => 1000,
        ]);

        // 开始事件监听
        $http->on('start', function (Server $server) {

            var_dump('start -------  hello world');
        });

        $http->on('request', function (Request $request, Response $response) {

            if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
                $response->end();
                return;
            }
            var_dump('--------  request  -----------');

            $server = $request->server;
            $uri = $server['request_uri'];
            $method = strtolower($server['request_method']);

            $pathInfo = $server['path_info'];

            if ($pathInfo == '/') {
                $pathInfo = '/';
            } else {
                $pathInfo = explode('/', $pathInfo);
            }
            if (!is_array($pathInfo)) {
                $response->status(404);
                $response->end('<meta charset="UTF-8">这里是主页');
            }

            //模块
            $model = (isset($pathInfo[1]) && !empty($pathInfo[1])) ? $pathInfo[1] : 'Swooleman';
            $model = ucfirst($model);
            //控制器
            $controller = (isset($pathInfo[2]) && !empty($pathInfo[2])) ? $pathInfo[2] : 'error';
            $controller = ucfirst($controller);
            //方法
            $method = (isset($pathInfo[3]) && !empty($pathInfo[3])) ? $pathInfo[3] : 'index';
            //结合错误处理
            $className = "\\App\\{$model}\\Controller\\{$controller}";

            $filePath = Happgo::getAlias('@base') . str_replace('\\', '/', $className) .'.php';

            // 判断控制器类是否存在
            if(file_exists($filePath)){
                require_once $filePath;
                $obj= new $className();
                //判断控制器方法是否存在
                if(!method_exists($obj,$method)){
                    $response->status(404);
                    $response->end("<meta charset='UTF-8'>方法不存在");
                }else{
                    //如果存在此方法，返回结果，return 无效
                    $return = $obj->$method($request, $response);
                    if (empty($return)){
                        // 这里做抛出异常
                    }

                    if (is_array($return)) {
                        $response->header("Content-Type", "application/json; charset=utf-8");
                        $return = json_encode($return);
                    }else{
                        $response->header("Content-Type", "text/html; charset=utf-8");
                    }
                    $response->end($return);
                }
            }else{
                $response->status(404);
                $response->end("<meta charset='UTF-8'>类不存在");
            }
        });

        $http->start();

        $this->afterInit();

    }

    public function afterInit()
    {
        dump('afterInit');
    }


    /**
     * 获取项目根地址
     * @return string
     * @author 张伯发 2019/9/19 17:02
     */
    private function findBasePath(): string
    {
        $filePath = ComposerHelper::getClassLoader()->findFile(static::class);
        $filePath = FSHelper::conv2abs($filePath, false);
        return dirname($filePath, 2);
    }

    public function setBasePath(string $path): void
    {
        $this->basePath = $path;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getAppPath(): string
    {
        return $this->appPath;
    }
}
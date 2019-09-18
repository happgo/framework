<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA
 * Author: 张伯发
 * Date  : 2019/9/10
 * Time  : 16:25
 */

namespace Happgo\Framework\Contract;


interface AppInterface
{
    public function beforeInit();

    public function run();

    public function afterInit();
}
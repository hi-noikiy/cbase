<?php
class EmptyController extends CController
{
    public function _empty()
    {
        echo '当调用控制器不存在的时候调用我';
    }
}

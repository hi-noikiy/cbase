<?php
/**
 *
 * Copyright(c)2013,上海瑞创网络科技股份有限公司
 * 摘	  要：员工信息缓存类
 * 作	  者：Gao
 * 修改时间：2013/06/04
 *
 */
class usercache
{
    /**
     * 根据部门ID获取部门信息
     * @param $did 部门ID号 0是所有部门
     * @param $path 自定义缓存路径
     **/
    public function getDpInfo($did = 0,  $path='')
    {
        $path   = $path ? $path : C_APP_CACHE_PATH.'user/';
        $result = CCache::getInstance('File')->get('dpinfo',$path);
        
        if(!empty($result))
        {
            if(!empty($did) && isset($result[$did]))
            {
                $result = $result[$did];
            }
        }
        else
        {
            loadExtend('system.net.curl');
            $curl = new Curl('http://oa.2345.cn/api/qsApi.php?act=getDepartment');
            $str_info = $curl->cGet();
            $result = iconv_array(unserialize($str_info), 'gbk', 'utf-8');
            CCache::getInstance('File')->set('dpinfo',$result , $path);
        }
        return $result;
    }
    
    /**
     * 根据部门ID获取部门所有员工
     * @param $did 部门ID号
     * @param $usercache_path 自定义缓存路径
     **/
    public function getDpUser($did = 0, $usercache_path = '')
    {
        $usercache_path   = $usercache_path ? $usercache_path : C_APP_CACHE_PATH.'user/';
        $arr_dp = $this->getDpInfo($did, $usercache_path);
        $dpname = $arr_dp['name'] ? $arr_dp['name'] : '';
        $alluser =  CCache::getInstance('File')->get('alluser', $usercache_path);
        
        if(empty($alluser))
        {
            loadExtend('system.net.curl');
            $curl = new Curl('http://oa.2345.cn/api/qsApi.php?act=allMembers');
            $str_info = $curl->cGet();
            $alluser  = iconv_array(unserialize($str_info), 'gbk', 'utf-8');
            CCache::getInstance('File')->set('alluser',$alluser, $usercache_path);
        }
        if(!empty($dpname) && isset($alluser[$dpname]))
        {
            return $alluser[$dpname];
        }
        else
        {
            return $alluser;
        }

    }

    /**
     * 根据用户ID获取用户信息
     * @param $uinfo
     * @param $path 自定义缓存路径
     **/
    public function getUserInfo($uinfo = 0, $path = '')
    {
        $path = $path ? $path :  C_APP_CACHE_PATH.'user/';
        $userinfo = CCache::getInstance('File')->get('userinfo', $path);
        if(empty($userinfo))
        {
            $arr_alluser = $this->getDpUser(0);
            foreach($arr_alluser as $dpuser)
            {
                foreach($dpuser as $value)
                {
                    $userinfo[$value['uid']] = $value;
                    $userinfo[$value['username']] = $value;
                }
            }
            CCache::getInstance('File')->set('userinfo', $userinfo, $path);
        }
        return isset($userinfo[$uinfo]) ? $userinfo[$uinfo] : $userinfo;
    }


    /**
     * 模糊获取用户信息(用户名)
     * @param $uinfo
     * @param $path 自定义缓存路径
     **/
    public function lgetUserInfo($uinfo = null, $path = '')
    {
        $result = null;
        $path = $path ? $path :  C_APP_CACHE_PATH.'user/';
        $userinfo = CCache::getInstance('File')->get('userinfo', $path);
        if(empty($userinfo))
        {
            $arr_user = array();
            $arr_alluser = $this->getDpUser();
            foreach($arr_alluser as $dpuser)
            {
                foreach($dpuser as $value)
                {
                    $arr_user[$value['uid']] = $value;
                    $arr_user[$value['username']] = $value;
                }
            }
            CCache::getInstance('File')->set('userinfo', iconv_array($arr_user, 'gbk', 'utf-8'), $path);
        }
        
        foreach($userinfo as $key=>$value)
        {
            if(strstr($key,$uinfo))
            {
                $result[] = $value; 
            }
        }
        return $result;
    }
}
<?php
/**
 * 
 * Copyright(c)2013,上海瑞创网络科技股份有限公司
 * 摘	  要：分页类 支持多重样式显示
 * 作	  者：Gao
 * 修改时间：2013/06/08
 *
 */
class Page 
{

    private $firstRow;   // 分页起始行数
    private $listRows;   // 列表每页显示行数
    private $pageName;   // 分页参数名称
    private $parameter;  // 页数跳转时要带的参数
    private $totalPages; // 总页码数
    private $totalRows;  // 总行数
    private $nowPage;    // 当前页数
    private $coolPages;  // 分页的栏的总页数
    private $rollPage;   // 分页栏每页显示的页数
    public  $config = array('prev' => '< Prev', 'next' => 'Next >', 'first' => 'First', 'last' => 'Last'); // 分页显示定制

    /**
     * 架构函数
     * @param integer $totalRows  总的记录数
     * @param array $parameter  分页跳转的参数
     * @param integer $listRows  每页显示记录数
     * */
    public function __construct($totalRows, $parameter = array(), $listRows = 0) 
    {
        // 总记录数量
        $this->totalRows = $totalRows;
        // 分页栏每页显示的页码数量
        $this->rollPage   = (int) CBase::getConfig('PAGE_NUMBERS') ? CBase::getConfig('PAGE_NUMBERS') : 5;
        // 每页显示的记录数量
        $this->listRows   = !empty($listRows) ? $listRows : CBase::getConfig('LIST_NUMBERS');
        // 总分页数量
        $this->totalPages = ceil($this->totalRows / $this->listRows);
        // 分页的栏的总页数
        $this->coolPages  = ceil($this->totalPages / $this->rollPage);
        // 页码参数
        $this->pageName   = CBase::getConfig('VAR_PAGE') ? CBase::getConfig('VAR_PAGE') : 'page';
        // 当前所在页
        $this->nowPage    = intval($_GET[$this->pageName]) ? intval($_GET[$this->pageName]) : 1;
        // 判断是否在最后一页
        if (!empty($this->totalPages) && $this->nowPage > $this->totalPages) 
        {
            $this->nowPage = $this->totalPages;
        }
        // 分页起始行数
        $this->firstRow = $this->listRows * ($this->nowPage - 1);
        // 分页后的参数
        $this->parameter = $parameter;
    }

    /**
     * 分页显示
     * 用于在页面显示的分页栏的输出
     * @access public 
     * @return string
     * */
    public function show($isArray = false) 
    {
        // 计算当前所在页码
        $nowCoolPage = ceil($this->nowPage / $this->rollPage);
        //上下翻页字符串
        $upRow   = $this->nowPage - 1;
        $downRow = $this->nowPage + 1;
        if ($upRow > 0) 
        {
            $url    = $this->creatUrl($upRow);
            $upPage = "<a href='{$url}'>" . $this->config['prev'] . '</a>';
        } 
        else 
        {
            $upPage = '<span class="disabled">' . $this->config['prev'] . '</span>';
        }
        
        if ($downRow <= $this->totalPages) 
        {
            $url = $this->creatUrl($downRow);
            $downPage = "<a href='{$url}'>" . $this->config['next'] . '</a>';
        } 
        else 
        {
            $downPage = '<span class="disabled">' . $this->config['next'] . '</span>';
        }
        
        if ($nowCoolPage == 1) 
        {
            $theFirst = '';
            $prePage  = '';
        }
        else 
        {
            $preRow    = $this->nowPage - $this->rollPage;
            $url       = $this->creatUrl($preRow);
            $prePage   = "<a href='{$url}' >上" . $this->rollPage . "页</a>";
            $url_first = $this->creatUrl(1);
            $theFirst  = "<a href='{$url_first}' class='first' >" . $this->config['first'] . "</a>";
        }
        
        if ($nowCoolPage == $this->coolPages) 
        {
            $nextPage = '';
            $theEnd   = '';
        }
        else 
        {
            $nextRow   = $this->nowPage + $this->rollPage;
            $url_next  = $this->creatUrl($nextRow);
            $nextPage  = "[<a href='{$url_next}' >下" . $this->rollPage . "页</a>]";
            if($this->totalPages > 0)
            {
                $url_end   = $this->creatUrl($this->totalPages);
                $theEnd    = "<a href='{$url_end}' class='last' >" . $this->config['last'] . "</a>";
            }
        }
        
        $linkPage = '';
        for ($i = 1; $i <= $this->rollPage; $i++) 
        {
            $page = ($nowCoolPage - 1) * $this->rollPage + $i;
            if ($page != $this->nowPage && $page <= $this->totalPages) 
            {
                $url = $this->creatUrl($page);
                $linkPage .= "<a href='{$url}'>" . $page . '</a>';
            }
            else if ($page == $this->nowPage)
            {
                $linkPage .= '<span class="current">' . $page . '</span>';
            }
        }
        $pageStr = $theFirst . $upPage . $linkPage . $downPage . $theEnd;
        if ($isArray) 
        {
            $pageArray['totalRows']  = $this->totalRows;
            $pageArray['upPage']     = $this->creatUrl($upPage);
            $pageArray['downPage']   = $this->creatUrl($downRow);
            $pageArray['totalPages'] = $this->totalPages;
            $pageArray['firstPage']  = $this->creatUrl(1);
            $pageArray['endPage']    = $this->creatUrl($theEndRow);
            $pageArray['nextPages']  = $this->creatUrl($nextRow);
            $pageArray['prePages']   = $this->creatUrl($preRow);
            $pageArray['linkPages']  = $linkPage;
            $pageArray['nowPage']    = $this->nowPage;
            return $pageArray;
        }
        return $pageStr;
    }

    /**
     * 根据页码创建不同的链接
     */
    public function creatUrl($page)
    {
        $this->parameter = array_merge($this->parameter, $_GET);
        $this->parameter[$this->pageName] = $page;
        return $_SERVER['PATH_INFO'] . '?' . http_build_query($this->parameter);
    }
}
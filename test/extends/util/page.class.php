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

    protected $firstRow	; // 分页起始行数
    protected $listRows	; // 列表每页显示行数
    protected $parameter  ; // 页数跳转时要带的参数
    protected $totalPages  ; // 页数跳转时要带的参数
    protected $totalRows  ; // 总行数
    protected $nowPage    ; // 当前页数
    protected $coolPages   ; // 分页的栏的总页数
    protected $rollPage   ; // 分页栏每页显示的页数
    protected $config  = array('prev'=>'上页','next'=>'下页','first'=>'首页','last'=>'末页'); // 分页显示定制

    /**
     * 架构函数
     * @param array $totalRows  总的记录数
     * @param array $parameter  分页跳转的参数
     * @param array $listRows  每页显示记录数
     **/
    public function __construct($totalRows, $parameter='', $listRows='')
    {    
        $this->totalRows = $totalRows;
        if(is_array($parameter)){
            foreach($parameter as $k => $v)
            $this->parameter .= "&$k=".  urlencode($v);
        }else{
            $this->parameter = $parameter;
        }
        $this->rollPage = CBase::getConfig('PAGE_NUMBERS');
        $this->listRows = !empty($listRows)?$listRows:CBase::getConfig('LIST_NUMBERS');
        $this->totalPages = ceil($this->totalRows/$this->listRows);     //总页数
        $this->coolPages  = ceil($this->totalPages/$this->rollPage);
        $this->nowPage  = !empty($_GET[CBase::getConfig('VAR_PAGE')])&&($_GET[CBase::getConfig('VAR_PAGE')] >0)?$_GET[CBase::getConfig('VAR_PAGE')]:1;

        if(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage = $this->totalPages;
        }
        $this->firstRow = $this->listRows*($this->nowPage-1);
    }

	
	public function setConfig($name,$value) {
		if(isset($this->config[$name])) {
			$this->config[$name]	=	$value;
		}
	}

    /**
     * 分页显示
     * 用于在页面显示的分页栏的输出
     * @access public 
     * @return string
     **/
    public function show($isArray=false){

        if(0 == $this->totalRows) return;
        $nowCoolPage      = ceil($this->nowPage/$this->rollPage);
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
        $url = parse_url($url);
        parse_str($url['query'], $params);
        $url = $url['path']. '?';
        foreach($params as $k => $v)
        {
            if($k != CBase::getConfig('VAR_PAGE')) $url .= "&$k=".urlencode($v);
        }
      
        //上下翻页字符串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            $upPage="<a href='".$url."&".CBase::getConfig('VAR_PAGE')."=$upRow'>".$this->config['prev']."</a>";
        }else{
            $upPage="<span class=\"disabled\">".$this->config['prev']."</span>";
        }

        if ($downRow <= $this->totalPages){
            $downPage="<a href='".$url."&".CBase::getConfig('VAR_PAGE')."=$downRow'>".$this->config['next']."</a>";
        }else{
            $downPage="<span class=\"disabled\">".$this->config['next']."</span>";
        }
        // << < > >>
        if($nowCoolPage == 1){
            $theFirst = "";
            $prePage = "";
        }else{
            $preRow =  $this->nowPage-$this->rollPage;
            $prePage = "<a href='".$url."&".CBase::getConfig('VAR_PAGE')."=$preRow' >上".$this->rollPage."页</a>";
            $theFirst = "<a href='".$url."&".CBase::getConfig('VAR_PAGE')."=1' class='first' >".$this->config['first']."</a>";
        }
        if($nowCoolPage == $this->coolPages){
            $nextPage = "";
            $theEnd="";
        }else{
            $nextRow = $this->nowPage+$this->rollPage;
            $theEndRow = $this->totalPages;
            $nextPage = "[<a href='".$url."&".CBase::getConfig('VAR_PAGE')."=$nextRow' >下".$this->rollPage."页</a>]";
            $theEnd = "<a href='".$url."&".CBase::getConfig('VAR_PAGE')."=$theEndRow' class='last' >".$this->config['last']."</a>";
        }
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page=($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "<a href='".$url."&".CBase::getConfig('VAR_PAGE')."=$page'>".$page."</a>";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= "<span class='current'>".$page."</span>";
                }
            }
        }
        $pageStr = $theFirst.$upPage.$linkPage.$downPage.$theEnd; 
        if($isArray) {
            $pageArray['totalRows'] =   $this->totalRows;
            $pageArray['upPage']    =   $url.'&'.CBase::getConfig('VAR_PAGE')."=$upRow";
            $pageArray['downPage']  =   $url.'&'.CBase::getConfig('VAR_PAGE')."=$downRow";
            $pageArray['totalPages']=   $this->totalPages;
            $pageArray['firstPage'] =   $url.'&'.CBase::getConfig('VAR_PAGE')."=1";
            $pageArray['endPage']   =   $url.'&'.CBase::getConfig('VAR_PAGE')."=$theEndRow";
            $pageArray['nextPages'] =   $url.'&'.CBase::getConfig('VAR_PAGE')."=$nextRow";
            $pageArray['prePages']  =   $url.'&'.CBase::getConfig('VAR_PAGE')."=$preRow";
            $pageArray['linkPages'] =   $linkPage;
			$pageArray['nowPage'] =   $this->nowPage;
        	return $pageArray;
        }
        return $pageStr;
    }

}
?>
<?php



/**
 * 用户的房子管理类
 * @author lvxin
 * @version 1.0
 * @created 03-六月-2015 11:13:32
 */
class _Scene
{

    /**
     * 增加房子
     * 
     * @param params    参数列表
     */
    public static function _Add($params)
    {
        return "{sldk}";
    }

    /**
     * 删除房子
     * 
     * @param params    用户名
     */
    public static function _Del($params)
    {
    }

    /**
     * 
     * @param params    房子唯一id
     */
    public static function _Modify($params)
    {
    }

    /**
     * 
     * @param params    用户名
     */
    public static function _List($params)
    {
        $arr = json_decode( ('
    {
            "code": 0, 
                    "msg": "ok", 
                        "results": {
                                    "scenes": [
                                                    {"sceneid": 1,"name": "晚餐模式"}, 
                                                                {"sceneid": 2,"name": "睡眠模式"}
                                                                        ]
                                                                            }
    }
'), true);
    return json_encode($arr);
    }

    /**
     * 
     * @param params
     */
    public static function _SetMainDevice($params)
    {
    }

}
?>

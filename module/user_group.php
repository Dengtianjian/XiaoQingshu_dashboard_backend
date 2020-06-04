<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}


class user_group
{
  public $methods = [
    "getGroup",
  ];

  /**
   * 获取指定ID的用户组信息
   * @param string<array> $_groupId 用户组ID
   * @return void
   */
  function getGroup()
  {
    $_groupId = addslashes($_GET['_groupid']);

    $result = Cloud::http("db")::query("db.collection('user_group').where({_id:'$_groupId'}).get()");

    if ($result['errcode'] != 0) {
      Response(null, "WECHAT_CLOUD_DATABASE_QUERY_ERROR");
    }
    if (count($result['data']) == 0) {
      return [];
    }
    $result = $result['data'][0];

    return $result;
  }
}

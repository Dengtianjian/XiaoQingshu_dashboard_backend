<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}


class user_group
{
  public $methods = [
    "getGroup",
    "getAllGroup",
    "saveGroup",
    "deleteGroup"
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

  /**
   * 获取全部用户组
   *
   * @return void
   */
  function getAllGroup()
  {
    $result = Cloud::http("db")::query("db.collection('user_group').get()");

    if ($result['errcode'] != 0) {
      Response(null, "WECHAT_CLOUD_DATABASE_QUERY_ERROR");
    }

    return $result['data'];
  }

  /**
   * 保存用户组信息
   * 如果有用户组的ID就是更新用户组信息
   * 如果是没有用户组的ID 就是添加一个用户组
   *
   * @return void
   */
  function saveGroup()
  {
    $name = addslashes(str_replace("'", "", $_GET['name']));
    $type = addslashes($_GET['type']);
    $experience = intval($_GET['experience']);

    $result = Cloud::http("db")::add("db.collection('user_group').add({data:{
      name:'$name',
      type:'$type',
      experience:$experience,
      icon:''
    }})");

    if ($result['errcode'] != 0) {
      Response($result['errmsg'], "WECHAT_CLOUD_DATABASE_QUERY_ERROR");
    }

    $groupId = $result['id_list'][0];
    if (count($result['id_list']) > 0) {
      $result = Cloud::http("db")::add("db.collection('user_group_permission').add({
        data:{
          _groupid:'$groupId',
          allow_access:true,
          allow_comment:true,
          allow_post:true
        }
      })");
      $permission = [
        "_id" => $result['id_list'][0],
        "allow_access" => true,
        "allow_comment" => true,
        "allow_post" => true
      ];
    }

    Response([
      'data' => [
        "_id" => $groupId,
        "permission" => $permission
      ],
      "message" => "添加成功"
    ]);
  }

  /**
   * 删除指定ID的用户组
   * 并且把相关用户的用户组改成指定的用户组
   *
   * @return void
   */
  function deleteGroup()
  {
    $deleteGroup = addslashes($_GET['_groupid']);
    $changeGroup = addslashes($_GET['_change_group']);

    $result = Cloud::http("db")::delete("db.collection('user_group').where({_id:'$deleteGroup'}).remove();");
    if ($result['deleted']) {
      Cloud::http("db")::delete("db.collection('user_group_permission').where({
        _groupid:'$deleteGroup'
      }).remove()");
      Cloud::http("db")::update("db.collection('user').where({group:'$deleteGroup'}).update({data:{group:'$changeGroup'}})");
    }
    return $result;
  }
}

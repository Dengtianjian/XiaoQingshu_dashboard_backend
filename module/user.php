<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}


class user
{
  public $methods = [
    "signin",
    "verify",
    "getUser",
    "getUsers",
    "getUserJoinedSchool",
    "getUserJoinedClass",
    "uploadUserAvatar",
    "saveUser"
  ];

  /**
   * 用户登录
   *
   * @return void
   */
  function signin()
  {
    global $_C, $USERPASS;
    $username = addslashes($_POST['username']);
    $password = addslashes($_POST['password']);

    $user = Table("user")->fetch_first_by_username($username);
    $passPeppered = hash_hmac("sha256", $password, $USERPASS['salt']);
    if (!password_verify($passPeppered, $user['user_password'])) {
      return Response(4010001, "登录失败，用户名或密码错误", 401);
    }
    $_C['user_id'] = $user['user_id'];
    $_C['user'] = $user;
    include_once includeClass("token");
    $Token = new Token();
    $Token->refreshToken();
    return [
      "message" => "登录成功",
      "data" => []
    ];
  }

  /**
   * 验证用户凭证
   *
   * @return void
   */
  function verify()
  {
    global $_C;
    return $_C['user'];
  }

  /**
   * 获取指定用户详细信息
   *
   * @return array
   */
  function getUser()
  {
    $openid = addslashes($_GET["_id"]);

    $result = Cloud::callFunction("User", "getUserProfile", [
      "_userid" => $openid
    ]);

    if ($result['errcode'] != 0) {
      Response(null, "WECHAT_CLOUD_DATABASE_QUERY_ERROR");
    }
    $result = $result['resp_data'];

    return $result;
  }

  /**
   * 获取所有用户
   * @param integer $page 页数
   * @param integer $limit 获取的数据量
   * @return array 用户数据
   */
  function getUsers()
  {
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $page = $page - 1 < 0 ? 0 : $page - 1;

    $result = Cloud::http("db")::query("db.collection('user').field({_id:true,avatar_url:true,nickname:true,status:true}).skip($page).limit($limit).get()");
    if ($result['errcode'] > 0) {
      Response(null, "WECHAT_CLOUD_DATABASE_QUERY_ERROR");
    }
    return $result['data'];
  }

  /**
   * 获取指定用户已加入的学校
   * @param string<array> $_userid 用户OPENID
   * @return void
   */
  function getUserJoinedSchool()
  {
    $_userid = addslashes($_GET['_id']);

    $result = Cloud::callFunction("Dashboard", "getUserJoinedSchool", [
      "_userid" => $_userid
    ]);
    if ($result['errcode'] != 0) {
      Response(null, "WECHAT_CLOUD_DATABASE_QUERY_ERROR");
    }
    $result = $result['resp_data'];
    return $result;
  }

  /**
   * 获取用户已加入的班级
   * @param string<array> @_userid 用户OPENID
   * @return void
   */
  function getUserJoinedClass()
  {
    $_userid = addslashes($_GET['_id']);

    $result = Cloud::callFunction("Dashboard", "getUserJoinedClass", [
      "_userid" => $_userid
    ]);
    if ($result['errcode'] != 0) {
      Response(null, "WECHAT_CLOUD_DATABASE_QUERY_ERROR");
    }
    $result = $result['resp_data'];
    return $result;
  }

  /**
   * 上传用户头像
   *
   * @return void
   */
  function uploadUserAvatar()
  {
    $avatarFile = $_FILES['avatar'];

    if ($avatarFile['size'] == 0) {
      Response("上传失败，文件错误", 400, 4000001);
    }

    $fileId = Cloud::http("storage")::uploadFile($avatarFile, "user/avatar/");
    $fileTempFile = Cloud::http("storage")::downloadFile([
      [
        "fileid" => $fileId,
        "max_age" => 1300
      ]
    ]);

    return $fileTempFile[0];
  }

  function saveUser()
  {
    global $USERPASS;
    $avatarUrl = addslashes($_GET['avatar_url']);
    $username = addslashes($_GET['username']);
    $password = addslashes($_GET['password']);
    $nickname = addslashes($_GET['nickname']);
    $group = addslashes($_GET['group']);
    $registationData = time() * 1000;

    $password = password_hash($password, PASSWORD_BCRYPT);

    $result = Cloud::http("db")::add("db.collection('user').add({
      data:{
        avatar_url:'$avatarUrl',
        username:'$username',
        password:'$password',
        nickname:'$nickname',
        group:'$group',
        registation_date:$registationData,
        allow_access:true,
        allow_comment:true,
        allow_post:true,
        class:null,
        school:null,
        credits:0,
        expreience:100,
        fans:0,
        message_news:{},
        report_weight:100,
        status:'normal'
      }
    })");

    if ($result['errcode'] != 0) {
      Response(null, "WECHAT_CLOUD_DATABASE_QUERY_ERROR");
    }

    $userId = $result['id_list'][0];

    Cloud::http("db")::add("db.collection('user_profile').add({
      data:{
        _userid:'$userId',
        birthday:0,
        education:null,
        email:null,
        gender:'secret',
        location:null,
        phone_number:null,
        realname:'',
        'space_bg_image':null,
        statement:''
      }
    })");

    $userInfo = [
      "_id" => $userId,
      "avatar_url" => $avatarUrl,
      "username" => $username,
      "password" => $password,
      "nickname" => $nickname,
      "group" => $group,
      "registation_date" => $registationData,
      "allow_access" => true,
      "allow_comment" => true,
      "allow_post" => true,
      "class" => null,
      "school" => null,
      "credits" => 0,
      "expreience" => 100,
      "fans" => 0,
      "message_news" => [],
      "report_weight" => 100,
      "status" => 'normal'
    ];

    return  $userInfo;
  }
}

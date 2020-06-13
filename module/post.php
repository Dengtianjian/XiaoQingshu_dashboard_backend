<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}


class post
{
  public $methods = [
    "uploadPostImage",
    "savePost"
  ];

  /**
   * 上传帖子图片
   *
   * @return array  图片数组包含云id 和临时地址
   */
  function uploadPostImage()
  {
    $file = $_FILES['image'];

    if ($file['size'] == 0) {
      Response("上传失败，图片错误", 400, 4000001);
    }

    $fileId = Cloud::http("storage")::uploadFile($file, "post/");

    $file = Cloud::http("storage")::downloadFile([
      [
        "fileid" => $fileId,
        "max_age" => 1300
      ]
    ]);

    return $file[0];
  }

  function savePost()
  {
    $title = addslashes($_GET['title']);
    $content = $_GET['content'];
    $content = preg_replace("/\n/", "<br/>", $content, -1, $count);
    $images = $_GET['images'];
    $images = explode(",", $_GET['images']);
    $images = json_encode($images);
    $_author = addslashes($_GET['_author']);
    $sort = addslashes($_GET['sort']);

    $result = Cloud::http("db")::add("db.collection('post').add({
      data:{
        title:'$title',
        content:'$content',
        images:$images,
        _authorid:'$_author',
        _school:null,
        checkResult:null,
        closed:false,
        hidden:false,
        likes:0,
        replies:0,
        sort:'$sort',
        status:'normal',
        topic:null,
        videos:[],
        views:0
      }
    })");

    if ($result['errcode'] != 0) {
      Response(null, "WECHAT_CLOUD_DATABASE_QUERY_ERROR");
    }

    $postID = $result['id_list'][0];
    Cloud::http("db")::update("db.collection('user').doc('$_author').update({
      data:{
        posts:_.inc(1)
      }
    })");

    return $postID;
  }
}

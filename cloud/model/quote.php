<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}

class Cloud_model_quote extends Cloud
{
  function getAll($skip = 0, $limit = 10)
  {
    $result = Cloud::http("db")::query("db.collection('quote').skip($skip).limit($limit).get()");
    if ($result['errcode']) {
      Response(null, "WECHAT_CLOUD_DATABASE_QUERY_ERROR");
    }
    $result = $result['data'];
    return $result;
  }

  function add($data)
  {
    $result = Cloud::http('db')::add("db.collection('quote').add({data:{content:'{$data['content']}',likes:0}})");

    if ($result['errcode']) {
      Response(null, "WECHAT_CLOUD_DATABASE_QUERY_ERROR");
    }

    $result = $result['id_list'][0];

    return $result;
  }

  function updateById($_id, $content)
  {
    $result = Cloud::http('db')::update("db.collection('quote').where({_id:'{$_id}'}).update(
      {data:{content:'{$content}'}}
    )");

    if ($result['errcode']) {
      Response(null, "WECHAT_CLOUD_DATABASE_QUERY_ERROR");
    }
    return $result;
  }

  function deleteById($_id)
  {
    $result = Cloud::http('db')::delete("db.collection('quote').where({_id:'{$_id}'}).remove()");

    if ($result['errcode']) {
      Response(null, "WECHAT_CLOUD_DATABASE_QUERY_ERROR");
    }
    return $result['deleted'];
  }
}

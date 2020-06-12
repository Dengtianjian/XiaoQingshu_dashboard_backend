<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}


class school
{
  public $methods = [
    "getAllSchool",
    "uploadSchoolLogo",
    "saveSchoolInfo"
  ];

  function getAllSchool()
  {
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
    $schools = Cloud::callFunction("Dashboard", "getAllSchool", [
      "limit" => $limit,
      "page" => $page
    ]);

    if ($schools['errcode'] != 0) {
      Response(null, "WECHAT_CLOUD_DATABASE_QUERY_ERROR");
    }
    $schools = $schools['resp_data'];


    $schoolLogo = [];
    foreach ($schools as $schoolItem) {
      if (strpos($schoolItem['logo'], "cloud://") === 0) {
        array_push($schoolLogo, [
          "fileid" => $schoolItem['logo'],
          "max_age" => 1700,
        ]);
      }
    }
    $tempFile = Cloud::http("storage")::downloadFile($schoolLogo);
    foreach ($schools as &$schoolItem) {
      foreach ($tempFile as $item) {
        if ($schoolItem['logo'] == $item['fileid']) {
          $schoolItem['logo'] = $item['download_url'];
        }
      }
    }


    return $schools;
  }

  function uploadSchoolLogo()
  {
    $logo = $_FILES['logo'];
    if ($logo['size'] <= 0) {
      Response("无效的文件", 400, 4000001);
    }

    $result = Cloud::http("storage")::uploadFile($logo, "school/logo/");
    $result = Cloud::http("storage")::downloadFile([
      [
        "fileid" => $result,
        "max_age" => 7200
      ]
    ]);
    if (count($result) == 0) {
      return [];
    }

    return $result[0];
  }

  function saveSchoolInfo()
  {
    $logo = addslashes($_GET['logo']);
    $name = addslashes($_GET['name']);
    $type = addslashes($_GET['type']);

    $result = Cloud::callFunction("Dashboard", "saveSchoolInfo", [
      "logo" => $logo,
      "name" => $name,
      "type" => $type
    ]);

    if ($result['errcode'] != 0) {
      Response(null, "WECHAT_CLOUD_DATABASE_QUERY_ERROR");
    }

    return $result['resp_data'];
  }
}

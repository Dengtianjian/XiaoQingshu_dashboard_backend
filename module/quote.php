<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}


class quote
{
  public $methods = [
    "getAll",
    "saveQuote",
    "deleteQuote"
  ];

  function getAll()
  {
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
    $skip = $page * $limit;
    $result = Cloud::model("quote")->getAll($skip, $limit);
    foreach ($result as &$item) {
      $item['content'] = str_replace("<br/>", "\n", $item['content']);
    }
    return $result;
  }

  function saveQuote()
  {
    $quoteId = $_GET['_id'];
    $content = addslashes($_GET['content']);
    $content = str_replace("\r", "", $content);
    $content = str_replace("\n", "<br/>", $content);

    if ($quoteId == "null" || !$quoteId) {
      $result = Cloud::model("quote")->add([
        "content" => nl2br($content)
      ]);
    } else {
      $result = Cloud::model("quote")->updateById(
        $quoteId,
        nl2br($content)
      );
    }
    return $result;
  }

  function deleteQuote()
  {
    $quoteId = addslashes($_GET['_id']);
    $result = Cloud::model("quote")->deleteById($quoteId);

    return $result;
  }
}

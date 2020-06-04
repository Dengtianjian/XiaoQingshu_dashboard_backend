<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}

class Cloud_http_storage extends Cloud
{
  /**
   * 上传文件
   *
   * @param array<string> $uploadFile 本地临时文件数组或者文件路径
   * @param string $saveCloudPath 保存到云存储的路径
   * @return string 云文件ID
   */
  static function uploadFile($uploadFile, $saveCloudPath = "temp/")
  {
    if (gettype($uploadFile) == "string") {
      $uploadFile = [
        "name" => $uploadFile,
        "tmp_name" => $uploadFile
      ];
    }
    $fileName = time() . "" . random_int(100, 99999);
    $fileExtension = substr($uploadFile['name'], strrpos($uploadFile['name'], "."));
    $filePath = "{$saveCloudPath}{$fileName}{$fileExtension}";
    $result = Cloud::request("uploadfile", [
      "path" => $filePath
    ]);
    if ($result['errcode'] != 0) {
      return $result;
    }

    $uploadUrl = $result['url'];
    $uploadToken = $result['token'];
    $uploadAuthorization = $result['authorization'];
    $uploadFileId = $result['file_id'];
    $uploadCosFileId = $result['cos_file_id'];
    HTTP::post($uploadUrl, [
      "key" => $filePath,
      "Signature" => $uploadAuthorization,
      "x-cos-security-token" => $uploadToken,
      "x-cos-meta-fileid" => $uploadCosFileId,
      "file" => file_get_contents($uploadFile['tmp_name'])
    ], [
      "content-type" => "multipart/form-data"
    ]);
    return $uploadFileId;
  }
  /**
   * 获取文件下载链接
   *
   * @param array.<object> $fileList 文件列表
   * @return array 文件列表
   */
  static function downloadFile($fileList)
  {
    if (count($fileList) === 0) {
      return [];
    }
    $result = Cloud::request("batchdownloadfile", [
      "file_list" => $fileList
    ]);
    if ($result['errcode'] != 0) {
      return $result;
    }
    return $result['file_list'];
  }
  /**
   * 删除文件
   *
   * @param array.<string> $fileList 文件列表
   * @return array 删除的文件列表
   */
  static function deleteFile($fileList)
  {
    if (count($fileList) === 0) {
      return [];
    }
    $result = Cloud::request("batchdeletefile", [
      "fileid_list" => $fileList
    ]);
    if ($result['errcode'] != 0) {
      return $result;
    }
    return $result['delete_list'];
  }
}

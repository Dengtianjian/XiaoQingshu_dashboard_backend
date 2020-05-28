<?php

if (!defined("IN_C")) {
  exit("NO ACCESS");
}

class Cloud_DB extends Cloud
{
  /**
   * 查询
   *
   * @param string $statement 查询语句
   * @return object pager 分页信息
   * @return array.<string> data 记录数组
   */
  static function query($statement)
  {
    $result = self::request("databasequery", [
      "query" => $statement
    ]);
    return $result;
  }
  /**
   * 聚合
   *
   * @param string $statement 聚合查询语句
   * @return array.<string> 记录数组
   */
  static function aggregate($statement)
  {
    $result = self::request("databaseaggregate", [
      "query" => $statement
    ]);
    return $result;
  }
  /**
   * 更新
   *
   * @param string $statement 更新语句
   * @return integer matched 更新条件匹配到的结果数
   * @return integer modified 修改的记录数，注意：使用set操作新插入的数据不计入修改数目
   * @return string id 新插入记录的id，注意：只有使用set操作新插入数据时这个字段会有值
   */
  static function update($statement)
  {
    $result = self::request("databaseupdate", [
      "query" => $statement
    ]);
    return $result;
  }
  /**
   * 删除
   *
   * @param string $statement 删除语句
   * @return integer 删除记录数量
   */
  static function delete($statement)
  {
    $result = self::request("databasedelete", [
      "query" => $statement
    ]);
    return $result;
  }
  /**
   * 插入
   *
   * @param string $statement 插入语句
   * @return array 执行结果
   */
  static function add($statement)
  {
    $result = self::request("databaseadd", [
      "query" => $statement
    ]);
    return $result;
  }
  /**
   * 获取集合信息
   *
   * @param int $limit 获取数量限制
   * @param int $offset 偏移量
   * @return array 集合信息
   */
  static function collectionGet($limit, $offset)
  {
    $result = self::request("databasecollectionget", [
      "limit" => $limit,
      "offset" => $offset
    ]);
    return $result;
  }
  /**
   * 删除集合
   *
   * @param string $collectionName 集合名称
   * @return boolean<array> 删除集合结果
   */
  static function collectionDelete($collectionName)
  {
    $result = self::request("databasecollectionget", [
      "collection_name" => $collectionName
    ]);
    if ($result['errcode'] == 0) {
      return true;
    }
    return $result;
  }
  /**
   * 新增集合
   *
   * @param string $collectionName 集合名称
   * @return boolean<array> 增加集合结果
   */
  static function collectionAdd($collectionName)
  {
    $result = self::request("databasecollectionget", [
      "collection_name" => $collectionName
    ]);
    if ($result['errcode'] == 0) {
      return true;
    }
    return $result;
  }
  /**
   * 更新索引
   *
   * @param string $collectionName 集合名称
   * @param array.<object> $createIndexs 新增索引
   * @param array.<object> $dropIndexs 删除索引
   * @return boolean<array> 更新索引结果
   */
  static function updateIndex($collectionName, $createIndexs, $dropIndexs)
  {
    $result = self::request("updateindex", [
      "collection_name" => $collectionName
    ]);
    if ($result['errcode'] == 0) {
      return true;
    }
    return $result;
  }
  /**
   * 迁移状态查询
   *
   * @param string $jobId 迁移任务ID
   * @return array 迁移状态
   */
  static function migrateQueryInfo($jobId)
  {
    $result = self::request("databasemigratequeryinfo", [
      "job_id" => $jobId
    ]);
    return $result;
  }
  /**
   * 数据库导出
   *
   * @param string $filePath 导出得云存储文件路径 文件会导出到同环境的云存储中
   * @param int.[1,2] $fileType 导出文件类型 1=JSON 2=CSV
   * @param string $query 导出条件
   * @return int job_id 任务ID
   */
  static function migrateExport($filePath, $fileType = 1, $query)
  {
    $result = self::request("databasemigrateexport", [
      "file_path" => $filePath,
      "file_type" => $fileType,
      "query" => $query
    ]);
    return $result;
  }
  /**
   * 数据库导入
   *
   * @param string $collectionName 导入collection名
   * @param string $filePath 导入文件路径 (导入文件需先上传到同环境的存储中，可使用开发者工具或 HTTP API的上传文件 API上传）
   * @param integer $fileType 导入文件类型
   * @param boolean $stopOnError 是否在遇到错误时停止导入
   * @param integer $conflictMode 冲突处理模式
   * @return integer job_id 导入任务ID，可使用数据库迁移进度查询 API 查询导入进度及结果

   */
  static function migrateImport($collectionName, $filePath, $fileType = 1, $stopOnError = true, $conflictMode = 1)
  {
    $result = self::request("databasemigrateexport", [
      "collection_name" => $collectionName,
      "file_path" => $filePath,
      "file_type" => $fileType,
      "stop_on_error" => $stopOnError,
      "conflictMode" => $conflictMode
    ]);
    return $result;
  }
}

<?php
// Very short CRUD helpers built on existing prepared helpers
// Usage: pass SQL, types string, and params array.

function crud_insert(string $sql, string $types = "", array $params = []) {
  return executePreparedUpdate($sql, $types, $params);
}

function crud_update(string $sql, string $types = "", array $params = []) {
  return executePreparedUpdate($sql, $types, $params);
}

function crud_delete(string $sql, string $types = "", array $params = []) {
  return executePreparedUpdate($sql, $types, $params);
}

function crud_find_all(string $sql, string $types = "", array $params = []) : array {
  $rs = executePreparedQuery($sql, $types, $params);
  if (!$rs) return [];
  $out = [];
  while ($row = $rs->fetch_assoc()) { $out[] = $row; }
  return $out;
}

function crud_find_one(string $sql, string $types = "", array $params = []) : ?array {
  $rs = executePreparedQuery($sql, $types, $params);
  if (!$rs) return null;
  $row = $rs->fetch_assoc();
  return $row ?: null;
}

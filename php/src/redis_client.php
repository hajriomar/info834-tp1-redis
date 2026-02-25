<?php
function redis_db0(): Redis {
  $r = new Redis();
  $r->connect("redis", 6379);
  $r->select(0);
  return $r;
}

function redis_db1(): Redis {
  $r = new Redis();
  $r->connect("redis", 6379);
  $r->select(1);
  return $r;
}

function rk(string $key): string {
  return "tpredis:" . $key;
}
?>
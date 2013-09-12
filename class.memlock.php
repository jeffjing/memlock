<?php
/**
 * 使用Memcache实现给进程加锁的类
 *
 * Copyright (C) 2013 JeffJing 
 * 
 * 一些时候需要让系统的某些操作串行化，这个时候就要对这些操作来加上一把锁。 好比你去上厕所, 你要先推厕所门看能否进去，进去的话上锁，其他人就进不来了， 等你拉完粑粑之后把锁打开，这样的话就保证了厕所永远只有1个人， 上厕所的过程是串行化的， 同时只有1个人上。
 * 网上的一些解决方案大多是使用文件的， 我就纳闷了，加锁的时候创建一个文件， 解锁的时候把文件删掉。 
 * 我就纳闷： Why not memcached ? 顺手写了这么一个 , 希望对大家"拉粑粑"的这种操作有所帮助
 * 
 * 举个栗子: 
 * 	$key = '厕锁';
 * 	if(MemLock::addLoack($key)) {
 * 		//拉粑粑喽,pu~pu~~~~~
 * 		MemLock::releaseLock($key);
 * 	} else {
 * 		//不好意思， 厕所有人啦！！
 * 	}
 * 
 * http://www.phpv5.com
 */
class MemLock {
	private static $memcache = null;
	
	/**
	 * 获取memcached连接
	 *
	 * @return Memcached
	 */
	public static function getConnection() {
		if (! isset ( self::$memcache )) {
			self::$memcache = new Memcache ();
			self::$memcache->connect ( '127.0.0.1', 11211 );
		}
		return self::$memcache;
	}
	
	/**
	 * 加锁
	 *
	 * @param $key 锁关键字        	
	 *
	 * @return boolean true 成功获取到锁 false 获取锁失败
	 */
	public static function addLock($key) {
		$memcache = self::getConnection ();
		//不存在的话，先创建空值
		$v = $memcache->get ( $key );
		if ($v === false) {
			$memcache->set ( $key, 0 );
		}
		$index = $memcache->increment ( $key, 1 );
		
		if ($index == 1) {
			return true;
		}
		return false;
	}
	
	/**
	 * 释放锁
	 *
	 * @param $key 锁关键字        	
	 *
	 * @return boolean true 释放成功 false 释放失败
	 */
	public static function releaseLock($key) {
		$memcache = self::getConnection ();
		return $memcache->delete ( $key );
	}
}
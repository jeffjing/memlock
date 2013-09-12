<?php
/**
 * ʹ��Memcacheʵ�ָ����̼�������
 *
 * Copyright (C) 2013 JeffJing 
 * 
 * һЩʱ����Ҫ��ϵͳ��ĳЩ�������л������ʱ���Ҫ����Щ����������һ������ �ñ���ȥ�ϲ���, ��Ҫ���Ʋ����ſ��ܷ��ȥ����ȥ�Ļ������������˾ͽ������ˣ� ������������֮������򿪣������Ļ��ͱ�֤�˲�����Զֻ��1���ˣ� �ϲ����Ĺ����Ǵ��л��ģ� ͬʱֻ��1�����ϡ�
 * ���ϵ�һЩ������������ʹ���ļ��ģ� �Ҿ������ˣ�������ʱ�򴴽�һ���ļ��� ������ʱ����ļ�ɾ���� 
 * �Ҿ����ƣ� Why not memcached ? ˳��д����ôһ�� , ϣ���Դ��"������"�����ֲ�����������
 * 
 * �ٸ�����: 
 * 	$key = '����';
 * 	if(MemLock::addLoack($key)) {
 * 		//�������,pu~pu~~~~~
 * 		MemLock::releaseLock($key);
 * 	} else {
 * 		//������˼�� ��������������
 * 	}
 * 
 * http://www.phpv5.com
 */
class MemLock {
	private static $memcache = null;
	
	/**
	 * ��ȡmemcached����
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
	 * ����
	 *
	 * @param $key ���ؼ���        	
	 *
	 * @return boolean true �ɹ���ȡ���� false ��ȡ��ʧ��
	 */
	public static function addLock($key) {
		$memcache = self::getConnection ();
		//�����ڵĻ����ȴ�����ֵ
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
	 * �ͷ���
	 *
	 * @param $key ���ؼ���        	
	 *
	 * @return boolean true �ͷųɹ� false �ͷ�ʧ��
	 */
	public static function releaseLock($key) {
		$memcache = self::getConnection ();
		return $memcache->delete ( $key );
	}
}
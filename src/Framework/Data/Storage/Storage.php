<?php

namespace Framework\Data\Storage;

use Framework\DB\DB;
use Framework\DB\DBLoader;

/**
 * Базовый класс для хранилищ объектов
 *
 * @author mkoshkin
 */
abstract class Storage {
    
    /**
     * @var DB
     */
    protected $db;
    
    /**
     * @var DBLoader
     */
    protected $dbLoader;
        
    /**
     * @param DB $db
     * @param DBLoader $dbLoader
     */
	public function __construct(DB $db, DBLoader $dbLoader) {
		$this->db = $db;
        $this->dbLoader = $dbLoader;
	}
}

<?php

namespace library\cache;

class mysql {

    private $link = null;

    public function __construct($db_host, $db_user, $db_pass, $db_name) {
        mb_internal_encoding('UTF-8');
        mb_regex_encoding('UTF-8');
        $this->link = new \mysqli($db_host, $db_user, $db_pass, $db_name);
        $this->link->set_charset("utf8");
    }

    public function __destruct() {
        if ($this->link) {
            $this->disconnect();
        }
    }

    /**
     * 查询并返回结果
     * @param type $query
     * @return boolean
     */
    public function query($query) {
        $results = $this->link->query($query);
        if ($this->link->error) {
            return false;
        } else {
            $row = array();
            while ($r = $results->fetch_assoc()) {
                $row[] = $r;
            }
            return $row;
        }
    }

    /**
     * Disconnect from db server
     * Called automatically from __destruct function
     */
    public function disconnect() {
        $this->link->close();
    }

}

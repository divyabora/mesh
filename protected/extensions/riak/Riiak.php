<?php

/**
 * The Riiak object holds information necessary to connect to
 * Riak. The Riak API uses HTTP, so there is no persistent
 * connection, and the Riiak object is extremely lightweight.
 * @package Riiak
 */
class Riiak extends CApplicationComponent {

    /**
     * Hostname or IP address
     * 
     * @var string Default: '127.0.0.1'
     */
    public $host = 'ec2-184-72-184-220.compute-1.amazonaws.com';
    /**
     * Port number
     * 
     * @var int Default: 8098
     */
    public $port = 8098;
    /**
     * Whether SSL is enabled
     * 
     * @var bool Default: false
     */
    public $ssl = false;
    /**
     * Interface prefix
     * 
     * @var string Default: 'riak'
     */
    public $prefix = 'riak';
    /**
     * MapReduce prefix
     * 
     * @var string Default: 'mapred'
     */
    public $mapredPrefix = 'mapred';
    /**
     * The clientID for this Riak client instance.
     * Only specify if you know what you're doing.
     * 
     * @var string
     */
    public $clientId;
    /**
     * R-Value setting for client.
     * Used by other Riiak class components as fallback value.
     *
     * @var int Default: 2
     */
    public $r = 2;
    /**
     * W-Value setting for client.
     * Used by other Riiak class components as fallback value.
     *
     * @var int Default: 2
     */
    public $w = 2;
    /**
     * DW-Value setting for client.
     * Used by other Riiak class components as fallback value.
     *
     * @var int Default: 2
     */
    public $dw = 2;
    /**
     * @var RiiakMapReduce
     */
    protected $mr;

    public function init() {
        parent::init();
        /**
         * Default the value of clientId if not already specified
         */
        if (empty($this->clientId))
            $this->clientId = 'php_' . base64_encode(rand(1, 1073741824));
    }

    /**
     * Get bucket by name
     *
     * @param string $name
     * @return RiiakBucket 
     */
    public function bucket($name) {
        return new RiiakBucket($this, $name);
    }

    /**
     * Return array of RiiakBucket objects
     *
     * @return array
     */
    public function buckets() {
        $response = RiiakUtils::httpRequest('GET', RiiakUtils::buildRestPath($this) . '?buckets=true');
        $responseObj = CJSON::decode($response['body']);
        $buckets = array();
        foreach ($responseObj->buckets as $name)
            $buckets[] = $this->bucket($name);
        return $buckets;
    }

    /**
     * Check if Riak server is alive
     *
     * @return bool
     */
    public function getIsAlive() {
        $response = RiiakUtils::httpRequest('GET', RiiakUtils::buildUrl($this) . '/ping');
        return ($response != NULL) && ($response['body'] == 'OK');
    }

    /**
     * Returns the RiiakMapReduce instance (created if not exists)
     *
     * @param bool $reset Whether to create a new RiiakMapReduce instance
     * @return RiiakMapReduce
     */
    public function getMapReduce($reset=false) {
        if ($reset || !($this->mr instanceof RiiakMapReduce))
            $this->mr = new RiiakMapReduce($this);
        return $this->mr;
    }

}
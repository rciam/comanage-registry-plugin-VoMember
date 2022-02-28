<?php

class VomsMemberClone extends AppModel
{
  public $name = "VomsMemberClone";

  public $useTable = false;

  protected $_schema = array(
    'id' => array('type' => 'integer', 'autoIncrement' => true, 'null' => false, 'default' => null, 'length' => 10, 'key' => 'primary'),
    'username' => array('type' => 'string', 'null' => true, 'length' => 512),
    'email' => array('type' => 'string', 'null' => true, 'length' => 512),
    'subject' => array('type' => 'string', 'null' => true, 'length' => 512),
    'issuer' => array('type' => 'string', 'null' => true, 'length' => 512),
    'vo_id' => array('type' => 'string', 'null' => true, 'length' => 256),
    'fqans' => array('type' => 'text', 'null' => true),
    'first_upate' => array('type' => 'datetime', 'null' => true),
    'last_update' => array('type' => 'datetime', 'null' => true),
    'indexes' => array(
      'PRIMARY' => array('column' => 'id', 'unique' => 1),
      'voms_members_i1' => array('column' => 'subject', 'unique' => 0),
      'voms_members_i2' => array('column' => 'issuer', 'unique' => 0),
      'voms_members_i3' => array('column' => 'vo_id', 'unique' => 0),
      'voms_members_i4' => array('column' => array('vo_id', 'issuer', 'subject'), 'unique' => 1),
      'voms_members_i5' => array('column' => 'email', 'unique' => 0),
    )
  );

  private $mapper = array(
    'USERVO' => 'username',
    'EMAIL'  => 'email',
    'CERTDN' => 'subject',
    'CA'     => 'issuer',
    'VO'     => 'vo_id',
    'fqans'  => 'fqans',
    'FIRST_UPDATE' => 'first_update',
    'LAST_UPDATE'  => 'last_update'
  );

  // Validation rules for table elements
  public $validate = array(
    'vo_id' => array(
      'rule' => '/.*/',
      'required' => true,
      'message' => 'A VO ID must be provided',
    ),
    'subject' => array(
      'content' => array(
        'rule' => array('maxLength', 512),
        'required' => false,
        'allowEmpty' => true,
        'message' => 'Please enter a valid cert subject DN',
      ),
      'filter' => array(
        'rule' => array('validateInput'),
      ),
    ),
    'issuer' => array(
      'content' => array(
        'rule' => array('maxLength', 512),
        'required' => false,
        'allowEmpty' => true,
        'message' => 'Please enter a valid cert issuer DN',
      ),
      'filter' => array(
        'rule' => array('validateInput'),
      ),
    ),
    'email' => array(
      'rule' => '/.*/',
      'required' => false,
      'allowEmpty' => true
    ),
    'username' => array(
      'rule' => '/.*/',
      'required' => false,
      'allowEmpty' => true
    ),
    'fqan' => array(
      'rule' => '/.*/', // TODO: Ideally we should be checking for JSON data
      'required' => false,
      'allowEmpty' => true
    ),
    'first_update' => array(
      'content' => array(
        'rule' => array('validateTimestamp'),
        'required' => false,
        'allowEmpty' => true
      )
    ),
    'last_update' => array(
      'content' => array(
        'rule' => array('validateTimestamp'),
        'required' => false,
        'allowEmpty' => true
      )
    )
  );

  /**
   * @param $from_table
   * @return void
   */
  public function tblCreate($from_table) {
    $db = ConnectionManager::getDataSource('default');
    $db->begin();

    if(isset($db->config['prefix'])) {
      $prefix = $db->config['prefix'];
    }

    try {
      // Create table as cm_voms_members only schema
      $table = Inflector::tableize($this->name);
      $table_with_prefix = ($prefix ?? '') . $table;

      $this->query('create table ' . $table_with_prefix . ' ( like ' . $from_table . ' including defaults including constraints including indexes)');
      $this->query('CREATE SEQUENCE IF NOT EXISTS ' . $table_with_prefix . '_id_seq');
      $this->query('ALTER SEQUENCE ' . $table_with_prefix . '_id_seq OWNED BY ' . $table_with_prefix . '.id');
      $this->query('ALTER TABLE ' . $table_with_prefix . ' ALTER COLUMN id SET DEFAULT nextval(\'' . $table_with_prefix . '_id_seq\')');
    } catch (Exception $e) {
      $err = filter_var($e->getMessage(),FILTER_SANITIZE_SPECIAL_CHARS);
      $this->log(__METHOD__ . "::error message => " . $err, LOG_DEBUG);
      $db->rollback();
      return;
    }
    $db->cacheSources = false;
    $db->commit();
    // Bind the Model to the newly created table
    $this->setSource($table);
  }

  /**
   * @param $data
   * @return void
   */
  public function importData($data) {
    $db = ConnectionManager::getDataSource('default');
    $db->begin();
    if(isset($db->config['prefix'])) {
      $prefix = $db->config['prefix'];
    }

    $table = Inflector::tableize($this->name);
    // Truncate in case something bad happened the last time i ran
    // and the clone did not rename.
    $this->query("TRUNCATE " . ($prefix ?? '') . $table);

    // Reset the model state
    $this->create($data);

    try {
      $ret = $this->saveAll($data);
    }
    catch(Exception $e) {
      $err = filter_var($e->getMessage(),FILTER_SANITIZE_SPECIAL_CHARS);
      $this->log(__METHOD__ . "::error message => " . $err, LOG_DEBUG);
      $db->rollback();
      return;
    }

    if(!$ret) {
      $invalidFields = $this->invalidFields();
      $this->log(__METHOD__ . "::invalid fields => " . $invalidFields, LOG_DEBUG);
      $db->rollback();
      return;
    }

    $db->commit();
  }

  /**
   * @param $to_table
   * @return void
   */
  public function tableRename($to_table) {
    $db = ConnectionManager::getDataSource('default');
    $db->begin();

    if(isset($db->config['prefix'])) {
      $prefix = $db->config['prefix'];
    }

    try {
      // Create table as cm_voms_members only schema
      $table = ($prefix ?? '') . Inflector::tableize($this->name);

      $this->query('ALTER TABLE IF EXISTS ' . $table . ' RENAME TO ' . $to_table);
      // Unbind the model from the Table
      $this->useTable = false;
      $this->tableToModel= [];
    } catch (Exception $e) {
      $err = filter_var($e->getMessage(),FILTER_SANITIZE_SPECIAL_CHARS);
      $this->log(__METHOD__ . "::error message => " . $err, LOG_DEBUG);
      $db->rollback();
      return;
    }
    $db->commit();
  }

}
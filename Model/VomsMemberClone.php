<?php

class VomsMemberClone extends AppModel
{
  public $name = "VomsMemberClone";

  public $useTable = false;

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

      $this->query('CREATE TABLE IF NOT EXISTS ' . $table_with_prefix . ' AS TABLE ' . $from_table . ' WITH NO DATA');
      // Bind the Model to the newly created table
      $this->useTable = $table;
    } catch (Exception $e) {
      $err = filter_var($e->getMessage(),FILTER_SANITIZE_SPECIAL_CHARS);
      $this->log(__METHOD__ . "::error message => " . $err, LOG_DEBUG);
      $db->rollback();
      return;
    }
    $db->commit();
  }

  /**
   * @param $data
   * @return void
   */
  public function importData($data) {
    $db = ConnectionManager::getDataSource('default');
    $db->begin();
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
    } catch (Exception $e) {
      $err = filter_var($e->getMessage(),FILTER_SANITIZE_SPECIAL_CHARS);
      $this->log(__METHOD__ . "::error message => " . $err, LOG_DEBUG);
      $db->rollback();
      return;
    }
    $db->commit();
  }

}
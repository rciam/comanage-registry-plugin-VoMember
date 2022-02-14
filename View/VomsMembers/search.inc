<?php
/**
 * COmanage Registry Voms Members Search
 *
 * Portions licensed to the University Corporation for Advanced Internet
 * Development, Inc. ("UCAID") under one or more contributor license agreements.
 * See the NOTICE file distributed with this work for additional information
 * regarding copyright ownership.
 *
 * UCAID licenses this file to you under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry
 * @since         Voms Member v1.0.0
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

// Globals
global $cm_lang, $cm_texts;

?>

<script type="text/javascript">
  function clearSearch(formObj) {
    for (var i=0; i<formObj.elements.length; i++) {
      t = formObj.elements[i].type;
      if(t == "text" || t == "select-one" ) {
        formObj.elements[i].value = "";
      }
      if(t == "checkbox"){
        formObj.elements[i].checked = false;
      }
    }
    formObj.submit();
  }

  $(function() {
    var searchFieldsAreOpen = (Cookies.get('comanage-top-search-fields-visible') === 'true');
    if (searchFieldsAreOpen) {
      $('#vomsMemberSearch').addClass('open');
      $('#top-search-fields').show();
    }

    $( "#top-search-toggle").click(function() {
      $("#top-search-fields").toggle();
      var searchFieldsAreOpen = $("#top-search-fields").is(':visible');
      Cookies.set('comanage-top-search-fields-visible', searchFieldsAreOpen);
      if (searchFieldsAreOpen) {
        $("#vomsMemberSearch").addClass('open');
      } else {
        $("#vomsMemberSearch").removeClass('open');
      }
    });
  });
</script>

<div id="vomsMemberSearch" class="top-search voms-members-searh">
  <?php
  print $this->Form->create('VomsMember',
                             array('url' => array('controller' => 'voms_members', 'action'=>'search'),
                             'id'  => 'form_search')
  );

  $args = array();
  $args['label'] = _txt('fd.action');
  $args['placeholder'] = _txt('fd.action');
  //$args['class'] = 'mdl-textfield__input';
  $args['aria-label'] = _txt('fd.action');
  // XXX shouldn't these fields be sanitized?
  $args['value'] = $this->action;
  print $this->Form->hidden('Action.name', $args) . PHP_EOL;
  print $this->Form->hidden('VomsMember.co_id', array('default' => $cur_co['Co']['id'])) . PHP_EOL;
  print $this->Form->hidden('VomsMember.all', array('default' => ($vv_all) ? 1 : 0)) . PHP_EOL;
  ?>
  <fieldset>
    <legend>
      <a href="#" id="top-search-toggle">
        <em class="material-icons">search</em>
        <?php print _txt('op.filter');?>
        <em class="material-icons drop-arrow">arrow_drop_down</em>
      </a>
    </legend>

    <div id="top-search-fields">
      <div class="search-field-subgroup">
        <?php
        $args = array();
        $args['label'] = _txt('sh.vo_members.subjectdn');
        $args['placeholder'] = _txt('sh.vo_members.subjectdn');
        $args['required'] = false;
        $args['aria-label'] = _txt('sh.vo_members.subjectdn');
        // XXX shouldn't these fields be sanitized?
        $args['value'] = !empty($this->request->params['named']['search.subject']) ? urlParamDecode($this->request->params['named']['search.subject']) : '';
        print $this->Form->input('search.subject', $args);

        $args = array();
        $args['label'] = _txt('sh.vo_members.issuerdn');
        $args['placeholder'] = _txt('sh.vo_members.issuerdn');
        $args['required'] = false;
        $args['aria-label'] = _txt('sh.vo_members.issuerdn');
        // XXX shouldn't these fields be sanitized?
        $args['value'] = !empty($this->request->params['named']['search.issuer']) ? urlParamDecode($this->request->params['named']['search.issuer']) : '';
        print $this->Form->input('search.issuer',$args);
        ?>
      </div>

      <div class="search-field-subgroup">
        <?php
        if($vv_voms_list_name === false) {
          $args = array();
          $args['label'] = _txt('sh.vo_members.vo_id');
          $args['placeholder'] = _txt('sh.vo_members.vo_id');
          $args['required'] = false;
          $args['aria-label'] = _txt('sh.vo_members.vo_id');
          // XXX shouldn't these fields be sanitized?
          $args['value'] = !empty($this->request->params['named']['search.void']) ? urlParamDecode($this->request->params['named']['search.void']) : '';
          print $this->Form->input('search.void',$args);
        } else {
          $formParams = array('options' => array_combine($vv_voms_list_name, $vv_voms_list_name),
            'type' => 'select',
            'label' => _txt('sh.vo_members.vo_id'),
            'empty' => _txt('sh.vo_members.all'),
            'value' => (!empty($this->request->params['named']['search.void']) ? urlParamDecode($this->request->params['named']['search.void']) : ''));
          print $this->Form->input('search.void', $formParams);
        }
        ?>
      </div>

      <div class="topSearchSubmit">
        <?php
        $args = array();

        // search button (submit)
        $args['aria-label'] = _txt('op.filter');
        $args['class'] = 'submit-button spin mdl-button mdl-js-button mdl-button--raised mdl-button--colored mdl-js-ripple-effect';
        print $this->Form->button(_txt('op.filter'),$args);

        // clear button
        $args['class'] = 'clear-button spin mdl-button';
        $args['aria-label'] = _txt('op.clear.all');
        $args['onclick'] = 'clearSearch(this.form)';
        print $this->Form->button(_txt('op.clear.all'),$args);
        ?>
      </div>
    </div>
  </fieldset>
  <?php print $this->Form->end();?>
</div>
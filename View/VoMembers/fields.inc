<!--
/**
 * COmanage Registry Rciam Stats Viewer Plugin Fields
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
 * @package       registry-plugin
 * @since         COmanage Registry v3.1.x
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */
-->
<?php
// Determine if fields are editable
$e = false;

if ($this->action == "edit" && $vv_permissions['edit']) {
  $e = true;
}

// We shouldn't get here if we don't have at least read permission, but check just in case
if (!$e && !$vv_permissions['view']) {
  return false;
}

$model = $this->name;
$controller = Inflector::underscore(Inflector::pluralize($model));

$crumbTxt = _txt('op.config') . " " . _txt('ct.voms_members.1');
$this->Html->addCrumb($crumbTxt);

print $this->Form->hidden('co_id', array('default' => $cur_co['Co']['id'])) . "\n";
?>

<div id="<?php
print $this->action; ?>_vo_members" class="explorerContainer">
  <div id="voMembersConfig" class="personExplorer">
    <ul>
      <!-- RCIAM Stats Config -->
      <li id="fields-ops-portal" class="fieldGroup">
        <a href="#tabs-database" class="fieldGroupName">
          <em class="material-icons">indeterminate_check_box</em>
          <?php
          print _txt('pl.vo_members.ops');
          ?>
        </a>
        <div id="names-container" class="fields">
          <ul id="tabs-database" class="fields form-list">
            <li>
              <div class="field-name">
                <div class="field-title">
                  <?php
                  print _txt('pl.vo_members.base.url'); ?>
                  <span class="required">*</span>
                </div>
                <div class="field-desc"><?php
                  print _txt('pl.vo_members.base.url.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                $value = empty($vv_vo_members['VoMember']['base_url'])
                  ? ''
                  : filter_var($vv_vo_members['VoMember']['base_url'], FILTER_SANITIZE_SPECIAL_CHARS);
                print $this->Form->input('base_url', array('size' => 512, 'value' => $value));
                ?>
              </div>
            </li>
            <li>
              <div class="field-name">
                <div class="field-title">
                  <?php
                  print _txt('pl.vo_members.endpoint'); ?>
                  <span class="required">*</span>
                </div>
                <div class="field-desc"><?php
                  print _txt('pl.vo_members.endpoint.ops.desc'); ?></div>
              </div>
              <div class="field-info">
                <?php
                $value = empty($vv_vo_members['VoMember']['endpoint_ops'])
                  ? ''
                  : filter_var($vv_vo_members['VoMember']['endpoint_ops'], FILTER_SANITIZE_SPECIAL_CHARS);
                print $this->Form->input('endpoint_ops', array('size' => 512, 'value' => $value));
                ?>
              </div>
            </li>
            <li>
              <div class="field-name">
                <div class="field-title">
                  <?php
                  print _txt('pl.vo_members.authkey'); ?>
                  <span class="required">*</span>
                </div>
              </div>
              <div class="field-info">
                <?php
                $value = empty($vv_vo_members['VoMember']['authkey_ops'])
                  ? ''
                  : filter_var($vv_vo_members['VoMember']['authkey_ops'], FILTER_SANITIZE_SPECIAL_CHARS);
                print $this->Form->input('authkey_ops', array('size' => 512, 'value' => $value));
                ?>
              </div>
            </li>
          </ul>
        </div>
      </li>
      <li id="fields-voms" class="fieldGroup">
        <a href="#tabs-stats" class="fieldGroupName">
          <em class="material-icons">indeterminate_check_box</em>
          <?php
          print _txt('pl.vo_members.place.holder'); ?>
        </a>
        <div id="names-container" class="fields">
          <ul id="tabs-stats" class="fields form-list">
            <li>
              <div class="field-name">
                <div class="field-title">
                  <?php
                  print _txt('pl.vo_members.place.holder'); ?>
                  <span class="required">*</span>
                </div>
                <div class="field-desc"><?php
                  print _txt('pl.vo_members.place.holder'); ?></div>
              </div>
              <div class="field-info">
                <?php
                //                        $value = empty($vv_vo_members['VoMember']['statisticsTableName']) ? ''
                //                        : filter_var($vv_vo_members['VoMember']['statisticsTableName'],FILTER_SANITIZE_SPECIAL_CHARS);
                //                        print $this->Form->input('statisticsTableName', array('size' => 50,'value' => $value));
                ?>
              </div>
            </li>
          </ul>
        </div>
      </li>
      <li id="fields-btn" class="fieldGroup">  <!-- Save Button -->
        <div id="names-container" class="fields">
          <ul class="fields form-list">
            <?php
            if ($e): ?>
              <li class="fields-submit">
                <div class="field-name">
                  <span class="required"><?php
                    print _txt('fd.req'); ?></span>
                </div>
                <div class="field-info">
                  <?php
                  print $this->Form->submit($submit_label); ?>
                </div>
              </li>
            <?php
            endif; ?>
          </ul>
        </div>
      </li>
    </ul>
  </div> <!-- personExplorer -->
  <!-- This is currently not supported due to a bug in COmanage. --><?php
  //print $this->element("changelog"); ?>
</div> <!-- explorerContainer -->
<script type="text/javascript">
  function responseConstruct(msg, status, testBtn) {
    color = (status === 'error') ? 'red' : 'green';
    text = "<span style='color:" + color + "'>" + msg + "</span>";
    $("#connection-test-lbl").html(text);

    testBtn.toggleClass('ui-state-disabled');
    testBtn.find('span').remove();
    testBtn.text("<?php print _txt('fd.server.test_connection'); ?>");
    testBtn.removeAttr('disabled');
  }

  <!-- /* JS specific to these fields */ -->
  $(function () {
    // Explorer menu toggles
    $(".fieldGroupName").click(function (event) {
      event.preventDefault();
      $(this).next(".fields").slideToggle("fast");
      // toggle the +/- icon:
      if ($(this).find(".material-icons").text() == "indeterminate_check_box") {
        $(this).find(".material-icons").text("add_box");
      } else {
        $(this).find(".material-icons").text("indeterminate_check_box");
      }
    });

    // Update the default value each time i change
    $('input').on('keyup', function () {
      this.defaultValue = this.value;
    });
    $('select').on('change', function () {
      $("option[value=" + this.value + "]", this)
        .attr("selected", true).siblings()
        .removeAttr("selected");
    });

  });
</script>
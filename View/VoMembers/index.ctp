<?php
/**
 * COmanage Registry Voms Member Index View
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
 * @since         VomsMember v1.0.0
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */
?>

<script>
  // Observers list
  var accordion_observer = new Array();
  // var filterbox_observer = new Array();

  // Options for the Dropdown Action Menu Observer
  const cmVomsAccordionOptions = {
    attributes: true,
    attributeFilter: ['class']
  };

  function observeFilterVisibility(element, target_element_class, modify_class) {
    accordion_observer[element] = new MutationObserver((mutationList) => { // Use traditional 'for loops' for IE 11
      for(const mutation of mutationList) {
        if (mutation.type === 'attributes') {
          curr_elem = mutation.target;
          elem_children = mutation.target.childNodes;
          $filter_box = $(elem_children).find('.filter-textbox');
          if($(curr_elem).hasClass('ui-state-active')) {
            $filter_box.removeClass(modify_class);
          } else {
            $filter_box.addClass(modify_class);
          }
        }
      }
    });
    accordion_observer[element].observe(element,cmVomsAccordionOptions);
  }


  // https://stackoverflow.com/questions/32383349/detect-value-change-in-input-tag-with-vanilla-javascript-and-mutationobserver
  function observeFilterText(element, property, callback, delay = 0) {
    let elementPrototype = Object.getPrototypeOf(element);
    if (elementPrototype.hasOwnProperty(property)) {
      let descriptor = Object.getOwnPropertyDescriptor(elementPrototype, property);
      Object.defineProperty(element, property, {
        get: function() {
          return descriptor.get.apply(this, arguments);
        },
        set: function () {
          let oldValue = this[property];
          descriptor.set.apply(this, arguments);
          let newValue = this[property];
          if (typeof callback == "function") {
            setTimeout(callback.bind(this, oldValue, newValue), delay);
          }
          return newValue;
        }
      });
    }
  }

  // Reset the text
  function filter_reset(elem) {
    $filter_box = $(elem).siblings('.cert-filter');
    $filter_box.val("").focus();
    $filter_box.trigger("input");
  }

  $(function() {

    // Handle Accordion - Filter-textbox toggle
    $('.co-person .person-panel').each( (key, elem) => {
      // Add observer for open/close accordion
      observeFilterVisibility(elem, 'filter-textbox', 'hidden');
      // Add event listener fot text read
      $filter_box_elem = $(elem).find('.cert-filter');
      $filter_box_elem.on("input", function () {
        let value = this.value.toLowerCase();
        $roles = $(this).closest('.co-person').find('.roles').children();
        $roles.each(function(index) {
          let $role = $(this);
          let text_payload = $role.text().toLowerCase();
          if(text_payload.includes(value)) {
            // Highlight the found letter combinations
            $role.unmark({
              done: function() {
                $role.mark(value);
              }
            });
            $role.show();
          } else {
            $role.hide();
          }
        });
      });
    });

    $( ".co-person" ).accordion({
      collapsible: true,
      active     : false,
      heightStyle: "content"
    });

    $("#clearSearchButton").button();
  });

  function toggleVomsList(state) {
    if (state == 'open') {
      $(".co-person" ).accordion( "option", "active", 0 );
      $(".filter-textbox").css("visibility", "visible");
    } else {
      $(".co-person" ).accordion( "option", "active", false );
      $(".filter-textbox").css("visibility", "hidden");
    }
  }
</script>

<?php
// Load CSS and JS Libraries
print $this->Html->css('/VoMember/css/vo_members', array('inline' => false));
// Highlight library
print $this->Html->script('https://cdnjs.cloudflare.com/ajax/libs/mark.js/8.11.0/jquery.mark.js', array('inline' => false));

// Construct and load Page title
$title = ($vv_all) ? _txt('ct.vo_members_all.pl') : _txt('ct.vo_members_my.pl');
$params = array('title' => $title);
print $this->element("pageTitle", $params);

// Add breadcrumbs
print $this->element("coCrumb");
$this->Html->addCrumb(_txt('ct.vo_members.pl'));

// Add top links
$params['topLinks'] = array();

?>
<?php if($vv_permissions['all']): ?>
<div id="sorter" class="listControl">
  <ul>
    <li class="spin">
      <?php
      print $this->Html->link(
        ($vv_all) ? _txt('ct.vo_members_my.pl') : _txt('ct.vo_members_all.pl'),
        array(
          'controller' => 'vo_members',
          'action' => 'index',
          'co' => $this->params['named']['co'],
          'all' => ($vv_all) ? false : true,
        ),
        array('class' => 'notebutton')
      );
      ?>
    </li>
  </ul>
</div>
<?php endif; ?>

<div id="voms_toggle" class="listControl">
  <?php print _txt('fd.toggle.all'); ?>:
  <ul>
    <li><?php print $this->html->link(_txt('fd.open'),'javascript:toggleVomsList(\'open\');'); ?></li>
    <li><?php print $this->html->link(_txt('fd.closed'),'javascript:toggleVomsList(\'closed\');'); ?></li>
  </ul>
</div>
<?php
if($vv_permissions['search']) {
  // Load the top search form
  $fileLocation = LOCAL . DS . 'Plugin' . DS . 'VoMember' . DS . "View/VoMembers/search.inc";
  if(file_exists($fileLocation)) {
    include($fileLocation);
  }
}
?>
<div id="voms-name-header" class="listControl" aria-label="<?php print _txt('ct.vo_members.vo'); ?>">
  <?php print _txt('ct.vo_members.vo'); ?>
</div>
<div id="voms_members_list" class="population-index">
  <?php $i = 0; ?>
  <?php foreach ($vv_voms_list as $vo_name => $certs): ?>
    <div class="co-person line<?php print ($i % 2)+1; ?>">
      <div class="person-panel">
        <div class="person-info">
          <div class="person-info-inner">
            <span class="person-name nameWithEmail">
              <?php
              print $vo_name
              ?>
            </span>
            <span class="person-email">
              <?php
                print '( #' . count($certs) . ' )';
              ?>
            </span>
            <?php
            if( isset($vv_cous[$vo_name]) ) {
              print '<span class="person-status">';
              print _txt('pl.vo_members.inregistry');
              print '</span>';
            }
            ?>
          </div>
        </div>
        <div class="person-admin">
          <?php
          if($vv_all) {
            $filter_cfg = array(
              'vv_id' => $vo_name . $i . "filterbox",
            );
            print $this->element('VoMember.filterTxtBox', $filter_cfg);
          }
          ?>
        </div>
        <span class="clearfix"></span>
      </div>
      <div class = "role-panel">
        <div class="roles-title"><?php print _txt('pl.vo_members.cert.abbreviation'); ?></div>
        <div class="roles">
          <?php
          foreach ($certs as $cert) {
            print '<div class = "role">';
            // Print Linked CO Person
            if(!empty($vv_cert_mlist) && !empty($vv_cert_mlist[ $cert["subject"] ]) ) {
              print '<div class = "rolestatus">';
              $plist = array();
              foreach($vv_cert_mlist[ $cert["subject"] ]['person'] as $person) {
                $found_role_key = false;
                if(!empty($vv_cert_mlist[ $cert["subject"] ]['roles'])) {
                  $roles_list = Hash::flatten($vv_cert_mlist[ $cert["subject"] ]['roles']);
                  $found_role_key = array_search($vo_name, $roles_list, true);
                }
                if ($found_role_key !== false) {
                  $t=1;
                }
                $plist[] = $this->Html->link(
                  $person['primary'] . "(" . $person['id'] . ")",
                  array('controller' => 'co_people',
                    'plugin' => null,
                    'action' => 'canvas',
                    $person['id']
                  ),
                  array('class' => ($found_role_key !== false) ? 'found' : 'notfound')
                );
              }
              print $this->Html->nestedList($plist);
              print '</div>';
            }
            print '<div class = "roleinfo">';
            print '<div class = "roletitle">';
            // Subject
            print _txt('pl.vo_members.subjectdn', array($cert['subject']));
            // Issuer
            if(!empty($cert['issuer'])) {
              print '<span class="roleTitleText" style="display:block;">';
              print _txt('pl.vo_members.issuerdn', array($cert['issuer']));
              print '</span>';
            }


            print '<span class="clearfix"></span>';
            print "</div>";  // roletitle
            print "</div>";  // roleinfo
            print "</div>";  // role
          }
          ?>
        </div>
      </div>
    </div>
    <?php $i++; ?>
  <?php endforeach; // $co_people ?>


  <?php
  if(empty($vv_voms_list)) {
    // No search results, or there are no people in this CO
    print('<div id="noResults">' . _txt('rs.search.none') . '</div>');
    print('<div id="restoreLink">');
    print('</div>');
  }
  ?>

  <?php print $this->element("pagination"); ?>
  <div class="clearfix"></div>

</div>